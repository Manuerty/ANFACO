<?php

    function pinta_contenido($estado){
        $titulo = ""; 
        $cabecera = "../html/header.html";
        $fileheadertext = "";
        $OcultarCabecera = 0;
        
        switch($estado){

            case 0:
                $cabecera = "";
                $filename = "../html/login.html";
                break;
            case 0.5:
                $titulo = "Usuarios";
                $filename = "../html/documentos.html";
                $OcultarCabecera = 1;
                break;
            case 1:
                $titulo = $_SESSION["Controlador"] -> miEstado -> nombreUsuario;
                $filename = "../html/dashboard.html";
                $OcultarCabecera = 2;
                break;
            case 1.5:
                $titulo = $_SESSION["Controlador"] -> miEstado -> nombreUsuario;
                $filename = "../html/documentos.html";
                $OcultarCabecera = 2;
                break;
            case 2:
                $titulo = "Barcos de " .$_SESSION["Controlador"] -> miEstado -> nombreUsuario;
                $filename = "../html/documentos.html";
                $OcultarCabecera = 1;
                break;
            case 3:
                $titulo = "Pescado de " .$_SESSION["Controlador"] -> miEstado -> nombreUsuario;
                $filename = "../html/documentos.html";
                break;
            case 4:
                $titulo = "Detalles de la Captura";
                $filename = "../html/documentos.html";
                $OcultarCabecera = 2;
                break;
        }

        if ($cabecera != ""){

            $fileheader = fopen($cabecera, "r");
            $filesize = filesize($cabecera);
            $fileheadertext = fread($fileheader, length: $filesize);
            fclose($fileheader);

            $fileheadertext = str_replace("%NombreE%",$titulo,$fileheadertext);


            if ($OcultarCabecera == 1){
                $fileheadertext = str_replace("<button id=\"LupaHeader2\" class=\"btn position-absolute end-0  h-100\" style=\"border: none; background: transparent; padding-right: 10px;\" >",
                "<button id=\"LupaHeader2\" class=\"btn position-absolute end-0  h-100\" style=\"border: none; background: transparent; padding-right: 10px; display: none;\" >", $fileheadertext);
            }
            elseif ($OcultarCabecera == 2){
                $fileheadertext = str_replace("<button id=\"LupaHeader\" class=\"col-2 flecha_volver %accionBuscarHeader%\" onclick=\"abrirTxtBoxBuscar()\">",
                '<button id="LupaHeader" class="col-2 flecha_volver %accionBuscarHeader%" onclick="abrirTxtBoxBuscar()" style="display: none;">', $fileheadertext);
            }
            
            if ($_SESSION["Controlador"] -> miEstado -> EstadosAnteriores[0] === 1.5){
                $fileheadertext = str_replace("<button id=\"LupaHeader2\" class=\"btn position-absolute end-0  h-100\" style=\"border: none; background: transparent; padding-right: 10px;\" >",
                "<button id=\"LupaHeader2\" class=\"btn position-absolute end-0  h-100\" style=\"border: none; background: transparent; padding-right: 10px; display: none;\" >", $fileheadertext);
                $fileheadertext = str_replace("<button id=\"CruzHeader\" class=\"col-2 flecha_volver d-none\">",
                "<button id=\"CruzHeader\" class=\"col-2 flecha_volver d-none\" style=\"display: none;\">", $fileheadertext);
                $fileheadertext = str_replace('<img src="Img/IconosAcciones/Lupa.png" width="20px" onclick="aplicaFiltrado(null, \'header\')">',
                "<img src=\"Img/IconosAcciones/Lupa.png\" width=\"20px\" onclick=\"aplicaFiltrado(null, 'tagPez')\">", $fileheadertext);
                $fileheadertext = str_replace("TxtBoxInputBuscarHeader","TxtBoxInputTagPez", $fileheadertext);
            }

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
            $filetext = str_replace('<button id="toggleAllFilters" class="btn btn-outline-info btn-sm" style="display: none;">','<button id="toggleAllFilters" class="btn btn-outline-info btn-sm">', $filetext);
        }
        elseif($_SESSION["Controlador"] -> miEstado -> Estado == 4){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(3),$filetext);
        }

       /*  if ($_SESSION["Controlador"] -> miEstado -> Estado == 0.5 || $_SESSION["Controlador"] -> miEstado -> Estado == 2 && $_SESSION["Controlador"] -> miEstado -> esAdmin == true){ */
        if ($_SESSION["Controlador"] -> miEstado -> Estado == 0.5  && $_SESSION["Controlador"] -> miEstado -> esAdmin == true){

            $filetext = str_replace('<button id="BtnAnadir" onclick="" class="btn_acciones mb-4" style="display: none;">', '<button id="BtnAnadir" onclick="mostrarModalFormulario()" class="btn_acciones mb-4">', $filetext);
            $filetext .= cargaModal();
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


    function cargaModal(){


        if(!isset($_SESSION["PlantillaModalForm"])){
            $fileModal = fopen('../html/PlantillaModalFormulario.html', "r");
            $filesizeModal = filesize('../html/PlantillaModalFormulario.html');
            $_SESSION["PlantillaModalForm"] = fread($fileModal, $filesizeModal);
            fclose($fileModal);
        }
        $PlantillaModal = $_SESSION["PlantillaModalForm"];

        //Creación de usuario//
        if ($_SESSION["Controlador"] -> miEstado -> Estado == 0.5){
            $PlantillaModal = str_replace("%TituloModal%","Creación de Usuario",$PlantillaModal);

            $input = "<label for='TxtBoxInputNombreUsuario'>Nombre de usuario</label>";
            $input .= "<input type='text' class='form-control my-2' id='TxtBoxInputNombreUsuario' placeholder='Nombre de usuario' required>";

            $input .= "<label for='TxtBoxContraseña'>Contraseña</label>";
            $input .= "<input type='password' class='form-control my-2' id='TxtBoxContraseña' placeholder='Contraseña' required>";
            $input .= "<input type='password' class='form-control my-2' id='TxtBoxConfirmarContraseña' placeholder='Confirmar contraseña' required>";

            // Checkbox para mostrar/ocultar contraseña
            $input .= "<div class='form-check my-2'>";
            $input .= "<input type='checkbox' class='form-check-input' id='MostrarContraseñas'  onclick=\"document.getElementById('TxtBoxContraseña').type = this.checked ? 'text' : 'password'; document.getElementById('TxtBoxConfirmarContraseña').type = this.checked ? 'text' : 'password';\">";
            $input .= "<label class='form-check-label' for='MostrarContraseñas'>Mostrar contraseñas</label>";
            $input .= "</div>";

            $input .= "<label for='SelectRol'>Rol</label>";
            $input .= "<select class='form-control my-2' id='SelectRol' required>
                        <option value='' disabled selected>Seleccione un rol</option>
                        <option value='Administrador'>Administrador</option>
                        <option value='Armador'>Armador</option>
                        <option value='Conservero'>Conservero</option>
                    </select>";


        }



        return str_replace("%BodyModal%",$input,$PlantillaModal);
        
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
            if (empty($_SESSION["Controlador"]->miEstado->barcosFiltrados)) {
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
                    $NombreUsuario = $usuario["NombreUsuario"];
                    $tipoUsuario = $usuario["Rol"];
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';

                    

                    $contenido .= "<div style='width: 70%; margin: 0 auto;'>"; // Contenedor ahora al 70%
                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";
            
                    $contenido .= "<div class='row align-items-center'>"; // Usamos row de Bootstrap
            
                    // Nombre de usuario
                    $contenido .= "<div class='col-5'>";
                    $contenido .= "<p class='card-title mb-0'>" . ($tituloAlternativo ?? "<strong>" . htmlspecialchars($usuario["NombreUsuario"]) . "</strong>") . "</p>";
                    $contenido .= "</div>";
            
                    // Rol de usuario
                    $contenido .= "<div class='col-4'>";
                    $contenido .= "<p class='card-text mb-0' style='font-size: 0.825rem;'>" . htmlspecialchars($tipoUsuario) . "</p>";
                    $contenido .= "</div>";
            
                    // Botón
                    $contenido .= "<div class='col-3 text-end'>";
                    if ($tipoUsuario == "Conservero") {
                        $contenido .= "<button type='submit' class='btn btn-primary btn-sm' onclick='dibuja_pagina([1.5, $idUsuario,".'"'.$NombreUsuario.'"'."  ])'>Entrar</button>";
                    } else {
                        $contenido .= "<button type='submit' class='btn btn-primary btn-sm' onclick='dibuja_pagina([1, $idUsuario,".'"'.$NombreUsuario.'"'." ])'>Entrar</button>";
                    }

                    $contenido .= "</div>";
            
                    $contenido .= "</div>"; // cierre row
                    
                    $contenido .= "</div>"; // cierre card
                    $contenido .= "</div>"; // cierre contenedor ancho limitado
                    
                }
            }

            // Lógica para buscador de conserveros

            if ($Pestana == 0.5) {
                $contenido = "";

                $contenido .= "<div class='d-flex justify-content-center align-items-center' style='min-height: 100vh;'>";
                $contenido .= "<div class='card p-3 border shadow-sm mb-3' style='background-color: white; width: 100%; max-width: 500px;'>"; // max-width para limitar el tamaño
                $contenido .= "<div class='d-flex align-items-center'>";
                $contenido .= "<input type='text' id='TxtBoxInputTagPez' class='form-control me-2' placeholder='Buscar por tag de pez' style='max-width: 200px;'>";
                $contenido .= "<button class='btn btn-primary' onclick='aplicaFiltrado(null ,".'"'.'tagPez'.'"'.") '>Buscar</button>";
                $contenido .= "</div>";
                $contenido .= "</div>";
                $contenido .= "</div>";

            }
    
            // Lógica específica para barcos
            if ($Pestana == 1) {
                foreach ($arraydatos as $index => $barco) {
                    $nombreBarcoJS = json_encode($barco["Nombre"]); // Escapar correctamente para JavaScript
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';
                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";

                    $contenido .= "<div class='d-flex justify-content-between align-items-start mb-2'>";
                    $contenido .= "<h5 class='card-title mb-0'><strong>" . htmlspecialchars($barco["Nombre"]) . "</strong></h5>";
                    $contenido .= "<button class='btn btn-outline-primary btn-sm' onclick='capturasDelBarco($nombreBarcoJS)'>Capturas</button>";
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
                    $tipoUltimoAlmacen = safe_html($captura["TipoAlmacen"] ?? null);
                    $ZonaCaptura = safe_html($captura["Zona"] ?? null);
                    $EspecieCapturada = safe_html($captura["Especie"] ?? null);
                    $FechaCaptura = safe_html($captura["FechaCaptura"] ?? null);
                    $NombreBarcoCaptura = safe_html($captura["NombreBarco"] ?? null);
                    $tagPez = htmlspecialchars($captura["TagPez"]);
                    $NombreComprador = safe_html($captura["NombreComprador"] ?? null);
                    $Armador = safe_html($captura["Armador"] ?? null);
                    $refPez = $captura["TagPez"];
    
                    $claseTemperaturaMaxima = ($temperaturaMaxima > -18) ? "text-danger" : "text-success";
                    $claseTemperaturaMinima = ($temperaturaMinima > -18) ? "text-danger" : "text-success";
                    $claseFecha = ($temperaturaMaxima > -18) ? "text-danger" : "text-success";
    
                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";
    
                    $contenido .= "<div class='d-flex mb-1' style='font-size: 0.825rem;'>";
    
                    $contenido .= "<div style='flex: 0 0 90%;'>";
                    $contenido .= "<h5 class='card-title mb-0' style='font-size: 1.125rem;'><strong>$tagPez</strong> - $EspecieCapturada</h5>";
                    $contenido .= "<details style='cursor: pointer; padding-top: 5px;'>";
                    $contenido .= "<summary style='font-size: 1.2rem; font-weight: bold; padding-left: 0;'></summary>";
    
                    $contenido .= "<div class='pt-2' style='padding: 10px; font-size: 1.25em;'>";
                    $contenido .= "<div class='row g-3'>";
    
                    $contenido .= "<div class='col'>";
                    $contenido .= "<p><span class='text-black'>Zona: </span> <span class='font-weight-bold'><strong>$ZonaCaptura</strong></span></p>";
                    $contenido .= "<p><span class='text-black'>Barco: </span> <span class='font-weight-bold'><strong>$NombreBarcoCaptura</strong></span></p>";
                    $contenido .= "<p><span class='text-black'>Armador: </span> <span class='font-weight-bold'><strong>$Armador</strong></span></p>";
                    $contenido .= "</div>";
    
                    $contenido .= "<div class='col'>";
                    $contenido .= "<p><span class='text-black'>Almacenes visitados: </span> <span class='font-weight-bold'><strong>$TotalAlmacenes</strong></span></p>";
                    $contenido .= "<p><span class='text-black'>Almacén actual: </span> <span class='font-weight-bold'><strong>$tipoUltimoAlmacen </strong></span></p>";
                    if($NombreComprador != null && $_SESSION["Controlador"] -> miEstado -> EstadosAnteriores[0] != 0.5){
                        $contenido .= "<p><span class='text-black'>Comprador: </span> <span class='font-weight-bold'><strong>$NombreComprador</strong></span></p>";
                    }
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
                    $contenido .= "<div class='$claseFecha' style='font-size: 1.125rem; text-align: right; white-space: nowrap; margin-bottom: 0.3rem;'>" . date('d/m/Y H:i', strtotime($FechaCaptura)) . "</div>";

                    $contenido .= "<div style='width: fit-content; margin: 0 auto;'>";
                    $contenido .= "<a title='Ver detalles completos' onclick='dibuja_pagina([3,".'"'.$refPez.'"'." ])'>";
                    $contenido .= "<img src='Img/DetallesCaptura.png' alt='Ver detalles' style='width: 40px; height: 27px; cursor: pointer; border: none; display: block;'>";
                    $contenido .= "</a>";
                    $contenido .= "</div>";

                    $contenido .= "</div>"; // fin columna derecha
                    $contenido .= "</div>"; // fin cabecera
                    $contenido .= "</div>"; // fin tarjeta
                }
                if(count($arraydatos) > $_SESSION["Controlador"] -> miEstado -> LimiteFilas){
                    $contenido .= "<div class='text-center'>";
                    $contenido .= "<button class='btn btn-primary' onclick='dibuja_pagina([])'>Ver más</button>";
                    $contenido .= "</div>";
                }
            }

            elseif ($Pestana == 3) {

                $coloresPredefinidos = [
                    '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd',
                    '#8c564b', '#e377c2', '#7f7f7f', '#bcbd22', '#17becf',
                    '#393b79', '#637939', '#8c6d31', '#843c39', '#7b4173'
                ];

                
               // 1. Obtener índices de los almacenes que no son "Bodega", en orden invertido
                $indicesValidos = [];
                foreach ($arraydatosAdiccional as $i => $almacen) {
                    if (($almacen['NombreTipo'] ?? '') !== 'Bodega') {
                        $indicesValidos[] = $i;
                    }
                }
                $indicesValidos = array_reverse($indicesValidos); // Orden invertido

                // 2. Mapear colores basados en consecutividad de tipos
                $mapaColores = [];
                $colorPos = 0;
                $prevTipo = null;

                foreach ($indicesValidos as $index) {
                    $tipoActual = $arraydatosAdiccional[$index]['IdTipo'] ?? null;

                    if ($tipoActual !== $prevTipo && $prevTipo !== null) {
                        $colorPos++;
                    }

                    $mapaColores[$index] = $coloresPredefinidos[$colorPos % count($coloresPredefinidos)];
                    $prevTipo = $tipoActual;
                }


            
                // Variables de captura
                $tagPez = htmlspecialchars($capturaDetalle["TagPez"] ?? '');
                $ZonaCaptura = safe_html($capturaDetalle["Zona"] ?? '');
                $EspecieCapturada = safe_html($capturaDetalle["Especie"] ?? '');
                $FechaCaptura = safe_html($capturaDetalle["FechaCaptura"] ?? '');
                $NombreBarcoCaptura = safe_html($capturaDetalle["NombreBarco"] ?? '');
                $temperaturaMaximaCaptura = safe_html($capturaDetalle["TemperaturaMaxima"] ?? '');
                $temperaturaMinimaCaptura = safe_html($capturaDetalle["TemperaturaMinima"] ?? '');
                $tipoUltimoAlmacenCaptura = safe_html($capturaDetalle["TipoAlmacen"] ?? '');
                $Armador = safe_html($capturaDetalle["Armador"] ?? '');
                $NombreComprador = safe_html($capturaDetalle["NombreComprador"] ?? '');
            
                $claseTemperaturaMaxima = ($temperaturaMaximaCaptura > -18) ? "text-danger" : "text-success";
                $claseTemperaturaMinima = ($temperaturaMinimaCaptura > -18) ? "text-danger" : "text-success";
            
                $contenido = "<div class='container-fluid' style='display: flex; height: 77.5vh;'>";
                $contenido .= "<div class='row' style='width: 100%; flex-grow: 1;'>";
            
                // Columna izquierda
                $contenido .= "<div class='col-md-4' style='display: flex; flex-direction: column; height: 100%;'>";
                $contenido .= "<div class='card p-3 border shadow-sm mb-3' style='flex-shrink: 0; height:100%'>";
                $contenido .= "<h5><strong> $tagPez - $EspecieCapturada</strong></h5>";
                $contenido .= "<p>Fecha de Captura: <strong>" . date('d/m/Y H:i', strtotime($FechaCaptura)) . "</strong></p>";
                $contenido .= "<p>Barco:<strong> $NombreBarcoCaptura</strong></p>";
                $contenido .= "<p>Zona:<strong> $ZonaCaptura</strong></p>";
                $contenido .= "<p><span class='text-black'>Temp: </span> <span class='$claseTemperaturaMinima'><strong>" . $temperaturaMinimaCaptura . "°C</strong></span><span> / </span> <span class='$claseTemperaturaMaxima'><strong>" . $temperaturaMaximaCaptura . "°C</strong></span></p>";
                $contenido .= "<p><span class='text-black'>Almacén Actual: </span> <strong>$tipoUltimoAlmacenCaptura</strong></p>";
                $contenido .= "<p><span class='text-black'>Armador del Barco: </span> <strong>$Armador</strong></p>";
                if($NombreComprador != null){
                    $contenido .= "<p><span class='text-black'>Conservero: </span> <strong>$NombreComprador</strong></p>";
                }
                $contenido .= "</div>";
                $contenido .= "</div>";
            
                // Columna derecha
                $contenido .= "<div class='col-md-8' style='display: flex; flex-direction: column; height: 100%;'>";
                $contenido .= "<div class='card p-3 border shadow-sm mb-3 d-flex justify-content-between align-items-center' style='flex-shrink: 0; max-width: 950px;' id='contenedor-grafica'>";
                $contenido .= "<div class='d-flex justify-content-between w-100 align-items-center'>";
                $contenido .= "<h5 class='card-title mb-0'>Gráfico de Temperatura</h5>";
                $contenido .= "<button onclick='filtrarAlmacen()' class='btn btn-secondary btn-sm'>Resetear Grafica</button>";
                $contenido .= "</div>";
                $contenido .= "<canvas id='graficaTemperatura' width='950' height='300' style='display: block; max-height: 300px;'></canvas>";
                $contenido .= "</div>";
                $contenido .= "<script>dibuja_pagina([4]);</script>";
            
                // Lista de almacenes
                $contenido .= "<div class='card p-3 border shadow-sm' style='flex-grow: 1; overflow-y: auto;'>";
                $contenido .= "<h5 class='card-title'>Almacenes Visitados</h5>";
                $contenido .= "<div style='flex-grow: 1; overflow-y: auto;'>";
            
                foreach ($arraydatosAdiccional as $index => $Almacenes) {

                
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';

                    $NombreAlmacen = $Almacenes['NombreTipo'] ?? '';
                    $idAlmacen = $Almacenes['IdAlmacen'] ?? '';
                    $ReferenciaAlmacen = $NombreAlmacen;
                    $FechaAlmacen = $Almacenes['FechaAlmacen'] ?? '';
                    $esBodegaDelBarco = ($NombreAlmacen == 'Bodega');
                    $Comprador = $Almacenes['Comprador'] ?? null;
                    $colorTexto = isset($mapaColores[$index]) ? $mapaColores[$index] : '#000';
            
                    $contenido .= "<div class='card p-2 border shadow-sm mb-2' style='$backgroundColor'>";
                    $contenido .= "<table class='table table-borderless mb-0' style='table-layout: fixed; width: 100%;'>";
                    $contenido .= "<tr>";
                    $contenido .= "<td style='text-align: center; vertical-align: middle; color: $colorTexto;'>$ReferenciaAlmacen</td>";
                    if($Comprador != null || $Comprador != ""){
                        $NombreComprador = $Comprador;
                    }
                    else{
                        $NombreComprador = "";
                    }
                    $contenido .= "<td style='text-align: center; vertical-align: middle; ;'>$NombreComprador</td>";
                    $contenido .= "<td style='text-align: center; vertical-align: middle;'>$FechaAlmacen</td>";
            
                    if ($esBodegaDelBarco) {
                        $contenido .= "<td style='text-align: center; vertical-align: middle;'>Bodega</td>";
                    } else {
                        $contenido .= "<td style='text-align: center; vertical-align: middle;'>
                                        <button onclick='filtrarAlmacen(\"$NombreAlmacen\" + "." \"$idAlmacen\" )' class='btn btn-primary'>Mostrar</button>
                                    </td>";
                    }
            
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
