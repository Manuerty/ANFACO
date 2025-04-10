<?php
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

    function get_barcos_usuario($id) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "SELECT IdBarco, IdUsuario, Nombre, Codigo FROM barcos WHERE IdUsuario = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }

            $stmt->bind_param("s", $id);

            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }

            $result = $stmt->get_result();
            $barcos = [];
            while ($row = $result->fetch_assoc()) {
                $barcos[] = $row;
            }

            $stmt->close();
            $conn->close();
            return $barcos;
        } catch (Exception $e) {
            return false;
        }
    }

    function get_capturas() {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "SELECT DISTINCT TagPez FROM almacen";
            $result = $conn->query($sql);

            $capturas = [];
            while ($row = $result->fetch_assoc()) {
                $tagPez = $row["TagPez"];

                // Obtener las temperaturas por TagPez
                $temperaturas = get_temperaturas_por_tag_pez($tagPez);
                if ($temperaturas !== false) {
                    foreach ($temperaturas as &$temperatura) {
                        $temperatura["FechaTemperatura"] = $temperatura["Fecha"];
                        unset($temperatura["Fecha"]);
                    }
                }

                $row["Temperaturas"] = $temperaturas ?? [];

                $temperaturasMaxMin = get_temperaturas_max_min_por_tag($tagPez);
                $ultimaPosicion = get_last_position($tagPez);
                
                if ($temperaturasMaxMin) {
                    $row = array_merge($row, $temperaturasMaxMin);
                } else {
                    $row["TotalTemperaturas"] = 0;
                    $row["TemperaturaMaxima"] = null;
                    $row["TemperaturaMinima"] = null;
                }

                if ($ultimaPosicion){
                    $row = array_merge($row, $ultimaPosicion); 
                } else {
                    $row["IdAlmacen"] = null;
                    $row["TipoAlmacen"] = null;  
                }

                $datosBodega = get_bodega($tagPez);
                if ($datosBodega) {
                    $row = array_merge($row, $datosBodega);
                } else {
                    $row["Zona"] = null;
                    $row["Especie"] = null;
                    $row["FechaCaptura"] = null;
                    $row["NombreBarco"] = null;
                }
                

                $capturas[] = $row;
            }

            $conn->close();
            return $capturas;
        } catch (Exception $e) {
            return false;
        }
    }

    function get_temperaturas_por_tag_pez($tagPez) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "SELECT IdAlmacen_Temperatura, Id, Temperatura, Fecha FROM almacen_temperaturas WHERE Id IN (SELECT Id FROM almacen WHERE TagPez = ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) return false;

            $stmt->bind_param("s", $tagPez);
            $stmt->execute();

            $result = $stmt->get_result();
            $temperaturas = [];
            while ($row = $result->fetch_assoc()) {
                $temperaturas[] = $row;
            }

            $stmt->close();
            $conn->close();
            return $temperaturas;
        } catch (Exception $e) {
            return false;
        }
    }

    function get_temperaturas_max_min_por_tag($tagPez) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            // Consulta SQL mejorada
            $sql = "
                SELECT 
                    COUNT(*) AS totalTemperaturas, 
                    MAX(Temperatura) AS temperaturaMaxima, 
                    MIN(Temperatura) AS temperaturaMinima,
                    -- Obtener la fecha de la última temperatura registrada
                    (SELECT Fecha 
                    FROM almacen_temperaturas 
                    WHERE Id IN (SELECT Id FROM almacen WHERE TagPez = ?) 
                    ORDER BY Fecha DESC LIMIT 1) AS fechaUltimaTemperatura,
                    -- Obtener la fecha de la temperatura máxima
                    (SELECT Fecha 
                    FROM almacen_temperaturas 
                    WHERE Id IN (SELECT Id FROM almacen WHERE TagPez = ?) 
                    AND Temperatura = (SELECT MAX(Temperatura) 
                                        FROM almacen_temperaturas 
                                        WHERE Id IN (SELECT Id FROM almacen WHERE TagPez = ?)) 
                    LIMIT 1) AS fechaTemperaturaMaxima,
                    -- Obtener la fecha de la temperatura mínima
                    (SELECT Fecha 
                    FROM almacen_temperaturas 
                    WHERE Id IN (SELECT Id FROM almacen WHERE TagPez = ?) 
                    AND Temperatura = (SELECT MIN(Temperatura) 
                                        FROM almacen_temperaturas 
                                        WHERE Id IN (SELECT Id FROM almacen WHERE TagPez = ?)) 
                    LIMIT 1) AS fechaTemperaturaMinima
                FROM almacen_temperaturas
                WHERE Id IN (SELECT Id FROM almacen WHERE TagPez = ?);
            ";

            // Preparar la consulta
            $stmt = $conn->prepare($sql);
            // Enlazar el parámetro TagPez a las subconsultas
            $stmt->bind_param("ssssss", $tagPez, $tagPez, $tagPez, $tagPez, $tagPez, $tagPez);
            $stmt->execute();

            

            // Obtener los resultados
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $stmt->close();
            $conn->close();

            
            
            // Devolver los resultados si existen
            return $row ? [
                
                'totalTemperaturas' => $row['totalTemperaturas'],
                'temperaturaMaxima' => $row['temperaturaMaxima'],
                'temperaturaMinima' => $row['temperaturaMinima'],
                'fechaUltimaTemperatura' => $row['fechaUltimaTemperatura'],
                'fechaTemperaturaMaxima' => $row['fechaTemperaturaMaxima'],
                'fechaTemperaturaMinima' => $row['fechaTemperaturaMinima']
            ] : false;
        } catch (Exception $e) {
            echo $e->getMessage();
            return false;
        }
    }


    function get_last_position($tagPez) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $query = "
                SELECT a.Id, t.Nombre AS TipoAlmacen
                FROM almacen a
                JOIN almacen_temperaturas at ON a.Id = at.Id
                JOIN tiposalmacen t ON a.IdTipoAlmacen = t.IdTipoAlmacen
                WHERE a.TagPez = ? 
                ORDER BY at.Fecha DESC LIMIT 1
            ";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $tagPez);
            $stmt->execute();

            $result = $stmt->get_result();
            $registro = $result->fetch_assoc();

            $stmt->close();
            $conn->close();

            return $registro ? ['IdAlmacen' => $registro['Id'], 'TipoAlmacen' => $registro['TipoAlmacen']] : null;
        } catch (Exception $e) {
            return false;
        }
    }

    function get_bodega($tagPez) {
        try {
            // Obtener la conexión a la base de datos
            $conn = obtener_conexion();
            if (!$conn) return false;
    
            // Consulta SQL para obtener los detalles de la bodega y el nombre del barco
            $sql = "
                SELECT 
                    b.Zona, 
                    b.Especie, 
                    b.FechaCaptura, 
                    b.TagPez, 
                    bar.Nombre AS NombreBarco
                FROM 
                    bodegas b
                INNER JOIN 
                    barcos bar ON b.IdBarco = bar.IdBarco
                WHERE 
                    b.TagPez = ?
            ";
    
            // Preparar la consulta
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $conn->close();
                return false;
            }
    
            // Enlazar el parámetro
            $stmt->bind_param("s", $tagPez);
    
            // Ejecutar la consulta
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }
    
            // Obtener el resultado
            $result = $stmt->get_result();
            
            // Verificar si se encontró la bodega para el TagPez
            if ($result->num_rows > 0) {
                // Obtener el primer resultado como un array asociativo
                $data = $result->fetch_assoc();
            } else {
                // Si no se encuentra el TagPez en la bodega
                $data = null;
            }
    
            // Cerrar la sentencia
            $stmt->close();
            $conn->close();
    
            return $data;
        } catch (Exception $e) {
            return false;
        }
    }
    
?>
