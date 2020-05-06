<?php
namespace cs\simplemultithreader;
use \SuperClosure\Serializer;
class Threader{
	public $arguments;
	public $jobsDir = "smt-jobs";
	public $logsDir = "smt-logs";
	public $nohup = true;
	
	public function __construct($config = []){
        if (!empty($config)) {
            self::configure($this, $config);
        }
        $this->init();
    }

    public function init(){
    	if(!file_exists($this->jobsDir))
    		mkdir($this->jobsDir, 0777);
    	if(!file_exists($this->logsDir))
    		mkdir($this->logsDir, 0777);
    }

	public function thread($closure){
        //dd(getcwd());
		$serializer = new Serializer;
		$jobId = md5(uniqid(rand(), true));

		file_put_contents("{$this->jobsDir}/{$jobId}_closure.ser", $serializer->serialize($closure));
        file_put_contents("{$this->jobsDir}/{$jobId}_arguments.ser", serialize($this->arguments));
        $command = "php ".__DIR__."/thread.php {$jobId} {$this->jobsDir} {$this->logsDir}";
        if(!self::isWindows() && $this->nohup)
        	$command = "nohup {$command} > /dev/null 2>&1 &";
        dd($command);
		return shell_exec($command);
	}

	public static function isWindows(){
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	public static function configure($object, $properties){
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }
        return $object;
    }
}
