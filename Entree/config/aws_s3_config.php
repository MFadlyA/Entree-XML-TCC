<?php
require $_SERVER['DOCUMENT_ROOT'] . '/Entree/vendor/autoload.php';

use Aws\S3\S3Client;

$s3 = new S3Client([
    'version'     => 'latest',
    'region'      => 'YOUR_REGION',
    'credentials' => [
        'key'    => 'YOUR_SECRET_KEY',
        'secret' => 'YOUR_SECRET_KEY',
    ],
]);

$bucketName = 'entree-uploads'; 
