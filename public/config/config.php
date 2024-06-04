<?php

// Recogemos los datos de db.json
$file = file_get_contents("db.json");
$dbData = json_decode($file,true);

// Credenciales de conexión de la Base de Datos
const DB_NAME = $dbData["DB_NAME"];
const DB_USER = $dbData["DB_USER"];
const DB_PASS = $dbData["DB_PASS"];
const DB_HOST = $dbData["DB_HOST"];
const DB_PORT = $dbData["DB_PORT"];

try{
    $connection = "mysql:dbname=".DB_NAME.";host=".DB_HOST.";port=".DB_PORT.";";
    $db = new PDO($connection, DB_USER,DB_PASS);
    $db->exec('SET NAMES utf8');
}catch(PDOException $e){
    echo "No se ha podido conectar a la base de datos.";
    die();
}