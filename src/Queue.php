<?php

namespace tanshuimaoxian\crontab;
class Queue
{
    protected static $waittime = 10;
    protected static $queues = array();
    protected static $callBacks = array();
    protected static $prefix = 'queue|';
    protected static $group = 'queue';
    protected static $redis;

    public static function setRedis($rds) 
    {
        self::$redis = $rds;
        return true;
    }

    public static function reg($queue, $callBack=null, $ts=0) 
    {
        if(is_array($callBack) && method_exists($callBack[0], $callBack[1])) {
            self::$queues[self::$prefix.$queue] = $ts;
            self::$callBacks[self::$prefix.$queue] = $callBack;
        }
    }
    
    public static function publish($queue, $msg='')
    {
        return self::$redis->publish(self::$prefix.$queue, $msg);
    }
    
    public static function subscribe()
    {
        self::$redis->subscribe(array_keys(self::$queues), array( __CLASS__, 'dispatch'));
    }
    
    public static function dispatch($redis, $queue, $msg)
    {
        if(isset(self::$callBacks[self::$prefix.$queue])){
            call_user_func_array(self::$callBacks[self::$prefix.$queue], array($msg));
        }
    }

    public static function waittime($waittime)
    {
        self::$waittime = $waittime;
    }
    
    public static function send($queue, $msg='')
    {
        return self::$redis->lPush(self::$prefix.$queue, $msg);
    }

    public static function presend($queue, $msg='')
    {
        self::$redis->push("lPush", array(self::$prefix.$queue, $msg));
        return true;
    }

    public static function receive($queue, $waittime=0)
    {
        $return = self::$redis->brPop(array(self::$prefix.$queue), $waittime);
        return empty($return) ? false : $return[1];
    }

    public static function receiveByRpop($queue)
    {
        $return = self::$redis->rPop(self::$prefix.$queue);
        return empty($return) ? false : $return;
    }

    public static function receiveAll()
    {
        $force = array();
        foreach(self::$queues as $queue => $cd){
            if($cd > 0) $force[$queue] = 0;
        }
        $queues = array_keys(self::$queues);
        $st = microtime(true);

        while(true) {
            $return = self::$redis->brPop($queues, self::$waittime);
            if(empty($return)){
                if(!empty($force)){
                    $now = microtime(true);
                    $cost = $now - $st;
                    $st = $now;
                    
                    foreach($force as $queue => $cd){
                        $cd += $cost;
                        $force[$queue] = $cd;
                        echo "{$queue} now wait time {$cd}\r\n";
                        if($cd >= self::$queues[$queue]){
                            $force[$queue] = 0;
                            echo "force call func {$queue}\r\n";
                            call_user_func_array(self::$callBacks[$queue], array());
                        }
                    }
                }
            } else {
                $queue = $return[0];
                $data = $return[1];
                if(isset($force[$queue])) $force[$queue] = 0;
                echo 'receive ' . $queue . ' time=' . date('Y-m-d H:i:s') . "\n";
                $st = microtime(true);
                call_user_func_array(self::$callBacks[$queue], array($data));
                echo 'cost=' . (microtime(true)-$st) . "\n";
            }
        }
    }

}
