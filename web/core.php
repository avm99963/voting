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

// @TODO: Delete these lines in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

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
function hex2hsl($hex) {
  if (substr($hex, 0, 1) == "#") {
    $hex = substr($hex, 1);
  }

  $hex = str_split($hex, 2);

  // RGB to HSL algorithm extracted from http://easyrgb.com/index.php?X=MATH&H=18#text18

  $var_R = hexdec($hex[0]) / 255;
  $var_G = hexdec($hex[1]) / 255;
  $var_B = hexdec($hex[2]) / 255;

  $var_Min = min( $var_R, $var_G, $var_B );    //Min. value of RGB
  $var_Max = max( $var_R, $var_G, $var_B );    //Max. value of RGB
  $del_Max = $var_Max - $var_Min;             //Delta RGB value

  $L = ( $var_Max + $var_Min ) / 2;

  if ( $del_Max == 0 ) {                   //This is a gray, no chroma...
     $H = 0;                               //HSL results from 0 to 1
     $S = 0;
  } else {                                 //Chromatic data...
     if ( $L < 0.5 ) $S = $del_Max / ( $var_Max + $var_Min );
     else           $S = $del_Max / ( 2 - $var_Max - $var_Min );

     $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
     $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
     $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

     if      ( $var_R == $var_Max ) $H = $del_B - $del_G;
     else if ( $var_G == $var_Max ) $H = ( 1 / 3 ) + $del_R - $del_B;
     else if ( $var_B == $var_Max ) $H = ( 2 / 3 ) + $del_G - $del_R;

     if ( $H < 0 ) $H += 1;
     if ( $H > 1 ) $H -= 1;
  }

  return array($H, $S, $L);
}
