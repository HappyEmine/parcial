<?php
require_once "vendor/autoload.php";
require_once "vendor/econea/nusoap/src/nusoap.php";

class ClienteSOAP {
    private $cliente;
    private $urlWsdl = "http://localhost/webservices/Parcial/primerparcialwebservices-master/invocar.php?wsdl";
    private $token;

    public function __construct() {
        $this->cliente = new nusoap_client($this->urlWsdl, true);

   
        $error = $this->cliente->getError();
        if ($error) {
            throw new Exception("Error en el constructor: " . $error);
        }
    }

    public function iniciarSesion($email, $password) {
        $result = $this->cliente->call("Servidor.iniciarSesion", array("email" => $email, "password" => $password));
        
      
        if ($this->cliente->fault) {
            return ["error" => "Fault", "detalle" => $result];
        } else {
            $error = $this->cliente->getError();
            if ($error) {
                return ["error" => "Error", "detalle" => $error];
            } else {
                $this->token = $result;
                return ["token" => $result];
            }
        }
    }

    public function obtenerProductos() {
        if (!$this->token) {
            return ["error" => "Error", "detalle" => "Debe iniciar sesion primero"];
        }
        
        $result = $this->cliente->call("Servidor.obtenerProductos", array("token" => $this->token));
        
       
        if ($this->cliente->fault) {
            return ["error" => "Fault", "detalle" => $result];
        } else {
            $error = $this->cliente->getError();
            if ($error) {
                return ["error" => "Error", "detalle" => $error];
            } else {
                return ["productos" => $result];
            }
        }
    }

    public function buscarProductosPorNombre($nombre) {
        if (!$this->token) {
            return ["error" => "Error", "detalle" => "Debe iniciar sesion primero"];
        }
        
        $result = $this->cliente->call("Servidor.buscarProductosPorNombre", array("nombre" => $nombre, "token" => $this->token));
        
     
        if ($this->cliente->fault) {
            return ["error" => "Fault", "detalle" => $result];
        } else {
            $error = $this->cliente->getError();
            if ($error) {
                return ["error" => "Error", "detalle" => $error];
            } else {
                return ["productos" => $result];
            }
        }
    }
}


try {
    $clienteSOAP = new ClienteSOAP();
    
  
    $respuestaLogin = $clienteSOAP->iniciarSesion("usuario@ejemplo.com", "contraseña");
    
    echo "<h2>Respuesta Inicio de Sesion</h2><pre>";
    print_r($respuestaLogin);
    echo "</pre>";
    

    $respuestaProductos = $clienteSOAP->obtenerProductos();
    
    echo "<h2>Respuesta Obtener Productos</h2><pre>";
    print_r($respuestaProductos);
    echo "</pre>";
    
  
    $respuestaBusqueda = $clienteSOAP->buscarProductosPorNombre("laptop");
    
    echo "<h2>Respuesta Búsqueda de Productos</h2><pre>";
    print_r($respuestaBusqueda);
    echo "</pre>";
    
} catch (Exception $e) {
    echo "<h2>Excepcion</h2><pre>" . $e->getMessage() . "</pre>";
}
?>