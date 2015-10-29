<?php

namespace tanshuimaoxian\crontab;
class Task extends \yii\console\Controller
{
	protected $try = 0;
	protected $tryLimit = 10;
	protected $date;
    public function actionRun()
    {
        echo 'run' . PHP_EOL;
    }

    public function actionStop()
    {
    	$cls = get_called_class();
    	$parts = explode('\\', strtolower($cls));
    	$taskName = substr(array_pop($parts),0,-10);
		$task = $taskName.'/run';
		$num = System::getProcessNum($task);
		if ($num > 0) {
			$this->log("stop {$task}");
			System::stop($task);
		}
    }

    public function actionRestart()
    {
    	$this->actionStop();
        $this->actionRun();
    }

    protected function sleep($cd)
    {
    	if (empty($this->date)) $this->date = date('Ymd');
    	if ($this->date != date('Ymd')) {
    		exit();
    	} 
    	sleep($cd);
    }

    protected function log($msg) 
    {
    	$time = date('Y-m-d H:i:s');
    	$day = date('Ymd');
    	$log = CRONTAB_LOG . get_called_class() . $day.'.log';
    	$msg = sprintf("[%s] %s", $time, $msg);
    	file_put_contents($log, $msg . PHP_EOL, FILE_APPEND);
    }
}
