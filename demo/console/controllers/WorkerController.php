<?php
namespace console\controllers;
use \tanshuimaoxian\crontab\Task;
use \tanshuimaoxian\crontab\System;

class WorkerController extends Task
{
	protected $taskName = "worker";
    public function actionRun()
  	{
  		while (true) {
  			$this->sleep(1);
  		}
  	}
}