iron_worker
===========

#Iron worker projects

##Getting Started

1. Install composure:

```ShellSession
curl -sS https://getcomposer.org/installer | php -d detect_unicode=Off
```

2. Install php-opencloud

```ShellSession
php -d detect_unicode=Off composer.phar require rackspace/php-opencloud:dev-master
```

##Image Manipulation (Display and Thumbnail)

###Deploy the Worker to Iron.io

```
$ iron_worker upload mission_photo_thumbnails
```

Queue the worker from the command line:

####Parameters
- image_url: url to the image location
- mission_photo_id: this is used to create the filename for the display and thumbnail images

```
iron_worker queue mission_photo_thumbnails -p '{"image_url":"http://fc04.deviantart.net/fs30/f/2008/164/9/f/Pretty_Sky_by_sererena.jpg","mission_photo_id":"1234"}'
```


###Sample Image URLs:

- http://fc04.deviantart.net/fs30/f/2008/164/9/f/Pretty_Sky_by_sererena.jpg


##GeoCoding

###Deploy the Worker to Iron.io

```
$ iron_worker upload geocoding
```

Queue the worker from the command line:

####Parameters
- origin_address
- destination_address
- mission_leg_id: this is used to create the filename for the display and thumbnail images

```
iron_worker queue geocoding -p '{"origin_address":"10554 Ohio Ave,Los Angeles,CA 90024","destination_address":"3161 Donald Douglas Loop South,Santa Monica,CA,90405","mission_leg_id":"1234"}'
```

```
 iron_worker queue geocoding -p '{"origin_address":"10 Northampton blvd, Stafford, VA, 22554","destination_address":"1600 Fedex Way, Landover, MD 20785","mission_leg_id":"112014"}'
```

