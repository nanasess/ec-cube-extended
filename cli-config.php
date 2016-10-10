<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/data/config/config.php';

$connectionParams = array(
    'dbname' => DB_NAME,
    'user' => DB_USER,
    'password' => DB_PASSWORD,
    'host' => DB_SERVER,
    'driver' => 'pdo_pgsql',
);

$paths = array(__DIR__.'/data/config/doctrine');
$isDevMode = true;
$config = \Doctrine\ORM\Tools\Setup::createYAMLMetadataConfiguration($paths, $isDevMode);
$entityManager = \Doctrine\ORM\EntityManager::create($connectionParams, $config);
return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);
