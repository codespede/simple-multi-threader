<?php
use SuperClosure\Serializer;
require('vendor/autoload.php');
$jobId = $argv[1];
$jobsDir = $argv[2];
if(!file_exists("{$jobsDir}/{$jobId}_closure.ser"))
	die("Closure file for Job ID: $jobId doesn't exist");
if(!file_exists("{$jobsDir}/{$jobId}_arguments.ser"))
	die("Arguments file for Job ID: $jobId doesn't exist");
$serializer = new Serializer;
$closure = $serializer->unserialize(file_get_contents("{$jobsDir}/{$jobId}_closure.ser"));
$arguments = unserialize(file_get_contents("{$jobsDir}/{$jobId}_arguments.ser"));
file_put_contents("smt_{$jobId}.log", $closure($arguments));
exit;