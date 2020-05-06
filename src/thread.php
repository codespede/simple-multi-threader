<?php
function getExceptionTraceAsString($exception) {
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
use SuperClosure\Serializer;
require('../vendor/autoload.php');
$jobId = $argv[1];
$jobsDir = $argv[2];
$logsDir = $argv[3];
if(!file_exists("{$jobsDir}/{$jobId}_closure.ser"))
    die("Closure file for Job ID: $jobId doesn't exist");
if(!file_exists("{$jobsDir}/{$jobId}_arguments.ser"))
    die("Arguments file for Job ID: $jobId doesn't exist");
$serializer = new Serializer;
try{
    $closure = $serializer->unserialize(file_get_contents("{$jobsDir}/{$jobId}_closure.ser"));
    $arguments = unserialize(file_get_contents("{$jobsDir}/{$jobId}_arguments.ser"));
    $app = require_once '../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $status = $kernel->handle(
        $input = new Symfony\Component\Console\Input\ArgvInput,
        new Symfony\Component\Console\Output\ConsoleOutput
    );
    file_put_contents("{$logsDir}/smt_{$jobId}.log", $closure($arguments));
}catch(\Exception $e){
    file_put_contents("{$logsDir}/smt_{$jobId}_error.log", "Caught Exception at ".date('Y-m-d H:i:s').": ".$e->getMessage()." at line: ".$e->getLine()." on file: ".$e->getFile().". Stack trace: ".getExceptionTraceAsString($e));
}
//garbage collection..
//unlink("{$jobsDir}/{$jobId}_closure.ser");
//unlink("{$jobsDir}/{$jobId}_arguments.ser");
exit;