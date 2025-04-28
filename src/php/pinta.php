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
                $titulo = $_SESSION["Controlador"] -> miEstado -> nombreUsuario;
                $filename = "../html/dashboard.html";
                break;
            case 1.5:
                $titulo = "Cliente";
                $filename = "../html/documentos.html";
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

        if(in_array($_SESSION["Controlador"] -> miEstado -> Estado, [0.5, 3])){
            if ($_SESSION["Controlador"] -> miEstado -> Estado == 0.5){
                $filetext = str_replace('<span id="filtros_dinamicos">',cargaFiltros(),$filetext);
                $filetext = str_replace('%FuncionFiltrar%','aplicafiltros()',$filetext);
            }
        }
        if($_SESSION["Controlador"] -> miEstado -> Estado == 0.5){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(0),$filetext);
        }
        if($_SESSION["Controlador"] -> miEstado -> Estado == 1.5){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(0.5),$filetext);
        }
        elseif($_SESSION["Controlador"] -> miEstado -> Estado == 2){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(1),$filetext);
        }
        elseif($_SESSION["Controlador"] -> miEstado -> Estado == 3){
            $filetext = str_replace(["%LineasE%","%DropdownBarcos%", "%DropdownEspecie%", "%DropdownZonas%"], [DibujaTablaGenerica(2), dibujaOpciones(1), dibujaOpciones(2), dibujaOpciones(3)],$filetext);
        }
        elseif($_SESSION["Controlador"] -> miEstado -> Estado == 4){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(3),$filetext);
        }
        return $filetext;
    }

    function cargaFiltros(){
        $arrayFiltros = array();
        $tipoDocf = 0;
        switch($_SESSION["Controlador"] -> miEstado -> Estado){
            case 0:
                $tipoDocf = 0;
                break;
            case 1:
                $tipoDocf = 1;
                break;
            case 2:
                $tipoDocf = 2;
                break;
            case 3:
                $tipoDocf = 3;
                break;
        }

        $arrayFiltros = array_filter($_SESSION["Controlador"] -> miEstado -> FiltrosDoc, function($docF) use($tipoDocf){
            return $docF["tipo"] == $tipoDocf;
        });

        $arrayFiltros = array_values($arrayFiltros);
        $txt_filtros = "<span id='filtros_dinamicos'>";
        if(count($arrayFiltros)>0){
            foreach($arrayFiltros as $valor){ 
                $txt_filtros .= '<button onclick="aplicafiltros('."'".$valor["Estado"]."'".')" style="color:black;" class="dropdown-item" id="'.$valor["Estado"].'" >'.$valor["Filtro"].'</button>';
            }
        }
        return $txt_filtros;
    }


     function dibujaOpciones($tab = 0){

        $contenido = "";

        
        $arrayDoc = $_SESSION["Controlador"]->miEstado->capturas;
        

        if ($tab == 1) {

            $arrayDoc = array_values($arrayDoc); // Asegura índices consecutivos
        
            // Extraer especies únicas
            $barcosUnicos = array_unique(array_column($arrayDoc, 'NombreBarco'));
            sort($barcosUnicos); // Ordenar alfabéticamente
        
            foreach ($barcosUnicos as $barco) {
                $contenido .= '<option value="' . htmlspecialchars($barco) . '">' . htmlspecialchars($barco) . '</option>';
            }
        }
        

        if ($tab == 2) {

            
            $arrayDoc = array_values($arrayDoc); // Asegura índices consecutivos
        
            // Extraer especies únicas
            $especiesUnicas = array_unique(array_column($arrayDoc, 'Especie'));
            sort($especiesUnicas); // Ordenar alfabéticamente
        
            foreach ($especiesUnicas as $especie) {
                $contenido .= '<option value="' . htmlspecialchars($especie) . '">' . htmlspecialchars($especie) . '</option>';
            }
        }

        if ($tab == 3){
            
            $arrayDoc = array_values($arrayDoc); // Asegura índices consecutivos
        
            // Extraer especies únicas
            $especiesUnicas = array_unique(array_column($arrayDoc, 'Zona'));
            sort($especiesUnicas); // Ordenar alfabéticamente
        
            foreach ($especiesUnicas as $especie) {
                $contenido .= '<option value="' . htmlspecialchars($especie) . '">' . htmlspecialchars($especie) . '</option>';
            }


        }

        return $contenido;
    } 


    function DibujaTablaGenerica($Pestana, $tituloAlternativo = null) {
        
        //Ventana de usuarios//
        if ($Pestana == 0) {
            // Si no hay usuarios filtrados (no existe o es null), se muestran todos los usuarios
            if (!isset($_SESSION["Controlador"]->miEstado->usuariosFiltrados) || 
                $_SESSION["Controlador"]->miEstado->usuariosFiltrados === null) {

                $arraydatos = $_SESSION["Controlador"]->miEstado->usuarios;

            } else {
                $arraydatos = $_SESSION["Controlador"]->miEstado->usuariosFiltrados;
            }
        }

        //Ventana de Conservero//
        if ($Pestana == 0.5) {
            
            $arraydatos = ["conservero", "tipo"];
        }

        //Ventana de barcos//
        elseif ($Pestana == 1) {
            // Si no hay barcos filtrados, se muestran todos los barcos
            if (!isset($_SESSION["Controlador"]->miEstado->barcosFiltrados) || 
                $_SESSION["Controlador"]->miEstado->barcosFiltrados === null) {

                $arraydatos = $_SESSION["Controlador"]->miEstado->barcos;

            } else {
                $arraydatos = $_SESSION["Controlador"]->miEstado->barcosFiltrados;
            }
        }

        //Ventana de capturas//
        elseif ($Pestana == 2) {
            // Si no hay capturas filtradas, se muestran todas las capturas
            if (!isset($_SESSION["Controlador"]->miEstado->capturasFiltradas) || 
                $_SESSION["Controlador"]->miEstado->capturasFiltradas === null) {

                $arraydatos = $_SESSION["Controlador"]->miEstado->capturas;

            } else {
                $arraydatos = $_SESSION["Controlador"]->miEstado->capturasFiltradas;
            }
        }

        //Ventana de detalles de captura//
        elseif ($Pestana == 3) {
            // Si no hay temperaturas, inicializa con array [0]
            if (empty($_SESSION['Controlador']->miEstado->temperaturas)) {
                $arraydatos = [0];
            } else {
                $arraydatos = $_SESSION['Controlador']->miEstado->temperaturas;
            }

            // Lógica de filtrado para almacenes
            if (!isset($_SESSION["Controlador"]->miEstado->almacenesFiltrados) || 
                $_SESSION["Controlador"]->miEstado->almacenesFiltrados === null) {

                $arraydatosAdiccional = $_SESSION["Controlador"]->miEstado->almacenes;

            } else {
                $arraydatosAdiccional = $_SESSION["Controlador"]->miEstado->almacenesFiltrados;
            }

            // Captura detalle siempre asignado
            $capturaDetalle = $_SESSION['Controlador']->miEstado->capturaDetalle;
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
    
    
            if ($Pestana == 0){
                foreach ($arraydatos as $index => $usuario) {
            
                    $idUsuario = $usuario["IdUsuario"];
                    $tipoUsuario = $usuario["Rol"];
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';
                    $contenido .= "<div style='width: 70%; margin: 0 auto;'>"; // Contenedor ahora al 70%
                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";
            
                    $contenido .= "<div class='row align-items-center'>"; // Usamos row de Bootstrap
            
                    // Nombre de usuario
                    $contenido .= "<div class='col-5'>";
                    $contenido .= "<h5 class='card-title mb-0'>" . ($tituloAlternativo ?? "<strong>" . htmlspecialchars($usuario["NombreUsuario"]) . "</strong>") . "</h5>";
                    $contenido .= "</div>";
            
                    // Rol de usuario
                    $contenido .= "<div class='col-4'>";
                    $contenido .= "<p class='card-text mb-0' style='font-size: 0.825rem;'>" . htmlspecialchars($tipoUsuario) . "</p>";
                    $contenido .= "</div>";
            
                    // Botón
                    $contenido .= "<div class='col-3 text-end'>";
                    if ($tipoUsuario == "Conservero") {
                        $contenido .= "<button type='submit' class='btn btn-primary btn-sm' onclick='dibuja_pagina([1.5, $idUsuario ])'>Entrar</button>";
                    } else {
                        $contenido .= "<button type='submit' class='btn btn-primary btn-sm' onclick='dibuja_pagina([1, $idUsuario ])'>Entrar</button>";
                    }

                    $contenido .= "</div>";
            
                    $contenido .= "</div>"; // cierre row
                    
                    $contenido .= "</div>"; // cierre card
                    $contenido .= "</div>"; // cierre contenedor ancho limitado
                    
                }
            }

            //Logica para buscador de conserveros

            if($Pestana == 0.5) {
                $nombreConservero = "Nombre del Conservero"; // Puedes cambiarlo luego
                $dniConservero = "00000000H"; // Puedes cambiarlo luego

                $contenido = "";

                // Primer div: Datos del conservero
                $contenido .= "<div class='card p-3 border shadow-sm mb-3' style='background-color: whitesmoke;'>";
                $contenido .= "<h5 class='card-title mb-2'><strong>" . htmlspecialchars($nombreConservero) . "</strong></h5>";
                $contenido .= "<div><span>Código: </span><strong>" . htmlspecialchars($dniConservero) . "</strong></div>";
                $contenido .= "</div>";

                // Segundo div: Formulario para buscar por tag
                $contenido .= "<div class='card p-3 border shadow-sm mb-3' style='background-color: white;'>";
                $contenido .= "<div class='d-flex align-items-center'>";
                $contenido .= "<input type='text' class='form-control me-2' placeholder='Buscar por tag de pez' style='max-width: 200px;'>";
                $contenido .= "<button class='btn btn-primary' onclick='dibuja_pagina([4])'>Buscar</button>";
                $contenido .= "</div>";
                $contenido .= "</div>";
            }
    
            // Lógica específica para barcos
            if ($Pestana == 1) {
                foreach ($arraydatos as $index => $barco) {
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';
                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";

                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'><strong>" . htmlspecialchars($barco["Nombre"]) . "</strong></h5>";
                    $contenido .= "</div>";

                    $contenido .= "<div><span>Código: </span><strong>" . htmlspecialchars($barco["CodigoBarco"]) . "</strong></div>";

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
                    $refPez = $captura["TagPez"];
    
                    $claseTemperaturaMaxima = ($temperaturaMaxima > 4) ? "text-danger" : "text-success";
                    $claseTemperaturaMinima = ($temperaturaMinima > 4) ? "text-danger" : "text-success";
                    $claseFecha = ($temperaturaMaxima > 4) ? "text-danger" : "text-success";
    
                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";
    
                    $contenido .= "<div class='d-flex mb-1' style='font-size: 0.825rem;'>";
    
                    $contenido .= "<div style='flex: 0 0 90%;'>";
                    $contenido .= "<h5 class='card-title mb-0' style='font-size: 1.125rem;'><strong>$tagPez - $EspecieCapturada</strong></h5>";
                    $contenido .= "<details style='cursor: pointer; padding-top: 5px;'>";
                    $contenido .= "<summary style='font-size: 1.2rem; font-weight: bold; padding-left: 0;'></summary>";
    
                    $contenido .= "<div class='pt-2' style='padding: 10px; font-size: 1.25em;'>";
                    $contenido .= "<div class='row g-3'>";
    
                    $contenido .= "<div class='col'>";
                    $contenido .= "<p><span class='text-black'>Captura: </span> <span class='font-weight-bold'><strong>$ZonaCaptura</strong></span></p>";
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
                    $contenido .= "<div class='$claseFecha' style='font-size: 1.125rem; text-align: right; white-space: nowrap; margin-bottom: 0.3rem;'><strong>" . date('d/m/Y H:i', strtotime($FechaCaptura)) . "</strong></div>";

                    $contenido .= "<div style='width: fit-content; margin: 0 auto;'>";
                    $contenido .= "<a title='Ver detalles completos' onclick='dibuja_pagina([3,".'"'.$refPez.'"'." ])'>";
                    $contenido .= "<img src='Img/DetallesCaptura.png' alt='Ver detalles' style='width: 40px; height: 27px; cursor: pointer; border: none; display: block;'>";
                    $contenido .= "</a>";
                    $contenido .= "</div>";

                    $contenido .= "</div>"; // fin columna derecha
                    $contenido .= "</div>"; // fin cabecera
                    $contenido .= "</div>"; // fin tarjeta
                }
            }

            elseif ($Pestana == 3) {

                
                $tagPez = htmlspecialchars($capturaDetalle["TagPez"] ?? '');
                $ZonaCaptura = safe_html($capturaDetalle["Zona"] ?? '');
                $EspecieCapturada = safe_html($capturaDetalle["Especie"] ?? '');
                $FechaCaptura = safe_html($capturaDetalle["FechaCaptura"] ?? '');
                $NombreBarcoCaptura = safe_html($capturaDetalle["NombreBarco"] ?? '');
                $temperaturaMaximaCaptura = safe_html($capturaDetalle["TemperaturaMaxima"] ?? '');
                $temperaturaMinimaCaptura = safe_html($capturaDetalle["TemperaturaMinima"] ?? '');
                $idUltimoAlmacenCaptura = safe_html($capturaDetalle["IdTipoAlmacen"] ?? '');
                $tipoUltimoAlmacenCaptura = safe_html($capturaDetalle["TipoAlmacen"] ?? '');
        
                $claseTemperaturaMaxima = ($temperaturaMaximaCaptura > 4) ? "text-danger" : "text-success";
                $claseTemperaturaMinima = ($temperaturaMinimaCaptura > 4) ? "text-danger" : "text-success";

                
                $contenido = "<div class='container-fluid' style='display: flex; height: 77.5vh;'>"; // Cambié para usar Flexbox
                $contenido .= "<div class='row' style='width: 100%; flex-grow: 1;'>";

                // Columna izquierda (datos generales de la captura)
                $contenido .= "<div class='col-md-4' style='display: flex; flex-direction: column; height: 100%;'>"; 
                $contenido .= "<div class='card p-3 border shadow-sm mb-3' style='flex-shrink: 0; height:100%'>"; // Tarjeta con tamaño fijo
                $contenido .= "<h5><strong> $tagPez - $EspecieCapturada</strong></h5>";
                $contenido .= "<p>Fecha de Captura: <strong>" . date('d/m/Y H:i', strtotime($FechaCaptura)) . "</strong></p>";
                $contenido .= "<p>Barco:<strong> $NombreBarcoCaptura</strong></p>";
                $contenido .= "<p>Zona:<strong> $ZonaCaptura</strong></p>";
                
                $contenido .= "<p><span class='text-black'>Temp: </span> <span class='$claseTemperaturaMinima'><span class='font-weight-bold'><strong>" . $temperaturaMinimaCaptura . "°C</span></span><span> / </span> <span class='$claseTemperaturaMaxima'><span class='font-weight-bold'>" . $temperaturaMaximaCaptura . "°C</strong></span></span></p>";
                $contenido .= "<p><span class='text-black'>Almacén Actual: </span> <span class='font-weight-bold'><strong>$tipoUltimoAlmacenCaptura $idUltimoAlmacenCaptura</strong></span></p>";
                $contenido .= "</div>";
                $contenido .= "</div>"; // fin col izquierda

                // Columna derecha (gráfica y lista de almacenes)
                $contenido .= "<div class='col-md-8' style='display: flex; flex-direction: column; height: 100%;'>";

                // Parte superior: gráfico de temperatura
                $contenido .= "<div class='card p-3 border shadow-sm mb-3' style='flex-shrink: 0; max-width: 950px;' id='contenedor-grafica'>";
                $contenido .= "<h5 class='card-title'>Gráfico de Temperatura</h5>";
                $contenido .= "<canvas id='graficaTemperatura' width='950' height='300' style='display: block; max-height: 300px;'></canvas>";
                $contenido .= "</div>";

                // Script para cargar automáticamente la gráfica
                $contenido .= "<script>dibuja_pagina([4]);</script>";

                // Parte inferior: listado de almacenes
                $contenido .= "<div class='card p-3 border shadow-sm' style='flex-grow: 1; overflow-y: auto;'>"; // Lista de almacenes ocupa el resto de la columna
                $contenido .= "<h5 class='card-title'>Almacenes Visitados</h5>";
                $contenido .= "<div  style='flex-grow: 1; overflow-y: auto;'>";

                foreach ($arraydatosAdiccional as $index => $Almacenes) {
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';
                    
                    $IdTipoAlmacen = $Almacenes['IdTipo'] ?? '';
                    $NombreAlmacen = $Almacenes['NombreTipo'] ?? '';
                    $ReferenciaAlmacen = $NombreAlmacen  . " " . $IdTipoAlmacen ;
                    $FechaAlmacen = $Almacenes['FechaAlmacen'] ?? '';
                    $Lector = $Almacenes['Lector'] ?? '';
                
                    $contenido .= "<div class='card p-2 border shadow-sm mb-2' style='$backgroundColor'>";
                    $contenido .= "<table class='table table-borderless mb-0' style='table-layout: fixed; width: 100%;'>";
                    $contenido .= "<tr>";
                    $contenido .= "<td style='text-align: center; vertical-align: middle;'>$ReferenciaAlmacen</td>";
                    $contenido .= "<td style='text-align: center; vertical-align: middle;'>$FechaAlmacen</td>";
                    $contenido .= "<td style='text-align: center; vertical-align: middle;'>$Lector</td>";
                    $contenido .= "</tr>";
                    $contenido .= "</table>";
                    $contenido .= "</div>";
                }
                
                $contenido .= "</div>"; // fin tarjeta almacenes
                $contenido .= "</div>"; // fin tarjeta almacenes
                $contenido .= "</div>"; // fin col derecha

                $contenido .= "</div>"; // fin row
                $contenido .= "</div>"; // fin container-fluid
        
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
