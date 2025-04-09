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
            $filetext = str_replace("%LineasE%", DibujaTablaBarcos(),$filetext);
        }

       

        return $filetext;
    }

    function DibujaTablaBarcos() {
        $listaBarcos = "<section>";

    
        if (!empty($_SESSION["barcos"])) {
            $listaBarcos .= "<table class='table table-striped table-bordered-bottom'>";
            $listaBarcos .= "<thead><tr>
                                <th>ID Barco</th>
                                <th>ID Usuario</th>
                                <th>Nombre</th>
                                <th>Código</th>
                             </tr></thead>";
            $listaBarcos .= "<tbody>";

            
    
            foreach ($_SESSION["barcos"] as $barco) {
                $listaBarcos .= "<tr>";
                $listaBarcos .= "<td>" . htmlspecialchars($barco["IdBarco"]) . "</td>";
                $listaBarcos .= "<td>" . htmlspecialchars($barco["IdUsuario"]) . "</td>";
                $listaBarcos .= "<td>" . htmlspecialchars($barco["Nombre"]) . "</td>";
                $listaBarcos .= "<td>" . htmlspecialchars($barco["Codigo"]) . "</td>";
                $listaBarcos .= "</tr>";
            }

        } else {
            $listaBarcos .= "<p>No hay barcos registrados.</p>";
        }
    
        $listaBarcos .= "</section>";
        return $listaBarcos;
    }
    


?>
