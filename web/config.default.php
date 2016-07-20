<?php
/**
 * Configuration
 *

    /////  //////  ////  //  //////  //  //////
   //     //  //  // // //  //          //
  //     //  //  //  ////  //////  //  //  //
 /////  //////  //   ///  //      //  //////

 */

// Welcome to the configuration file! In comments you have the explanation of what each variable does.
// Save this file as config.php so the application works

// Define the MySQL DataBase settings:
$host_db = ''; // DB Host (default: localhost)
$usuario_db = ''; // DB User
$clave_db = ''; // DB Password
$nombre_db = ''; // DB name

$conf = array();

// Define the website name:
$conf["appname"] = "Voting";

// Define the language: (currently only "en" and "es" are supported)
$conf["language"] = "en";

// Path in the URL to the voting app. It will be used to set up the session cookie.
// Examples: "/voting/", "/", "/p/voting/"
$conf["path"] = "/voting/web/";
