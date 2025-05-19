<?php
    function ConexionBD($srvr = "81.169.200.39:23458", $bd = "anfaco", $un = "Anfaco", $ps = "Rodrigo-01*")
    {
        // Variables for the database connection
        $bbdd = $bd;
        $pass = $ps;
        $username = $un;
        $server = $srvr;


        try {
            // Create a connection using mysqli
            $conn = new mysqli($server, $username, $pass, $bbdd);

            // Check for connection errors
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            // Set the character set to UTF-8
            $conn->set_charset("utf8");


        } catch (Exception $e) {
            echo "No se logró conectar correctamente con la base de datos: $bbdd.\nerror:  " . $e->getMessage() ;
            return null;
        }

        return $conn;
    }
?>
