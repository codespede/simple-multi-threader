<?php
/**
 * @package   simple-multi-threader
 * @author    Mahesh S Warrier <maheshs60@gmail.com>
 * @copyright Copyright &copy; Mahesh S Warrier, 2020
 * @version   1.0.0
 */
namespace cs\simplemultithreader;
use Opis\Closure\SerializableClosure;
use function Opis\Closure\{serialize as s, unserialize as u};

/**
 * Class Threader
 * @package codespede\simple-multi-threader
 */
class Threader{

    /**
     * @var Arguments for the closure
     */
	public $arguments;

    /**
     * @var Directory where jobs will be saved
     */
	public $jobsDir = "smt-jobs";

    /**
     * @var Directory where logs will be saved
     */
	public $logsDir = "smt-logs";

    /**
     * @var Whether to ignore the HUP (hangup) signal in unix based systems
     */
	public $nohup = true;

    /**
     * @var Fully qualified class name of the Helper to be used
     */
    public $helperClass = "cs\\simplemultithreader\\CommandHelper";

    /**
     * Threader constructor.
     * @param array $config
     */
	public function __construct($config = []){
        if (!empty($config)) {
            self::configure($this, $config);
        }
        $this->init();
    }

    /**
     * Threader initializer.
     */
    public function init(){
        $basePath = $this->getAppBasePath();
        if(!file_exists($basePath."/".$this->jobsDir))
            mkdir($basePath."/".$this->jobsDir, 0777);
        if(!file_exists($basePath."/".$this->logsDir))
            mkdir($basePath."/".$this->logsDir, 0777);
    }

    /**
     * Execute the given closure in a separate process.
     * @param Closure $closure
     * @return string
     */
    public function thread($closure){
        $jobId = md5(uniqid(rand(), true));
        $jobsDir = $this->getAppBasePath()."/".$this->jobsDir;
        file_put_contents("{$jobsDir}/{$jobId}_closure.ser", serialize(new SerializableClosure($closure)));
        file_put_contents("{$jobsDir}/{$jobId}_arguments.ser", s($this->arguments));
        $command = "php ".str_replace('\\', '/', __DIR__)."/thread.php {$jobId} {$this->jobsDir} {$this->logsDir} {$this->helperClass}";
        if(!self::isWindows()){
            $command = ($this->nohup? "nohup " : "") . "{$command} > /dev/null 2>&1 &";
            shell_exec($command);
        }
        elseif(self::isWindows()){
            $WshShell = new \COM("WScript.Shell");
            $WshShell->Run($command, 0, false);
        }
        return $jobId;
    }

    /**
     * Check whether the current environement is Windows or not.
     */
	public static function isWindows(){
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

    /**
     * Configure the threader object with given properties.
     * @param Threader $object
     * @param array $properties
     * @return Threader
     */
	public static function configure($object, $properties){
        foreach ($properties as $name => $value) {
            $object->$name = $value;
        }
        return $object;
    }

    /**
     * Get the base path of the application
     * @return string
     */
    public function getAppBasePath(){
        return dirname(__DIR__, 4);
    }
}
