<?php

/**
 * Punto di inizio
 * 
 * @authors: Miguel Delli Carpini, Matteo Scirea, Javier Jara
 */

//Solo fatal error
error_reporting(E_ERROR);

//Dogana
//----------------------------------------------


//Attiviamo l'autoloader di Composer
require_once __DIR__ . '/../vendor/autoload.php';

//Percorso da dove inizia l'applicazione
define('APP_BASEDIR', __DIR__ . '/../');

//Fine dogana
//----------------------------------------------



//Namespaces
//----------

//Usiamo i comandi di Elle4Engine
use Engine\Kernel\MainExecutor;


//Lanciamo il wrapper dell'applicazione vero e proprio

//Il grande Executor Tassadar ancora vive!
$tassadar = new MainExecutor();

$tassadar->mainLoop();


?>