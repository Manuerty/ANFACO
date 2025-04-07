<?php
    function ConexionBD($srvr = "localhost", $bd = "prueba_1", $un = "root", $ps = "")
    {
        // Variables for the database connection
        $bbdd = $bd;
        $username = $un;
        $pass = $ps;
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
            echo "No se logró conectar correctamente con la base de datos: $bbdd, error: " . $e->getMessage();
            return null;
        }

        return $conn;
    }
?>
