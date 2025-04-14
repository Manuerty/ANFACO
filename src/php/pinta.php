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
            case 4:
                $titulo = "Datos de la Captura";
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
            
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(0),$filetext);
        }elseif($_SESSION["Controlador"] -> miEstado -> Estado == 3){
            
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(1),$filetext);
        }
        return $filetext;
    }


    function DibujaTablaGenerica($Pestana, $tituloAlternativo = null) {

        $arraydatos = $_SESSION['AllData'];

        //var_dump($arraydatos);

       
        if ($Pestana == 0){
            $arraydatos = $_SESSION['AllData'];
        }elseif($Pestana == 1){
            $arraydatos = $_SESSION['AllData'];
        }elseif($Pestana == 2){
            $arraydatos = $_SESSION[''];
        }
        

        $contenido = "<section>";
        
        // Verificar si existe la variable de sesión
        if (isset($arraydatos) && !empty($arraydatos)) {

            $contenido .= "<table class='table table-striped table-bordered-bottom'>";
            //echo $_SESSION[$nombreVariableSesion];
            // Lógica específica para capturas
            if ($Pestana == 1) {
                
                foreach ($arraydatos as $index => $captura) {
    
                    // Obtener los valores de las capturas
                    $TotalAlmacenes      = safe_html($captura["CuentaAlmacen"] ?? null);
                    $temperaturaMaxima      = safe_html($captura["TemperaturaMaxima"] ?? null);
                    $temperaturaMinima      = safe_html($captura["TemperaturaMinima"] ?? null);
                    $fechaUltimaTemperatura = safe_html($captura["FechaUltimoAlmacen"] ?? null);
                    $idUltimoAlmacen        = safe_html($captura["IdTipoAlmacen"] ?? null);
                    $tipoUltimoAlmacen      = safe_html($captura["TipoAlmacen"] ?? null);
                    $ZonaCaptura            = safe_html($captura["Zona"] ?? null);
                    $EspecieCapturada       = safe_html($captura["Especie"] ?? null);
                    $FechaCaptura           = safe_html($captura["FechaCaptura"] ?? null);
                    $NombreBarcoCaptura     = safe_html($captura["NombreBarco"] ?? null);
                
                    // Contenedor principal de la tarjeta
                    $contenido .= "<div class='card mb-3 p-3 border shadow-sm'>";
    
                    // Cabecera: Título con el TagPez
                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'><strong>Tag Pescado:</strong> " . htmlspecialchars($captura["TagPez"]) . "</h5>";
                    $contenido .= "</div>"; // fin cabecera
    
                    // Fila para los datos (dividido en tres columnas)
                    $contenido .= "<div class='d-flex justify-content-between align-items-start' style='gap: 20px;'>"; // Reducir la distancia entre las columnas con 'gap'
    
                    // Columna izquierda: Información de la captura (Fecha, Lugar, Especie, Barco)
                    $contenido .= "<div class='d-flex flex-column align-items-start' style='flex: 1;'>";
    
                    $contenido .= "<div><strong>Fecha de Captura:</strong> " . date('d/m/Y H:i', strtotime($FechaCaptura)) . "</div>";
                    $contenido .= "<div><strong>Lugar de Captura:</strong> " . $ZonaCaptura . "</div>";
                    $contenido .= "<div><strong>Especie Capturada:</strong> " . $EspecieCapturada . "</div>";
                    $contenido .= "<div><strong>Nombre del Barco:</strong> " . $NombreBarcoCaptura . "</div>";
    
                    $contenido .= "</div>"; // fin columna izquierda
    
                    // Columna central: Número de temperaturas registradas, última temperatura y datos del almacén
                    $contenido .= "<div class='d-flex flex-column align-items-start' style='flex: 1;'>";
    
                    // Número de temperaturas registradas
                    $contenido .= "<div><strong>Almacenes por los que ha pasado:</strong> " . $TotalAlmacenes . "</div>";
    
                    // Fecha de la última temperatura registrada a la izquierda del número de registros
                    if (!empty($fechaUltimaTemperatura)) {
                        $contenido .= "<div><strong>Última Temperatura Registrada:</strong> " . date('d/m/Y H:i', strtotime($fechaUltimaTemperatura)) . "</div>";
                    }
    
                    // Información adicional: Datos del último almacén
                    $contenido .= "<div><strong>ID Último Almacén:</strong> " . $idUltimoAlmacen . "</div>";
                    $contenido .= "<div><strong>Tipo de Almacén:</strong> " . $tipoUltimoAlmacen . "</div>";
    
                    $contenido .= "</div>"; // fin columna central
    
                    // Columna derecha: Temperatura máxima, mínima y sus fechas
                    $contenido .= "<div class='d-flex flex-column align-items-start' style='flex: 1;'>";
    
                    // Mostrar la temperatura máxima y su fecha asociada
                    $claseTemperaturaMaxima = ($temperaturaMaxima > 4) ? "text-danger" : ""; // Si es mayor a 4°C, aplicar color rojo
                    $contenido .= "<div><strong>Temperatura Máxima:</strong> <span class='$claseTemperaturaMaxima'>" . $temperaturaMaxima . "°C</span></div>";
                    
    
                    // Mostrar la temperatura mínima y su fecha asociada
                    $claseTemperaturaMinima = ($temperaturaMinima > 4) ? "text-danger" : ""; // Si es mayor a 4°C, aplicar color rojo
                    $contenido .= "<div><strong>Temperatura Mínima:</strong> <span class='$claseTemperaturaMinima'>" . $temperaturaMinima . "°C</span></div>";
                    
    
                    $contenido .= "</div>"; // fin columna derecha
    
                    $contenido .= "</div>"; // fin d-flex (fila)
    
                    // Botón para desplegar las temperaturas
                    $contenido .= "<div class='mt-2'>";
                    $contenido .= "<button class='btn btn-sm btn-outline-secondary' type='button' onclick='dibuja_pagina([3])'>";
                    $contenido .= "Mostrar temperaturas 🔽";
                    $contenido .= "</button>";
                    $contenido .= "</div>";
                    $contenido .= "</div>"; // cierre tarjeta
                }
            }

            // Lógica específica para barcos
            elseif ($Pestana == 0) {
                
                foreach ($arraydatos as $index => $barco) {
                    $contenido .= "<div class='card mb-3 p-3 border shadow-sm'>";
            
                    // Cabecera con ID del barco
                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'>" . ($tituloAlternativo ?? "ID Barco: " . htmlspecialchars($barco["IdBarco"])) . "</h5>";
                    $contenido .= "</div>";
            
                    // Detalles del barco
                    $contenido .= "<div><strong>Nombre:</strong> " . htmlspecialchars($barco["NombreBarco"]) . "</div>";
                    $contenido .= "<div><strong>Código:</strong> " . htmlspecialchars($barco["CodigoBarco"]) . "</div>";
            
                    $contenido .= "</div>"; // cierre de tarjeta
                }
            }

            elseif ($Pestana == 2) {
                foreach ($arraydatos as $index => $captura) {
                    $contenido .= "<div class='card mb-3 p-3 border shadow-sm'>";
            
                    // Cabecera con ID del barco
                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'>" . ($tituloAlternativo ?? "ID Barco: " . htmlspecialchars($captura["IdBarco"])) . "</h5>";
                    $contenido .= "</div>";
            
                    // Detalles del barco
                    $contenido .= "<div><strong>Nombre:</strong> " . htmlspecialchars($captura["Nombre"]) . "</div>";
                    $contenido .= "<div><strong>Código:</strong> " . htmlspecialchars($captura["Codigo"]) . "</div>";
                    $contenido .= "<div><strong>ID Usuario:</strong> " . htmlspecialchars($captura["IdUsuario"]) . "</div>";
            
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


    function safe_html($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    
?>
