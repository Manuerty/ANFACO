<?php

    function pinta_contenido($estado){
        $titulo = ""; 
        $cabecera = "../html/header.html";
        $fileheadertext = "";
        
        switch($estado){

            case 0:
                $cabecera = "";
                $filename = "../html/login.html";
                break;
            case 1:
                $titulo = "Dashboard";
                $filename = "../html/dashboard.html";
                break;
            case 2:
                $titulo = "Barcos";
                $filename = "../html/documentos.html";
                break;
            case 3:
                $titulo = "Capturas";
                $filename = "../html/documentos.html";
                break;
        }

        if ($cabecera != ""){

            $fileheader = fopen($cabecera, "r");
            $filesize = filesize($cabecera);
            $fileheadertext = fread($fileheader, length: $filesize);
            fclose($fileheader);

            $fileheadertext = str_replace("%NombreE%",$titulo,$fileheadertext);

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

        if($_SESSION["Controlador"] -> miEstado -> Estado == 2){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica("barcos"),$filetext);
        }elseif($_SESSION["Controlador"] -> miEstado -> Estado == 3){
            $filetext = str_replace("%LineasE%", DibujaTablaGenerica("capturas"),$filetext);
        }
        return $filetext;
    }

    function DibujaTablaGenerica($nombreVariableSesion, $tituloAlternativo = "No hay datos disponibles.") {
        $html = "<section>";
        
        
        if (!empty($_SESSION[$nombreVariableSesion]) && is_array($_SESSION[$nombreVariableSesion])) {
            $datos = $_SESSION[$nombreVariableSesion];

            
    
            // Comprobar que hay al menos un elemento y es un array asociativo
            if (!empty($datos[0]) && is_array($datos[0])) {
                $html .= "<table class='table table-striped table-bordered-bottom'>";
                $html .= "<thead><tr>";
    
                // Cabecera: obtener las claves del primer elemento
                foreach (array_keys($datos[0]) as $columna) {
                    $html .= "<th>" . htmlspecialchars($columna) . "</th>";
                }

                
    
                $html .= "</tr></thead><tbody>";
    
                // Filas
                foreach ($datos as $fila) {
                    $html .= "<tr>";
                    foreach ($fila as $valor) {
                        $html .= "<td>" . htmlspecialchars($valor) . "</td>";
                    }
                    $html .= "</tr>";
                }
    
                $html .= "</tbody></table>";
            } else {
                $html .= "<p>$tituloAlternativo</p>";
            }
        } else {
            $html .= "<p>$tituloAlternativo</p>";
        }
    
        $html .= "</section>";
        return $html;
    }
    
    


?>
