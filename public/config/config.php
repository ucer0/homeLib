<?php

// Credenciales de conexión de la Base de Datos
const DB_NAME = "library";
const DB_USER = "root";
const DB_PASS = "";
const DB_HOST = "localhost";
const DB_PORT = "3306";

try{
    $connection = "mysql:dbname=".DB_NAME.";host=".DB_HOST.";port=".DB_PORT.";";
    $db = new PDO($connection, DB_USER,DB_PASS);
    $db->exec('SET NAMES utf8');
}catch(PDOException $e){
    echo "No se ha podido conectar a la base de datos.";
    die();
}