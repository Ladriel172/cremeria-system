<?php

require_once 'config/database.php';

$database = new Database();
$conn = $database->connect();

if($conn){
    echo "Conexión exitosa a la base de datos";
}else{
    echo "Error de conexión";
}

?>