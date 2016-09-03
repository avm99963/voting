<?php
/**
 * Core
 *

    /////  //////  //////  /////
   //     //  //  //  //  //___
  //     //  //  //////  //´´´´
 /////  //////  // //   /////

 */

// Timezone and language
date_default_timezone_set("Europe/Madrid");
setlocale(LC_ALL,"es_ES");

// Aquí se recoge la configuración
require("config.php");

// Aquí se accede a la BD y a la sesión
$con = @mysqli_connect($host_db, $usuario_db, $clave_db,$nombre_db) or die("Check Mysqli settings in config.php"); // Conectamos y seleccionamos BD
mysqli_set_charset($con, "utf8");

session_set_cookie_params(0, $conf["path"]);
session_start();

// Custom error handler
function myErrorHandler($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        // This error code is not included in error_reporting
        return;
    }

    switch ($errno) {
    case E_USER_ERROR:
        echo "<div class='alert alert-danger'><b>Error:</b> [$errno] $errstr<br>\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...</div>\n";
        exit(1);
        break;

    case E_USER_WARNING:
        echo "<div class='alert alert-warning'><b>Warning:</b> [$errno] $errstr on line $errline in file $errfile</div>\n";
        break;

    case E_WARNING:
        echo "<div class='alert alert-warning'><b>Warning:</b> [$errno] $errstr on line $errline in file $errfile</div>\n";
        break;

    case E_ERROR:
        echo "<div class='alert alert-danger'><b>Error:</b> [$errno] $errstr<br>\n";
        echo "  Fatal error on line $errline in file $errfile";
        echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
        echo "Aborting...</div>\n";
        exit(1);
        break;

    case E_USER_NOTICE:
        echo "<div class='alert alert-warning'><b>Notice:</b> [$errno] $errstr on line $errline in file $errfile</div>\n";
        break;

    default:
        echo "<div class='alert alert-warning'>Unknown error type: [$errno] $errstr on line $errline in file $errfile</div>\n";
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}

$old_error_handler = set_error_handler("myErrorHandler");

spl_autoload_register(function($className) {
  include_once("inc/".$className.".php");
});

// Funciones:
