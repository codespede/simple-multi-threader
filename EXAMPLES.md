Example 1
=========
Suppose you have a time consuming task to be done(like sending emails to 100,000 member of your website) but you don't want the current request sent from the browser to wait until the mail sending process is finished.

So, suppose the below line of code sends the message to all members:

`$mailerObject->sendMail($mail); //takes say 10 mins to finish processing`

If you put this code in your main script, the request sent to the server from browser too will take 10 mins because, the above code is in the main thread.

You can make this line of code to be processed parallelly in the background by using this extension as below:
```
$threader = new \cs\simplemultithreader\Threader(['arguments' => ['mailerObject' => $mailerObect, 'mail' => $mail]]);
$jobId = $threader->thread(function($arguments){
    $mailer = $arguments['mailerObject'];
    $mail = $arguments['mail'];
    $mailer->sendMail($mail);
});
```
By using like this, the request won't have to wait for the mail sending to be finished(as it's a parallel process) and hence will get finished instantly(if no other time consuming code is present).

You can start any number of threads like this from the any script which will all get processed parallelly as independant PHP processes.
