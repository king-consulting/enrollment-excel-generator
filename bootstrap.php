<?php

require_once __DIR__ . '/vendor/autoload.php';

// this will load fedev for now
// $config = \AgentPlatform\EnhancedAgentNotifications\Management\Config::get_instance();

// this will use a local sqlite db
//$config = new \AgentPlatform\EnhancedAgentNotifications\Orm\Config\LocalSqlite();

// $ormFactory = new \AgentPlatform\EnhancedAgentNotifications\Orm\Factory($config);
// $entityManager = $ormFactory->getDoctrineEntityManager();
// $readOnlyEntityManager = $ormFactory->getReadOnlyDoctrineEntityManager();

// set up legacy db handle path; needed for writing to CRM
// define('DB_HANDLE_INCLUDE_PATH', $config->getLegacyDbHandlePath());

