<?php
use SuperClosure\Serializer;
require_once(__DIR__."/CommandHelper.php");
$helper = new \cs\simplemultithreader\CommandHelper;
require_once($helper->getAppBasePath().'/vendor/autoload.php');
$jobId = $argv[1];
$jobsDir = $argv[2];
$logsDir = $argv[3];
if(!file_exists("{$jobsDir}/{$jobId}_closure.ser"))
	die("Closure file for Job ID: $jobId doesn't exist");
if(!file_exists("{$jobsDir}/{$jobId}_arguments.ser"))
	die("Arguments file for Job ID: $jobId doesn't exist");
$serializer = new Serializer;
try{
	$helper->bootstrap();
	$closure = $serializer->unserialize(file_get_contents("{$jobsDir}/{$jobId}_closure.ser"));
	$arguments = unserialize(file_get_contents("{$jobsDir}/{$jobId}_arguments.ser"));
	file_put_contents("{$logsDir}/smt_{$jobId}.log", $closure($arguments));
}catch(\Exception $e){
	file_put_contents("{$logsDir}/smt_{$jobId}_error.log", "Caught Exception at ".date('Y-m-d H:i:s').": ".$e->getMessage()." at line: ".$e->getLine()." on file: ".$e->getFile().". Stack trace: ".$helper::getExceptionTraceAsString($e));
}
//garbage collection..
unlink("{$jobsDir}/{$jobId}_closure.ser");
unlink("{$jobsDir}/{$jobId}_arguments.ser");
exit;