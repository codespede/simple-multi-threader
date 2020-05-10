<?php
use Opis\Closure\SerializableClosure;
use function Opis\Closure\{serialize as s, unserialize as u};
require_once(dirname(__DIR__, 4).'/vendor/autoload.php');
$jobId = $argv[1];
$jobsDir = $argv[2];
$logsDir = $argv[3];
$helperClass = $argv[4];
$helper = new $helperClass;
$basePath = $helper->getAppBasePath();
if(!file_exists("{$basePath}/{$jobsDir}/{$jobId}_closure.ser"))
	die("Closure file for Job ID: $jobId doesn't exist");
if(!file_exists("{$basePath}/{$jobsDir}/{$jobId}_arguments.ser"))
	die("Arguments file for Job ID: $jobId doesn't exist");
try{
	$helper->bootstrap();
	$wrapper = unserialize(file_get_contents("{$basePath}/{$jobsDir}/{$jobId}_closure.ser"));
	$arguments = u(file_get_contents("{$basePath}/{$jobsDir}/{$jobId}_arguments.ser"));
	file_put_contents("{$basePath}/{$logsDir}/smt_{$jobId}.log", $wrapper($arguments));
}catch(\Exception $e){
	file_put_contents("{$basePath}/{$logsDir}/smt_{$jobId}_error.log", "Caught Exception at ".date('Y-m-d H:i:s').": ".$e->getMessage()." at line: ".$e->getLine()." on file: ".$e->getFile().". Stack trace: ".$helper::getExceptionTraceAsString($e));
}
//garbage collection..
unlink("{$basePath}/{$jobsDir}/{$jobId}_closure.ser");
unlink("{$basePath}/{$jobsDir}/{$jobId}_arguments.ser");
exit;