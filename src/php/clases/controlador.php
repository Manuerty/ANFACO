<?php

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


            //reinicializar variables
            if($this -> miEstado -> Estado == 0){
                $this -> cerrarSesion();
                $_SESSION["Usuarios"] = array();
                
            } elseif( $_SESSION["es_admin"] && $this -> miEstado -> Estado == 0.5){
                $this -> miEstado -> IdUsuario = null;
                $_SESSION["Capturas"] = array();
                $_SESSION["Barcos"] = array();
            }
            
            //reinicializar variables
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

    function set_and_print($id_user){
        $_SESSION["Capturas"] = get_capturas($id_user);
        $_SESSION["Barcos"] = get_Barcos($id_user);
    }

    function generarContenido($arrayDatos = array()){

        $arrayAuxiliarHtml = array();
        $accionJs = null;
        $msgError = "" ;
        $AccionSinRepintar = 0;
    
        $c = $this -> miEstado -> Estado; 
    
        $nav = "";
        // Asegurarse de que $arrayDatos tenga al menos un elemento antes de acceder a $arrayDatos[0]
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
        elseif($c === 0.5 && !empty($arrayDatos) && isset($arrayDatos[0]) && ($arrayDatos[0] == 1)){

            $this ->miEstado -> IdUsuario = $arrayDatos[1];
            $this -> set_and_print($this -> miEstado -> IdUsuario);
            $nav = null;
            switch($arrayDatos[0]){
                case 1:
                    $nav = 1;
                    break;
            }

            $this -> navegarPestanas($nav);
        }

        elseif($c === 1 && !empty($arrayDatos) && isset($arrayDatos[0]) && ($arrayDatos[0] == 3 || $arrayDatos[0] == 4)){
            
            
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
        }elseif($c == 3 && !empty($arrayDatos) && isset($arrayDatos[0]) && $arrayDatos[0] == 3){

            
            $nav = null;
            switch($arrayDatos[0]){
                case 3:
                    $nav = 4;
                    break;
            }
            $this -> navegarPestanas($nav);
        }
        elseif(isset($arrayDatos[0]) && $arrayDatos[0] == -1){
            $this -> navegarPestanas(-1);
        }
        
        $txtErr = "";

        
        $txtErr = "idUsuarioLogIn : ".$this -> miEstado -> IdUsuarioLogin.
        "<br> idUsuarioElegido: ".$this -> miEstado -> IdUsuario."<br> Estado: ".$this -> miEstado -> Estado.
        "<br> EstadosAnteriores: ".implode(",",$this -> miEstado -> EstadosAnteriores).
        "<br> ArrayDatos: ".implode($arrayDatos).
        "<br> Capturas: ".count($_SESSION["Capturas"]).
        "<br> Barcos: ".count($_SESSION["Barcos"]).
        "<br> Users: ".count($_SESSION["Usuarios"]);   
    
        return array(pinta_contenido($this -> miEstado -> Estado).$txtErr,$msgError,$AccionSinRepintar,$arrayAuxiliarHtml,$accionJs);
    }
        
}

?>
