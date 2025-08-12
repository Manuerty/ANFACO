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
            case 0.0625:
                $titulo = "Dashboard de Admin";
                $filename = "../html/dashboardAdmin.html";
                $OcultarCabecera = 2;
                break;
            case 0.125 :
                $titulo = $_SESSION["Controlador"] -> miEstado -> nombreUsuario;
                $filename = "../html/dashboardComercial.html";
                $OcultarCabecera = 2;
                break;
            case 0.25:
                if ($_SESSION["Controlador"] -> miEstado -> EstadosAnteriores[0] == 0.0625){
                    $titulo = "Lista de Almacenes";
                } else {
                    $titulo = "Almacenes de " . $_SESSION["Controlador"] -> miEstado -> nombreUsuario;
                }
                $filename = "../html/documentos.html";
                $OcultarCabecera = 1;
                break;
            case 0.5:
                $titulo = "Usuarios";
                $filename = "../html/documentos.html";
                $OcultarCabecera = 1;
                break;
            case 1:
                $titulo = $_SESSION["Controlador"] -> miEstado -> nombreUsuario;
                $filename = "../html/dashboardArmador.html";
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
            case 10.25:
                $titulo = "Sobre Nosotros";
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
        if($_SESSION["Controlador"] -> miEstado -> Estado == 0.25){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(0.25),$filetext);
            $filetext = str_replace('<button id="toggleAllFilters" class="btn btn-outline-info btn-sm" style="display: none;">','<button id="toggleAllFilters" class="btn btn-outline-info btn-sm">', $filetext);
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
            $filetext = str_replace(["%LineasE%","%DropdownBarcos%", "%DropdownEspecie%", "%DropdownZonas%", "%DropdownAlmacenes%"], [DibujaTablaGenerica(2), dibujaOpciones(1), dibujaOpciones(2), dibujaOpciones(3), dibujaOpciones(4)],$filetext);
            $filetext = str_replace('<button id="toggleAllFilters" class="btn btn-outline-info btn-sm" style="display: none;">','<button id="toggleAllFilters" class="btn btn-outline-info btn-sm">', $filetext);
        }
        elseif($_SESSION["Controlador"] -> miEstado -> Estado == 4){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica(3),$filetext);
        }
        elseif($_SESSION["Controlador"] -> miEstado -> Estado == 10.25){
            $filetext = str_replace("%LineasE%", sobreNosotros(), $filetext);
            $filetext = str_replace("%TituloModal%","Sobre Nosotros",$filetext);
            $filetext = str_replace("%TipoFormulario%", "sobre_nosotros", $filetext);
            $filetext = str_replace("%BodyModal%", "<p>Información sobre el proyecto y sus objetivos.</p>", $filetext);
        }

        /*  if ($_SESSION["Controlador"] -> miEstado -> Estado == 0.5 || $_SESSION["Controlador"] -> miEstado -> Estado == 2 && $_SESSION["Controlador"] -> miEstado -> esAdmin == true){ */
        if ($_SESSION["Controlador"] -> miEstado -> Estado == 0.25  && $_SESSION["Controlador"] -> miEstado -> esAdmin == true){

            $filetext = str_replace('<button id="BtnAnadir" onclick="" class="btn_acciones mb-4" style="display: none;">', '<button id="BtnAnadir" onclick="cargarModalFormularioDinamico()" class="btn_acciones mb-5">', $filetext);
            $filetext .= cargaModal();
        }

       /*  if ($_SESSION["Controlador"] -> miEstado -> Estado == 0.5 || $_SESSION["Controlador"] -> miEstado -> Estado == 2 && $_SESSION["Controlador"] -> miEstado -> esAdmin == true){ */
        if ($_SESSION["Controlador"] -> miEstado -> Estado == 0.5  && $_SESSION["Controlador"] -> miEstado -> esAdmin == true){

            $filetext = str_replace('<button id="BtnAnadir" onclick="" class="btn_acciones mb-4" style="display: none;">', '<button id="BtnAnadir" onclick="cargarModalFormularioDinamico()" class="btn_acciones mb-5">', $filetext);
            $filetext .= cargaModal();
        }

        /*  if ($_SESSION["Controlador"] -> miEstado -> Estado == 0.5 || $_SESSION["Controlador"] -> miEstado -> Estado == 2 && $_SESSION["Controlador"] -> miEstado -> esAdmin == true){ */
        if ($_SESSION["Controlador"] -> miEstado -> Estado == 2  && $_SESSION["Controlador"] -> miEstado -> esAdmin == true){

            $filetext = str_replace('<button id="BtnAnadir" onclick="" class="btn_acciones mb-4" style="display: none;">', '<button id="BtnAnadir" onclick="cargarModalFormularioDinamico()" class="btn_acciones mb-5">', $filetext);
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

        $input = "";
        $inputAux = "";


        if(!isset($_SESSION["PlantillaModalForm"])){
            $fileModal = fopen('../html/PlantillaModalFormulario.html', "r");
            $filesizeModal = filesize('../html/PlantillaModalFormulario.html');
            $_SESSION["PlantillaModalForm"] = fread($fileModal, $filesizeModal);
            fclose($fileModal);
        }
        $PlantillaModal = $_SESSION["PlantillaModalForm"];

        
        if(!isset($_SESSION["PlantillaModalFormAuxiliar"])){
            $fileModal = fopen('../html/PlantilaModalFormAuxiliar.html', "r");
            $filesizeModal = filesize('../html/PlantilaModalFormAuxiliar.html');
            $_SESSION["PlantillaModalFormAuxiliar"] = fread($fileModal, $filesizeModal);
            fclose($fileModal);
        }

        $PlantillaModalAux = $_SESSION["PlantillaModalFormAuxiliar"];
        


        // Modal para creación de almacén
        if ($_SESSION["Controlador"] -> miEstado -> Estado == 0.25){

            $PlantillaModal = str_replace("%TituloModal%","Creación de Almacén",$PlantillaModal);
            $PlantillaModal = str_replace("%TipoFormulario%", "crear_almacen", $PlantillaModal);
            
            $input .= "<input type='text' class='form-control my-2' id='TxtBoxInputNombreAlmacen' placeholder='Nombre del Almacen' required>";

            // Si es admin y está en pantalla de almacenes de admin
            if ($_SESSION["Controlador"] -> miEstado -> EstadosAnteriores[0] == 0.0625 && $_SESSION["Controlador"] -> miEstado -> esAdmin == true) {
                $input .= "<div class='input-group my-2'>";
                $input .= "  <select class='form-select border-end-0' id='SelectNombreUsuario' required>";
                $input .= "    <option value='' disabled selected>Seleccione el Usuario</option>";
                foreach ($_SESSION["Controlador"]->miEstado->usuarios as $usuario) {
                    $input .= "    <option value='" . htmlspecialchars($usuario["IdUsuario"]) . "'>" . htmlspecialchars($usuario["NombreUsuario"]) . "</option>";
                }
                $input .= "  </select>";
                $input .= "<button type='button' id='btnClearUsuario' class='btn btn-outline-secondary border border-start-0 reset-button'>&times;</button>";
                $input .= "</div>";
            }
            elseif ($_SESSION["Controlador"] -> miEstado -> esAdmin == true) {
                // Admin pero fuera de la pantalla de admin de almacenes
                $input .= "<select class='form-control my-2' id='SelectNombreUsuario' disabled required>";
                $input .= "<option selected value='" . htmlspecialchars($_SESSION["Controlador"] -> miEstado -> IdUsuarioSeleccionado) . "'>" . htmlspecialchars($_SESSION["Controlador"] -> miEstado -> nombreUsuario) . "</option>";
                $input .= "</select>";
            }
            else {
                // No admin
                $input .= "<select class='form-control my-2' id='SelectNombreUsuario' disabled required>";
                $input .= "<option value='" . htmlspecialchars($_SESSION["Controlador"] -> miEstado -> IdUsuarioLogin) . "'>" . htmlspecialchars($_SESSION["Controlador"] -> miEstado -> nombreUsuario) . "</option>";
                $input .= "</select>";
            }

            // Si hay barcos, mostrar select + botón X
            if ($_SESSION["Controlador"] -> miEstado -> barcos && count($_SESSION["Controlador"] -> miEstado -> barcos) > 0) {
                $input .= "<div class='input-group my-2'>";
                $input .= "<select class='form-select border-end-0' id='SelectNombreBarco'>";
                $input .= "<option value='' disabled selected>Seleccione el Barco</option>";
                foreach ($_SESSION["Controlador"] -> miEstado -> barcos as $barco) {
                    $input .= "<option value='" . htmlspecialchars($barco["IdBarco"]) . "'>" . htmlspecialchars($barco["Nombre"]) . "</option>";
                }
                $input .= "</select>";
                $input .= "<button type='button' id='btnClearBarco' class='btn btn-outline-secondary border border-start-0 reset-button'>&times;</button>";
                $input .= "</div>";
            }
            else {
                // No hay barcos
                $input .= "<select class='form-control my-2' id='SelectNombreBarco' style='display: none;'>";
                $input .= "<option value='' disabled selected>Seleccione el Barco</option>";
                foreach ($_SESSION["Controlador"] -> miEstado -> barcos as $barco) {
                    $input .= "<option value='" . htmlspecialchars($barco["IdBarco"]) . "'>" . htmlspecialchars($barco["Nombre"]) . "</option>";
                }
                $input .= "</select>";
            }
        }

        //Creación de usuario//
        if ($_SESSION["Controlador"] -> miEstado -> Estado == 0.5){

            //Usar PlatillaModal paraa creación de usuario
            $PlantillaModal = str_replace("%TituloModal%","Creación de Usuario",$PlantillaModal);
            $PlantillaModal = str_replace("%TipoFormulario%", "crear_usuario", $PlantillaModal);

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
                        <option value='Intermediario'>Intermediario</option>
                    </select>";


            // Usar PlantillaModalAux para la modificación de usuario

            $PlantillaModalAux = str_replace("%TituloModal%","Edicion de Usuario",$PlantillaModalAux);
            $PlantillaModalAux = str_replace("%TipoFormulario%", "editar_usuario", $PlantillaModalAux);

            $inputAux = "<label for='TxtBoxInputNombreUsuario'>Nombre de usuario</label>";
            $inputAux .= "<input type='text' class='form-control my-2' id='TxtBoxInputNombreUsuarioAux' placeholder='Nombre de usuario' required>";

            $inputAux .= "<label for='TxtBoxContraseña'>Contraseña</label>";
            $inputAux .= "<input type='password' class='form-control my-2' id='TxtBoxContraseñaAux' placeholder='Contraseña' required>";
            $inputAux .= "<input type='password' class='form-control my-2' id='TxtBoxConfirmarContraseñaAux' placeholder='Confirmar contraseña' required>";

            // Checkbox para mostrar/ocultar contraseña
            $inputAux .= "<div class='form-check my-2'>";
            $inputAux .= "<input type='checkbox' class='form-check-input' id='MostrarContraseñasAux'  onclick=\"document.getElementById('TxtBoxContraseñaAux').type = this.checked ? 'text' : 'password'; document.getElementById('TxtBoxConfirmarContraseñaAux').type = this.checked ? 'text' : 'password';\">";
            $inputAux .= "<label class='form-check-label' for='MostrarContraseñas'>Mostrar contraseñas</label>";
            $inputAux .= "</div>";

            $inputAux .= "<label for='SelectRolAux'>Rol</label>";
            $inputAux .= "<select class='form-control my-2' id='SelectRolAux' required>
                        <option value='' disabled selected>Seleccione un rol</option>
                        <option value='Administrador'>Administrador</option>
                        <option value='Armador'>Armador</option>
                        <option value='Conservero'>Conservero</option>
                        <option value='Intermediario'>Intermediario</option>
                    </select>";


        }

        //Modal para creación de barco//
        if ($_SESSION["Controlador"] -> miEstado -> Estado == 2){

            $PlantillaModal = str_replace("%TituloModal%","Creación de Barco",$PlantillaModal);
            $PlantillaModal = str_replace("%TipoFormulario%", "crear_barco", $PlantillaModal);

            $input .= "<input type='text' class='form-control my-2' id='TxtBoxInputNombreBarco' placeholder='Nombre del Barco' required>";

        }


        $modalPrincipal = str_replace("%BodyModal%", $input, $PlantillaModal);
        $modalAuxiliar = str_replace("%BodyModal%", $inputAux ?? '', $PlantillaModalAux ?? '');

        
        return $modalPrincipal . $modalAuxiliar;
        
    }


    function dibujaOpciones($tab = 0){

        $contenido = "";

        $arrayDoc = $_SESSION["Controlador"]->miEstado->capturas;

        //var_dump($arrayDoc);

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

        if ($tab == 4){
            $arrayDoc = array_values($arrayDoc); // Asegura índices consecutivos

            // Extraer especies únicas
            $especiesUnicas = array_unique(array_column($arrayDoc, 'TipoAlmacen'));
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

        // Ventana de Almacenes//
        if ($Pestana == 0.25) {

            if (!isset($_SESSION["Controlador"]->miEstado->tiposalmacenFiltrados) || 
                $_SESSION["Controlador"]->miEstado->tiposalmacenFiltrados === null) {

                $arraydatos = $_SESSION["Controlador"]->miEstado->tiposalmacen;

            } else {
                $arraydatos = $_SESSION["Controlador"]->miEstado->tiposalmacenFiltrados;
            }
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

            $arraydatosAdiccional = $_SESSION["Controlador"]->miEstado->almacenes;

            /* // Lógica de filtrado para almacenes
            if (!isset($_SESSION["Controlador"]->miEstado->almacenesFiltrados) || 
                $_SESSION["Controlador"]->miEstado->almacenesFiltrados === null) {

                $arraydatosAdiccional = $_SESSION["Controlador"]->miEstado->almacenes;

            } else {
                $arraydatosAdiccional = $_SESSION["Controlador"]->miEstado->almacenesFiltrados;
            }
 */
            // Captura detalle siempre asignado
            $capturaDetalle = $_SESSION['Controlador']->miEstado->capturaDetalle;
        }

        $contenido = "";

        if (isset($arraydatos) && !empty($arraydatos)) {

            if($Pestana == 0){

                $contenido = "<section style='border: none; box-shadow: none;'>"; // Elimina bordes y sombra
                $contenido .= "<div style='max-width: 700px; margin: auto; padding-bottom: 100px;'>"; // Contenedor centrado y limitado
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

                    $contenido .= "<div class='col-12 col-lg-9 mx-auto'>";
                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";

                    // Row general
                    $contenido .= "<div class='row align-items-center'>";

                    // Nombre (col-6 en xs, col-4 en lg)
                    $contenido .= "<div class='col-6 col-lg-4'>";
                    $contenido .= "<strong>" . htmlspecialchars($usuario["NombreUsuario"]) . "</strong>";
                    $contenido .= "</div>";

                    // Rol (col-6 en xs, col-3 en lg) con texto alineado a la izquierda en xs y centrado en lg
                    $contenido .= "<div class='col-6 col-lg-3 d-flex align-items-center justify-content-end justify-content-lg-center' style='font-size: 0.825rem;'>";
                    $contenido .= htmlspecialchars($tipoUsuario);
                    $contenido .= "</div>";

                    // Botones (col-12 en xs, col-5 en lg) con flex justify-content-start en xs y justify-content-lg-end en lg, sin padding izquierda en xs
                    $contenido .= "<div class='col-12 col-lg-5 d-flex justify-content-start justify-content-lg-end mt-2 mt-lg-0 ps-0'>";

                    $onclickEntrar = ($tipoUsuario == "Armador")
                        ? "dibuja_pagina([1, $idUsuario, " . '"' . $NombreUsuario . '"' . "])"
                        : "dibuja_pagina([1.5, $idUsuario, " . '"' . $NombreUsuario . '"' . "])";

                    $contenido .= "<button type='button' class='btn btn-sm' onclick='$onclickEntrar'>
                        <img src='Img/IconosAcciones/boton_entrar.png' alt='Entrar' style='width: 32px; height: 32px;'>
                    </button>";

                    $contenido .= "<button type='button' class='btn btn-sm text-white ms-2' data-id='$idUsuario' data-nombre='$NombreUsuario' data-rol='$tipoUsuario' onclick='abrirModalEdicion(this)'>
                        <img src='Img/IconosAcciones/boton_editar.png' alt='Editar' style='width: 32px; height: 32px;'>
                    </button>";

                    $contenido .= "<button type='button' class='btn btn-sm text-white ms-2' title='Eliminar' onclick='if(confirm(\"¿Eliminar este usuario?\")) { dibuja_pagina([1, -1, $idUsuario]); }'>
                        <img src='Img/IconosAcciones/boton_eliminar.png' alt='Eliminar' style='width: 32px; height: 32px;'>
                    </button>";

                    $contenido .= "</div>"; // fin botones

                    $contenido .= "</div>"; // fin row
                    $contenido .= "</div>"; // fin card
                    $contenido .= "</div>"; // fin contenedor ancho limitado


                    
                }
            }

            if ($Pestana == 0.25) {

            if ($arraydatos && is_array($arraydatos)) {
                foreach ($arraydatos as $index => $tipo) {
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';

                    $nombreTipo = htmlspecialchars($tipo["NombreTipo"]);

                    
                    

                    $contenido .= "<div class='card p-3 border shadow-sm' style='$backgroundColor margin-bottom: 0;'>";

                    // 🔁 Contenedor flex para título + botón
                    $contenido .= "<div class='d-flex justify-content-between align-items-center mb-2'>";

                    // 📍 Nombre del almacén
                    $contenido .= "<h5 class='card-title mb-0' style='font-size: 1.125rem;'><strong>$nombreTipo</strong></h5>";

                    // 🔘 Botón capturas alineado a la derecha
                    if ($_SESSION["Controlador"]-> miEstado ->EstadosAnteriores[0] != 0.0625){
                        $contenido .= "<button class='btn btn-outline-primary btn-sm' onclick='capturasDelAlmacen(\"$nombreTipo\")'>Capturas</button>";
                    }

                    $contenido .= "</div>"; // fin d-flex

                    // ⬇️ Resto del contenido (details)
                    $contenido .= "<details>";
                    $contenido .= "<summary style='cursor: pointer; width: fit-content; list-style-type: disclosure-closed;'></summary>";

                    $contenido .= "<div class='pt-2' style='padding: 10px; font-size: 1.1em;'>";

                    if ( $_SESSION["Controlador"] -> miEstado -> esAdmin == true && $_SESSION["Controlador"] -> miEstado -> EstadosAnteriores[0] == 0.0625) {

                        if (isset($tipo["Usuario"]) && $tipo["Usuario"] !== null) {
                            $contenido .= "<p><span class='text-black'>Propietario: </span> <strong>" . htmlspecialchars($tipo["Usuario"]) . "</strong></p>";
                        } else {
                            $contenido .= "<p><span class='text-black'>Propietario: </span> <strong>No asignado</strong></p>";
                        }

                    }
                    if (isset($tipo["Barco"]) && $tipo["Barco"] !== null) {
                        $contenido .= "<p><span class='text-black'>Barco: </span> <strong>" . htmlspecialchars($tipo["Barco"]) . "</strong></p>";
                    }
                    else {
                        $contenido .= "<p><span class='text-black'>Ubicación: </span> <strong>Almacen de tierra</strong></p>";
                    }

                    if (isset($tipo["Tipo"]) && $tipo["Tipo"] !== null) {
                        $contenido .= "<p><span class='text-black'>Tipo: </span> <strong>" . htmlspecialchars($tipo["Tipo"]) . "</strong></p>";
                    }

                    $contenido .= "</div>";
                    $contenido .= "</details>";
                    
                    $contenido .= "</div>"; // fin card
                }
            }else {
                $contenido .= "<div class='alert alert-warning'>No se pudieron obtener los tipos de almacén.</div>";
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
    
                    $contenido .= "<div class='w-100'>";

                    // Contenedor responsive
                    $contenido .= "<div class='d-flex flex-column flex-md-row justify-content-between align-items-start'>";

                    // Título (siempre primero)
                    $contenido .= "<div class='order-1 w-100'>";
                    $contenido .= "<h5 class='card-title mb-0' style='font-size: 1.125rem;'><strong>$tagPez</strong> - $EspecieCapturada</h5>";
                    $contenido .= "</div>";

                    $contenido .= "<div class='order-2 order-md-2 mt-2 mt-md-0 text-center text-md-end'>";
                    $contenido .= "<div class='d-flex flex-column align-items-center align-items-md-end'>";

                    // Fecha centrada en móvil, derecha en PC
                    $contenido .= "<div class='$claseFecha' style='font-size: 1.125rem; white-space: nowrap; margin-bottom: 0.3rem;'>"
                            . date('d/m/Y H:i', strtotime($FechaCaptura)) . "</div>";

                    // Botón: izquierda en móvil, centrado debajo de la fecha en PC
                    $contenido .= "<a title='Ver detalles completos' onclick='dibuja_pagina([3,\"$refPez\"])' class='me-auto mx-md-auto'>";
                    $contenido .= "<img src='Img/DetallesCaptura.png' alt='Ver detalles' style='width: 40px; height: 27px; cursor: pointer; border: none;'>";
                    $contenido .= "</a>";

                    $contenido .= "</div>"; // fin interno
                    $contenido .= "</div>"; // fin contenedor fecha + botón

                    $contenido .= "</div>"; // fin flex container

                    // Sección DETAILS (siempre al final)
                    $contenido .= "<details  style='cursor: pointer;'>";
                    $contenido .= "<summary style='font-size: 1.2rem; font-weight: bold; padding-left: 0;'></summary>";

                    $contenido .= "<div class='pt-2' style='padding: 10px; font-size: 1.25em;'>";

                    // Grid con una columna en móvil, tres en escritorio
                    $contenido .= "<div class='row row-cols-1 row-cols-md-3 g-3'>";

                    // 1a fila
                    $contenido .= "<div><p><span class='text-black'>Zona: </span> <strong>$ZonaCaptura</strong></p></div>";
                    $contenido .= "<div><p><span class='text-black'>Almacenes visitados: </span> <strong>$TotalAlmacenes</strong></p></div>";
                    $contenido .= "<div>";
                    if (!empty($fechaUltimaTemperatura)) {
                        $contenido .= "<p><span class='text-black'>Última Fecha: </span> <strong>" . date('d/m/Y H:i', strtotime($fechaUltimaTemperatura)) . "</strong></p>";
                    }
                    $contenido .= "</div>";

                    // 2a fila
                    $contenido .= "<div><p><span class='text-black'>Barco: </span> <strong>$NombreBarcoCaptura</strong></p></div>";
                    $contenido .= "<div><p><span class='text-black'>Último Almacén: </span> <strong>$tipoUltimoAlmacen</strong></p></div>";
                    $contenido .= "<div><p><span class='text-black'>Temp: </span> <span class='$claseTemperaturaMinima'><strong>" . $temperaturaMinima . "°C</strong></span> / <span class='$claseTemperaturaMaxima'><strong>" . $temperaturaMaxima . "°C</strong></span></p></div>";

                    // 3a fila
                    $contenido .= "<div><p><span class='text-black'>Armador: </span> <strong>$Armador</strong></p></div>";
                    $contenido .= "<div>";
                    if ($NombreComprador != null) {
                        $contenido .= "<p><span class='text-black'>Propietario: </span> <strong>$NombreComprador</strong></p>";
                    }
                    $contenido .= "</div>";
                    $contenido .= "<div></div>"; // celda vacía para completar la fila

                    $contenido .= "</div>"; // fin row
                    $contenido .= "</div>"; // fin contenido colapsable
                    $contenido .= "</details>"; // fin details

                    $contenido .= "</div>"; // fin contenedor general

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
                // Versión escritorio (visible solo en md y superiores)
                $contenido .= "<div class='d-none d-md-block col-md-4' style='display: flex; flex-direction: column; height: 100%;'>";
                $contenido .= "<div class='card p-3 border shadow-sm mb-3' style='flex-shrink: 0; height:100%'>";
                $contenido .= "<h5><strong> $tagPez - $EspecieCapturada</strong></h5>";
                $contenido .= "<p>Fecha de Captura: <strong>" . date('d/m/Y H:i', strtotime($FechaCaptura)) . "</strong></p>";
                $contenido .= "<p>Barco:<strong> $NombreBarcoCaptura</strong></p>";
                $contenido .= "<p><span class='text-black'>Armador del Barco: </span> <strong>$Armador</strong></p>";
                $contenido .= "<p>Zona:<strong> $ZonaCaptura</strong></p>";
                $contenido .= "<p><span class='text-black'>Almacén Actual: </span> <strong>$tipoUltimoAlmacenCaptura</strong></p>";

                if($NombreComprador != null){
                    $contenido .= "<p><span class='text-black'>Propietario: </span> <strong>$NombreComprador</strong></p>";
                }

                $contenido .= "<p><span class='text-black'>Temp: </span> <span class='$claseTemperaturaMinima'><strong>" . $temperaturaMinimaCaptura . "°C</strong></span><span> / </span> <span class='$claseTemperaturaMaxima'><strong>" . $temperaturaMaximaCaptura . "°C</strong></span></p>";

                $contenido .= "</div>";
                $contenido .= "</div>";


                // Versión móvil (visible solo en xs y sm)
                // Sin height 100%, display block para adaptar altura al contenido
                $contenido .= "<div class='d-block d-md-none col-12' style='display: block; height: auto;'>";
                $contenido .= "<div class='card p-3 border shadow-sm mb-3' style='flex-shrink: 0; height:auto;'>";
                $contenido .= "<h5><strong> $tagPez - $EspecieCapturada</strong></h5>";
                $contenido .= "<p>Fecha de Captura: <strong>" . date('d/m/Y H:i', strtotime($FechaCaptura)) . "</strong></p>";
                $contenido .= "<p>Barco:<strong> $NombreBarcoCaptura</strong></p>";
                $contenido .= "<p><span class='text-black'>Armador del Barco: </span> <strong>$Armador</strong></p>";
                $contenido .= "<p>Zona:<strong> $ZonaCaptura</strong></p>";
                $contenido .= "<p><span class='text-black'>Almacén Actual: </span> <strong>$tipoUltimoAlmacenCaptura</strong></p>";

                if($NombreComprador != null){
                    $contenido .= "<p><span class='text-black'>Propietario: </span> <strong>$NombreComprador</strong></p>";
                }

                $contenido .= "<p><span class='text-black'>Temp: </span> <span class='$claseTemperaturaMinima'><strong>" . $temperaturaMinimaCaptura . "°C</strong></span><span> / </span> <span class='$claseTemperaturaMaxima'><strong>" . $temperaturaMaximaCaptura . "°C</strong></span></p>";

                $contenido .= "</div>";
                $contenido .= "</div>";
            
                // Columna derecha
                $contenido .= "<div class='col-md-8' style='display: flex; flex-direction: column; height: 100%;'>";
                $contenido .= "<div class='card p-3 border shadow-sm mb-3 d-flex justify-content-between align-items-center' style='flex-shrink: 0; max-width: 950px;' id='contenedor-grafica'>";
                $contenido .= "<div class='d-flex justify-content-between w-100 align-items-center'>";
                $contenido .= "<h5 class='card-title mb-0'>Gráfico de Temperatura</h5>";
                $contenido .= "</div>";
                $contenido .= "<canvas id='graficaTemperatura' width='950' height='300' style='display: block; max-height: 300px;'></canvas>";
                $contenido .= "</div>";
                $contenido .= "<script>dibuja_pagina([4]);</script>";
            
                // Lista de almacenes
                $contenido .= "<div class='card p-3 border shadow-sm' style='flex-grow: 1; overflow-y: auto;'>";
                $contenido .= "<h5 class='card-title'>Almacenes Visitados</h5>";
                $contenido .= "<div style='flex-grow: 1; overflow-y: auto;'>";

                $idBotonAnterior = (int) ($_SESSION["Controlador"]->miEstado->idBoton ?? 0);
            
                foreach ($arraydatosAdiccional as $index => $Almacenes) {
                
                    $backgroundColor = ($index % 2 == 0) ? 'background-color: whitesmoke;' : 'background-color: white;';

                    $NombreAlmacen = $Almacenes['NombreTipo'] ?? '';
                    $idAlmacen = $Almacenes['IdAlmacen'] ?? '';
                    $ReferenciaAlmacen = $NombreAlmacen;
                    $FechaAlmacen = $Almacenes['FechaAlmacen'] ?? '';
                    $esBodegaDelBarco = ($NombreAlmacen == 'Bodega');
                    $Propietario = $Almacenes['Comprador'] ?? null;
                    $colorTexto = isset($mapaColores[$index]) ? $mapaColores[$index] : '#000';
            
                    $contenido .= "<div class='card p-2 border shadow-sm mb-2' style='$backgroundColor'>";
                    $contenido .= "<table class='table table-borderless mb-0' style='table-layout: fixed; width: 100%;'>";
                    $contenido .= "<tr>";
                    if ($esBodegaDelBarco) {
                        $contenido .= "<td style='text-align: center; vertical-align: middle;'>
                                            <button type='button' class='btn btn-sm btn-outline-success'
                                                onclick='seleccionarTodosLosToggles()'>
                                                Seleccionar todos
                                            </button>
                                        </td>";
                    } else {
                        $contenido .= "<td style='text-align: center; vertical-align: middle; display: flex; align-items: center; justify-content: center; gap: 6px;'>
                                            <button type='button' class='btn btn-sm btn-outline-primary'
                                                onclick='seleccionarSoloEste(\"$idAlmacen\")'>
                                                Solo este
                                            </button>

                                            <div class='form-check form-switch m-0'>
                                                <input class='form-check-input' type='checkbox' role='switch'
                                                    id='toggle_$idAlmacen'
                                                    data-color='$colorTexto'
                                                    checked
                                                    onchange='toggleTramo(\"$idAlmacen\", \"$colorTexto\", this.checked)'>
                                            </div>
                                        </td>";


                    }
                    $contenido .= "<td style='text-align: center; vertical-align: middle; color: $colorTexto;'>$ReferenciaAlmacen</td>";

                    $NombrePropietario = ($Propietario != null && $Propietario != "") ? $Propietario : "";

                    $contenido .= "<td style='text-align: center; vertical-align: middle;'>$NombrePropietario</td>";
                    $contenido .= "<td style='text-align: center; vertical-align: middle;'>$FechaAlmacen</td>";

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

    function sobreNosotros(){
        $contenido = '
    <div class="container text-center my-4">
        <h2 class="mb-4" style="color: #007fa3;">Sobre Nosotros</h2>

        <h5 class="mb-3 fw-bold">Nombre del reto:</h5>
        <p class="mb-4">
            Sensor/Dispositivo para Trazabilidad y Medición de Parámetros en Túnidos
        </p>

        <div class="row justify-content-center align-items-center g-4">
            <div class="col-6 col-md-4">
                <img src="Img/ANFACO_CYTMA.png" alt="Logo ANFACO" class="img-fluid" style="max-height: 120px;  transform: scale(1.4);">
            </div>
            <div class="col-6 col-md-4">
                <img src="Img/Uvigo_logo.png" alt="Logo UVigo" class="img-fluid" style="max-height: 120px;">
            </div>
            <br/>
            <div class="col-12 mt-3">
                <h5 class="fw-bold">Diseñado por:</h5>
                <img src="Img/esquio_logo.png" alt="Logo Esquío" class="img-fluid" style="max-height: 120px;">
            </div>
        </div>
    </div>
';

        return $contenido;
    }
    
    
?>
