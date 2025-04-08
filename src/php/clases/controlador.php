<?php


require_once 'pinta.php'; 
Class Controlador{

    
    
    public $miEstado;

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
