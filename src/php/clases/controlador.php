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
            if($this -> miEstado-> EstadosAnteriores[""] == 0 && $this->miEstado->Estado == 1){
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
    

    function IniciarSesion($usuario, $contrasena){
        
        $datosSesion = comprueba_usuario($usuario, $contrasena);
        $this -> miEstado = new Estado();
        $this -> miEstado -> Estado = 0;
        $this -> miEstado-> IdPersonal = $datosSesion[0];
        
        if($datosSesion != false && $datosSesion != 0){
            return true;
        }
        elseif($datosSesion == 0){
            return 0;
        }
        else{
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
        if($c === 0 && !empty($arrayDatos) && $arrayDatos[0] != -1 ){
            //Log In//
            $nav = 0;
            $InicioS = $this -> IniciarSesion($arrayDatos[0], $arrayDatos[1]);
            if($InicioS ===false){
                $msgError = "Error de conexión con el servidor, por favor inténtelo más tarde.";
            }elseif($InicioS === 0){
                $msgError = "Usuario o contraseña incorrectos.";
            }elseif($InicioS === true){
                $this -> miEstado -> Estado = 1;
                $nav = 1;
            }
            $this -> navegarPestanas($nav);
        }elseif($c === 1 && !empty($arrayDatos) && $arrayDatos[0] == 3 || $arrayDatos[0] == 4){
            
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
        }elseif($arrayDatos[0]== -1){
            $this -> navegarPestanas(-1);
        }
        $txtErr = "";

        
        return array(pinta_contenido($this -> miEstado -> Estado).$txtErr,$msgError,$AccionSinRepintar,$arrayAuxiliarHtml,$accionJs);
    }
}

?>
