<?php

header('Content-Type: text/xml; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

require_once "conexion.php";

class Servidor {

    private $db;

    public function __construct() {
        $this->db = (new Conexion())->getConexion();
    }

    public function autenticar($token) {
       
        $stmt = $this->db->prepare("SELECT usu_id, ultima_actividad FROM usuarios WHERE token = ? AND est = 1");
        $stmt->execute([$token]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$usuario) {
            return false;
        }
        
       
        $ultimaActividad = strtotime($usuario['ultima_actividad']);
        $ahora = time();
        if (($ahora - $ultimaActividad) > 1800) {
        
            $updateStmt = $this->db->prepare("UPDATE usuarios SET token = NULL WHERE usu_id = ?");
            $updateStmt->execute([$usuario['usu_id']]);
            return false;
        }
        
   
        $updateStmt = $this->db->prepare("UPDATE usuarios SET ultima_actividad = NOW() WHERE usu_id = ?");
        $updateStmt->execute([$usuario['usu_id']]);
        
        return true;
    }

    public function iniciarSesion($correo) {
      
        $token = bin2hex(random_bytes(16));
        $expira = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        $stmt = $this->db->prepare("UPDATE usuarios SET token = ?, ultima_actividad = ? WHERE usu_correo = ?");
        $stmt->execute([$token, $expira, $correo]);

        if ($stmt->rowCount() > 0) {
            return ["success" => true, "token" => $token];
        } else {
            return ["success" => false, "message" => "Correo no encontrado"];
        }
    }

    public function obtenerProductos($token) {
        if (!$this->autenticar($token)) {
            return ["error" => "Token inválido o expirado"];
        }

        $query = $this->db->query("SELECT id, nombre, precio, stock FROM productos");
        $productos = $query->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($producto) {
            return [
                'id' => (int)$producto['id'],
                'nombre' => $producto['nombre'],
                'precio' => (float)$producto['precio'],
                'stock' => (int)$producto['stock']
            ];
        }, $productos);
    }

    public function buscarProductosPorNombre($nombre, $token) {
        if (!$this->autenticar($token)) {
            return ["success" => false, "message" => "Token inválido o expirado"];
        }

      
        $stmt = $this->db->prepare("SELECT * FROM productos WHERE nombre LIKE ?");
        $search = "%$nombre%";
        $stmt->bindParam(1, $search, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


$options = [
    'uri' => 'urn:servicioProductos',
    'soap_version' => SOAP_1_1
];

$server = new SoapServer(null, $options);
$server->setClass("Servidor");
$server->handle();
?>
