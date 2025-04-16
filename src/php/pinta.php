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
            case 0.5:
                $titulo = "Usuarios";
                $filename = "../html/documentos.html";
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

        
        if($_SESSION["Controlador"] -> miEstado -> Estado == 0.5){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(0),$filetext);
        }
        elseif($_SESSION["Controlador"] -> miEstado -> Estado == 2){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(1),$filetext);
        }
        elseif($_SESSION["Controlador"] -> miEstado -> Estado == 3){
            
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(2),$filetext);
        }
        return $filetext;
    }


    function DibujaTablaGenerica($Pestana, $tituloAlternativo = null) {

        if ($Pestana == 0){
            $arraydatos = $_SESSION['Usuarios'];
        } elseif($Pestana == 1){
            $arraydatos = $_SESSION['Barcos'];
        } elseif($Pestana == 2){
            $arraydatos = $_SESSION['Capturas'];
        }

        $contenido = "";
    
        if (isset($arraydatos) && !empty($arraydatos)) {

            if($Pestana == 0){

                $contenido = "<section style='border: none; box-shadow: none;'>"; // Elimina bordes y sombra
                $contenido .= "<div style='max-width: 700px; margin: auto;'>"; // Contenedor centrado y limitado
                $contenido .= "<table class='table table-borderless' style='width: 50%; margin: 0 auto;'>"; // Sin bordes y ancho del 50%
            }else{
                $contenido = "<section>";
                $contenido .= "<table class='table table-striped table-bordered-bottom'>";
            }
    
    
           // Lógica específica para usuarios
            if ($Pestana == 0){
                foreach ($arraydatos as $index => $usuario) {

                    $idUsuario = $usuario["IdUsuario"];
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';
                    $contenido .= "<div style='width: 50%; margin: 0 auto;'>"; // Contenedor para centrar y limitar el ancho
                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";
                    
                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'>" . ($tituloAlternativo ?? "Nombre: <strong>" . htmlspecialchars($usuario["NombreUsuario"]) . "</strong>") . "</h5>";
                    $contenido .= "<button type='submit' class='btn btn-primary btn-sm' onclick='dibuja_pagina([1, $idUsuario])' >Entrar</button>";

                    $contenido .= "</div>"; // cierre d-flex
                    
                    $contenido .= "</div>"; // cierre card
                    $contenido .= "</div>"; // cierre contenedor ancho limitado
                    
                }
            }
    
            // Lógica específica para barcos
            if ($Pestana == 1) {
                foreach ($arraydatos as $index => $barco) {
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';
                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";
    
                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'>" . ($tituloAlternativo ?? "ID Barco: " . htmlspecialchars($barco["IdBarco"])) . "</h5>";
                    $contenido .= "</div>";
    
                    $contenido .= "<div>Nombre: <strong>" . htmlspecialchars($barco["Nombre"]) . "</strong></div>";
                    $contenido .= "<div>Código: <strong>" . htmlspecialchars($barco["CodigoBarco"]) . "</strong></div>";
                    $contenido .= "</div>";
                }
            }
    
            // Lógica específica para capturas
            elseif ($Pestana == 2) {
                foreach ($arraydatos as $index => $captura) {
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';
    
                    $TotalAlmacenes = safe_html($captura["CuentaAlmacen"] ?? null);
                    $temperaturaMaxima = safe_html($captura["TemperaturaMaxima"] ?? null);
                    $temperaturaMinima = safe_html($captura["TemperaturaMinima"] ?? null);
                    $fechaUltimaTemperatura = safe_html($captura["FechaUltimoAlmacen"] ?? null);
                    $idUltimoAlmacen = safe_html($captura["IdTipoAlmacen"] ?? null);
                    $tipoUltimoAlmacen = safe_html($captura["TipoAlmacen"] ?? null);
                    $ZonaCaptura = safe_html($captura["Zona"] ?? null);
                    $EspecieCapturada = safe_html($captura["Especie"] ?? null);
                    $FechaCaptura = safe_html($captura["FechaCaptura"] ?? null);
                    $NombreBarcoCaptura = safe_html($captura["NombreBarco"] ?? null);
                    $tagPez = htmlspecialchars($captura["TagPez"]);
    
                    $claseTemperaturaMaxima = ($temperaturaMaxima > 4) ? "text-danger" : "text-success";
                    $claseTemperaturaMinima = ($temperaturaMinima > 4) ? "text-danger" : "text-success";
                    $claseFecha = ($temperaturaMaxima > 4) ? "text-danger" : "text-success";
    
                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";
    
                    $contenido .= "<div class='d-flex mb-1' style='font-size: 0.825rem;'>";
    
                    $contenido .= "<div style='flex: 0 0 90%;'>";
                    $contenido .= "<h5 class='card-title mb-0' style='font-size: 1.125rem;'><strong>$tagPez</strong></h5>";
                    $contenido .= "<details style='cursor: pointer; padding-top: 5px;'>";
                    $contenido .= "<summary style='font-size: 1.2rem; font-weight: bold; padding-left: 0;'></summary>";
    
                    $contenido .= "<div class='pt-2' style='padding: 10px; font-size: 1.25em;'>";
                    $contenido .= "<div class='row g-3'>";
    
                    $contenido .= "<div class='col'>";
                    $contenido .= "<p><span class='text-black'>Captura: </span> <span class='font-weight-bold'><strong>$ZonaCaptura</strong></span></p>";
                    $contenido .= "<p><span class='text-black'>Especie: </span> <span class='font-weight-bold'><strong>$EspecieCapturada</strong></span></p>";
                    $contenido .= "<p><span class='text-black'>Barco: </span> <span class='font-weight-bold'><strong>$NombreBarcoCaptura</strong></span></p>";
                    $contenido .= "</div>";
    
                    $contenido .= "<div class='col'>";
                    $contenido .= "<p><span class='text-black'>Almacenes visitados: </span> <span class='font-weight-bold'><strong>$TotalAlmacenes</strong></span></p>";
                    $contenido .= "<p><span class='text-black'>Almacén: </span> <span class='font-weight-bold'><strong>$tipoUltimoAlmacen $idUltimoAlmacen</strong></span></p>";
                    $contenido .= "</div>";
    
                    $contenido .= "<div class='col'>";
                    if (!empty($fechaUltimaTemperatura)) {
                        $contenido .= "<p><span class='text-black'>Última Fecha: </span> <span class='font-weight-bold'><strong>" . date('d/m/Y H:i', strtotime($fechaUltimaTemperatura)) . "</strong></span></p>";
                    }
                    $contenido .= "<p><span class='text-black'>Temp: </span> <span class='$claseTemperaturaMinima'><span class='font-weight-bold'><strong>" . $temperaturaMinima . "°C</span></span><span> / </span> <span class='$claseTemperaturaMaxima'><span class='font-weight-bold'>" . $temperaturaMaxima . "°C</strong></span></span></p>";
                    $contenido .= "</div>";
    
                    $contenido .= "</div>"; // fin row
                    $contenido .= "</div>"; // fin contenido colapsable
                    $contenido .= "</details>"; // fin details
                    $contenido .= "</div>"; // fin columna izquierda
    
                    $contenido .= "<div class='d-flex flex-column align-items-end' style='flex: 0 0 10%;'>";
                    $contenido .= "<div class='$claseFecha mb-1' style='font-size: 1.125rem; text-align: right;'><strong>" . date('d/m/Y H:i', strtotime($FechaCaptura)) . "</strong></div>";
    
                    $contenido .= "<div style='width: fit-content; margin: 0 auto;'>";
                    $contenido .= "<a title='Ver detalles completos' onclick='dibuja_pagina([3])'>";
                    $contenido .= "<img src='Img/DetallesCaptura.png' alt='Ver detalles' style='width: 40px; height: 27px; cursor: pointer; border: none; display: block;'>";
                    $contenido .= "</a>";
                    $contenido .= "</div>";
    
                    $contenido .= "</div>"; // fin columna derecha
                    $contenido .= "</div>"; // fin cabecera
                    $contenido .= "</div>"; // fin tarjeta
                }
            }
    
            elseif ($Pestana == 3) {
                foreach ($arraydatos as $index => $barcos) {
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';
                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";
    
                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'>" . ($tituloAlternativo ?? "ID Barco: " . htmlspecialchars($barcos["IdBarco"])) . "</h5>";
                    $contenido .= "</div>";
    
                    $contenido .= "<div><strong>Nombre:</strong> " . htmlspecialchars($barcos["Nombre"]) . "</div>";
                    $contenido .= "<div><strong>Código:</strong> " . htmlspecialchars($barcos["CodigoBarco"]) . "</div>";
                    $contenido .= "</div>";
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
