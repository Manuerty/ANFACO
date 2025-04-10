<?php
    require_once 'Conexion.php';


    function comprueba_usuario($usuario, $contrasena) {
        try {
            // Llamada a la función ConexionBD() para conectar con la base de datos
            $conn = ConexionBD("localhost", "prueba_1", "root", ""); // Aquí puedes cambiar los parámetros
    
            if (!$conn) {
                return false; 
            }
    
            // Preparamos la consulta SQL
            $sql = "SELECT IdUsuario, Usuario, Contrasena, Rol  FROM usuarios WHERE Usuario = ? AND Contrasena = ? ";
    
            // Preparamos la sentencia
            $stmt = $conn->prepare($sql);
            
            // Comprobamos si la preparación fue exitosa
            if (!$stmt) {
                return false;
            }
    
            // Vinculamos los parámetros
            $stmt->bind_param("ss", $usuario, $contrasena );
    
            // Ejecutamos la consulta
            if (!$stmt->execute()) {
                $conn->close();
                return false;
            }
    
            // Obtenemos los resultados
            $result = $stmt->get_result();
            
            // Si encontramos un usuario válido
            if ($row = $result->fetch_assoc()) {
                $conn->close();
                return array($row['IdUsuario'], $row["Usuario"], $row["Contrasena"], $row["Rol"]);
            }
    
            // Si no se encuentra el usuario
            $conn->close();
            return 0;
    
        } catch (Exception $e) {
            return false; 
        }
    }
    
    function get_barcos_usuario($id) {
        try {
            $conn = ConexionBD("localhost", "prueba_1", "root", ""); 
    
            if (!$conn) {
                return false; 
            }
    
            $sql = "SELECT IdBarco, IdUsuario, Nombre, Codigo FROM barcos WHERE IdUsuario = ?";
    
            $stmt = $conn->prepare($sql);
            
            if (!$stmt) {
                return false;
            }
    
            $stmt->bind_param("s", $id); // Corregido: solo un parámetro, por eso un solo "s"
    
            if (!$stmt->execute()) {
                $conn->close();
                return false;
            }
    
            $result = $stmt->get_result();
    
            $barcos = [];
    
            while ($row = $result->fetch_assoc()) {
                $barcos[] = $row; // Guardamos cada barco como array asociativo
            }
    
            $conn->close();
    
            return $barcos;
    
        } catch (Exception $e) {
            return false; 
        }
    }
    function get_capturas() {
        try {
            $conn = ConexionBD("localhost", "prueba_1", "root", "");
        
            if (!$conn) return false;
        
            // Consultar los tags de los peces (sin agrupar por almacén)
            $sql = "SELECT DISTINCT TagPez FROM almacen";
            $result = $conn->query($sql);
        
            $capturas = [];
        
            while ($row = $result->fetch_assoc()) {
                $tagPez = $row["TagPez"];
                
                // Obtener las temperaturas relacionadas por TagPez
                $temperaturas = get_temperaturas_por_tag_pez($tagPez);
                
                // Si se obtienen temperaturas, añadimos la fecha a cada una
                if ($temperaturas !== false) {
                    foreach ($temperaturas as &$temperatura) {
                        $temperatura["FechaTemperatura"] = $temperatura["Fecha"]; // Guardamos la fecha de la temperatura
                        unset($temperatura["Fecha"]); // Opcional: Eliminar el campo 'Fecha' si no lo necesitas
                    }
                }
                
                // Obtener datos de temperaturas máximas, mínimas y el total por TagPez
                $temperaturasMaxMin = get_temperaturas_max_min_por_tag($tagPez);
                
                // Si la consulta de max/min retorna datos, agregarlos al array
                if ($temperaturasMaxMin) {
                    $row["TotalTemperaturas"] = $temperaturasMaxMin['totalTemperaturas'];
                    $row["TemperaturaMaxima"] = $temperaturasMaxMin['temperaturaMaxima'];
                    $row["TemperaturaMinima"] = $temperaturasMaxMin['temperaturaMinima'];
                    $row["FechaUltimaTemperatura"] = $temperaturasMaxMin['fechaUltimaTemperatura'];
                    $row["FechaTemperaturaMaxima"] = $temperaturasMaxMin['fechaTemperaturaMaxima'];
                    $row["FechaTemperaturaMinima"] = $temperaturasMaxMin['fechaTemperaturaMinima'];
                } else {
                    $row["TotalTemperaturas"] = 0;
                    $row["TemperaturaMaxima"] = null;
                    $row["TemperaturaMinima"] = null;
                }
        
                // Añadir las temperaturas al array principal
                $row["Temperaturas"] = $temperaturas !== false ? $temperaturas : [];
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
            $conn = ConexionBD("localhost", "prueba_1", "root", "");
        
            if (!$conn) return false;
        
            // Consultar las temperaturas relacionadas con el TagPez
            $sql = "SELECT IdAlmacen_Temperatura, Id, Temperatura, Fecha 
                    FROM almacen_temperaturas 
                    WHERE Id IN (SELECT Id FROM almacen WHERE TagPez = ?)";
            $stmt = $conn->prepare($sql);
        
            if (!$stmt) return false;
        
            $stmt->bind_param("s", $tagPez);  // Usamos 's' porque TagPez es un string
            $stmt->execute();
        
            $result = $stmt->get_result();
            $temperaturas = [];
        
            while ($row = $result->fetch_assoc()) {
                $temperaturas[] = $row;
            }
        
            $conn->close();
            return $temperaturas;
        
        } catch (Exception $e) {
            return false;
        }
    }
    
    

    function get_temperaturas_max_min_por_tag($tagPez) {
    try {
        // Conectar a la base de datos
        $conn = ConexionBD("localhost", "prueba_1", "root", "");
        
        if (!$conn) return false;
        
        // Consulta SQL que utiliza funciones agregadas MAX, MIN y COUNT para un TagPez,
        // además de obtener las fechas de la temperatura máxima, mínima y la última temperatura
        $sql = "
            SELECT 
                COUNT(*) AS totalTemperaturas, 
                MAX(Temperatura) AS temperaturaMaxima, 
                MIN(Temperatura) AS temperaturaMinima,
                
                -- Obtener la fecha de la última temperatura registrada
                (SELECT Fecha FROM almacen_temperaturas 
                 WHERE Id IN (SELECT Id FROM almacen WHERE TagPez = ?) 
                 ORDER BY Fecha DESC LIMIT 1) AS fechaUltimaTemperatura,
                
                -- Obtener la fecha de la temperatura máxima
                (SELECT Fecha FROM almacen_temperaturas 
                 WHERE Id IN (SELECT Id FROM almacen WHERE TagPez = ?) 
                 AND Temperatura = (SELECT MAX(Temperatura) 
                                    FROM almacen_temperaturas 
                                    WHERE Id IN (SELECT Id FROM almacen WHERE TagPez = ?)) 
                 LIMIT 1) AS fechaTemperaturaMaxima,
                
                -- Obtener la fecha de la temperatura mínima
                (SELECT Fecha FROM almacen_temperaturas 
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
        
        // Enlazar los parámetros (solo 3 parámetros en lugar de 7)
        $stmt->bind_param("ssssss", $tagPez, $tagPez, $tagPez, $tagPez, $tagPez, $tagPez);  
        
        // Ejecutar la consulta
        $stmt->execute();
        
        // Obtener los resultados
        $result = $stmt->get_result();
        
        // Verificar si la consulta devuelve resultados
        if ($row = $result->fetch_assoc()) {
            // Cerrar la conexión
            $stmt->close();
            $conn->close();
            
            // Retornar los resultados en un array
            return [
                'totalTemperaturas' => $row['totalTemperaturas'],
                'temperaturaMaxima' => $row['temperaturaMaxima'],
                'temperaturaMinima' => $row['temperaturaMinima'],
                'fechaUltimaTemperatura' => $row['fechaUltimaTemperatura'],
                'fechaTemperaturaMaxima' => $row['fechaTemperaturaMaxima'],
                'fechaTemperaturaMinima' => $row['fechaTemperaturaMinima']
            ];
        } else {
            // Si no hay resultados
            return false;
        }
        
    } catch (Exception $e) {
        return false; // Si ocurre algún error, retornar false
    }
}

    
    
?>