mailbox
=======

## Config Redis

- Go to app/config/database.php, add code:
```php
'redis' => array(

		'cluster' => false,

		'default' => array(
			'host'     => '127.0.0.1',
			'port'     => 6379,
			'database' => 0,
		),

	),
```
- Go to app/config/cache.php, modify the code:
```php
  ...
  'driver' => 'redis',
  ...
```

## Multi Queue Config

- Go to app/config/queue.php, add code:
```php
...
'default' => 'low',
...

	'connections' => array(

		...

    'high' => array(
      'driver' => 'redis',
      'queue'  => 'high',
    ),
    'low' => array(
      'driver' => 'redis',
      'queue'  => 'low',
    ),

	),
```
- Open CMD, and run command: `php artisan queue:listen --queue=low --sleep=0 --timeout=0`
- Open CMD, and run command: `php artisan queue:listen --queue=high --sleep=0 --timeout=0`
- Add queue:
```php
  \Queue::push(function($job)
  {
     \Log::info('10This is was written via the MailWorker class at '.time().', id is '.$job->getJobId());
     $job->release(10);
  },array('message' => 'aa'), 'high');

  \Queue::push( '\DavinBao\Mailbox\MailWorker@receiveMail', array('message' => 'aa'), 'low');
```
