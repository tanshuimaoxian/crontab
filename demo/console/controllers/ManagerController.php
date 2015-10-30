<?php
namespace console\controllers;
use \tanshuimaoxian\crontab\Task;
use \tanshuimaoxian\crontab\System;

class ManagerController extends Task
{
    protected $taskName = "manager";
    protected $tasks = array();
    public function actionRun()
    {
        $mtask = 'manager/run';
        if (System::exists($mtask)) {
            exit();
        }

        $this->tasks = \Yii::$app->params['tasks'];
        if (empty($this->tasks)){
            exit();
        }
        while(true) {
            try {
                $this->check();
                sleep(1);
            } catch(Exception $e) {
                $this->log($e->getMessage());
            }  
        }  
    }

    public function actionRestart()
    {
        //关闭进程
        $this->actionStop();
        //启动进程
        $task = $this->taskName . '/run';
        while ($this->try < $this->tryLimit) {
            $this->try++;
            $status = System::startByRoot(CRONTAB_ROOT.'/yii', $task, 1);
            if ($status) {
                echo $msg = "start {$task}",PHP_EOL;
                $this->log($msg);
                break;
            }
        }
    }

    public function actionStop()
    {
        //关闭MANAGER进程
        $task = $this->taskName . '/run';
        $num = System::getProcessNum($task);
        if ($num > 0) {
          while (true) {
              if (System::stop($task)) {
                   echo $msg = "stop {$task}",PHP_EOL;
                   $this->log($msg);
                  break;
              }
              sleep(5);
          }
        }

        //关闭WORKER进程
        $this->tasks = \Yii::$app->params['tasks'];
        if (empty($this->tasks)){
           exit();
        }
        
        foreach ($this->tasks as $item) {
            $task = $item[0];
            $num = System::getProcessNum($task);
            if ($num < 1) {
                continue;
            }
            echo $msg = "stop {$task} ...";
            $this->log($msg,'');
            $this->sendSign($task, 'stop');
            //等待进程关闭
            do {
                echo $msg = ".";
                $this->log($msg,'');
                usleep(500);
                $num = System::getProcessNum($task);
            } while ($num>0);
            $this->sendSign($task, '');
            echo $msg = "ok\n";
            $this->log($msg,'');
        }
        echo $msg = "done",PHP_EOL;
        $this->log($msg);
    }

    protected function check() 
    {
        foreach ($this->tasks as $key => $item) {
            $task = $item[0];
            $num = $item[1];
            $runType = $item[2];
            $state = $item[3];
            if (!$state || ($runType && !IS_MASTER)) {
                unset($this->tasks[$key]);
                continue;
            }
            $current = System::getProcessNum($task);
            $num-= $current;
            if ($num > 0) {
                $msg = sprintf("[%s] %s ", date('Y-m-d H:i:s'), "start {$task}");
                if (System::start(CRONTAB_ROOT.'/yii', $task, $num, '/dev/null', '')) {
                    $this->log("start {$msg} ok");
                } else {
                    $this->log("start {$msg} fail");
                }
            }
        }
    }
}