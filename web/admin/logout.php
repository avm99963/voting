<?php
    include('../core.php'); // incluimos los datos de acceso a la BD
    // comprobamos que se haya iniciado la sesión
    if(isset($_SESSION['id'])) {
        session_destroy();
        header("Location: login.php?msg=logoutsuccess");
    }else {
        echo "Operación incorrecta.";
    }
