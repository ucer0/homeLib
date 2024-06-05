<?php

// Recogemos los datos de db.json
$file = file_get_contents(__DIR__."/db.json");
$dbData = json_decode($file,true);

// Credenciales de conexión de la Base de Datos
define("DB_NAME", $dbData["DB_NAME"]);
define("DB_USER", $dbData["DB_USER"]);
define("DB_PASS", $dbData["DB_PASS"]);
define("DB_HOST", $dbData["DB_HOST"]);
define("DB_PORT", $dbData["DB_PORT"]);

try{
    $connection = "mysql:dbname=".DB_NAME.";host=".DB_HOST.";port=".DB_PORT.";";
    $db = new PDO($connection, DB_USER,DB_PASS);
    $db->exec('SET NAMES utf8');
}catch(PDOException $e){
    echo "No se ha podido conectar a la base de datos.";
    die();
}