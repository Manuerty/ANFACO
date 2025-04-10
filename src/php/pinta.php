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

                // Obtener los valores de las temperaturas máximas, mínimas, el total y las fechas
                $totalTemperaturas = htmlspecialchars($captura["TotalTemperaturas"]);
                $temperaturaMaxima = htmlspecialchars($captura["TemperaturaMaxima"]);
                $temperaturaMinima = htmlspecialchars($captura["TemperaturaMinima"]);
                $fechaUltimaTemperatura = htmlspecialchars($captura["FechaUltimaTemperatura"]);
                $fechaTemperaturaMaxima = htmlspecialchars($captura["FechaTemperaturaMaxima"]);
                $fechaTemperaturaMinima = htmlspecialchars($captura["FechaTemperaturaMinima"]);

                // ID único para el collapse
                $collapseId = "temperaturasCollapse_" . $index;

                // Contenedor principal de la tarjeta
                $contenido .= "<div class='card mb-3 p-3 border shadow-sm'>";

                // Cabecera: Título con el TagPez
                $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                $contenido .= "<h5 class='card-title mb-0'><strong>Tag Pescado:</strong> " . htmlspecialchars($captura["TagPez"]) . "</h5>";
                $contenido .= "</div>"; // fin cabecera

                // Fila para los datos de las temperaturas
                $contenido .= "<div class='d-flex justify-content-between align-items-center'>"; // Espaciado por defecto entre elementos

                // Nueva columna para las temperaturas (Temperatura Máxima y Mínima)
                $contenido .= "<div class='d-flex flex-column align-items-start'>";

                // Mostrar la temperatura máxima y su fecha asociada
                $claseTemperaturaMaxima = ($temperaturaMaxima > 4) ? "text-danger" : ""; // Si es mayor a 4°C, aplicar color rojo
                $contenido .= "<div><strong>Temperatura Máxima:</strong> <span class='$claseTemperaturaMaxima'>" . $temperaturaMaxima . "°C</span></div>";
                // Fecha de la temperatura máxima a la derecha
                if (!empty($fechaTemperaturaMaxima)) {
                    $contenido .= "<div class='text-muted' style='text-align: right;'><strong>Fecha:</strong> " . date('d/m/Y H:i', strtotime($fechaTemperaturaMaxima)) . "</div>";
                }

                // Mostrar la temperatura mínima y su fecha asociada
                $claseTemperaturaMinima = ($temperaturaMinima > 4) ? "text-danger" : ""; // Si es mayor a 4°C, aplicar color rojo
                $contenido .= "<div><strong>Temperatura Mínima:</strong> <span class='$claseTemperaturaMinima'>" . $temperaturaMinima . "°C</span></div>";
                // Fecha de la temperatura mínima a la derecha
                if (!empty($fechaTemperaturaMinima)) {
                    $contenido .= "<div class='text-muted' style='text-align: right;'><strong>Fecha:</strong> " . date('d/m/Y H:i', strtotime($fechaTemperaturaMinima)) . "</div>";
                }

                // Número de temperaturas registradas debajo de la temperatura mínima
                $contenido .= "<div><strong>Número de Temperaturas Registradas:</strong> " . $totalTemperaturas . "</div>";
                // Fecha de la última temperatura registrada a la derecha del número de registros
                if (!empty($fechaUltimaTemperatura)) {
                    $contenido .= "<div class='text-muted' style='text-align: right;'><strong>Última Temperatura Registrada:</strong> " . date('d/m/Y H:i', strtotime($fechaUltimaTemperatura)) . "</div>";
                }

                $contenido .= "</div>"; // fin de la columna de temperaturas

                $contenido .= "</div>"; // fin d-flex

                // Botón para desplegar las temperaturas
                $contenido .= "<div class='mt-2'>";
                $contenido .= "<button class='btn btn-sm btn-outline-secondary' type='button' data-bs-toggle='collapse' data-bs-target='#$collapseId' aria-expanded='false' aria-controls='$collapseId'>";
                $contenido .= "Mostrar temperaturas 🔽";
                $contenido .= "</button>";
                $contenido .= "</div>";

                // Contenedor colapsable para mostrar las temperaturas registradas
                $contenido .= "<div class='collapse mt-2' id='$collapseId'>";
                if (!empty($captura["Temperaturas"])) {
                    $contenido .= "<ol class='mb-0'>";
                    foreach ($captura["Temperaturas"] as $temperatura) {
                        // Verificar si la temperatura es mayor a 4°C
                        $claseTemperatura = "";
                        if ($temperatura["Temperatura"] > 4) {
                            $claseTemperatura = "text-danger"; // Clase CSS para texto en rojo
                        }

                        // Mostrar la temperatura y la fecha de la temperatura con la clase si es mayor a 4°C
                        $contenido .= "<li>";
                        $contenido .= "<span class='$claseTemperatura'>Temperatura: " . htmlspecialchars($temperatura["Temperatura"]) . "°C</span>";

                        // Si existe la fecha de la temperatura, mostrarla
                        if (!empty($temperatura["FechaTemperatura"])) {
                            // Formateo de la fecha
                            $fechaTemperatura = DateTime::createFromFormat('Y-m-d H:i:s', $temperatura["FechaTemperatura"]);
                            if ($fechaTemperatura) {
                                $contenido .= " <span class='text-muted'>(registrada el " . $fechaTemperatura->format('d/m/Y') . " a las " . $fechaTemperatura->format('H:i') . ")</span>";
                            }
                        }
                        $contenido .= "</li>";
                    }
                    $contenido .= "</ol>";
                } else {
                    // En caso de que no haya temperaturas registradas
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
