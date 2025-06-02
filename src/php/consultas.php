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

    function get_pescado($IdUsuario = null, $IdComprador = null) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;
    
            $sql = "SELECT bodegas.IdBodega, bodegas.Zona, bodegas.Especie, bodegas.FechaCaptura, bodegas.TagPez, 
                           barcos.Nombre as Barco, barcos.IdBarco, 
                           UltimaFecha.FechaUltimoAlmacen, UltimaFecha.CuentaAlmacen, UltimaFecha.Fecha_ultimo_comprador,
                           AlmacenUltimoComprador.IdComprador, UltimoComprador.Usuario as Comprador,
                           MaxTemperatura.temperaturaMaxima, MaxTemperatura.temperaturaMinima, 
                           AlmacenUltimo.IdTipoAlmacen, tiposalmacen.Nombre, barcos.Codigo, usuarios.Usuario , Armador.Usuario as Armador 
                    FROM bodegas 
                    LEFT JOIN (
                        SELECT TagPez, MAX(fecha) AS FechaUltimoAlmacen, COUNT(TagPez) AS CuentaAlmacen,
                               MAX(CASE WHEN IdComprador = 0 THEN '01/01/2050' ELSE FECHA END) AS Fecha_ultimo_comprador
                        FROM almacen GROUP BY TagPez
                    ) UltimaFecha ON bodegas.TagPez = UltimaFecha.TagPez
                    LEFT JOIN (
                        SELECT MAX(TempMax) AS temperaturaMaxima, MIN(TempMin) AS temperaturaMinima, TagPez 
                        FROM almacen 
                        GROUP BY TagPez
                    ) MaxTemperatura ON MaxTemperatura.TagPez = bodegas.TagPez
                    LEFT JOIN barcos ON barcos.IdBarco = bodegas.IdBarco 
                    LEFT JOIN usuarios ON barcos.IdUsuario = usuarios.IdUsuario
                    LEFT JOIN almacen AlmacenUltimo ON AlmacenUltimo.TagPez = bodegas.TagPez AND AlmacenUltimo.Fecha = UltimaFecha.FechaUltimoAlmacen
                    LEFT JOIN almacen AlmacenUltimoComprador ON AlmacenUltimoComprador.TagPez = bodegas.TagPez AND AlmacenUltimoComprador.Fecha = UltimaFecha.Fecha_ultimo_comprador
                    LEFT JOIN tiposalmacen ON tiposalmacen.IdTipoAlmacen = AlmacenUltimo.IdTipoAlmacen
                    LEFT JOIN usuarios UltimoComprador ON UltimoComprador.IdUsuario = AlmacenUltimoComprador.IdComprador
                    LEFT JOIN usuarios Armador ON Armador.IdUsuario = barcos.IdUsuario";
    
            // Condiciones WHERE opcionales
            $conditions = [];
            $params = [];
            $types = "";
    
            if ($IdUsuario !== null) {
                $conditions[] = "bodegas.IdBarco IN (SELECT IdBarco FROM barcos WHERE IdUsuario = ?)";
                $params[] = $IdUsuario;
                $types .= "i";
            }
    
            if ($IdComprador !== null) {
                $conditions[] = "EXISTS (
                    SELECT 1 FROM almacen a 
                    WHERE a.TagPez = bodegas.TagPez AND a.IdComprador = ?
                )";
                $params[] = $IdComprador;
                $types .= "i";
            }
    
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }
    
            $sql .= " ORDER BY FechaCaptura DESC";
    
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                $conn->close();
                return false;
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
                    'NombreComprador'      => $row['Comprador'],
                    'Armador'              => $row['Armador'],
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

    function getTemperaturasProcesar($tagPez) {
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql ="SELECT TagPez, DatosTemp, Id, DatosProcesados, IdTipoAlmacen
                   FROM almacen 
                   WHERE TagPez = ?";
            
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
            $almacenesProcesar = [];

            while ($row = $result->fetch_assoc()) {
                $almacenesProcesar[] = [
                    "TagPezProcesar" => $row["TagPez"],
                    "DatosTempProcesar" => $row["DatosTemp"],
                    "IdAlmacenProcesar" => $row["Id"],
                    "DatosProcesados"=> $row["DatosProcesados"],
                    "IdTipoAlmacen" => $row["IdTipoAlmacen"],
                ];
            }
    
            $stmt->close();
            $conn->close();


            return $almacenesProcesar;

        }
        catch (Exception $e) {
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
        $primerTrama = $partes[1];

        $timestampHex = substr($primerTrama, 0, 9);


        $tempHex = substr($primerTrama, 9, 4);


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

    function get_Almacenes($tagPez){
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;
    
            $sql = "SELECT  Fecha, LectorRFID, tiposalmacen.Nombre, tiposalmacen.IdTipoAlmacen, Id, Comprador.Usuario as Comprador
                            FROM almacen 
                            LEFT JOIN tiposalmacen ON tiposalmacen.IdTipoAlmacen = almacen.IdTipoAlmacen
                            LEFT JOIN usuarios Comprador ON Comprador.IdUsuario = almacen.IdComprador
                            WHERE almacen.TagPez = ?
                    UNION
                    SELECT FechaCaptura as Fecha, 'Bodega' as LectorRFID, 'Bodega'as Nombre, 0 as IdTipoAlmacen, 0 as Id, NULL as Comprador
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
                    "FechaAlmacen"      => $row["Fecha"],
                    "Lector"            => $row["LectorRFID"],
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

    function get_tiposAlmacen(){
        try {
            $conn = obtener_conexion();
            if (!$conn) return false;
    
            $sql = "SELECT * FROM tiposalmacen;";

    
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
            $almacenes = [];
    
            while ($row = $result->fetch_assoc()) {
                $almacenes[] = [
                    "NombreTipo"        => $row["Nombre"],
                    "IdTipoAlmacen"     => $row["IdTipoAlmacen"],
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

    function insertTipoAlmacen($tipoAlmacen) {

        $NombreTipo = $tipoAlmacen[0];

        try {
            $conn = obtener_conexion();
            if (!$conn) return false;

            $sql = "INSERT INTO tiposalmacen (Nombre) VALUES (?)";
            $stmt = $conn->prepare($sql);

            if (!$stmt) {
                $conn->close();
                return false;
            }

            // Vincular parámetros
            $stmt->bind_param("s", $NombreTipo);

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
    
    
?>
