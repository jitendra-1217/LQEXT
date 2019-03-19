[![Build Status](https://travis-ci.org/jitendra-1217/lqext.svg?branch=master)](https://travis-ci.org/jitendra-1217/lqext)

# lqext

## What
1) Makes Laravel's dispatchers which are buses, events & mailable, transaction  
aware. I.e. dispatching events, jobs or mailable, be it queued or sync in  
complex flows, inside nested transaction etc will work as normal expectation.  

2) ~Makes Laravel's queued dispatcher to log failures to redis storage and  
adds support for re attempting those failed remote pushes.~  Because of [a minor  
issue](https://github.com/laravel/framework/issues/27825) in framework's code the logging queue push failures part errors right.  
now. Please wait while a workaround is added.  

## How
Thanks to framework's design which allows extending any service of its easily  
and within limits.  

The library extends a very few methods of events, mailer, dispatcher, queue  
factory & queue services of framework. The extending is done via decoration  
pattern instead of sub-classing.  

## Use
Install using composer  
```bash
composer require jitendra/lqext
```

Copy configuration  
```bash
cp vendor/jitendra/lqext/src/config.php config/lqext.php
```

### Transaction aware dispatching
Besides whitelisting dispatch-able names in above config file, we can use below  
trait in any of job, mailable classes.  
```php
class Job
{
    use \Jitendra\Lqext\TransactionAware;

    // ...
}
```

### Logging queue push failures
Uses default redis connection and maintains log of jobs which failed push to  
remote service in a list. Also Laravel's queue manager will have a method now  
to retery failed pushes.  
```php
// Retry last 1k failed to push jobs. You could add a controller or write a  
// CLI command and invoke this method call from there.  
$this->app->queue->retryFailedToPushJobs();
```
