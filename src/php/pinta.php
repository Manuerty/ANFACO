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
                foreach ($_SESSION[$nombreVariableSesion] as $captura) {
                    $contenido .= "<div class='card mb-3 p-3 border shadow-sm'>";
            
                    // Cabecera con título a la izquierda y fecha a la derecha
                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'>" . ($tituloAlternativo ?? "ID: " . htmlspecialchars($captura["Id"])) . "</h5>";
                    $contenido .= "<small class='text-muted'>" . htmlspecialchars($captura["Fecha"]) . "</small>";
                    $contenido .= "</div>";
            
                    $contenido .= "<div><strong>TagPez:</strong> " . htmlspecialchars($captura["TagPez"]) . "</div>";
                    $contenido .= "<div><strong>Lector RFID:</strong> " . htmlspecialchars($captura["LectorRFID"]) . "</div>";
            
                    if (!empty($captura["Temperaturas"])) {
                        $contenido .= "<div><strong>Temperaturas:</strong><ol class='mb-0'>";
                        foreach ($captura["Temperaturas"] as $temperatura) {
                            $contenido .= "<li>Temperatura: " . htmlspecialchars($temperatura["Temperatura"]) . "°C</li>";
                        }
                        $contenido .= "</ol></div>";
                    } else {
                        $contenido .= "<div><strong>Temperaturas:</strong> No hay temperaturas</div>";
                    }
            
                    $contenido .= "</div>"; // cierre de tarjeta
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
