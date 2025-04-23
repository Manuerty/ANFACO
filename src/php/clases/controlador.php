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
    
        if ($datosSesion != false && $datosSesion != 0) {
            $this->miEstado->IdUsuarioLogin = $datosSesion[0];
    
            // Determinar si es administrador
            $this -> miEstado -> esAdmin = ($datosSesion[3] === "Administrador");


            // Inicializar variables
            $usuarios = [];
            $capturas = [];
            $barcos = [];
            $temperaturas = [];
            $almacenes = [];

            // Obtener datos según el tipo de usuario
            if ($this -> miEstado -> esAdmin) {
                $usuarios = get_usuarios();
                
                
            } else {
                $capturas = get_capturas($datosSesion[0]);
                $barcos = get_Barcos($datosSesion[0]);
                
            }

            // Asignar a sesión, usando operador ternario o directamente el valor
            $this -> miEstado -> capturas = $capturas ?: [];
            $this -> miEstado -> barcosFiltrados = $barcos ?: [];
            $this -> miEstado -> usuarios = $usuarios ?: [];
            $this -> miEstado -> temperaturas = $temperaturas ?: [];
            $this -> miEstado -> almacenes =  $almacenes ?:[];


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

    function setNewUser($IdUser){

        if ($IdUser == $this -> miEstado -> IdLastUser) {
            return;
        }
        else{
            $this -> miEstado -> IdUsuarioSeleccionado = $IdUser;
            $this -> miEstado -> capturas = get_capturas($IdUser);
            $this -> miEstado -> barcos = get_Barcos($IdUser);
        }
    }


    function setNewCaptura($tagPez){
        if($tagPez == $this -> miEstado -> LastTagPez){
            return;
        } else {
            $this -> miEstado -> TagPez = $tagPez;

            // Obtener los datos adicionales de la captura
            $this -> miEstado -> temperaturas = get_Temperaturas($tagPez);
            $this -> miEstado -> almacenes = get_Almacenes($tagPez);
    
            // Llamar a details_Captura para llenar la variable de sesión con los detalles de la captura
            $this->details_Captura($tagPez);
    
            // Ahora $this -> miEstado -> capturaDetalle debería tener los datos si la captura fue encontrada
            
        }
    }
    

    function details_Captura($tagPez){
        // Verificar si las capturas están disponibles en la sesión
        if (isset($this -> miEstado -> capturas) && !empty($this -> miEstado -> capturas)) {
            
            // Buscar la captura que coincide con el tagPez directamente usando array_filter
            $captura = array_filter($this -> miEstado -> capturas, function($item) use ($tagPez) {
                return $item['TagPez'] == $tagPez;
            });
    
            // Si encontramos la captura, almacenamos el primer resultado en la sesión
            if (!empty($captura)) {
                $this -> miEstado -> capturaDetalle = array_values($captura)[0]; // Tomamos el primer elemento de la array filtrado
                return true; // Se encontró la captura
            }
        }
    
        // Si no se encuentra la captura o no hay capturas en la sesión
        $this -> miEstado -> capturaDetalle = null;
        return false; // No se encontró la captura
    }

    function generarDatosGrafica($temperaturasVS, $almacenesVS) {
        $temperaturas = $temperaturasVS;
        $almacenes = $almacenesVS;
        
        $dataset = [];
        foreach ($temperaturas as $temp) {
            foreach ($almacenes as $almacen) {
                if ($temp['IdLector'] == $almacen['IdAlmacen']) {
                    $dataset[] = [
                        "x" => strtotime($temp["FechaTemperatura"]) * 1000,
                        "y" => $temp["ValorTemperatura"],
                        "almacen" => "Almacén: " . $almacen["NombreTipo"] . " " . $almacen["IdTipo"]
                    ];
                    break;
                }
            }
        }
        $this -> miEstado -> dataset = $dataset;
    }  

    function filtrarNombre($filtro, $pestana){

        $filtro = strtolower($filtro);


        //filtro de usuarios
        if ($pestana == 0.5){
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
            $arrayFiltrado = array_filter($almacenes, function($item) use($filtro){
                return trim(strtolower($item["NombreTipo"])) === trim(strtolower($filtro));
            });

            
        }

        return $arrayFiltrado;
    }


    function generarContenido($arrayDatos = array()){

        
        $arrayAuxiliarHtml = array();
        $accionJs = null;
        $msgError = "" ;
        $AccionSinRepintar = 0;
    
        $c = $this -> miEstado -> Estado; 
    
        $nav = "";
        // Asegurarse de que $arrayDatos tenga al menos un elemento antes de acceder a $arrayDatos[0]
        //Logica Login//
        if($c === 0  && !empty($arrayDatos) && isset($arrayDatos[0]) && $arrayDatos[0] != -1){
            //Log In//
            $nav = 0;
            
            $InicioS = $this -> IniciarSesion($arrayDatos[0], $arrayDatos[1]);
            if($InicioS ===false){
                $msgError = "Error de conexión con el servidor, por favor inténtelo más tarde.";
            }elseif($InicioS === 0){
                $msgError = "Usuario o contraseña incorrectos.";
            }elseif($InicioS === true){
                if($this -> miEstado -> esAdmin){
                    
                    $nav = 0.5;
                }else{
                    
                    $nav = 1;
                }
            }   
            $this -> navegarPestanas($nav);
        }

        //Logica Seleccion de usuario//
        elseif($c === 0.5 && !empty($arrayDatos) && isset($arrayDatos[0]) && ($arrayDatos[0] == 1)){
            
            
            $this -> setNewUser($arrayDatos[1]);
            $this ->miEstado -> IdUsuarioSeleccionado = $arrayDatos[1];
            $nav = null;
            switch($arrayDatos[0]){
                case 1:
                    $nav = 1;
                    break;
            }

            $this -> navegarPestanas($nav);
        }

        //Logica Dashboard//
        elseif($c === 1  && !empty($arrayDatos) && isset($arrayDatos[0]) && ($arrayDatos[0] == 3 || $arrayDatos[0] == 4)){
            
            
            $nav = null;
            switch($arrayDatos[0]){
                case 3:
                    $nav = 2;
                    break;
                case 4:
                    $nav = 3;
                    break;
            }
            
            $this -> navegarPestanas($nav);
        }
        //Logica acceder a Detalles de Captura//
        elseif($c == 3 && !empty($arrayDatos) && isset($arrayDatos[0]) && $arrayDatos[0] == 3){
 
            $nav = null;

            $this -> setNewCaptura($arrayDatos[1]);
            $this -> generarDatosGrafica($this -> miEstado -> temperaturas, $this -> miEstado -> almacenes);
            $this ->miEstado -> TagPez = $arrayDatos[1];
            
            switch($arrayDatos[0]){
                case 3:
                    $nav = 4;
                    break;
            }
            $arrayAuxiliarHtml = array("graficaTemperatura" => $this -> miEstado -> dataset);
            $accionJs = 4;
            $this -> navegarPestanas($nav);
        }

        //logica de cerrar sesion//
        elseif (isset($arrayDatos[0]) && $arrayDatos[0] == -1 && $arrayDatos[1] == -1){
            $nav = 0;
            $this -> cerrarSesion();
            $this -> navegarPestanas($nav);
        }

        //Logica Boton Volver//
        elseif(isset($arrayDatos[0]) && $arrayDatos[0] == -1){
            $this -> navegarPestanas(-1);
        }


        //Logica de Filtros//

        elseif($c == 0.5  && !empty($arrayDatos) && $arrayDatos[0] == 0 && $arrayDatos[1] == 4 ){

            $arrayFiltrado = $this -> filtrarNombre($arrayDatos[2], $c);
            $this -> miEstado -> usuariosFiltrados = $arrayFiltrado;
        }

        elseif($c == 2  && !empty($arrayDatos) && $arrayDatos[0] == 0 && $arrayDatos[1] == 4 ){

            $arrayFlitrado = $this -> filtrarNombre($arrayDatos[2], $c);

            $this -> miEstado -> barcosFiltrados = $arrayFlitrado;
    
        }

        elseif($c == 3  && !empty($arrayDatos) && $arrayDatos[0] == 0 && $arrayDatos[1] == 4 ){

            $arrayFlitrado = $this -> filtrarNombre($arrayDatos[2], $c);

            $this -> miEstado -> capturasFiltradas = $arrayFlitrado;
    
        }

        elseif($c == 4  && !empty($arrayDatos) && $arrayDatos[0] == 0 && $arrayDatos[1] == 4 ){

            $arrayFlitrado = $this -> filtrarNombre($arrayDatos[2], $c);

            $this -> miEstado -> almacenesFiltrados = $arrayFlitrado;
    
        }

        
        
        
         
        $txtErr = "";

        
        $txtErr = "idUsuarioLogIn : ".$this -> miEstado -> IdUsuarioLogin.
        "<br> idUsuarioElegido: ".$this -> miEstado -> IdUsuarioSeleccionado.
        "<br> IdLastUser: ".$this -> miEstado -> IdLastUser.
        "<br> TagPez: ".$this -> miEstado -> TagPez.
        "<br> LastTagPez: ".$this -> miEstado -> LastTagPez.
        "<br> Estado: ".$this -> miEstado -> Estado.
        "<br> EstadosAnteriores: ".implode(",",$this -> miEstado -> EstadosAnteriores).
        
        "<br> ArrayDatos: ".implode($arrayDatos);   

        
    
        return array(pinta_contenido($this -> miEstado -> Estado).$txtErr,$msgError,$AccionSinRepintar,$arrayAuxiliarHtml,$accionJs);

        
    }
        
}

?>
