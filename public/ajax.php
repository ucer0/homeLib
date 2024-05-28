<?php

require_once(__DIR__."/api/homeLibAdmin.class.php");
require_once(__DIR__."/config/config.php");

// Creamos una instancia de la clase
$lib = new homeLibAdmin\Library($db);

header('Content-Type: application/json');
date_default_timezone_set('Europe/Madrid');
$post = json_decode(file_get_contents('php://input'), true);

if (!empty($post['accion'])) {
    switch ($post['accion']) {
        case 'getPersonalLibrary':
            $list = $lib->getPersonalLibrary($post["id"],$post["filter"]);
            response($list);
            break;
        case 'saveBook':
            $list = $lib->saveBook($post["id"],$post["data"],$post["isNew"]);
            response($list);
            break;
        case 'updateBook':
            $list = $lib->updateBook($post["id"],$post["data"]);
            response($list);
            break;
        case 'getStorage':
            $list = $lib->getStorage();
            response($list);
            break;
        case 'getFormat':
            $list = $lib->getFormat();
            response($list);
            break;
        case 'getGenre':
            $list = $lib->getGenre();
            response($list);
            break;
        default: 
        break;
    }
}

function response($data = null) {
    die(json_encode($data));
}

