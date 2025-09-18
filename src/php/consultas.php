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

        // Fecha inicial siempre en Madrid
        $fecha = new DateTime("@$timestamp", new DateTimeZone('Europe/Madrid'));

        $resultados = [];
        $resultados[] = $fecha->format('Y-m-d,H:i') . "," . $temperatura;

        for ($i = 2; $i < count($partes); $i++) {
            $tempHex = trim($partes[$i]); // elimina espacios y saltos de línea
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
            $conn = obtener_conexion();
            if (!$conn) return false;

            $conn->begin_transaction();

            // 1) Traer registros no procesados
            $sql = "SELECT Id, TagPez, DatosTemp
                    FROM almacen
                    WHERE DatosProcesados = 0";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $conn->close();
                return false;
            }
            if (!$stmt->execute()) {
                $stmt->close();
                $conn->rollback();
                $conn->close();
                return false;
            }

            $result = $stmt->get_result();
            $almacenesProcesar = [];
            while ($row = $result->fetch_assoc()) {
                $almacenesProcesar[] = [
                    "IdAlmacen" => $row["Id"],
                    "TagPez"    => $row["TagPez"],
                    "DatosTemp" => $row["DatosTemp"]
                ];
            }
            $stmt->close();

            // 2) Preparar statement para obtener FechaCaptura (primera captura) por TagPez
            $sqlCaptura = "SELECT FechaCaptura
                        FROM capturas
                        WHERE TagPez = ?";
            $stmtCaptura = $conn->prepare($sqlCaptura);
            if (!$stmtCaptura) {
                $conn->rollback();
                $conn->close();
                return false;
            }

            // 3) Procesar cada registro de almacen por separado
            foreach ($almacenesProcesar as $almacen) {
                // Descomprimir y parsear el string a array de registros
                $procesardatos = descomprimirTemperaturas($almacen["DatosTemp"]);
                $datos = procesarTemperaturasString($procesardatos, $almacen["IdAlmacen"]);
                if (empty($datos)) continue; // nada que procesar en este registro

                // 3.a) Obtener FechaCaptura de la tabla capturas para este TagPez
                $tag = $almacen['TagPez'];
                $stmtCaptura->bind_param("s", $tag);
                if (!$stmtCaptura->execute()) {
                    $stmtCaptura->close();
                    $conn->rollback();
                    $conn->close();
                    return false;
                }

                $resCap = $stmtCaptura->get_result();
                $rowCap = $resCap->fetch_assoc();

                // 3.b) Determinar fecha límite (FechaCaptura + 24h).
                // Si no hay FechaCaptura en la BBDD, se usa como fallback la primera fecha del propio string.
                $fechaLimiteDT = null;
                if (!empty($rowCap['FechaCaptura'])) {
                    try {
                        $fechaCap = new DateTime($rowCap['FechaCaptura'], new DateTimeZone('Europe/Madrid'));
                        $fechaCap->modify('+1 day');
                        $fechaLimiteDT = $fechaCap;
                    } catch (Exception $e) {
                        $fechaLimiteDT = null;
                    }
                }

                if ($fechaLimiteDT === null) {
                    // Fallback: usar la primera temperatura del string (índice 0)
                    // Asumimos que procesarTemperaturasString devuelve registros en orden cronológico.
                    try {
                        $fechaPrimTemp = new DateTime($datos[0]['FechaTemperatura'], new DateTimeZone('Europe/Madrid'));
                        $fechaPrimTemp->modify('+1 day');
                        $fechaLimiteDT = $fechaPrimTemp;
                    } catch (Exception $e) {
                        // Si tampoco se puede parsear, no hay base para excluir 24h: considerar todo el registro
                        $fechaLimiteDT = null;
                    }
                }

                // 3.c) Filtrar las temperaturas del registro que sean posteriores a fechaLimiteDT
                if ($fechaLimiteDT !== null) {
                    $datosFiltrados = array_filter($datos, function($d) use ($fechaLimiteDT) {
                        try {
                            $dt = new DateTime($d['FechaTemperatura'], new DateTimeZone('Europe/Madrid'));
                        } catch (Exception $e) {
                            return false;
                        }
                        return $dt > $fechaLimiteDT; // estrictamente posteriores a las primeras 24h
                    });
                } else {
                    // Si no tenemos referencia de fecha, no filtramos (tomamos todo)
                    $datosFiltrados = $datos;
                }

                // 3.d) Calcular max/min (o null si no hay datos válidos)
                if (!empty($datosFiltrados)) {
                    $valores = array_column($datosFiltrados, 'ValorTemperatura');
                    $maximo = max($valores);
                    $minimo = min($valores);
                } else {
                    $maximo = null;
                    $minimo = null;
                }

                // 3.e) Actualizar SOLO este registro de almacen
                // bind_param con tipos 'd' no acepta null fácilmente, por eso construimos la parte SET dinámicamente
                // Aseguramos que el decimal use '.' como separador.
                $maxSql = ($maximo !== null) ? str_replace(',', '.', (string)$maximo) : "NULL";
                $minSql = ($minimo !== null) ? str_replace(',', '.', (string)$minimo) : "NULL";

                $sqlUpdate = "UPDATE almacen
                            SET TempMax = " . ($maximo !== null ? $maxSql : "NULL") . ",
                                TempMin = " . ($minimo !== null ? $minSql : "NULL") . ",
                                DatosProcesados = 1
                            WHERE Id = ?";
                $stmtUpdate = $conn->prepare($sqlUpdate);
                if (!$stmtUpdate) {
                    $stmtCaptura->close();
                    $conn->rollback();
                    $conn->close();
                    return false;
                }
                $stmtUpdate->bind_param("i", $almacen['IdAlmacen']);
                if (!$stmtUpdate->execute()) {
                    $stmtUpdate->close();
                    $stmtCaptura->close();
                    $conn->rollback();
                    $conn->close();
                    return false;
                }
                $stmtUpdate->close();
            } // foreach

            $stmtCaptura->close();
            $conn->commit();
            $conn->close();
            return true;

        } catch (Exception $e) {
            if (isset($conn) && $conn) {
                $conn->rollback();
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

    function updateBarco($barco){
        $idBarco = $barco[0];
        $nuevoNombreBarco = $barco[1];
        try{
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "UPDATE barcos SET Nombre = ? WHERE IdBarco = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }

            // Vincular parámetros
            $stmt->bind_param("si", $nuevoNombreBarco, $idBarco);

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

    function delete_Barco($IdBarco) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "DELETE FROM barcos WHERE IdBarco = ?";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }

            $stmt->bind_param("i", $IdBarco);

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
