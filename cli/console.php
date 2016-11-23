#!/usr/bin/env php
<?php

/**
 * Console main wrapper
 * 
 * @authors: Miguel Delli Carpini
 */
define('APP_BASEDIR', __DIR__ . '/../');

//Evitiamo inceppamenti per timeout
set_time_limit(0);
ini_set("memory_limit",-1);
error_reporting(E_ERROR);

//Attiviamo l'autoloader di Composer
require_once APP_BASEDIR . 'vendor/autoload.php';

//Percorso da dove inizia l'applicazione


//Dogana
//----------------------------------------------

//Namespaces
//----------
//Usiamo l'applicazione di console di Symfony
use Symfony\Component\Console\Application;

//Access to command namespace
use Engine\Commands;

$app = new Application();
$app->addCommands(array(new Commands\VoidExample()));
$app->run();
?>