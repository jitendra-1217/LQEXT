[![Build Status](https://travis-ci.org/jitendra-1217/lqext.svg?branch=master)](https://travis-ci.org/jitendra-1217/lqext)

# lqext

**Work in progress!**

## What
Makes laravel's dispatcher, database transaction aware. I.e. dispatching events,  
jobs or mailables, be it queued or sync in complex flows, inside nested  
transaction etc will work as normal expectation.  

Makes laravel's queued dispatcher to log failures to storage by a interface  
writer, in a format which can be replayed later etc.  

## How
Thanks to framework's design which allows extending any service of its easily  
and within limits.  

The library extends a very few methods of events, mailer, dispatcher, queue  
factory & queue services of framework. The extending is done via decoration  
pattern instead of subclassing.  

## Use

Install using composer  
```sh
# As this lib is not published, you will need to specify composer's remote repo.
composer require jitendra/lqext
```

Copy over [lqext/src/config.php](https://github.com/jitendra-1217/lqext/blob/master/src/config.php) to your project's config/lqext.php.  
Specify flags by env variables or keep hard coded in your config file.

Besides whitelisting in above config file, can use below trait in any of job, mailabe classes.  
```php
use \Jitendra\Lqext\TransactionAware;
```

## Todos/plan
- [x] Update above sections with details.
- [x] Update this list with things to do to wrap up.
- [ ] Get file and redis implementation to store and scroll through the failed  
    push job logs.
- [ ] Enhancements to provide cli interface to replay jobs with various filters  
    etc.
- [ ] Add unit tests.
