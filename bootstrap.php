<?php

require_once __DIR__ . '/vendor/autoload.php';

$environment = new \Riki\Environment(realpath(__DIR__));
$application = new \App\Application($environment);

return $application;
