<?php

namespace tanshuimaoxian\crontab;
class System
{
    public static function getCpu() 
    {
        if (file_exists('/proc/cpuinfo')) {
            $info = shell_exec("cat /proc/cpuinfo");
            return substr_count($info, "processor");
        }
        return 1;
    }
    
    public static function getProcessId($script) 
    {
        exec("ps -ef | grep '{$script}' | grep -v grep | awk '{print $2}'", $output);
        return $output;
    }
    
    public static function getProcessInfo($script) 
    {
        exec("ps -ef | grep '{$script}' | grep -v grep | awk '{print $9}'", $output);
        return $output;
    }
    
    public static function getProcessNum($script) 
    {
        return shell_exec ( "ps -ef | grep '{$script}' | grep -v grep | awk '{count++}END{print count}'");
    }
    
    public static function stop($script) 
    {
        $ids = self::getProcessId($script);
        $status = true;
        if ($ids && is_array($ids)) {
            // echo "kill: {$script}...";
            foreach($ids as $id){
                if (self::stopByid($id)) {
                    // echo "ok";
                } else {
                    $status = false;
                    // echo "fail";
                    break;
                }
            }
            // echo "\n";
        }
        return $status;
    }
    
    public static function stopByid($id) 
    {        
        exec("kill -9 $id", $output, $status);
        return $status === 0 ? true : false;
    }

    public static function start($bin, $script, $num = 1, $log = '/dev/null', $sudo = 'sudo -u nobody ') 
    {
        $current = self::getProcessNum($script);
        $num -= $current;
        if ($num > 0) {
            $command = "{$sudo} {$bin} {$script} >> $log &";
            for($i = 0; $i<$num; $i++) {
                exec($command, $output, $status);
                // echo "{$command}...{$status}\n";
                if ($status !== 0) {
                    return false;
                }
            }
        }
        return true;
    }

    public static function startByRoot($bin, $script, $num = 1, $log = '/dev/null') 
    {
        return self::start($bin, $script, $num, $log, '');
    }
    
    public static function exists($script) 
    {
        return self::getProcessNum($script) > 1;
    }

    public static function getServerIp() 
    {
        exec( "/sbin/ifconfig | awk '/inet addr/{print $2}' | awk -F: '{print $2}'", $output);
        return $output;
    }
}
