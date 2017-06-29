<?php

$config = include __DIR__ .'/config.php';

$app = new Backuper();
$app->addPlugin('folder', new PluginFolder());
$app->addPlugin('mysql', new PluginMysql());

$app->run($config);

class Backuper
{
    private $root = __DIR__;
    private $pugins = [];
    
    function addPlugin($name, $object) {
        $this->pugins[$name] = $object;
    }
    
    function run($config) {
        if (isset($config['backup_root'])) {
            $this->root = $config['backup_root'];
        }
        
        foreach ($config['hosts'] as $name => $host) {
            echo "$name:\n";
            foreach ($host as $plugin => $pluginConf) {
                $this->pugins[$plugin]->run($pluginConf, ['backup_root' => $this->root .'/'. $name]);
            }
        }
    }
}

class PluginFolder
{
    function run($conf, $options) {
        foreach ($conf as $name => $folder) {
            $command = ['rsync --perms --times --delete -og --recursive'];
            
            if (is_string($folder)) { $path = $folder; $folder = []; }
            if (isset($folder[0])) { $path = $folder[0]; }
            
            if (isset($folder['exclude'])) {
                foreach ((array)$folder['exclude'] as $exclude) {
                    $command[] = '--exclude='. escapeshellarg($exclude);
                }
            }
            
            $dir = "{$options['backup_root']}/$name/daily";
            
            if (!is_dir($dir)) {
                array_unshift($command, 'mkdir -p '. escapeshellarg($dir) .';');
            }
            
            $command[] = escapeshellarg(rtrim($path, '/') .'/');
            $command[] = escapeshellarg($dir);
            
            $exec = join(' ', $command);
            
            echo "   ", $exec, "\n";
            system($exec);
        }
    }
}

class PluginMysql
{
    function run($conf, $options) {
        foreach ($conf as $name => $db) {
            $command = ['mysqldump -R'];
            
            if (!empty($db['user'    ])) { $command[] = '-u'. escapeshellarg($db['user']); }
            if (!empty($db['password'])) { $command[] = '-p'. escapeshellarg($db['password']); }
            if (!empty($db['host'    ])) { $command[] = '-h'. escapeshellarg($db['host']); }
            if (!empty($db['dbname'  ])) { $command[] = escapeshellarg($db['dbname']); }
            
            $dir = "{$options['backup_root']}/$name";
            
            if (!is_dir($dir)) {
                array_unshift($command, 'mkdir -p '. escapeshellarg($dir) .';');
            }
            
            $dump = "$dir/". strftime("%Y-%m-%d_%H-%M-%S.$name.sql");
            
            $command[] = "> $dump";
            
            $exec = join(' ', $command);
            echo "   ", $exec, "\n";
            system($exec);
            
            if (!empty($db['compress'])) {
                $compressor = trim(`which bzip2`) ?: trim(`which gzip`);
                
                if ($compressor) {
                    $command = escapeshellcmd($compressor) .' -f '. escapeshellarg($dump);
                    echo "   ", $command, "\n";
                    system($command);
                } else {
                    echo "no compressor\n";
                }
            }
            
            if (!empty($db['dumps_limit'])) {
                $dumps = glob("$dir/*.$name.sql*");
                
                if (count($dumps) > $db['dumps_limit']) {
                    $remove = array_splice($dumps, 0, -$db['dumps_limit']);
                    
                    foreach ($remove as $file) {
                        $command = sprintf('rm %s', escapeshellarg($file));
                        echo "   ", $command, "\n";
                        system($command);
                    }
                }
            }
        }
    }
}