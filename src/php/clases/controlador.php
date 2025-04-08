<?php

require_once 'estado.php';
require_once 'pinta.php'; 

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
    }

    function navegarPestanas($ps){
        //volver a la aterior
        if($ps == -1){
            //salir del modo formulario
            
            if($this -> miEstado -> cargarForm == 1){
                $this -> miEstado -> cargarForm = 0;
            }else{
                $estadoAnterior = array_shift($this -> miEstado -> EstadosAnteriores);
                $this -> miEstado -> Estado = $estadoAnterior;
            }
            //reinicializar variables
            
            $this -> miEstado -> nombreDocumentoPadre = null;
            $this -> miEstado -> IdPropietario = null; 
        }else{
            array_unshift($this -> miEstado -> EstadosAnteriores , $this -> miEstado -> Estado);
            $this -> miEstado -> Estado = $ps;
        }
        $this -> miEstado -> CadenaFiltro = null;
        $this -> miEstado -> IdsTiposFiltro = array();
        $this -> miEstado -> adjuntarDocumentoFormAutomatico = 0;
    }

    function IniciarSesion($usuario, $contrasena){
        
        $datosSesion = comprueba_usuario($usuario, $contrasena);
        
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


    function generarContenido($arrayDatos = array()){
        $arrayAuxiliarHtml = array();
        $accionJs = null;
        $msgError = "" ;
        $AccionSinRepintar = 0;
        $c = $this -> miEstado -> Estado; 

        $nav = "";
        if($c === 0 && !empty($arrayDatos) && $arrayDatos[0] != -1 && ($this -> miEstado -> tipo_App == 1 || $this -> miEstado -> tipo_App == 2)){
            $nav = 0;
            echo"$arrayDatos[0] $arrayDatos[1]";
            $InicioS = $this -> IniciarSesion($arrayDatos[0], $arrayDatos[1]);
            if($InicioS ===false){
                $msgError = "Error de conexión con el servidor, por favor inténtelo más tarde.";
            }elseif($InicioS === 0){
                $msgError = "Usuario o contraseña incorrectos.";
            }
        
        }
        $txtErr = "";
        return array(pinta_contenido($this -> miEstado -> Estado).$txtErr,$msgError,$AccionSinRepintar,$arrayAuxiliarHtml,$accionJs);
    }
}

?>
