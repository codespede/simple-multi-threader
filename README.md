Simple Multi Threader
===========================

A simple PHP Multithreader extension which is ready for use without any setups or configurations.

- No need to integrate any Queues or similar implementations
- Write the code you want to process in the background in a closure(anonymous function) along with arguments(if any) and provide it to the extension. That's all.
- Works with Core PHP(Normal PHP), Laravel and Yii2 out of the box.
- Can work with any PHP Framework/Platform by just adding the required bootstrap code(See [here](https://github.com/codespede/simple-multi-threader#making-it-compatible-with-the-platform-you-use)).
- Compatible with PHP installations in Windows and \*nix(Unix like)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require codespede/simple-multi-threader
```

or add

```json
"codespede/simple-multi-threader": "*"
```

to the `require` section of your composer.json.

How to use
----------

If you want to run some code in the background, just provide it in a closure(anonymous function) to an object of the class `Threader` as below:

```
$threader = new cs\simplemultithreader\Threader(['arguments' => ['myObject' => new \stdClass, 'myArray' => ['a', 'b', 'c']]]);
$jobId = $threader->thread(function($arguments){
	//your code here..
 	//you can access any of the above given $arguments here like $arguments['myObject'] or $arguments['myArray']
});

```
That's all, the Threader will create the required job files in the specified `$jobsDir` and will start a PHP process in the background executing your code. The main thread(code above and after `$threader->thread...`) will run without waiting for the sub-thread(code in the closure) to finish. You will also get the started job's id(an md5 string) as the return value of the method `thread` which you can use for [debugging](https://github.com/codespede/simple-multi-threader#debugging).

Any data returned from the closure function will be logged to a file with name `smt_<jobId>.log` in the default directory specified for logs: `smt-logs`.

For examples, [click here](https://github.com/codespede/simple-multi-threader/blob/master/EXAMPLES.md)

Configurable Options
--------------------
 `$arguments` - Arguments for the closure - type: mixed, default = null
 
 `$jobsDir` - Directory(auto-created if not existing) where temporary job files will be stored - type: string, default = `"smt-jobs"`
 
 `$logsDir` - Directory(auto-created if not existing) where log files will be stored - type: string, default = `"smt-logs"`
 
 `$nohup` - Whether to ignore the HUP (hangup) signal in Unix based systems - type: boolean, default = `true`
 
 `$helperClass` - Fully qualified class name of the Helper to be used. - type: string, default =  `"cs\\simplemultithreader\\CommandHelper"`

Any option above can be used to configure the Threader in the below way:
```
$threader = new cs\simplemultithreader\Threader(['jobsDir' => 'MyJobsDir', 'helperClass' => '\namespace\of\MyHelperClass']);
```
or like:
```
$threader = new cs\simplemultithreader\Threader;
$threader->arguments = ['myObject' => new \stdClass, 'myArray' => ['a', 'b', 'c']];
$threader->jobsDir = 'MyJobsDir';
...
```

Making it compatible with the platform you use
----------------------------------------------
Even if your application is not built either on Core PHP, Laravel or Yii2 which are already supported, you can make it compatible with this extension by the below steps:

There are two ways to accomplish this:
1. Easiest way - Insert the bootstrap code of your Platform before your code in the closure. 
   ```
   $jobId = $threader->thread(function($arguments){
      //bootstrap code here..
      //your actual code here..
   });
   ```
2. Recommended way - Extend `$helperClass` with your own Helper class and include the corresponding bootstrap function in it as below:
   
   Suppose the platform you use is WordPress. Create a function in the extended Class as below:
   ```
   public function executeWordpressBootStrap(){
      //code required to bootstrap Wordpress..
   }
   ```
   and override the `getPlatform` method to just return the string 'wordpress'(as WordPress is the only platform you use in that app) as below:
   ```
   public function getPlatform(){
       return 'wordpress';
   }
   ```
   That's all! `executeWordpressBootStrap` will be executed before executing your code allowing Wordpress's native functions and usages in your code inside the Closure.

   By doing this just once, the extension is now ready for executing any code(normal and platform related) anywhere in your application.
   You're also getting the freedom to override any additional logic defined in the `CommandHelper` class.

   **Pull Requests for supporting additional platforms are always welcome!**

Debugging
---------
Whenever an exception gets thrown from your code provided in the closure, it will be logged with a filename: `smt_<jobId>_error.log`.
You can find the details of the error including the Stack Trace inside it.

