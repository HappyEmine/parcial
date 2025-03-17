<?php
require_once "vendor/autoload.php";
require_once "vendor/econea/nusoap/src/nusoap.php";
require_once "servidor.php";


$server = new nusoap_server();
$server->configureWSDL('ServicioProductos', 'urn:servicioProductos');
$server->wsdl->schemaTargetNamespace = 'urn:servicioProductos';


$server->register(
    'iniciarSesion',  
    array('email' => 'xsd:string', 'password' => 'xsd:string'),  
    array('return' => 'xsd:string'),  
    'urn:servicioProductos',  
    'urn:servicioProductos#iniciarSesion',  
    'rpc', 
    'encoded',  
    'Inicia sesion y devuelve un token de autenticacion'  
);


$server->register(
    'obtenerProductos',
    array('token' => 'xsd:string'),
    array('return' => 'tns:ProductosArray'),
    'urn:servicioProductos',
    'urn:servicioProductos#obtenerProductos',
    'rpc',
    'encoded',
    'Obtiene la lista de productos'
);


$server->register(
    'buscarProductosPorNombre',
    array('nombre' => 'xsd:string', 'token' => 'xsd:string'),
    array('return' => 'tns:ProductosArray'),
    'urn:servicioProductos',
    'urn:servicioProductos#buscarProductosPorNombre',
    'rpc',
    'encoded',
    'Busca productos por nombre'
);


$server->wsdl->addComplexType(
    'Producto',
    'complexType',
    'struct',
    'all',
    '',
    array(
        'id' => array('name' => 'id', 'type' => 'xsd:int'),
        'nombre' => array('name' => 'nombre', 'type' => 'xsd:string'),
        'precio' => array('name' => 'precio', 'type' => 'xsd:float'),
        'stock' => array('name' => 'stock', 'type' => 'xsd:int')
    )
);

// Definir el array de productos
$server->wsdl->addComplexType(
    'ProductosArray',
    'complexType',
    'array',
    '',
    'SOAP-ENC:Array',
    array(),
    array(
        array('ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'tns:Producto[]')
    ),
    'tns:Producto'
);


$servidor = new Servidor();


function iniciarSesion($email, $password) {
    global $servidor;
    return $servidor->iniciarSesion($email, $password);
}


function obtenerProductos($token) {
    global $servidor;
    return $servidor->obtenerProductos($token);
}


function buscarProductosPorNombre($nombre, $token) {
    global $servidor;
    return $servidor->buscarProductosPorNombre($nombre, $token);
}


$HTTP_RAW_POST_DATA = isset($HTTP_RAW_POST_DATA) ? $HTTP_RAW_POST_DATA : '';
$HTTP_RAW_POST_DATA = file_get_contents("php://input");
$server->service($HTTP_RAW_POST_DATA);
?>