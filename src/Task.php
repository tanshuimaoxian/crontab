<?php

namespace tanshuimaoxian\crontab;
class Task extends \yii\console\Controller
{
    protected $try = 0;
    protected $tryLimit = 10;
    protected $date;
    protected $taskName = "task";
    public function actionRun()
    {
        echo 'run' . PHP_EOL;
    }

    public function actionStop()
    {
        $task = $this->taskName.'/run';
        $num = System::getProcessNum($task);
        if ($num > 0) {
            echo "stop {$task} ...";
            // System::stop($task);
            $this->sendSign($task);
            //等待进程关闭
            do {
                echo ".";
                sleep(1);
                $num = System::getProcessNum($task);
            } while ($num>0);
            echo "ok\n";
        }
        $this->sendSign($task, '');
    }

    public function actionRestart()
    {
        $task = $this->taskName.'/run';
        $num = System::getProcessNum($task);

        //关闭进程
        $this->actionStop();
        //启动进程
        echo "start {$task}\n";
        if ($num<1) $num = 1;
        System::start(CRONTAB_ROOT.'/yii', $task, $num, '/dev/null', '');
    }

    public function sendSign($task, $sign='stop')
    {
        \Yii::$app->redis->hset('CRONTAB|SIGN', $task, $sign);
    }

    public function fetchSign($task)
    {
        return \Yii::$app->redis->hget('CRONTAB|SIGN', $task);
    }

    protected function sleep($cd)
    {
        //接收关闭进程信号
        $task = $this->taskName.'/run';
        $sign = $this->fetchSign($task);
        if ($sign == 'stop') {
            exit();
        }
        //隔天自动重启
        if (empty($this->date)) $this->date = date('Ymd');
        if ($this->date != date('Ymd')) {
            exit();
        }
        sleep($cd);
    }

    protected function log($msg, $tail = "\n") 
    {
        $time = date('Y-m-d H:i:s');
        $day = date('Ymd');
        $log = CRONTAB_LOG . get_called_class() . $day.'.log';
        if (!empty($tail)) $msg = sprintf("[%s] %s", $time, $msg.$tail);
        file_put_contents($log, $msg, FILE_APPEND);
    }
}
