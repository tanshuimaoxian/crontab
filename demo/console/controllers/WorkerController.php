<?php
namespace console\controllers;
use \tanshuimaoxian\crontab\Task;
use \tanshuimaoxian\crontab\System;

class WorkerController extends Task
{
	protected $taskName = "worker";
	public $server;
    public function actionRun()
  	{
  		$this->server = \Yii::$app->queue;
  		while (true) {
  			$this->server->send('testqueue', [
  				'job' => ['app\models\Channel@test'],
  				'data' => date('Y-m-d H:i:s'),
  				]);
        
  			$this->checkSign();
        sleep(5);
  		}
  	}
}