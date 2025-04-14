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
       
        if ($Pestana == 0){
            $arraydatos = $_SESSION['Barcos'];
        }elseif($Pestana == 1){
            $arraydatos = $_SESSION['AllData'];
        }elseif($Pestana == 2){
            $arraydatos = $_SESSION[''];
        }

        //var_dump( $arraydatos );
        

        $contenido = "<section>";
        
        // Verificar si existe la variable de sesión
        if (isset($arraydatos) && !empty($arraydatos)) {

            $contenido .= "<table class='table table-striped table-bordered-bottom'>";
            //echo $_SESSION[$nombreVariableSesion];
            // Lógica específica para capturas
            if ($Pestana == 1) {
                foreach ($arraydatos as $index => $captura) {
                    $TotalAlmacenes          = safe_html($captura["CuentaAlmacen"] ?? null);
                    $temperaturaMaxima       = safe_html($captura["TemperaturaMaxima"] ?? null);
                    $temperaturaMinima       = safe_html($captura["TemperaturaMinima"] ?? null);
                    $fechaUltimaTemperatura  = safe_html($captura["FechaUltimoAlmacen"] ?? null);
                    $idUltimoAlmacen         = safe_html($captura["IdTipoAlmacen"] ?? null);
                    $tipoUltimoAlmacen       = safe_html($captura["TipoAlmacen"] ?? null);
                    $ZonaCaptura             = safe_html($captura["Zona"] ?? null);
                    $EspecieCapturada        = safe_html($captura["Especie"] ?? null);
                    $FechaCaptura            = safe_html($captura["FechaCaptura"] ?? null);
                    $NombreBarcoCaptura      = safe_html($captura["NombreBarco"] ?? null);
                    $tagPez                  = htmlspecialchars($captura["TagPez"]);
            
                    $claseTemperaturaMaxima = ($temperaturaMaxima > 4) ? "text-danger" : "";
                    $claseTemperaturaMinima = ($temperaturaMinima > 4) ? "text-danger" : "";
            
                    // Clase para la fecha según temperatura máxima
                    $claseFecha = ($temperaturaMaxima > 4) ? "text-danger" : "text-success";
            
                    // Tarjeta principal
                    $contenido .= "<div class='card mb-3 p-3 border shadow-sm'>";
            
                    // Cabecera: Tag + Fecha a la derecha
                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'><strong>$tagPez</strong></h5>";
                    $contenido .= "<div class='small $claseFecha'>" . date('d/m/Y H:i', strtotime($FechaCaptura)) . "</div>";
                    $contenido .= "</div>";
            
                    // Temperatura máxima visible solo si es >= 4
                    if ($temperaturaMaxima >= 4) {
                        $contenido .= "<div class='mb-2'><p>Temp Max: <span class='$claseTemperaturaMaxima'> <strong>" . $temperaturaMaxima ."°C</span></strong> </p></div>";
                    }
            
                    // Botón para desplegar detalles
                    $contenido .= "<button class='btn btn-sm btn-outline-secondary mb-2' type='button' data-bs-toggle='collapse' data-bs-target='#detallesCaptura$index' aria-expanded='false'>";
                    $contenido .= "Ver más detalles 🔽</button>";
            
                    // Contenido colapsable
                    $contenido .= "<div class='collapse' id='detallesCaptura$index'>";
                    $contenido .= "<div class='pt-2'>";
            
                    // Layout en tres columnas
                    $contenido .= "<div class='row'>";
                    $contenido .= "<div class='col-md-4'><strong>Lugar de Captura:</strong> $ZonaCaptura</div>";
                    $contenido .= "<div class='col-md-4'><strong>Especie Capturada:</strong> $EspecieCapturada</div>";
                    $contenido .= "<div class='col-md-4'><strong>Nombre del Barco:</strong> $NombreBarcoCaptura</div>";
                    $contenido .= "<div class='col-md-4'><strong>Almacenes por los que ha pasado:</strong> $TotalAlmacenes</div>";
            
                    if (!empty($fechaUltimaTemperatura)) {
                        $contenido .= "<div class='col-md-4'><strong>Última Temperatura Registrada:</strong> " . date('d/m/Y H:i', strtotime($fechaUltimaTemperatura)) . "</div>";
                    }
            
                    $contenido .= "<div class='col-md-4'><strong>ID Último Almacén:</strong> $idUltimoAlmacen</div>";
                    $contenido .= "<div class='col-md-4'><strong>Tipo de Almacén:</strong> $tipoUltimoAlmacen</div>";
                    $contenido .= "<div class='col-md-4'><strong>Temperatura Mínima:</strong> <span class='$claseTemperaturaMinima'>" . $temperaturaMinima . "°C</span></div>";
                    $contenido .= "</div>"; // fin row
            
                    $contenido .= "</div></div>"; // fin collapse y contenido
            
                    $contenido .= "</div>"; // fin tarjeta
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
                foreach ($arraydatos as $index => $barcos) {
                    $contenido .= "<div class='card mb-3 p-3 border shadow-sm'>";
            
                    // Cabecera con ID del barco
                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'>" . ($tituloAlternativo ?? "ID Barco: " . htmlspecialchars($barcos["IdBarco"])) . "</h5>";
                    $contenido .= "</div>";
            
                    // Detalles del barco
                    $contenido .= "<div><strong>Nombre:</strong> " . htmlspecialchars($barcos["Nombre"]) . "</div>";
                    $contenido .= "<div><strong>Código:</strong> " . htmlspecialchars($barcos["CodigoBarco"]) . "</div>";
            
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
