<?php

use Dom\Element;

require_once 'estado.php';
require_once 'pinta.php'; 
require_once "consultas.php";

Class Controlador{


    public $miEstado;

    function __construct($Estado = null){
        $this -> miEstado = new Estado();
        $this -> miEstado -> Estado = 0;
        $this -> miEstado -> Documentos = array();
        $this -> miEstado -> FiltrosDoc = array();
        $this -> miEstado -> acciones = array("archivos"=> 0,
                                        "observaciones" => 0,
                                        "añadir" => 0);
        $this -> miEstado -> EstadosAnteriores = array();
    }

    function __destruct(){
        $_SESSION["Controlador"] = $this;
    }

    function navegarPestanas($ps){
        //volver a la anterior
        if($ps == -1){



            //limpiar Filtros antes de cambiar de pagina//

            if($this -> miEstado -> Estado == 0.25) {
                $this -> resetFilter($this -> miEstado -> Estado);
            }
            elseif ($this -> miEstado -> Estado == 0.5) {
                $this -> resetFilter($this -> miEstado -> Estado);
            }
            elseif ($this -> miEstado -> Estado == 2) {
                $this -> resetFilter($this -> miEstado -> Estado);
            }
            elseif( $this ->miEstado->Estado == 3) {
                $this -> resetFilter($this -> miEstado -> Estado);
            }
            elseif ( $this -> miEstado->Estado == 4) {
                $this -> resetFilter($this -> miEstado -> Estado);
            }


            //salir del modo formulario
            $estadoAnterior = array_shift($this->miEstado->EstadosAnteriores);
            $this->miEstado->Estado = $estadoAnterior;


           // Verificar estado y reinicializar según el caso
           //Reiniciar todas las variables si vas al log in
            if ($this->miEstado->Estado == 0) {
                $this->cerrarSesion();
            }
            //reiniciar variables de usuario
            elseif ($this ->miEstado->Estado == 0.5) {
                $this -> miEstado -> IdLastUser = $this -> miEstado -> IdUsuarioSeleccionado;
                $this -> miEstado -> IdUsuarioSeleccionado = null;
                $this -> resetFilter();
            }
            //reiniciar nombre si vas a la vista de conservero
            elseif ($this ->miEstado->Estado == 1.5) {
                $this -> miEstado -> nombreUsuario = $this -> miEstado -> nombreConservero;
            }
            //reiniciar variables de captura
            elseif( $this ->miEstado->Estado == 3) {
                $this -> miEstado -> LastTagPez = $this -> miEstado -> TagPez;
                $this -> miEstado -> TagPez = null;
                
            }

            // Reinicialización común
            $this->miEstado->nombreDocumentoPadre = null;
            $this->miEstado->IdPropietario = null;

 
            
            // Imprimir en el log del servidor
            error_log('nombreDocumentoPadre: ' . $this->miEstado->nombreDocumentoPadre);
            error_log('IdPropietario: ' . $this->miEstado->IdPropietario);
            
        } else {
            
            array_unshift($this->miEstado->EstadosAnteriores , $this->miEstado->Estado);
            $this->miEstado->Estado = $ps;
        }
    
        // Reinicializar otras variables
        $this->miEstado->CadenaFiltro = null;
        $this->miEstado->IdsTiposFiltro = array();
        
        // Imprimir en el log del servidor
        error_log('CadenaFiltro: ' . $this->miEstado->CadenaFiltro);
        error_log('IdsTiposFiltro: ' . implode(',', $this->miEstado->IdsTiposFiltro));
        
    }

    function IniciarSesion($usuario, $contrasena) {
        $datosSesion = comprueba_usuario($usuario, $contrasena);
        $this->miEstado = new Estado();
        $this->miEstado->Estado = 0;
        procesarInformacion();
    
        if ($datosSesion != false && $datosSesion != 0) {
            
            $this->miEstado->IdUsuarioLogin = $datosSesion[0];
            $this -> miEstado -> nombreUsuario = $datosSesion[1];

            // Determinar si es administrador
            $this -> miEstado -> esAdmin = ($datosSesion[3] === "Administrador");

            // Determinar si es conservero
            $this  -> miEstado -> esArmador = ($datosSesion[3] === "Armador");


            // Inicializar variables
            $usuarios = [];
            $capturas = [];
            $barcos = [];
            $temperaturas = [];
            $almacenes = [];
            $tiposAlmacen = [];

            // Obtener datos según el tipo de usuario
            if ($this -> miEstado -> esAdmin) {

                $usuarios = get_usuarios();
                $barcos = get_Barcos();
        
            } else {
                $capturas = get_pescado( $datosSesion[0]);
                $tiposAlmacen = get_tiposAlmacen($datosSesion[0]);
                $barcos = get_Barcos($datosSesion[0]);
                
            }

            // Asignar a sesión, usando operador ternario o directamente el valor
            $this -> miEstado -> capturas = $capturas ?: [];
            $this -> miEstado -> barcos = $barcos ?: [];
            $this -> miEstado -> usuarios = $usuarios ?: [];
            $this -> miEstado -> temperaturas = $temperaturas ?: [];
            $this -> miEstado -> almacenes =  $almacenes ?:[];
            $this -> miEstado -> tiposalmacen = $tiposAlmacen ?: [];

            return true;

        } elseif ($datosSesion == 0) {
            return 0;
        } else {
            return false;
        }
    }


    function cerrarSesion(){
    //Cerrar sesion reinicializando variables
        $this -> miEstado = new Estado();
        $this -> miEstado -> Estado = 0;
        $this -> miEstado -> Documentos = array();
        $this -> miEstado -> FiltrosDoc = array();
        $this -> miEstado -> acciones = array("archivos"=> 0,
                                        "observaciones" => 0,
                                        "añadir" => 0);
        $this -> miEstado -> usuarios = [];
        $this -> miEstado -> capturas = [];
        $this -> miEstado -> barcosFiltrados = [];
        $this -> miEstado -> temperaturas = [];
        $this -> miEstado -> almacenes = [];
        if(isset($_SESSION["header"])){
            $this -> miEstado -> header = $_SESSION["header"];
            $_SESSION["header"] = null;
        }else{
            $header_Empresa = '../html/header.html';
            $header = fopen($header_Empresa, "r");
            $this -> miEstado -> header = fread($header,filesize($header_Empresa));
            fclose($header);
        }  
        
    }

    function setNewUser($IdUser, $UserName){

        

        if ($IdUser == $this -> miEstado -> IdLastUser) {
            return;
        }
        else{
            $this -> miEstado -> IdUsuarioSeleccionado = $IdUser;
            $this -> miEstado -> nombreUsuario = $UserName;
            $this -> miEstado -> capturas = get_pescado( $IdUser);
            $this -> miEstado -> tiposalmacen = get_tiposAlmacen($IdUser);
            $this -> miEstado -> barcos = get_Barcos($IdUser);
            $this -> miEstado -> LastTagPez = null; // Reiniciar LastTagPez al cambiar de usuario
        }
    }


    function setNewCaptura($tagPez) {
        // Buscar la captura por TagPez (en ambos casos se necesita)
        $capturaEncontrada = null;

        foreach ($this->miEstado->capturas as $captura) {
            if (isset($captura["TagPez"]) && $captura["TagPez"] === $tagPez) {
                $capturaEncontrada = $captura;
                break;
            }
        }

        if ($capturaEncontrada === null) {
            echo "No se encontró el tag '$tagPez' en capturas.";
            return;
        }

        // Obtener la fecha límite desde la captura encontrada
        $fechaLimite = $capturaEncontrada["FechaUltimoAlmacen"];

        if ($tagPez == $this->miEstado->LastTagPez) {
            // Ya se ha procesado este tag, no repetir trabajo
            return;
        }

        // Guardar el nuevo tag
        $this->miEstado->TagPez = $tagPez;

        // Mostrar los datos de la captura
        //var_dump($fechaLimite, $capturaEncontrada);

        // Obtener almacenes

        $this->miEstado->almacenes = get_Almacenes($tagPez, $fechaLimite);

        // Obtener y procesar temperaturas
        $temperaturasProcesar = getTemperaturasProcesar($tagPez, $fechaLimite);
        $tempProcesada = [];

        foreach ($temperaturasProcesar as $temp) {
            $datosTemp = $temp['DatosTempProcesar'] ?? null;
            $idAlmacen = $temp['IdAlmacenProcesar'] ?? null;

            if ($datosTemp !== null && $idAlmacen !== null) {
                $datosformateados = descomprimirTemperaturas($datosTemp);
                $resultado = procesarTemperaturasString($datosformateados, $idAlmacen);
                if (is_array($resultado)) {
                    $tempProcesada = array_merge($tempProcesada, $resultado);
                }
            }
        }

        $this->miEstado->temperaturas = $tempProcesada;

        // Llamar a details_Captura para completar detalles
        $this->details_Captura($tagPez);
    }

    

    function details_Captura($tagPez){
        // Verificar si las capturas están disponibles en la sesión
        if (isset($this -> miEstado -> capturas) && !empty($this -> miEstado -> capturas)) {
            
            // Buscar la captura que coincide con el tagPez directamente usando array_filter
            $captura = array_filter($this -> miEstado -> capturas, function($item) use ($tagPez) {
                return $item['TagPez'] == $tagPez;
            });

            //var_dump($this -> miEstado -> capturas);
    
            // Si encontramos la captura, almacenamos el primer resultado en la sesión
            if (!empty($captura)) {
                //var_dump(array_values($captura)[0]);
                $this -> miEstado -> capturaDetalle = array_values($captura)[0]; // Tomamos el primer elemento de la array filtrado
                return true; // Se encontró la captura
            }
        }
    
        // Si no se encuentra la captura o no hay capturas en la sesión
        $this -> miEstado -> capturaDetalle = null;
        return false; // No se encontró la captura
    }

    function incrementarCodigo($codigo) {
        // Extrae el signo, el número y la letra
        preg_match('/^(-?)(\d{8})([A-Z]+)$/i', $codigo, $partes);
        
        if (!$partes) {
            return "Formato inválido";
        }

        $signo = $partes[1];       // Puede ser "-" o vacío
        $numero = (int)$partes[2]; // Convertimos a entero para sumar
        $letra = strtoupper($partes[3]);

        // Incrementamos el número
        $numero++;

        // Si el número supera 99999999, reiniciamos y avanzamos letra
        if ($numero > 99999999) {
            $numero = 0;
            $letra = $this -> incrementarLetra($letra);
        }

        // Formateamos de nuevo a 8 dígitos
        $numeroFormateado = str_pad($numero, 8, '0', STR_PAD_LEFT);

        return $signo . $numeroFormateado . $letra;
    }

    function incrementarLetra($letra) {
        $letra = strtoupper($letra);
        $long = strlen($letra);
        $abc = range('A', 'Z');
        $max = count($abc) - 1;

        // Convertimos la letra a un número base 26
        $num = 0;
        for ($i = 0; $i < $long; $i++) {
            $num *= 26;
            $num += ord($letra[$i]) - ord('A');
        }

        // Incrementamos
        $num++;

        // Convertimos de nuevo a base 26 con letras
        $nuevaLetra = '';
        do {
            $resto = $num % 26;
            $nuevaLetra = chr(ord('A') + $resto) . $nuevaLetra;
            $num = intdiv($num, 26);
        } while ($num > 0);

        return $nuevaLetra;
    }


    function generarDatosGrafica2($temperaturasVS, $almacenesVS) {

       
        
        $temperaturas = $temperaturasVS;
        $almacenes = $almacenesVS;


        //$temperaturas =  procesarTemperaturas(4, 0);
        
        $dataset = [];
        foreach ($temperaturas as $temp) {
            foreach ($almacenes as $almacen) {
                if ($temp['IdAlmacen'] == $almacen['IdAlmacen']) {
                    $dataset[] = [
                        "x" => strtotime($temp["FechaTemperatura"]) * 1000,
                        "y" => $temp["ValorTemperatura"],
                        "almacen" =>  $almacen["NombreTipo"] . $almacen["IdTipo"],
                    ];
                    break;
                }
            }
        }
        $this -> miEstado -> dataset = $dataset;
    } 
    
    function generarDatosGrafica($temperaturasVS, $almacenesVS) {
        $temperaturas = $temperaturasVS;
        $almacenes = $almacenesVS;

        $datasetAgrupado = [];

        foreach ($almacenes as $almacen) {
            $claveAlmacen = $almacen["NombreTipo"] . $almacen["IdTipo"];
            $datos = [];

            foreach ($temperaturas as $temp) {
                if ($temp['IdAlmacen'] == $almacen['IdAlmacen']) {
                    $datos[] = [
                        "x" => strtotime($temp["FechaTemperatura"]) * 1000,
                        "y" => $temp["ValorTemperatura"]
                    ];
                }
            }

            if (!empty($datos)) {
                $datasetAgrupado[] = [
                    "almacen" => $claveAlmacen,
                    "datos" => $datos
                ];
            }
        }

        $dataset = array_reverse($datasetAgrupado);


        $this->miEstado->dataset = $dataset;
    }

    function filtrarSimple($filtro, $pestana){

        if ($pestana != 4){
            $filtro = strtolower($filtro);
        }

        if ($pestana == 0.25){
            $tiposAlmacen = is_array($this->miEstado->tiposalmacen) ? $this->miEstado->tiposalmacen : [];
            $arrayFiltrado = array_filter($tiposAlmacen, function($item) use($filtro){
                return trim(strtolower($item["NombreTipo"])) === trim(strtolower($filtro));
            });
        }

        //filtro de usuarios
        elseif ($pestana == 0.5){
            $usuarios = is_array($this->miEstado->usuarios) ? $this->miEstado->usuarios : [];
            $arrayFiltrado = array_filter($usuarios, function($item) use($filtro){
                return trim(strtolower($item["NombreUsuario"])) === trim(strtolower($filtro));
            });
        }
        
        //filtro de barcos
        elseif ($pestana == 2){
            $barcos = is_array($this->miEstado->barcos) ? $this->miEstado->barcos : [];
            $arrayFiltrado = array_filter($barcos, function($item) use($filtro){
                return trim(strtolower($item["Nombre"])) === trim(strtolower($filtro)  );
            });
        }

        //filtro de capturas
        elseif ($pestana == 3){
            $capturas = is_array($this->miEstado->capturas) ? $this->miEstado->capturas : [];
            $arrayFiltrado = array_filter($capturas, function($item) use($filtro){
                return trim(strtolower($item["Especie"])) === trim(strtolower($filtro))
                 or trim(strtolower($item["TipoAlmacen"])) === trim(strtolower($filtro))
                 or trim(strtolower($item["Zona"])) === trim(strtolower($filtro))
                 or trim(strtolower($item["NombreBarco"])) === trim(strtolower($filtro))
                 or trim(strtolower($item["TagPez"])) === trim(strtolower($filtro));
            });
        }

        //filtro de detalles de captura
        elseif ($pestana == 4){

            $almacenes = is_array($this->miEstado->almacenes) ? $this->miEstado->almacenes : [];

            $arrayFiltrado = array_filter($almacenes, function($item) use ($filtro) {
                return in_array($item["IdAlmacen"], $filtro);
            });

            
        }

        return $arrayFiltrado;
    }

    // Función para limpiar el texto del timezone en la fecha
    function limpiarFechaJS($fecha) {
        return preg_replace('/\s*\(.*?\)\s*$/', '', $fecha);
    }

    function filtrarDesplegable($data, $arrayFiltros) {


        //FECHAS//


        // Limpiamos las fechas recibidas desde JS
        $fechaInicioLimpia = $this->limpiarFechaJS($arrayFiltros[0]);
        $fechaFinLimpia = $this->limpiarFechaJS($arrayFiltros[1]);

        // Creamos los objetos DateTime con las fechas limpias
        $diaInicio = new DateTime($fechaInicioLimpia);
        $diaFin = new DateTime($fechaFinLimpia);

        //TEMPERATURAS//

        // Asegurar valores válidos, incluso si son 0
        $temperaturaMin = (isset($arrayFiltros[2]) && $arrayFiltros[2] !== '') ? (float)$arrayFiltros[2] : null;
        $temperaturaMax = (isset($arrayFiltros[3]) && $arrayFiltros[3] !== '') ? (float)$arrayFiltros[3] : null;


        //BARCOS//
        $nombreBarco = $arrayFiltros[4];

        //ZONA DE CAPTURA//

        $zonaCaptura = $arrayFiltros[5];

        //ESPECIE//

        $especieCaptura = $arrayFiltros[6];

        //TAG PEZ//

        $tagPezCaptura = $arrayFiltros[7];

        //ALMACEN//

        $almacenCaptura = $arrayFiltros[8];

        $resultado = array_filter($data, function($item) use ($diaInicio, $diaFin, $temperaturaMin, $temperaturaMax, $fechaInicioLimpia, $fechaFinLimpia, $nombreBarco, $zonaCaptura, $especieCaptura, $tagPezCaptura, $almacenCaptura) {

            //FECHAS//

            // Validar rango de fechas si se proporciona
            if (!empty($item['FechaCaptura'])) {
                $fechaCaptura = new DateTime(substr($item['FechaCaptura'], 0, 10));

                if (!empty($fechaInicioLimpia) && !empty($fechaFinLimpia)) {
                    // Validar entre ambas fechas
                    if ($fechaCaptura < $diaInicio || $fechaCaptura > $diaFin) {
                        return false;
                    }
                } elseif (!empty($fechaInicioLimpia)) {
                    // Solo desde fecha de inicio
                    if ($fechaCaptura < $diaInicio) {
                        return false;
                    }
                } elseif (!empty($fechaFinLimpia)) {
                    // Solo hasta fecha fin
                    if ($fechaCaptura > $diaFin) {
                        return false;
                    }
                }
            }

            //TEMPERATURAS//

            // Si falta alguna temperatura en el item, no lo incluimos
            if (!isset($item['TemperaturaMinima']) || !isset($item['TemperaturaMaxima'])) {
                return false;
            }

            // Validar temperatura mínima
            if (!is_null($temperaturaMin) && $item['TemperaturaMinima'] < $temperaturaMin) {
                return false;
            }

            // Validar temperatura máxima
            if (!is_null($temperaturaMax) && $item['TemperaturaMaxima'] > $temperaturaMax) {
                return false;
            }

            //BARCOS//

            if(!empty($nombreBarco) && $item['NombreBarco'] != $nombreBarco ){
                    return false;
            }
    
            //ZONA DE CAPTURA//

            if($zonaCaptura != 0 && $item['Zona'] != $zonaCaptura ){
                return false;
            }

            //ESPECIE//

            if($especieCaptura != 0 && $item['Especie'] != $especieCaptura ){
                return false;
            }

            //TAG PEZ//

            if(!empty($tagPezCaptura) && $tagPezCaptura != $item['TagPez'] ){
                return false;
            }

            // ALMACEN //
            if (!empty($almacenCaptura) && $item['TipoAlmacen'] != $almacenCaptura) {
                return false;
            }



            return true;
        });



        if ($resultado == null){
            $resultado = [];
        }


        return array_values($resultado); // Reindexamos
    }



    function resetFilter($data = null){

        if ($data == null){
            $this -> miEstado -> usuariosFiltrados = null;
            $this -> miEstado -> barcosFiltrados = null;
            $this -> miEstado -> capturasFiltradas = null;
            $this -> miEstado -> almacenesFiltrados = null;
        }
        else{
            switch ($data) {

                case 0.25:
                    $this -> miEstado -> tiposalmacenFiltrados = null;
                    break;
                case 0.5:
                    $this -> miEstado -> IdLastUser = null;
                    $this -> miEstado -> tiposalmacen = $this -> miEstado -> tiposalmacenAdmin;
                    $this -> miEstado -> barcos = $this -> miEstado -> barcosAdmin;
                    $this -> miEstado -> usuariosFiltrados = null;
                    break;
                case 2:
                    $this -> miEstado -> barcosFiltrados = null;
                    break;
                case 3:
                    $this -> miEstado -> capturasFiltradas = null;
                    break;
                case 4:
                    $this -> miEstado -> almacenesFiltrados = null;
                    break;
            }
        }
    }

    function validarForm($arrayDatos) {
        if ($this->miEstado->Estado == 0.25 && count($arrayDatos) == 3) {
            $nombretipo = $arrayDatos[0];


            foreach ($this->miEstado->tiposalmacen as $tipo) {
                if ($nombretipo === $tipo["NombreTipo"]) {
                    return [
                        'validado' => false,
                        'mensaje' => 'El tipo de almacen ya existe'
                    ];
                }
            }

            return [
                'validado' => true,
                'mensaje' => ''
            ];
        }

        return [
            'validado' => false,
            'mensaje' => 'Condiciones de validación no cumplidas'
        ];
    }

    function mergeSinDuplicadosPorNombreTipo(array $viejos, array $nuevos): array {
        $index = [];

        foreach ($viejos as $row) {
            if (isset($row['NombreTipo'])) {
                $index[$row['NombreTipo']] = $row;
            }
        }

        foreach ($nuevos as $row) {
            if (isset($row['NombreTipo'])) {
                $index[$row['NombreTipo']] = $row; // Reemplaza si ya existe
            }
        }

        return array_values($index);
    }


    function generarContenido($arrayDatos = array()) {

        //var_dump($arrayDatos);

        $arrayAuxiliarHtml = [];
        $accionJs = null;
        $msgError = "";
        $AccionSinRepintar = 0;
        if($this->miEstado->Estado < 1 && $this->miEstado->Estado > 0){
            $c = (float) $this->miEstado->Estado;
        }
        else{
            $c = intval($this->miEstado->Estado);
        }
        

        $arraycolor = $arrayDatos[3][0] ?? null;
        $this ->miEstado -> idBoton = $arrayDatos[3][1] ?? 0;

        // Cerrar sesión
        if (isset($arrayDatos[0], $arrayDatos[1]) && $arrayDatos[0] == -1 && $arrayDatos[1] == -1) {
            $this->cerrarSesion();
            $this->navegarPestanas(0);
        }
    
        // Botón volver
        elseif (isset($arrayDatos[0]) && $arrayDatos[0] == -1) {
            $this->navegarPestanas(-1);
        }

        // Login
        elseif ($c === 0 && !empty($arrayDatos) && isset($arrayDatos[0]) && $arrayDatos[0] != -1) {
            $InicioS = $this->IniciarSesion($arrayDatos[0], $arrayDatos[1]);
    
            if ($InicioS === false) {
                $msgError = "Error de conexión con el servidor, por favor inténtelo más tarde.";
            } elseif ($InicioS === 0) {
                $msgError = "Usuario o contraseña incorrectos.";
            } elseif ($InicioS === true) {
                $pestana = 1;

                if ($this->miEstado->esAdmin) {

                    $pestana = 0.0625;

                    $this -> miEstado -> tiposalmacen = get_tiposAlmacen();
                    $this -> miEstado -> tiposalmacenAdmin = $this -> miEstado -> tiposalmacen ?: [];
                    $this -> miEstado -> usuarios = get_usuarios();
                    $this -> miEstado -> barcos = get_Barcos();
                    $this -> miEstado -> barcosAdmin = $this -> miEstado -> barcos ?: [];

                } elseif (!$this->miEstado->esArmador) {

                    $this -> miEstado -> capturas = get_pescado( $this -> miEstado -> IdUsuarioLogin);
                    $this -> miEstado -> tiposalmacen = get_tiposAlmacen($this -> miEstado -> IdUsuarioLogin);

                    $pestana = 0.125;
                }

                $this->navegarPestanas($pestana);

                
                
            }

        }

        // Creación de tipos de Almacen
        elseif ($c === 0.25 && isset($arrayDatos[0]) && $arrayDatos[1] == -1 && count($arrayDatos[2]) == 3) {
            
        // Formulario si no vienes desde la ventan de administrador
           if ($this -> miEstado -> EstadosAnteriores[0] != 0.0625) {

                $resultadoValidacion = $this->validarForm($arrayDatos[2]);

                if ($resultadoValidacion['validado']) {
                    $tiposAlmacenAntiguos = $this->miEstado->tiposalmacenAdmin ?? [];



                    $nombreTipo = $arrayDatos[2][0];
                    $idUsuario  = $arrayDatos[2][1];
                    $idBarco    = $arrayDatos[2][2];

                    if ($idBarco != null && $idUsuario != null && $idBarco != 0 && $idBarco != "") {
                        insertTipoAlmacen($nombreTipo, $idUsuario, $idBarco); 
                    } elseif ($idUsuario != null) {
                        insertTipoAlmacen($nombreTipo, $idUsuario); 
                    }

                    $nuevosTiposAlmacen = get_tiposAlmacen($this->miEstado->IdUsuarioSeleccionado);


                    $this->miEstado->tiposalmacenAdmin = $this->mergeSinDuplicadosPorNombreTipo(
                        $tiposAlmacenAntiguos ?: [],
                        $nuevosTiposAlmacen ?: []
                    );


                    $this->miEstado->tiposalmacen = $nuevosTiposAlmacen ?: [];

                } else {

                    $msgError = $resultadoValidacion['mensaje'];

                } 
            }

            // Formulario si vienes desde la ventana de administrador
            else {

                $resultadoValidacion = $this->validarForm($arrayDatos[2]);

                if ($resultadoValidacion['validado']) {

                    $nombreTipo = $arrayDatos[2][0];
                    $idUsuario = $arrayDatos[2][1];
                    $idBarco = $arrayDatos[2][2];

                    if ($idBarco != null && $idUsuario != null){
                        insertTipoAlmacen($nombreTipo, $idUsuario, $idBarco); 
                    } elseif ($idUsuario != null) {
                        insertTipoAlmacen($nombreTipo, $idUsuario); 
                    } 

                    
                    $tiposAlmacen = get_tiposAlmacen();
                    $this->miEstado->tiposalmacen = $tiposAlmacen ?: [];

                } else {

                    $msgError = $resultadoValidacion['mensaje'];

                }
            }

        }   

        // Acciones de usuario
        elseif ($c === 0.5 && isset($arrayDatos[0]) && $arrayDatos[1] == -1 ) {

            // Creación de usuario
            if (is_array($arrayDatos[2]) && count($arrayDatos[2]) == 5){
                insertUsuario($arrayDatos[2]); 
                $usuarios = get_usuarios();
                $this -> miEstado -> usuarios = $usuarios ?: [];
            }

            //Eliminación de usuario
            if (isset($arrayDatos[2]) && is_numeric($arrayDatos[2])) {

                delete_User($arrayDatos[2]); 
                $usuarios = get_usuarios();
                $this->miEstado->usuarios = $usuarios ?: [];

            }

            // Actualización de usuario
            if (is_array($arrayDatos[2]) && count($arrayDatos[2]) == 6) {
                
                updateUsuario($arrayDatos[2]);
                $usuarios = get_usuarios();
                $this->miEstado->usuarios = $usuarios ?: [];

            }

        }

        // Creación de barco
        elseif ($c === 2 && isset($arrayDatos[0]) && $arrayDatos[1] == -1 && count($arrayDatos[2]) == 1) {

            $nombreBarco = $arrayDatos[2][0];
            $codigoAntiguo = get_last_codigo_barco();
            $codigoBarco = $this -> incrementarCodigo($codigoAntiguo);

            if ($codigoBarco != null && $nombreBarco != null && $codigoBarco != 0 && $codigoBarco != "") {
                insertBarco($nombreBarco, $codigoBarco, $this->miEstado->IdUsuarioSeleccionado);
            } else {
                $msgError = "El nombre del barco y el código son obligatorios.";
            }

            $barcos = get_Barcos($this->miEstado->IdUsuarioSeleccionado);
            $this -> miEstado -> barcos = $barcos ?: [];

        }

        // Dashboard de administrador
        elseif ($c === 0.0625 && isset($arrayDatos[0])) {
                

                $barcos = is_array($this -> miEstado -> barcos) ? $this -> miEstado -> barcos : [];
                $usuarios = is_array($this -> miEstado -> usuarios) ? $this -> miEstado -> usuarios : [];

                $arrayAuxiliarHtml = ["barcos" => $barcos, "usuarios" => $usuarios];
                $accionJs = 5;


                $this->navegarPestanas($arrayDatos[0]);
        }


        // Dashboard de comercial
        elseif ($c === 0.125 || $c === 1 && isset($arrayDatos[0])) {
            $this->navegarPestanas($arrayDatos[0]);
        }

        
        // Selección de usuario
        elseif ($c === 0.5 && isset($arrayDatos[0]) && ($arrayDatos[0] == 1 || $arrayDatos[0] == 1.5)) {
            
            $this->setNewUser($arrayDatos[1], $arrayDatos[2] );
            $this->miEstado->IdUsuarioSeleccionado = $arrayDatos[1];
            if ($arrayDatos[0] == 1) {
                $this->navegarPestanas(1);
            } elseif ($arrayDatos[0] == 1.5) {
                $this -> miEstado -> capturas = get_pescado( $this->miEstado->IdUsuarioSeleccionado);
                $this -> miEstado -> tiposalmacen = get_tiposAlmacen($this->miEstado->IdUsuarioSeleccionado);
                $this->navegarPestanas(0.125);
            }
        }

        // Vista de Barcos
        elseif ($c === 2 && isset($arrayDatos[0]) && $arrayDatos[0] == 2) {
            
            if (!empty($arrayDatos) && $arrayDatos[0] == 2 && $arrayDatos[1] == 1 && isset($arrayDatos[2])) {
                $arrayFiltrado = $this-> filtrarDesplegable($this -> miEstado -> capturas, $arrayDatos[2]);            
                $this->miEstado->capturasFiltradas = $arrayFiltrado;
            }

            $this->navegarPestanas(3);
        }

    
        // Detalles de captura
        elseif ($c === 3 && isset($arrayDatos[0]) && $arrayDatos[0] == 3) {
            $this->setNewCaptura($arrayDatos[1]);
            $this->miEstado->TagPez = $arrayDatos[1];
            $this->generarDatosGrafica($this->miEstado->temperaturas, $this->miEstado->almacenes);
            $dataSetGrafica = ["graficaTemperatura" => $this->miEstado->dataset];
            $arrayAuxiliarHtml = ["datos" => $dataSetGrafica, "color" => $arraycolor];
            $accionJs = 4;
            $this->navegarPestanas(4);
        }
    
    
        // Filtros
        //Header
        elseif (!empty($arrayDatos) && $arrayDatos[0] == 0 && $arrayDatos[1] == 0 && isset($arrayDatos[2])) {

           

            if ($arrayDatos[2] == null) {

                $this -> resetFilter($this -> miEstado -> Estado);

                if ($c == 4){
                    $this -> generarDatosGrafica($this->miEstado->temperaturas, $this->miEstado->almacenes);
                    $dataSetGrafica = ["graficaTemperatura" => $this->miEstado->dataset];
                    $arrayAuxiliarHtml = ["datos" => $dataSetGrafica, "color" => $arraycolor];
                    
                    $accionJs = 4;
                }

            }
            else{
                $arrayFiltrado = $this->filtrarSimple($arrayDatos[2], $c);
                
        
                switch ($c) {
                    case 0.25:
                        $this -> miEstado -> tiposalmacenFiltrados = $arrayFiltrado;
                        break;
                    case 0.5:
                        $this -> miEstado -> usuariosFiltrados = $arrayFiltrado;
                        break;
                    case 2:
                        $this -> miEstado -> barcosFiltrados = $arrayFiltrado;
                        break;
                    case 3:
                        $this -> miEstado -> capturasFiltradas = $arrayFiltrado;
                        break;
                    case 4:
                        $this -> miEstado -> almacenesFiltrados = $arrayFiltrado;
                        if ($this -> miEstado -> almacenesFiltrados){
                            $this->generarDatosGrafica($this->miEstado->temperaturas, $this->miEstado->almacenesFiltrados);
                        }
                        else{
                            $this->generarDatosGrafica($this->miEstado->temperaturas, $this->miEstado->almacenes);
                        }
                        $dataSetGrafica = ["graficaTemperatura" => $this->miEstado->dataset];
                        $arrayAuxiliarHtml = ["datos" => $dataSetGrafica, "color" => $arraycolor];
                        /* $arrayAuxiliarHtml= ["graficaTemperatura" => $this->miEstado->dataset]; */
                        $accionJs = 4;
                        break;
                }
            }       
        }

        //Desplegable
        elseif (!empty($arrayDatos) && $arrayDatos[0] == 0 && $arrayDatos[1] == 1 && isset($arrayDatos[2])) {
            $arrayFiltrado = $this-> filtrarDesplegable($this -> miEstado -> capturas, $arrayDatos[2]);            
            $this->miEstado->capturasFiltradas = $arrayFiltrado;
            
        }
        
        

        $txtErr = "";

        $txtErr = sprintf(
            "idUsuarioLogIn : %s<br>idUsuarioElegido: %s<br>IdLastUser: %s<br>TagPez: %s<br>LastTagPez: %s<br>Estado: %s<br>IdBoton: %s<br>EstadosAnteriores: %s<br>ArrayDatos: %s",
            $this->miEstado->IdUsuarioLogin,
            $this->miEstado->IdUsuarioSeleccionado,
            $this->miEstado->IdLastUser,
            $this->miEstado->TagPez,
            $this->miEstado->LastTagPez,
            $this->miEstado->Estado,
            $this->miEstado->idBoton,
            implode(",", $this->miEstado->EstadosAnteriores),
            json_encode($arrayDatos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        ); 

    


    
        return [
            pinta_contenido($this->miEstado->Estado) . $txtErr,
            $msgError,
            $AccionSinRepintar,
            $arrayAuxiliarHtml,
            $accionJs,
        ];
    }


    
        
}

?>
