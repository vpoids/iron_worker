iron worker
===========

#Worker Configuration:

The file config_worker_env_vars.txt contains the private keys used by the workers. When deploying to iron.io, the keys contained in this file are used to set environment variables for use by the workers.

The instructions on the iron.io php page detail the steps necessary to install the iron_worker CLI, and steps required to deploy a worker to the service:

http://dev.iron.io/worker/languages/php/

#Queue a Worker using php:

1. Download the iron_worker.phar file from: https://github.com/iron-io/iron_worker_php
  - included in the repo for your convenience.

2. Example to queue the waiver_receipt worker:

```php
  <?php
  require("phar://iron_worker.phar");

  $payload = array(
      'waiver_id' => 9,
      'recipients' => 'travishubbard@gmail.com',
  );

  $worker = new IronWorker();
  $res = $worker->postTask('waiver_receipt', $payload);
  print_r($res);
  ?>
```
#Workers

###1. Image Manipulation (Display and Thumbnail)

####Deploy the Worker to Iron.io:

```
$ iron_worker upload mission_photo_thumbnails
```

####Queue the worker from the command line:

#####Parameters
- image_url: url to the image location
- mission_photo_id: this is used to create the filename for the display and thumbnail images

```
$ iron_worker queue mission_photo_thumbnails -p '{"image_url":"http://fc04.deviantart.net/fs30/f/2008/164/9/f/Pretty_Sky_by_sererena.jpg","mission_photo_id":"1234"}'
```


#####Sample Image URL:

- http://fc04.deviantart.net/fs30/f/2008/164/9/f/Pretty_Sky_by_sererena.jpg


###2. GeoCoding

####Deploy the Worker to Iron.io:

```
$ iron_worker upload geocoding
```

####Queue the worker from the command line:

#####Parameters
- origin_address
- destination_address
- mission_leg_id: this is used to create the filename for the display and thumbnail images

```
$ iron_worker queue geocoding -p '{"origin_address":"10554 Ohio Ave,Los Angeles,CA 90024","destination_address":"3161 Donald Douglas Loop South,Santa Monica,CA,90405","mission_leg_id":"1234"}'
```

```
$ iron_worker queue geocoding -p '{"origin_address":"10 Northampton blvd, Stafford, VA, 22554","destination_address":"1600 Fedex Way, Landover, MD 20785","mission_leg_id":"112014"}'
```

###3. Waiver Receipt

####Deploy the Worker to Iron.io:

```
$ iron_worker upload waiver_receipt
```

####Queue the worker from the command line:

#####Parameters
- waiver_id: id of the waiver, used as lookup to pull the data used for waiver signatures, etc.
- recipients: comma delimited list of email addresses of those that should receive the waiver

```
$ iron_worker queue waiver_receipt -p '{"waiver_id":14,"recipients":"w.travis.hubbard@gmail.com"}'
```

####Development Notes:

1. Install composure:

```ShellSession
$ curl -sS https://getcomposer.org/installer | php -d detect_unicode=Off
```

2. Install php-opencloud

```ShellSession
$ php -d detect_unicode=Off composer.phar require rackspace/php-opencloud:dev-master
```

3. Install the Mialgun php library

```ShellSession
$ php -d detect_unicode=Off composer.phar require mailgun/mailgun-php:~1.7.1
```
