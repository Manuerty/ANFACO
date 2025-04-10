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
                foreach ($_SESSION[$nombreVariableSesion] as $index => $captura) {
                    // Separar fecha y hora
                    $fechaCompleta = htmlspecialchars($captura["Fecha"]);
                    $fechaPartes = explode(" ", $fechaCompleta);

                    // Convertimos la fecha al formato dd/mm/yyyy
                    $soloFecha = '';
                    if (!empty($fechaPartes[0])) {
                        $fechaObj = DateTime::createFromFormat('Y-m-d', $fechaPartes[0]);
                        if ($fechaObj) {
                            $soloFecha = $fechaObj->format('d/m/Y');
                        }
                    }

                    $soloHora = $fechaPartes[1] ?? '';

                    // ID único para el collapse
                    $collapseId = "temperaturasCollapse_" . $index;

                    $contenido .= "<div class='card mb-3 p-3 border shadow-sm'>";

                    // Cabecera: título + fecha/hora
                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'>" . ($tituloAlternativo ?? "ID: " . htmlspecialchars($captura["Id"])) . "</h5>";
                    $contenido .= "<div class='text-end'>";
                    $contenido .= "<div class='fw-bold text-muted'>" . $soloFecha . "</div>";
                    $contenido .= "<div class='text-muted'>" . $soloHora . "</div>";
                    $contenido .= "</div>";
                    $contenido .= "</div>";

                    $contenido .= "<div><strong>TagPez:</strong> " . htmlspecialchars($captura["TagPez"]) . "</div>";
                    $contenido .= "<div><strong>Lector RFID:</strong> " . htmlspecialchars($captura["LectorRFID"]) . "</div>";

                    // Botón para desplegar temperaturas
                    $contenido .= "<div class='mt-2'>";
                    $contenido .= "<button class='btn btn-sm btn-outline-secondary' type='button' data-bs-toggle='collapse' data-bs-target='#$collapseId' aria-expanded='false' aria-controls='$collapseId'>";
                    $contenido .= "Mostrar temperaturas 🔽";
                    $contenido .= "</button>";
                    $contenido .= "</div>";

                    // Contenedor colapsable
                    $contenido .= "<div class='collapse mt-2' id='$collapseId'>";
                    if (!empty($captura["Temperaturas"])) {
                        $contenido .= "<ol class='mb-0'>";
                        foreach ($captura["Temperaturas"] as $temperatura) {
                            $contenido .= "<li>Temperatura: " . htmlspecialchars($temperatura["Temperatura"]) . "°C</li>";
                        }
                        $contenido .= "</ol>";
                    } else {
                        $contenido .= "<div class='text-muted'>No hay temperaturas</div>";
                    }
                    $contenido .= "</div>"; // fin collapse

                    $contenido .= "</div>"; // cierre tarjeta
                }
            }

            
            // Lógica específica para barcos
            elseif ($nombreVariableSesion == "barcos") {
                foreach ($_SESSION[$nombreVariableSesion] as $barco) {
                    $contenido .= "<div class='card mb-3 p-3 border shadow-sm'>";
            
                    // Cabecera con ID del barco
                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'>" . ($tituloAlternativo ?? "ID Barco: " . htmlspecialchars($barco["IdBarco"])) . "</h5>";
                    $contenido .= "</div>";
            
                    // Detalles del barco
                    $contenido .= "<div><strong>Nombre:</strong> " . htmlspecialchars($barco["Nombre"]) . "</div>";
                    $contenido .= "<div><strong>Código:</strong> " . htmlspecialchars($barco["Codigo"]) . "</div>";
                    $contenido .= "<div><strong>ID Usuario:</strong> " . htmlspecialchars($barco["IdUsuario"]) . "</div>";
            
                    $contenido .= "</div>"; // cierre de tarjeta
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
