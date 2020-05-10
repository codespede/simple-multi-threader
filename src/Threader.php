<?php
namespace cs\simplemultithreader;
use Opis\Closure\SerializableClosure;
use function Opis\Closure\{serialize as s, unserialize as u};
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
        $basePath = $this->getAppBasePath();
    	if(!file_exists($basePath."/".$this->jobsDir))
    		mkdir($basePath."/".$this->jobsDir, 0777);
    	if(!file_exists($basePath."/".$this->logsDir))
    		mkdir($basePath."/".$this->logsDir, 0777);
    }

	public function thread($closure){
		$jobId = md5(uniqid(rand(), true));
        $jobsDir = $this->getAppBasePath()."/".$this->jobsDir;
		file_put_contents("{$jobsDir}/{$jobId}_closure.ser", serialize(new SerializableClosure($closure)));
        file_put_contents("{$jobsDir}/{$jobId}_arguments.ser", s($this->arguments));
        $command = "php ".__DIR__."/thread.php {$jobId} {$this->jobsDir} {$this->logsDir}";
        if(!self::isWindows() && $this->nohup)
        	$command = "nohup {$command} > /dev/null 2>&1 &";
        die($command);
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

    public function getAppBasePath(){
        return dirname(__DIR__, 4);
    }
}
