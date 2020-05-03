<?php
namespace cs\simplemultithreader;
use \SuperClosure\Serializer;
class Threader{
	public $arguments;
	public $jobsDir = "smt-jobs";
	public $logPath = "smt-logs";
	public $nohup = true;
	
	public function thread($closure){
		$serializer = new Serializer;
		$jobId = md5(uniqid(rand(), true));
		file_put_contents("{$this->jobsDir}/{$jobId}_closure.ser", $serializer->serialize($closure));
        file_put_contents("{$this->jobsDir}/{$jobId}_arguments.ser", serialize($this->arguments));
        $command = "php ".__DIR__."/thread.php {$jobId} {$this->jobsDir}";
        if(!self::isWindows() && $this->nohup)
        	$command = "nohup {$command} > /dev/null 2>&1 &";
		return shell_exec($command);
	}

	public static function isWindows(){
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}
}
