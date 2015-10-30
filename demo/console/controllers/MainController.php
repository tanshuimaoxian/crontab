<?php
namespace console\controllers;
use \tanshuimaoxian\crontab\Task;
use \tanshuimaoxian\crontab\System;

class MainController extends Task
{
  protected $taskName = "main";
  public function actionRun()
  {
      echo "start run..\n";
      $task = 'manager/run';
      $num = System::getProcessNum($task);
      if ($num > 0) {
         return true;
      }
      while ($this->try < $this->tryLimit) {
        $this->try++;
        $status = System::startByRoot(CRONTAB_ROOT.'/yii', $task, 1);
        if ($status) {
            echo "start {$task}\n";
            break;
        }
      }
      echo "run..\n";
  }

  public function actionStop()
  {
      echo "start stop..\n";
      $task = 'manager/stop';
      $num = System::getProcessNum($task);
      if ($num > 0) {
         return true;
      }
      while ($this->try < $this->tryLimit) {
        $this->try++;
        $status = System::startByRoot(CRONTAB_ROOT.'/yii', $task, 1);
        if ($status) {
            echo "start {$task}\n";
            break;
        }
      }
      echo "stopped..\n";
  }

  public function actionRestart()
  {
      echo "start restart..\n";
      $task = 'manager/restart';
      $num = System::getProcessNum($task);
      if ($num > 0) {
         return true;
      }
      while ($this->try < $this->tryLimit) {
        $this->try++;
        $status = System::startByRoot(CRONTAB_ROOT.'/yii', $task, 1);
        if ($status) {
            echo "restart {$task}\n";
            break;
        }
      }
      echo "restarted..\n";
  }
}