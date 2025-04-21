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
            $_SESSION["es_admin"] = ($datosSesion[3] === "Administrador");

            // Inicializar variables
            $usuarios = [];
            $capturas = [];
            $barcos = [];
            $temperaturas = [];
            $almacenes = [];

            // Obtener datos según el tipo de usuario
            if ($_SESSION["es_admin"]) {
                $usuarios = get_users();
                
                
            } else {
                $capturas = get_capturas($datosSesion[0]);
                $barcos = get_Barcos($datosSesion[0]);
                
            }

            // Asignar a sesión, usando operador ternario o directamente el valor
            $_SESSION["Capturas"] = $capturas ?: [];
            $_SESSION["Barcos"] = $barcos ?: [];
            $_SESSION["Usuarios"] = $usuarios ?: [];
            $_SESSION["Temperaturas"] = $temperaturas ?: [];
            $_SESSION["Almacenes"] =  $almacenes ?:[];


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
        $_SESSION["Usuarios"] = [];
        $_SESSION["Capturas"] = [];
        $_SESSION["Barcos"] = [];
        $_SESSION["Temperaturas"] = [];
        $_SESSION["Almacenes"] = [];
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
            $_SESSION["Capturas"] = get_capturas($IdUser);
            $_SESSION["Barcos"] = get_Barcos($IdUser);
        }
    }


    function setNewCaptura($tagPez){
        if($tagPez == $this -> miEstado -> LastTagPez){
            return;
        } else {
            $this -> miEstado -> TagPez = $tagPez;

            // Obtener los datos adicionales de la captura
            $_SESSION["Temperaturas"] = get_Temperaturas($tagPez);
            $_SESSION["Almacenes"] = get_Almacenes($tagPez);
    
            // Llamar a details_Captura para llenar la variable de sesión con los detalles de la captura
            $this->details_Captura($tagPez);
    
            // Ahora $_SESSION["CapturaDetalle"] debería tener los datos si la captura fue encontrada
            
        }
    }
    

    function details_Captura($tagPez){
        // Verificar si las capturas están disponibles en la sesión
        if (isset($_SESSION["Capturas"]) && !empty($_SESSION["Capturas"])) {
            
            // Buscar la captura que coincide con el tagPez directamente usando array_filter
            $captura = array_filter($_SESSION["Capturas"], function($item) use ($tagPez) {
                return $item['TagPez'] == $tagPez;
            });
    
            // Si encontramos la captura, almacenamos el primer resultado en la sesión
            if (!empty($captura)) {
                $_SESSION["CapturaDetalle"] = array_values($captura)[0]; // Tomamos el primer elemento de la array filtrado
                return true; // Se encontró la captura
            }
        }
    
        // Si no se encuentra la captura o no hay capturas en la sesión
        $_SESSION["CapturaDetalle"] = null;
        return false; // No se encontró la captura
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
                if($_SESSION["es_admin"]){
                    
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
        //Logica Detalle de captura//
        elseif($c == 3 && !empty($arrayDatos) && isset($arrayDatos[0]) && $arrayDatos[0] == 3){
 
            $nav = null;

            $this -> setNewCaptura($arrayDatos[1]);
            $this ->miEstado -> TagPez = $arrayDatos[1];
            
            switch($arrayDatos[0]){
                case 3:
                    $nav = 4;
                    break;
            }
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
        

         
        $txtErr = "";

        
        $txtErr = "idUsuarioLogIn : ".$this -> miEstado -> IdUsuarioLogin.
        "<br> idUsuarioElegido: ".$this -> miEstado -> IdUsuarioSeleccionado.
        "<br> IdLastUser: ".$this -> miEstado -> IdLastUser.
        "<br> TagPez: ".$this -> miEstado -> TagPez.
        "<br> LastTagPez: ".$this -> miEstado -> LastTagPez.
        "<br> Estado: ".$this -> miEstado -> Estado.
        "<br> EstadosAnteriores: ".implode(",",$this -> miEstado -> EstadosAnteriores).
        "<br> Capturas: ".count($_SESSION["Capturas"]).
        "<br> Barcos: ".count($_SESSION["Barcos"]).
        "<br> Users: ".count($_SESSION["Usuarios"]).
        "<br> Temperaturas: ".count($_SESSION["Temperaturas"]).
        "<br> Almacenes: ".count($_SESSION["Almacenes"]).
        "<br> ArrayDatos: ".implode($arrayDatos);   
    
        return array(pinta_contenido($this -> miEstado -> Estado).$txtErr,$msgError,$AccionSinRepintar,$arrayAuxiliarHtml,$accionJs);
    }
        
}

?>
