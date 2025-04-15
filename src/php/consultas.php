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

    function get_users(){
        try{

            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = 'SELECT IdUsuario, Usuario, Contrasena
                    FROM usuarios
                    WHERE Rol = "Usuarios"';

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
                    'Contrasena'=> $row['Contrasena']
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

    function get_all_data($idUsuario = null) {
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
?>
