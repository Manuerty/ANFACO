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
        
            $sql = "SELECT Id, Fecha, LectorRFID, TagPez, DatosTemp, IdTipoAlmacen FROM almacen";
            $result = $conn->query($sql);
        
            $capturas = [];
        
            require_once "consultas.php"; // Para asegurarte de tener acceso a get_temperaturas_por_almacen
        
            while ($row = $result->fetch_assoc()) {
                // 🔁 Añadir las temperaturas relacionadas
                $temperaturas = get_temperaturas_por_almacen($row["Id"]);
                $row["Temperaturas"] = $temperaturas !== false ? $temperaturas : [];
        
                $capturas[] = $row;
            }
        
            $conn->close();
            return $capturas;
        
        } catch (Exception $e) {
            return false;
        }
    }
    

    function get_temperaturas_por_almacen($idAlmacen) {
        try {
            $conn = ConexionBD("localhost", "prueba_1", "root", ""); 
        
            if (!$conn) return false;
        
            $sql = "SELECT IdAlmacen_Temperatura, Id, Temperatura, Fecha FROM almacen_temperaturas WHERE Id = ?";
            $stmt = $conn->prepare($sql);
        
            if (!$stmt) return false;
        
            $stmt->bind_param("i", $idAlmacen);
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
    
    
    
?>