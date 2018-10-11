<?php

require_once __DIR__ . '/vendor/autoload.php';

ini_set('memory_limit','1G');

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use KingConsulting\Excel\Generator;
use KingConsulting\Service\RawDataService;

// Create a simple "default" Doctrine ORM configuration for Annotations
$isDevMode = true;
$config = Setup::createAnnotationMetadataConfiguration(array(__DIR__."vendor/king-consulting/enrollment-orm/config/Entity"), $isDevMode);

// the connection configuration
$conn = array(
  'driver'   => 'pdo_mysql',
  'user'     => '__USER__',
  'password' => '__PASSWORD__',
  'dbname'   => 'Enrollment',
);

// obtaining the entity manager
$entityManager = EntityManager::create($conn, $config);

$RawDataService = new RawDataService($entityManager);

// Create new PHPExcel object
$PHPExcel = new PHPExcel();

$ExcelGenerator = new Generator($PHPExcel, __DIR__ . '/output');
