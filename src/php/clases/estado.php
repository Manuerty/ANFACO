
<?php
class Estado{
    //variables de estado?
    public $Estado;
    public $EstadosAnteriores = array();
    public $IdIdentidad;
    public $datosPersonal = array();

    public $IdUsuarioLogin;
    public $IdUsuarioSeleccionado;

    public $esAdmin;

    public $esArmador;
    
    public $IdLastUser;

    public $nombreUsuario;

    public $nombreConservero;

    public $TagPez;
    public $LastTagPez;

    public $capturas;

    public $capturasFiltradas;

    public $barcos;

    public $barcosAdmin;

    public $barcosFiltrados;

    public $usuarios;

    public $usuariosFiltrados;

    public $almacenes;

    public $almacenesFiltrados;

    public $tiposalmacen;

    public $tiposalmacenFiltrados;

    public $tiposalmacenAdmin;

    public $capturaDetalle;

    public $temperaturas;

    public $temperaturasProcesar;

    public $idBoton;

    



   

    public $dataset;


    
    public $LimiteFilas = 30;
    



 

    


    //variables documentos
    public $tipo_documento;
    public $filtrado_estado;
    
    public $nombreDocumentoPadre;
    

    //public $documentos = array();
    public $punteros;
    public $puntero_posicion;
    public $numero_total_documentos;

    public $Documentos = array();
    public $ArchivosDocumento = array();
    public $FiltrosDoc = array();
    
    public $CadenaFiltro;
    public $IdsTiposFiltro = array();
    public $tipofiltro;

    
    //variables sociedad
    public $lista_sociedades = array();
    public $id_sociedad;
    public $NombreSociedad;
    public $PersonasContacto = array();

    //datos del cliente
    public $IdCliente;
    public $nombre_cliente;
    public $nombre_descriptivo;

    //datos del empleado
    public $IdPersonal;
    public $IdPersonalAdmin;
    public $permisosSecciones = array();
    public $configuracionesUsuario = array();
    public $EstadoJornada = array();
    public $HistoricoJornada;




    //Portal del empleado Vacaciones
    public $listaAnoFiltroVacaciones = null;
    public $AnioSV = null;

    //datos navegacion archivos PE
    public $IdTipoPropietario;
    public $IdPropietario;
    
    
    //generador formularios
    public $cargarForm;
    public $formularios = array();
    public $dropdownsFormularios = array();
    public $formulariosDuplasValores; 
    public $cargarFormFirma;
    public $IdDocumentoPadre;
    public $archivostiposAccesos;
    public $linkDocumentoSubido;
    //public $camposFormularios = null;
    public $IdPropietarioAuxiliar;

    //Datos de conexion
    public $IP;
    public $bbdd;

    //Datos de aplicación
    public $tipo_App;
    public $header;
    public $footer;

    //acciones
    public $acciones = array();
    public $datosProyectos = array();

    public $arrayDatosAux = array();
}

?>