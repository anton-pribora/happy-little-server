<?php

$config = require __DIR__ .'/logposter-config.php';
$poster = new LogPoster();

$poster->setErrorCallback(function ($error) {
    echo $error, PHP_EOL;
});

$poster->setMessageCallback(function ($message) {
    echo $message, PHP_EOL;
});

$poster->run($config);

class LogPoster
{
    private $onError;
    private $onMessage;
    
    public function run($config)
    {
        foreach ($config as $endpoint) {
            $this->sendLogs($endpoint);
        }
    }
    
    public function sendLogs($endpoint)
    {
        $message = new Message();
        $storage = new Storage($endpoint['storage']);
        $limit   = isset($endpoint['limit']) ? $endpoint['limit'] : 30;
        
        foreach ($this->files($endpoint) as $file) {
            $fileData = new FileData($file, $storage);
            
            if ($fileData->hasWrongSize()) {
                $storage->remove($file);
                $storage->save();
            }
            
            if ($fileData->hasNewData()) {
                $message->add($file);
                $message->add('---------------------');
                $message->add($fileData->getNewData($limit));
            }
        }
        
        if ($message->hasData()) {
            if (is_callable($this->onMessage)) {
                call_user_func($this->onMessage, $message);
            }
            
            if ($this->post($endpoint, $message)) {
                $storage->save();
                return true;
            } else {
                return false;
            }
        }
        
        return true;
    }
    
    public function setErrorCallback(callable $function)
    {
        $this->onError = $function;
    }
    
    public function setMessageCallback(callable $function)
    {
        $this->onMessage = $function;
    }
    
    private function files($endpoint)
    {
        foreach ($endpoint['logs'] as $pattern) {
            foreach (glob($pattern) as $file) {
                yield $file;
            }
        }
    }
    
    private function error($errorText)
    {
        if (is_callable($this->onError)) {
            call_user_func($this->onError, $errorText);
        }
    }
    
    private function post($endpoint, $message)
    {
        $params = array_merge($endpoint['params'], ['data' => (string) $message]);
        
        $context = stream_context_create([
            'http' => [
                'method'          => 'POST',
                'follow_location' => 0,
                'ignore_errors'   => true,
                'header'          => 'Content-type: application/x-www-form-urlencoded',
                'content'         => http_build_query($params),
            ],
        ]);
        
        $fp = @fopen($endpoint['url'], 'r', false, $context);
        
        if ($fp) {
            $meta = stream_get_meta_data($fp);
            
            $code    = 'unknown';
            $text    = 'none';
            $content = '';
            
            if (isset($meta['wrapper_data'][0])) {
                if (preg_match('~\b(?P<code>\d{3})\b(?P<text>.*)$~', $meta['wrapper_data'][0], $matches)) {
                    $code = $matches['code'];
                    $text = trim($matches['text']);
                }
            } else {
                // Это был не web-адрес...
                $text = 'Bad HTTP url: '. $endpoint['url'];
            }
            
            $content = stream_get_contents($fp);
            
            fclose($fp);
            
            if ($code == '200') {
                // Всё хорошо
                return true;
            } else {
                // Произошла HTTP-ошибка
                $this->error(sprintf('HTTP error %s: %s. URL: %s', $code, $text, $endpoint['url']));
                $this->error($content);
            }
        } else {
            // Произошла PHP-ошибка
            $this->error(sprintf('Error: %s', error_get_last()['message']));
        }
        
        return false;
    }
}

class Message
{
    private $data = [];
    
    function add($data)
    {
        $this->data[] = $data;
    }
    
    function hasData()
    {
        return count($this->data) > 0;
    }
    
    function __toString()
    {
        return join("\n", $this->data);
    }
}

class Storage
{
    private $path;
    private $data = [];
    
    public function __construct($pathToJson)
    {
        $this->path = $pathToJson;
        $this->load();
    }
    
    private function load()
    {
        if (file_exists($this->path)) {
            $this->data = json_decode(file_get_contents($this->path), true);
        }
    }
    
    public function save()
    {
        return file_put_contents($this->path, json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
    
    public function set($file, $key, $value)
    {
        $this->data[$file][$key] = $value;
    }
    
    public function get($file, $key)
    {
        return isset($this->data[$file][$key]) ? $this->data[$file][$key] : null;
    }
    
    public function remove($file)
    {
        unset($this->data[$file]);
    }
}

class FileData
{
    private $storage;
    private $file;
    
    public function __construct($file, Storage $storage)
    {
        $this->file    = $file;
        $this->storage = $storage;
    }
    
    public function hasWrongSize()
    {
        $currentSize = filesize($this->file);
        $lastSize    = $this->storage->get($this->file, 'size');
        
        return $lastSize > $currentSize;
    }
    
    public function hasNewData()
    {
        $currentSize = filesize($this->file);
        $lastSize    = $this->storage->get($this->file, 'size');
        
        if ($lastSize > $currentSize) {
            $this->storage->set($this->file, 'size', 0);
            $lastSize = 0;
        }
        
        return $currentSize > $lastSize;
    }
    
    public function getNewData($limit)
    {
        $fp = fopen($this->file, 'r');
        
        $lastSize = $this->storage->get($this->file, 'size');
        
        if ($lastSize > 0) {
            fseek($fp, $lastSize, SEEK_SET);
        }
        
        $i         = 0;
        $data      = [];
        $moreLines = 0;
        $moreBytes = 0;
        
        while (!feof($fp)) {
            $line      = fgets($fp);
            $lastSize += strlen($line);
            
            if (++$i <= $limit) {
                $data[] = $line;
            } else {
                $moreLines += 1;
                $moreBytes += strlen($line);
            }
        }
        
        fclose($fp);
        
        if ($moreLines) {
            $data[] = sprintf("... And %s lines (%s bytes) more ...\n", 
                number_format($moreLines), 
                number_format($moreBytes)
            );
        }
        
        $this->storage->set($this->file, 'size', $lastSize);
        $this->storage->set($this->file, 'time', date('r'));
        
        return join($data);
    }
}
