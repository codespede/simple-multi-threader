<?php
namespace cs\simplemultithreader;
class CommandHelper
{
	public function bootstrap(){
		if(is_null($framework = $this->getFramework()))
			return;
		$this->{"execute".ucfirst($framework)."Bootstrap"}();
	}

	public function executeLaravelBootstrap(){
		$app = require_once $this->getAppBasePath().'/bootstrap/app.php';
		$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
		try{
		$status = $kernel->handle(
		    $input = new \Symfony\Component\Console\Input\ArgvInput,
		    new \Symfony\Component\Console\Output\ConsoleOutput
		);
		}catch(\Exception $e){}
	}

	public function executeYiiBootStrap(){
		$basePath = $this->getAppBasePath();
		require($basePath . '/vendor/autoload.php');
		require($basePath . '/vendor/yiisoft/yii2/Yii.php');
		require($basePath . '/common/config/bootstrap.php');
		$config = array_merge(
		    require($basePath . '/common/config/main.php'),
		    require($basePath . '/common/config/main-local.php')
		);
		$application = new \yii\console\Application($config);
		try{
			$application->run();
		}catch(\Exception $e){}
	}

	public function getAppBasePath(){
		return dirname(__DIR__, 4);
	}

	public function getFramework(){
		return file_exists($this->getAppBasePath()."/artisan")? "laravel" : (file_exists($this->getAppBasePath()."/yii")? "yii" : null);
	}

	public static function getExceptionTraceAsString($exception) {
	    $rtn = "";
	    $count = 0;
	    foreach ($exception->getTrace() as $frame) {
	        $args = "";
	        if (isset($frame['args'])) {
	            $args = array();
	            foreach ($frame['args'] as $arg) {
	                if (is_string($arg)) {
	                    $args[] = "'" . $arg . "'";
	                } elseif (is_array($arg)) {
	                    $args[] = "Array";
	                } elseif (is_null($arg)) {
	                    $args[] = 'NULL';
	                } elseif (is_bool($arg)) {
	                    $args[] = ($arg) ? "true" : "false";
	                } elseif (is_object($arg)) {
	                    $args[] = get_class($arg);
	                } elseif (is_resource($arg)) {
	                    $args[] = get_resource_type($arg);
	                } else {
	                    $args[] = $arg;
	                }
	            }
	            $args = join(", ", $args);
	        }
	        $current_file = "[internal function]";
	        if(isset($frame['file']))
	        {
	            $current_file = $frame['file'];
	        }
	        $current_line = "";
	        if(isset($frame['line']))
	        {
	            $current_line = $frame['line'];
	        }
	        $rtn .= sprintf( "#%s %s(%s): %s(%s)\n",
	            $count,
	            $current_file,
	            $current_line,
	            $frame['function'],
	            $args );
	        $count++;
	    }
	    return $rtn;
	}
}
