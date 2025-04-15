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
            if( $this->miEstado->Estado == 1 or $this->miEstado->Estado == 0.5){
                $this->miEstado->Estado = 0;
            } else {
                $estadoAnterior = array_shift($this->miEstado->EstadosAnteriores);
                $this->miEstado->Estado = $estadoAnterior;
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
    
            // Puedes hacer lógica condicional si necesitas
            $_SESSION["es_admin"] = ($datosSesion[3] === "Administrador") ? true : false;

            $usuarios = [];

            if($_SESSION["es_admin"]){
                $capturas = get_all_data();
                $barcos = get_Barcos();
                $usuarios = get_users();
                
            }else{
                $capturas = get_all_data($datosSesion[0]);
                $barcos = get_Barcos($datosSesion[0]);
            }

            $_SESSION ["AllData"] = $capturas;
            $_SESSION ["Barcos"] = $barcos;
            $_SESSION ["Usuarios"] = $usuarios;

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
        $this -> miEstado -> tipo_App = $_SESSION["TipoPortal"];
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

        $this -> miEstado -> Estado = 1;

        $this -> miEstado -> IdUsuario = $id_user;

        $_SESSION["AllData"] = get_all_data($id_user);
        $_SESSION["Barcos"] = get_Barcos($id_user);

        echo "hola";

        
        $this -> miEstado -> Estado = 1;
        $nav = 1;
        $this -> navegarPestanas($nav);

    }
    


    function generarContenido($arrayDatos = array()){
        $arrayAuxiliarHtml = array();
        $accionJs = null;
        $msgError = "" ;
        $AccionSinRepintar = 0;

        
    
        if($this -> miEstado -> Estado == null){
            $this -> miEstado -> Estado = 0;
        }
    
        $c = $this -> miEstado -> Estado; 

        
    
        $nav = "";
        // Asegurarse de que $arrayDatos tenga al menos un elemento antes de acceder a $arrayDatos[0]
        if($c === 0 or $c === 0.5 && !empty($arrayDatos) && isset($arrayDatos[0]) && $arrayDatos[0] != -1){
            //Log In//
            $nav = 0;
            $InicioS = $this -> IniciarSesion($arrayDatos[0], $arrayDatos[1]);
            if($InicioS ===false){
                $msgError = "Error de conexión con el servidor, por favor inténtelo más tarde.";
            }elseif($InicioS === 0){
                $msgError = "Usuario o contraseña incorrectos.";
            }elseif($InicioS === true){
                if($_SESSION["es_admin"]){
                    $this -> miEstado -> Estado = 0.5;
                    $nav = 0.5;
                }else{
                    $this -> miEstado -> Estado = 1;
                    $nav = 1;
                }
            }   
            $this -> navegarPestanas($nav);
        }
        elseif($c === 0.5 && !empty($arrayDatos) && isset($arrayDatos[0]) && ($arrayDatos[0] == 1)){

            $nav = null;
            switch($arrayDatos[0]){
                case 1:
                    $nav = 1;
                    break;
            }
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
    
        return array(pinta_contenido($this -> miEstado -> Estado).$txtErr,$msgError,$AccionSinRepintar,$arrayAuxiliarHtml,$accionJs);
    }
        
}

?>
