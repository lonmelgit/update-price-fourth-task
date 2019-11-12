
<?php

use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';

ini_set('memory_limit', -1);
set_time_limit(0);

$bootstrap = Bootstrap::create(BP, $_SERVER);

$obj = $bootstrap->getObjectManager();

$objectManager = \Magento\Framework\App\ObjectManager::getInstance();



$test = $objectManager->get('\Update\Price\Cron\TaskUpdate');

$test->execute();

