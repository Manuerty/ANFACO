<?php

use Pdo\Sqlite;
    require_once 'Conexion.php';
    require_once "clases/controlador.php";

    // Función para obtener la conexión a la base de datos
    function obtener_conexion() {
        return ConexionBD();
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

            $sql = 'SELECT IdUsuario, Usuario, Contrasena,
                CASE 
                    WHEN Rol = "Usuarios" THEN "Armador"
                    ELSE Rol 
                END AS Rol
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

    function get_pescado($IdPropietario) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "SELECT 
                        capturas.IdCaptura, capturas.Zona, capturas.Especie, capturas.FechaCaptura, capturas.TagPez, 
                        barcos.Nombre AS Barco, barcos.IdBarco, 
                        UltimaFecha.FechaUltimoAlmacen, UltimaFecha.CuentaAlmacen, UltimaFecha.Fecha_ultimo_comprador,
                        AlmacenUltimoComprador.IdPropietario, UltimoComprador.Usuario AS Comprador,
                        MaxTemperatura.temperaturaMaxima, MaxTemperatura.temperaturaMinima, 
                        AlmacenUltimo.IdTipoAlmacen, tiposalmacen.Nombre, barcos.Codigo, 
                        usuarios.Usuario, Armador.Usuario AS Armador 
                    FROM capturas 

                    LEFT JOIN (
                        SELECT 
                            a1.TagPez,
                            MAX(a1.Fecha) AS FechaUltimoAlmacen,
                            COUNT(*) AS CuentaAlmacen,
                            MAX(CASE WHEN a1.IdPropietario = 0 THEN '2050-01-01' ELSE a1.Fecha END) AS Fecha_ultimo_comprador
                        FROM almacen a1
                        INNER JOIN (
                            SELECT TagPez, MAX(Fecha) AS UltimaFechaPropietario
                            FROM almacen
                            WHERE IdPropietario = ?
                            GROUP BY TagPez
                        ) filtro ON a1.TagPez = filtro.TagPez AND a1.Fecha <= filtro.UltimaFechaPropietario
                        GROUP BY a1.TagPez
                    ) UltimaFecha ON capturas.TagPez = UltimaFecha.TagPez

                    LEFT JOIN (
                        SELECT 
                            a1.TagPez,
                            MAX(a1.TempMax) AS temperaturaMaxima,
                            MIN(a1.TempMin) AS temperaturaMinima
                        FROM almacen a1
                        INNER JOIN (
                            SELECT TagPez, MAX(Fecha) AS UltimaFechaPropietario
                            FROM almacen
                            WHERE IdPropietario = ?
                            GROUP BY TagPez
                        ) filtro ON a1.TagPez = filtro.TagPez AND a1.Fecha <= filtro.UltimaFechaPropietario
                        GROUP BY a1.TagPez
                    ) MaxTemperatura ON MaxTemperatura.TagPez = capturas.TagPez

                    LEFT JOIN barcos ON barcos.IdBarco = capturas.IdBarco 
                    LEFT JOIN usuarios ON barcos.IdUsuario = usuarios.IdUsuario
                    LEFT JOIN almacen AlmacenUltimo 
                        ON AlmacenUltimo.TagPez = capturas.TagPez AND AlmacenUltimo.Fecha = UltimaFecha.FechaUltimoAlmacen
                    LEFT JOIN almacen AlmacenUltimoComprador 
                        ON AlmacenUltimoComprador.TagPez = capturas.TagPez AND AlmacenUltimoComprador.Fecha = UltimaFecha.Fecha_ultimo_comprador
                    LEFT JOIN tiposalmacen ON tiposalmacen.IdTipoAlmacen = AlmacenUltimo.IdTipoAlmacen
                    LEFT JOIN usuarios UltimoComprador ON UltimoComprador.IdUsuario = AlmacenUltimoComprador.IdPropietario
                    LEFT JOIN usuarios Armador ON Armador.IdUsuario = barcos.IdUsuario

                    WHERE EXISTS (
                        SELECT 1 FROM almacen a 
                        WHERE a.TagPez = capturas.TagPez AND a.IdPropietario = ?
                    )

                    ORDER BY FechaCaptura DESC";

            $stmt = $conn->prepare($sql);
            if (!$stmt) return false;

            $stmt->bind_param("iii", $IdPropietario, $IdPropietario, $IdPropietario);
            if (!$stmt->execute()) return false;

            $result = $stmt->get_result();
            $capturas = [];

            while ($row = $result->fetch_assoc()) {
                $capturas[] = [
                    'IdCaptura'           => $row['IdCaptura'],
                    'Zona'               => $row['Zona'],
                    'Especie'            => $row['Especie'],
                    'FechaCaptura'       => $row['FechaCaptura'],
                    'TagPez'             => $row['TagPez'],
                    'NombreBarco'        => $row['Barco'],
                    'IdBarco'            => $row['IdBarco'],
                    'FechaUltimoAlmacen' => $row['FechaUltimoAlmacen'],
                    'CuentaAlmacen'      => $row['CuentaAlmacen'],
                    'TemperaturaMaxima'  => $row['temperaturaMaxima'],
                    'TemperaturaMinima'  => $row['temperaturaMinima'],
                    'IdTipoAlmacen'      => $row['IdTipoAlmacen'],
                    'TipoAlmacen'        => $row['Nombre'],
                    'NombreComprador'    => $row['Comprador'],
                    'Armador'            => $row['Armador'],
                ];
            }

            $stmt->close();
            $conn->close();

            return $capturas;

        } catch (Exception $e) {
            return false;
        }
    }


    function get_Barcos($idUsuario = null) {

        
        try{
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "SELECT IdBarco, Nombre, Codigo, IdUsuario  FROM barcos";
            
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
                    "IdUsuario" => $row['IdUsuario'],
                ];
            }
    
            $stmt->close();
            $conn->close();

            return $barcos;

        }catch (Exception $e) {
            
            return false;
        }
    }

    function getTemperaturasProcesar($tagPez, $fechaLimite = null) {
    try {
        $conn = obtener_conexion();
        if (!$conn) return false;

        $sql = "SELECT TagPez, DatosTemp, Id, DatosProcesados, IdTipoAlmacen
                FROM almacen 
                WHERE TagPez = ?";

        if ($fechaLimite !== null) {
            $sql .= " AND Fecha <= ?";
        }

        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            $conn->close();
            return false;
        }

        $types = "";
        $params = [];

        if ($tagPez) {
            $types .= "s";
            $params[] = $tagPez;
        }

        if ($fechaLimite !== null) {
            $types .= "s";
            $params[] = $fechaLimite;
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        if (!$stmt->execute()) {
            $stmt->close();
            $conn->close();
            return false;
        }

        $result = $stmt->get_result();
        $almacenesProcesar = [];

        while ($row = $result->fetch_assoc()) {
            $almacenesProcesar[] = [
                "TagPezProcesar" => $row["TagPez"],
                "DatosTempProcesar" => $row["DatosTemp"],
                "IdAlmacenProcesar" => $row["Id"],
                "DatosProcesados" => $row["DatosProcesados"],
                "IdTipoAlmacen" => $row["IdTipoAlmacen"],
            ];
        }

        $stmt->close();
        $conn->close();

        return $almacenesProcesar;
    } catch (Exception $e) {
        return false;
    }
}

    function procesarTemperaturasString($txtTemperaturas, $IdAlmacen) {

        // 1. Procesamiento del texto de temperaturas
        $cadenaTemperatura = trim($txtTemperaturas, "#");
        $registros = explode(";", $cadenaTemperatura);
    
        $datos = [];
    
        foreach ($registros as $registro) {
            $partes = explode(",", $registro);
            if (count($partes) === 3) {
                $fechaHora = $partes[0] . ' ' . $partes[1] . ':00';
                $valor = (float)$partes[2];
    
                $datos[] = [
                    'FechaTemperatura' => $fechaHora,
                    'ValorTemperatura' => $valor,
                    'IdAlmacen' => $IdAlmacen
                ];
            }
        }
    
        return $datos;
    }

     function complementoA2($hex) {
        $valor = hexdec($hex);
        if ($valor & 0x8000) { // si bit más alto está activo (signo negativo)
            $valor -= 0x10000;
        }
        //$valor = $valor * -1; // Convertir a negativo
        return $valor;
    }

    function descomprimirTemperaturas($datosTemp) {

        $datosTemp = base64_decode($datosTemp);
        $datosTemp = gzuncompress($datosTemp);

        // Separar por ';'
        $partes = explode(';', $datosTemp);

        // Tiempo de muestreo
        $tiempoMuestreoHex = trim($partes[0], '-');

        $tiempoMuestreoSeg = hexdec($tiempoMuestreoHex);

        // Primer trama completa con timestamp y temperatura

        $primerTrama = preg_replace('/\s+/', '', $partes[1]);

        $timestampHex = substr($primerTrama, 0, 8);


        $tempHex = substr($primerTrama, 8, 4);


        $timestamp = hexdec($timestampHex);


        $temperaturaRaw = complementoA2($tempHex);

        $temperatura = $temperaturaRaw / 10;


        // Fecha inicial
        $fecha = new DateTime("@$timestamp");
        $fecha->setTimezone(new DateTimeZone('Europe/Madrid'));

        $resultados = [];
        $resultados[] = $fecha->format('Y-m-d,H:i') . "," . $temperatura;

        for ($i = 2; $i < count($partes); $i++) {
            $tempHex = $partes[$i];
            if (strlen($tempHex) != 4) continue;

            $temperaturaRaw = complementoA2($tempHex);
            $temperatura = $temperaturaRaw / 10;

            $fecha->add(new DateInterval('PT' . $tiempoMuestreoSeg . 'S'));

            $resultados[] = $fecha->format('Y-m-d,H:i') . "," . $temperatura;
        }

        $resultadoFinal = "#" . implode(";", $resultados) . ";#";

        return $resultadoFinal;
    }





    function procesarInformacion() {
        try {
            // Obtener una única conexión
            $conn = obtener_conexion();
            if (!$conn) return false;
    
            // Iniciar la transacción
            $conn->begin_transaction();
    
            // Consulta para obtener los datos de almacenes no procesados
            $sql = "SELECT DatosTemp, TempMax, TempMin, Id FROM almacen WHERE DatosProcesados = 0";
            $stmt = $conn->prepare($sql);
            if (!$stmt->execute()) {
                $conn->rollback();  // Deshacer cambios si ocurre un error
                $stmt->close();
                $conn->close();
                return false;
            }
    
            $result = $stmt->get_result();
            $almacenesProcesar = [];
    
            // Almacenar los datos de los almacenes en un array
            while ($row = $result->fetch_assoc()) {
                $almacenesProcesar[] = [
                    "DatosTemp" => $row["DatosTemp"],
                    "TempMax" => $row["TempMax"],
                    "TempMin" => $row["TempMin"],
                    "IdAlmacen" => $row["Id"],
                ];
            }
    
            $stmt->close();
    
            // 2. Procesar los datos de cada almacén
            foreach ($almacenesProcesar as $almacen) {
                // Procesar las temperaturas
                $procesardatos =  descomprimirTemperaturas($almacen["DatosTemp"]);
                $datos = procesarTemperaturasString($procesardatos, $almacen["IdAlmacen"]);
    
                if (empty($datos)) {
                    continue;  // Si no hay datos, saltar al siguiente almacén
                }
    
                // Obtener los valores de temperatura
                $valores = array_column($datos, 'ValorTemperatura');
    
                // Calcular el valor máximo y mínimo de las temperaturas
                $maximo = max($valores);
                $minimo = min($valores);
    
                // Consulta de actualización de temperaturas
                $sqlUpdate = 'UPDATE almacen
                              SET TempMax = ?, TempMin = ?, DatosProcesados = 1
                              WHERE Id = ?';
    
                $stmtUpdate = $conn->prepare($sqlUpdate);
                if (!$stmtUpdate) {
                    $conn->rollback();  // Deshacer cambios si hay error
                    $conn->close();
                    return false;
                }
    
                // Vincular parámetros y ejecutar la consulta de actualización
                $stmtUpdate->bind_param("dds", $maximo, $minimo, $almacen['IdAlmacen']);
                if (!$stmtUpdate->execute()) {
                    $stmtUpdate->close();
                    $conn->rollback();  // Deshacer cambios si hay error
                    $conn->close();
                    return false;
                }
    
                // Cerrar la declaración de actualización
                $stmtUpdate->close();
            }
    
            // Si todo ha ido bien, hacer commit de la transacción
            $conn->commit();
    
            // Cerrar la conexión después de procesar todos los almacenes
            $conn->close();
            return true;
    
        } catch (Exception $e) {
            // En caso de error, hacer rollback de la transacción y cerrar la conexión
            if ($conn) {
                $conn->rollback();  // Deshacer cambios
                $conn->close();
            }
            return false;
        }
    }

    function get_Almacenes($tagPez, $fechaLimite = null) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;
    
            $sql = "SELECT  Fecha, tiposalmacen.Nombre, tiposalmacen.IdTipoAlmacen, Id, Comprador.Usuario as Comprador
                            FROM almacen 
                            LEFT JOIN tiposalmacen ON tiposalmacen.IdTipoAlmacen = almacen.IdTipoAlmacen
                            LEFT JOIN usuarios Comprador ON Comprador.IdUsuario = almacen.IdPropietario
                            WHERE almacen.TagPez = ? ";
            if ($fechaLimite !== null) {
                $sql .= " AND almacen.Fecha <= ? ";
            }
            $sql .= "UNION
                    SELECT FechaCaptura as Fecha, 'Bodega'as Nombre, 0 as IdTipoAlmacen, 0 as Id, NULL as Comprador
                                            FROM capturas
                                            WHERE capturas.TagPez = ?
                                            ORDER BY FECHA DESC;";

    
            $stmt = $conn->prepare($sql);
    
            if (!$stmt) {
                $conn->close();
                return false;
            }
    
            if ($fechaLimite !== null) {
                $stmt->bind_param("sss", $tagPez, $fechaLimite, $tagPez);
            } else {
                // Si no hay fecha límite, solo vinculamos el tagPez
                $stmt->bind_param("ss", $tagPez, $tagPez);
            }
            
    
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }
    
            $result = $stmt->get_result();
            $almacenes = [];
    
            while ($row = $result->fetch_assoc()) {
                $almacenes[] = [
                    "FechaAlmacen"      => $row["Fecha"],
                    "NombreTipo"        => $row["Nombre"],
                    "IdTipo"            => $row["IdTipoAlmacen"],
                    "IdAlmacen"         => $row["Id"],
                    "Comprador"         => $row["Comprador"],
                ];
            }
    
            $stmt->close();
            $conn->close();
    
            return $almacenes;
    
        } catch (Exception $e) {
            return false;
        }

    }

    function get_tiposAlmacen($IdPropietario = null){
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            
                // Si no se proporciona IdPropietario, obtenemos todos los tipos de almacén
                $sql = "SELECT tiposalmacen.Nombre, usuarios.Usuario, barcos.Nombre as NombreBarco, tiposalmacen.Tipo
                            FROM tiposalmacen
                            left join usuarios on tiposalmacen.IdUsuario = usuarios.IdUsuario
                            left join barcos on tiposalmacen.IdBarco = barcos.IdBarco";
                            
            if ($IdPropietario !== null) {
                // Si se proporciona IdPropietario, filtramos por él
                $sql .= " WHERE tiposalmacen.IdUsuario = ?";
            }
            

            $stmt = $conn->prepare($sql);
    
            if (!$stmt) {
                $conn->close();
                return false;
            }

            // Vincular parámetros
            if ($IdPropietario !== null) {
                $stmt->bind_param("i", $IdPropietario);
            }

            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }
    
            $result = $stmt->get_result();
            $almacenes = [];
    
            while ($row = $result->fetch_assoc()) {
                $almacenes[] = [
                    "NombreTipo"        => $row["Nombre"],
                    "Usuario"           => $row["Usuario"],
                    "Barco"             => $row["NombreBarco"],
                    "Tipo"              => $row["Tipo"],
                ];
            }
    
            $stmt->close();
            $conn->close();
    
            return $almacenes;
    
        } catch (Exception $e) {
            return false;
        }
    }


    function insertUsuario($usuario){

        $NombreUsuario = $usuario[0];
        $Contrasena = $usuario[1];
        $Rol = $usuario[4];


        try {
            $conn = obtener_conexion();
            if (!$conn) return false;
    
            $sql = "INSERT INTO usuarios (Usuario, Contrasena, Rol) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
    
            if (!$stmt) {
                $conn->close();
                return false;
            }
    
            // Vincular parámetros
            $stmt->bind_param("sss", $NombreUsuario, $Contrasena, $Rol);   
    
            // Ejecutar la consulta
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }
    
            // Cerrar la declaración y la conexión
            $stmt->close();
            $conn->close();
    
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function insertTipoAlmacen($tipoAlmacen, $IdPropietario = 2, $IdBarco = null) {

        $NombreTipo = $tipoAlmacen;

        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            if ($IdBarco !== null) {
                $sql = "INSERT INTO tiposalmacen (Nombre, IdUsuario, IdBarco) VALUES (?, ?, ?)";
            }
            else {
                $sql = "INSERT INTO tiposalmacen (Nombre, IdUsuario) VALUES (?, ?)";
            }
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }

            // Vincular parámetros
            if ($IdBarco !== null) {
                $stmt->bind_param("sii", $NombreTipo, $IdPropietario, $IdBarco);
            } else {
                $stmt->bind_param("si", $NombreTipo, $IdPropietario);
            }

            // Ejecutar la consulta
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }

            // Cerrar la declaración y la conexión
            $stmt->close();
            $conn->close();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function insertBarco($nombreBarco, $codigoBarco, $IdUsuario) {

        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "INSERT INTO barcos (Nombre, Codigo, IdUsuario) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }

            // Vincular parámetros
            $stmt->bind_param("ssi", $nombreBarco, $codigoBarco, $IdUsuario);

            // Ejecutar la consulta
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }

            // Cerrar la declaración y la conexión
            $stmt->close();
            $conn->close();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function get_last_codigo_barco(){

        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "SELECT Codigo FROM anfaco.barcos ORDER BY Codigo DESC LIMIT 1;";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }

            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }

            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            $stmt->close();
            $conn->close();

            return isset($row['Codigo']) ? $row['Codigo'] : null;
        } catch (Exception $e) {
            return false;
        }
    }

    function delete_User($IdUsuario) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "DELETE FROM usuarios WHERE IdUsuario = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }

            $stmt->bind_param("i", $IdUsuario);

            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }

            $stmt->close();
            $conn->close();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    function updateUsuario($usuario) {
        $idUsuario = $usuario[0];
        $NombreUsuario = $usuario[1];
        $Contrasena = $usuario[2];
        $Rol = $usuario[5];   
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "UPDATE usuarios SET Usuario = ?, Contrasena = ?, Rol = ? WHERE IdUsuario = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }

            // Vincular parámetros
            $stmt->bind_param("sssi", $NombreUsuario, $Contrasena, $Rol, $idUsuario);

            if (!$stmt->execute()) {
                $stmt->close();
                $conn->close();
                return false;
            }

            $stmt->close();
            $conn->close();

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    
?>
