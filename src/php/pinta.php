<?php

    function pinta_contenido($estado){
        $titulo = ""; 
        $cabecera = "../html/header.html";
        $fileheadertext = "";
        
        switch($estado){

            case 0:
                $cabecera = "";
                $filename = "../html/login.html";
                break;
            case 1:
                $titulo = "Dashboard";
                $filename = "../html/dashboard.html";
                break;
            case 2:
                $titulo = "Barcos";
                $filename = "../html/documentos.html";
                break;
            case 3:
                $titulo = "Capturas";
                $filename = "../html/documentos.html";
                break;
        }

        if ($cabecera != ""){

            $fileheader = fopen($cabecera, "r");
            $filesize = filesize($cabecera);
            $fileheadertext = fread($fileheader, length: $filesize);
            fclose($fileheader);

            $fileheadertext = str_replace("%NombreE%",$titulo,$fileheadertext);

        }


        if(isset($filename) && $filename != "" ){
            $file = fopen($filename, "r");
            $filesize = filesize($filename);
            $filetext = fread($file, $filesize);
            $filetext =  $fileheadertext. $filetext;
            fclose($file);
        }else{
            $filetext = "";
        }

        if($_SESSION["Controlador"] -> miEstado -> Estado == 2){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica("barcos"),$filetext);
        }elseif($_SESSION["Controlador"] -> miEstado -> Estado == 3){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica("capturas"),$filetext);
        }
        return $filetext;
    }


    function DibujaTablaGenerica($nombreVariableSesion, $tituloAlternativo = null) {
        $contenido = "<section>";
        
        // Verificar si existe la variable de sesión
        if (isset($_SESSION[$nombreVariableSesion]) && !empty($_SESSION[$nombreVariableSesion])) {
            $contenido .= "<table class='table table-striped table-bordered-bottom'>";
    
            // Lógica específica para capturas
            if ($nombreVariableSesion == "capturas") {
                // Generamos los encabezados de la tabla para capturas
                if ($tituloAlternativo) {
                    $contenido .= "<thead><tr><th>$tituloAlternativo</th><th>Fecha</th><th>TagPez</th><th>Lector RFID</th><th>Temperaturas</th></tr></thead>";
                } else {
                    $contenido .= "<thead><tr><th>ID</th><th>Fecha</th><th>TagPez</th><th>Lector RFID</th><th>Temperaturas</th></tr></thead>";
                }
                
                $contenido .= "<tbody>";
    
                // Iterar sobre las capturas (almacenes)
                foreach ($_SESSION[$nombreVariableSesion] as $captura) {
                    $contenido .= "<tr>";
                    $contenido .= "<td>" . htmlspecialchars($captura["Id"]) . "</td>";
                    $contenido .= "<td>" . htmlspecialchars($captura["Fecha"]) . "</td>";
                    $contenido .= "<td>" . htmlspecialchars($captura["TagPez"]) . "</td>";
                    $contenido .= "<td>" . htmlspecialchars($captura["LectorRFID"]) . "</td>";
    
                    // Mostrar las temperaturas de manera numerada
                    if (!empty($captura["Temperaturas"])) {
                        $contenido .= "<td><ol>"; // Abrimos la lista ordenada (numerada)
                        
                        foreach ($captura["Temperaturas"] as $temperatura) {
                            $contenido .= "<li>Temperatura: " . htmlspecialchars($temperatura["Temperatura"]) . "°C</li>";
                        }
    
                        $contenido .= "</ol></td>"; // Cerramos la lista ordenada
                    } else {
                        $contenido .= "<td>No hay temperaturas</td>";
                    }
    
                    $contenido .= "</tr>";
                }
            }
            // Lógica específica para barcos
            elseif ($nombreVariableSesion == "barcos") {
                // Generamos los encabezados de la tabla para barcos
                if ($tituloAlternativo) {
                    $contenido .= "<thead><tr><th>$tituloAlternativo</th><th>Nombre</th><th>Código</th><th>Id Usuario</th></tr></thead>";
                } else {
                    $contenido .= "<thead><tr><th>ID Barco</th><th>Nombre</th><th>Código</th><th>Id Usuario</th></tr></thead>";
                }
    
                $contenido .= "<tbody>";
    
                // Iterar sobre los barcos
                foreach ($_SESSION[$nombreVariableSesion] as $barco) {
                    $contenido .= "<tr>";
                    $contenido .= "<td>" . htmlspecialchars($barco["IdBarco"]) . "</td>";
                    $contenido .= "<td>" . htmlspecialchars($barco["Nombre"]) . "</td>";
                    $contenido .= "<td>" . htmlspecialchars($barco["Codigo"]) . "</td>";
                    $contenido .= "<td>" . htmlspecialchars($barco["IdUsuario"]) . "</td>";
                    $contenido .= "</tr>";
                }
            }
    
            $contenido .= "</tbody></table>";
        } else {
            $contenido .= "<p>$tituloAlternativo</p>";
        }
    
        $contenido .= "</section>";
        return $contenido;
    }
    
    
    
    
    
    
    


?>
