<?php
require 'vendor/autoload.php';

use OpenCloud\Rackspace;

$client = new Rackspace(Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => 'angelflightwest',
    'apiKey'   => 'eca78b0ce406c0000e3ae8d342d4ccae'
));

// Obtain an Object Store service object from the client.
$region = 'DFW';
$objectStoreService = $client->objectStoreService(null, $region);

// Get container list.
$containers = $objectStoreService->listContainers();
foreach ($containers as $container) {
    /** @var $container OpenCloud\ObjectStore\Resource\Container  **/
    printf("Container name: %s\n", $container->getName());
}

// 3. Get container.
$container = $objectStoreService->getContainer('public_files');

// 4. Set container metadata.
$containerMetadata = $container->getMetadata();

/** @var $container $containerMetadata OpenCloud\ObjectStore\Resource\ContainerMetadata **/
printf("Container author: %s\n", $containerMetadata->getProperty('author'));


$objects = $container->objectList();
foreach ($objects as $object) {
    /** @var $object OpenCloud\ObjectStore\Resource\DataObject  **/
    printf("Object name: %s\n", $object->getName());
}