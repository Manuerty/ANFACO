<?php

use Pdo\Sqlite;
    require_once 'Conexion.php';

    // Función para obtener la conexión a la base de datos
    function obtener_conexion() {
        return ConexionBD("localhost", "prueba_1", "root", "");
    }

    function comprueba_usuario($usuario, $contrasena) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "SELECT IdUsuario, Usuario, Contrasena, Rol FROM usuarios WHERE Usuario = ? AND Contrasena = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }

            $stmt->bind_param("ss", $usuario, $contrasena);
            
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $stmt->close();
            $conn->close();

            return $row ? array($row['IdUsuario'], $row['Usuario'], $row['Contrasena'], $row['Rol']) : 0;
        } catch (Exception $e) {
            return false;
        }
    }

    function get_usuarios(){
        try{

            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = 'SELECT IdUsuario, Usuario, Contrasena, Rol
                    FROM usuarios
                    WHERE Rol != "Administrador"';

            $stmt = $conn->prepare($sql);


            if (!$stmt->execute()) {
                $stmt->close();
                return false;
            }


            $result = $stmt->get_result();

            $usuarios = [];

            while ($row = $result->fetch_assoc()) {
                $usuarios[] = [

                    'IdUsuario'=> $row['IdUsuario'],
                    'NombreUsuario'=> $row['Usuario'],
                    'Contrasena'=> $row['Contrasena'],
                    'Rol'=> $row['Rol'],
                ];
            }

            $stmt->close();
            $conn->close();

            // Guardar en variable de sesión como un array plano, sin agrupar por TagPez
                
            return $usuarios;

        }catch (Exception $e) {
            return false;
        }
    }

    function get_Captura($tag_pez = null) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;


    
            // Si no se proporciona un TagPez, devolvemos false
            if (!$tag_pez) return false;




    
            // Consulta SQL para obtener la captura específica por TagPez
            $sql = "SELECT bodegas.IdBodega, bodegas.Zona, bodegas.Especie, bodegas.FechaCaptura, bodegas.TagPez, 
                           barcos.Nombre as Barco, barcos.IdBarco, 
                           UltimaFecha.FechaUltimoAlmacen, UltimaFecha.CuentaAlmacen, 
                           MaxTemperatura.temperaturaMaxima, MaxTemperatura.temperaturaMinima, 
                           AlmacenUltimo.IdTipoAlmacen, tiposalmacen.Nombre, barcos.Codigo, usuarios.Usuario  
                    FROM bodegas 
                    LEFT JOIN (
                        SELECT TagPez, MAX(fecha) AS FechaUltimoAlmacen, COUNT(TagPez) AS CuentaAlmacen 
                        FROM almacen GROUP BY TagPez
                    ) UltimaFecha ON bodegas.TagPez = UltimaFecha.TagPez
                    LEFT JOIN (
                        SELECT MAX(temperatura) AS temperaturaMaxima, MIN(temperatura) AS temperaturaMinima, TagPez 
                        FROM almacen 
                        INNER JOIN almacen_temperaturas ON almacen.ID = almacen_temperaturas.ID
                        GROUP BY TagPez
                    ) MaxTemperatura ON MaxTemperatura.TagPez = bodegas.TagPez
                    LEFT JOIN barcos ON barcos.IdBarco = bodegas.IdBarco 
                    LEFT JOIN usuarios ON barcos.IdUsuario = usuarios.IdUsuario
                    LEFT JOIN almacen AlmacenUltimo ON AlmacenUltimo.TagPez = bodegas.TagPez AND AlmacenUltimo.Fecha = UltimaFecha.FechaUltimoAlmacen
                    LEFT JOIN tiposalmacen ON tiposalmacen.IdTipoAlmacen = AlmacenUltimo.IdTipoAlmacen
                    WHERE bodegas.TagPez = ?"; // Filtrar por TagPez específico
    
            $stmt = $conn->prepare($sql);


            
    
            if (!$stmt) {
                $conn->close();
                return false;
            }
    
            // Vinculamos el parámetro TagPez
            $stmt->bind_param("s", $tag_pez); // "s" es para string, ya que TagPez puede ser un string

    
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }

    
            $result = $stmt->get_result();

            $captura = [];

         
    
            // Si encontramos un resultado, lo agregamos al array de captura
            if ($row = $result->fetch_assoc()) {
                $captura = [
                    'IdBodega'             => $row['IdBodega'],
                    'Zona'                 => $row['Zona'],
                    'Especie'              => $row['Especie'],
                    'FechaCaptura'         => $row['FechaCaptura'],
                    'TagPez'               => $row['TagPez'],
                    'NombreBarco'          => $row['Barco'],
                    'IdBarco'              => $row['IdBarco'],
                    'FechaUltimoAlmacen'   => $row['FechaUltimoAlmacen'],
                    'CuentaAlmacen'        => $row['CuentaAlmacen'],
                    'TemperaturaMaxima'    => $row['temperaturaMaxima'],
                    'TemperaturaMinima'    => $row['temperaturaMinima'],
                    'IdTipoAlmacen'        => $row['IdTipoAlmacen'],
                    'TipoAlmacen'          => $row['Nombre'],
                    'NombreUsuario'        => $row['Usuario'],
                ];
            }

            
    
            $stmt->close();
            $conn->close();

            // Retornar el resultado
            
            return $captura;
    
        } catch (Exception $e) {
            return false;
        }
    }
    

    function get_capturas($idUsuario = null) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;
    
            // Si el $idUsuario es proporcionado, se agrega un filtro para ese usuario
            $sql = "SELECT bodegas.IdBodega, bodegas.Zona, bodegas.Especie, bodegas.FechaCaptura, bodegas.TagPez, 
                           barcos.Nombre as Barco, barcos.IdBarco, 
                           UltimaFecha.FechaUltimoAlmacen, UltimaFecha.CuentaAlmacen, 
                           MaxTemperatura.temperaturaMaxima, MaxTemperatura.temperaturaMinima, 
                           AlmacenUltimo.IdTipoAlmacen, tiposalmacen.Nombre, barcos.Codigo  
                    FROM bodegas 
                    LEFT JOIN (
                        SELECT TagPez, MAX(fecha) AS FechaUltimoAlmacen, COUNT(TagPez) AS CuentaAlmacen 
                        FROM almacen GROUP BY TagPez
                    ) UltimaFecha ON bodegas.TagPez = UltimaFecha.TagPez
                    LEFT JOIN (
                        SELECT MAX(temperatura) AS temperaturaMaxima, MIN(temperatura) AS temperaturaMinima, TagPez 
                        FROM almacen 
                        INNER JOIN almacen_temperaturas ON almacen.ID = almacen_temperaturas.ID 
                        GROUP BY TagPez
                    ) MaxTemperatura ON MaxTemperatura.TagPez = bodegas.TagPez
                    LEFT JOIN barcos ON barcos.IdBarco = bodegas.IdBarco 
                    LEFT JOIN almacen AlmacenUltimo ON AlmacenUltimo.TagPez = bodegas.TagPez AND AlmacenUltimo.Fecha = UltimaFecha.FechaUltimoAlmacen
                    LEFT JOIN tiposalmacen ON tiposalmacen.IdTipoAlmacen = AlmacenUltimo.IdTipoAlmacen";
    
            // Si se pasó un IdUsuario, filtramos los datos por ese IdUsuario
            if ($idUsuario) {
                $sql .= " WHERE bodegas.IdBarco IN (SELECT IdBarco FROM barcos WHERE IdUsuario = ?)";
            }

            $sql .= " ORDER BY FechaCaptura DESC";
    
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $conn->close();
                return false;
            }
    
            // Si hay un IdUsuario, lo vinculamos a la consulta
            if ($idUsuario) {
                $stmt->bind_param("i", $idUsuario);
            }      
    
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }
    
            $result = $stmt->get_result();
            $capturas = [];
    
            while ($row = $result->fetch_assoc()) {
                $capturas[] = [
                    'IdBodega'             => $row['IdBodega'],
                    'Zona'                 => $row['Zona'],
                    'Especie'              => $row['Especie'],
                    'FechaCaptura'         => $row['FechaCaptura'],
                    'TagPez'               => $row['TagPez'],
                    'NombreBarco'          => $row['Barco'],
                    'IdBarco'              => $row['IdBarco'],
                    'FechaUltimoAlmacen'   => $row['FechaUltimoAlmacen'],
                    'CuentaAlmacen'        => $row['CuentaAlmacen'],
                    'TemperaturaMaxima'    => $row['temperaturaMaxima'],
                    'TemperaturaMinima'    => $row['temperaturaMinima'],
                    'IdTipoAlmacen'        => $row['IdTipoAlmacen'],
                    'TipoAlmacen'          => $row['Nombre'],
                ];
            }
    
            $stmt->close();
            $conn->close();

            // Guardar en variable de sesión como un array plano, sin agrupar por TagPez
                
            return $capturas;
    
        } catch (Exception $e) {
            return false;
        }
    }

    function get_Barcos($idUsuario = null) {

        
        try{
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "SELECT IdBarco, Nombre, Codigo FROM barcos";
            
            // Si se pasó un IdUsuario, filtramos los datos por ese IdUsuario
            if ($idUsuario) {
                $sql .= " WHERE  idUsuario = ? ";
            }

            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }
    
            // Si hay un IdUsuario, lo vinculamos a la consulta
            if ($idUsuario) {
                $stmt->bind_param("i", $idUsuario);
            }
    
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }
    
            $result = $stmt->get_result();
            $barcos = [];
    
            while ($row = $result->fetch_assoc()) {
                $barcos[] = [

                    "IdBarco" => $row['IdBarco'],
                    "Nombre" => $row['Nombre'],
                    "CodigoBarco" => $row['Codigo'],
                ];
            }
    
            $stmt->close();
            $conn->close();

            return $barcos;

        }catch (Exception $e) {
            
            return false;
        }
    }

    function get_Temperaturas($tagPez) {
        try {

            $conn = obtener_conexion();
            if (!$conn) return false;
    
            $sql = "SELECT aTmp.Fecha, aTmp.Temperatura, atmp.Id
                    FROM almacen_temperaturas aTmp
                    JOIN almacen a ON aTmp.Id = a.Id
                    WHERE a.TagPez = ?
                    ORDER BY aTmp.Fecha DESC";
    
            $stmt = $conn->prepare($sql);
    
            if (!$stmt) {
                $conn->close();
                return false;
            }
    
            // Si hay un tagPez, lo vinculamos a la consulta
            if ($tagPez) {
                $stmt->bind_param("s", $tagPez); // "s" porque TagPez es tipo string
            }
    
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }
    
            $result = $stmt->get_result();
            $temperaturas = [];
    
            while ($row = $result->fetch_assoc()) {
                $temperaturas[] = [
                    "FechaTemperatura" => $row["Fecha"],
                    "ValorTemperatura" => $row["Temperatura"],
                    "IdLector" => $row["Id"]
                ];
            }
    
            $stmt->close();
            $conn->close();
    
            return $temperaturas;
    
        } catch (Exception $e) {
            return false;
        }
    }

    function get_Almacenes($tagPez){
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;
    
            $sql = "SELECT  Fecha, LectorRFID, tiposalmacen.Nombre, tiposalmacen.IdTipoAlmacen, Id  
                            FROM almacen 
                            LEFT JOIN tiposalmacen ON tiposalmacen.IdTipoAlmacen = almacen.IdTipoAlmacen
                            WHERE almacen.TagPez = ?
                    UNION
                    SELECT FechaCaptura as Fecha, 'Bodega' as LectorRFID, 'Bodega'as Nombre, 0 as IdTipoAlmacen, 0 as Id
                                            FROM bodegas
                                            WHERE bodegas.TagPez = ?
                                            ORDER BY FECHA DESC;";

    
            $stmt = $conn->prepare($sql);
    
            if (!$stmt) {
                $conn->close();
                return false;
            }
    
            
            $stmt->bind_param("ss", $tagPez, $tagPez);
            
    
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }
    
            $result = $stmt->get_result();
            $almacenes = [];
    
            while ($row = $result->fetch_assoc()) {
                $almacenes[] = [
                    "FechaAlmacen" => $row["Fecha"],
                    "Lector" => $row["LectorRFID"],
                    "NombreTipo"=> $row["Nombre"],
                    "IdTipo"=> $row["IdTipoAlmacen"],
                    "IdAlmacen"=> $row["Id"]
                ];
            }
    
            $stmt->close();
            $conn->close();
    
            return $almacenes;
    
        } catch (Exception $e) {
            return false;
        }

    }
    
?>
