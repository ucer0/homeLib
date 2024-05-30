<?php

/**
 * Sintaxis de errores (EJ: "SU0116"):
 * "SU": [Dos iniciales del dev que lo haya añadido]
 * "01": [Número de módulo de los errores] Quizás cambio a nombre?
 * "16": [Número de error]
 */

//  [Iniciales Dev][Módulo][Núm Error] 

// Códigos de error
const QUERY_OK                     = "SU0100";
const QUERY_NO_EJECUTADA           = "SU0101";
const QUERY_SIN_DATOS              = "SU0102";

// Mensajes de códigos de error
const QUERY_OK_MSG                 = "La consulta se ejecutó correctamente";

const QUERY_UPDATE_MSG             = "Libro actualizado correctamente";
const QUERY_UPDATE_USER_MSG        = "Usuario actualizado correctamente";
const QUERY_NO_UPDATE_MSG          = "No se ha cambiado ningún campo";

const QUERY_INSERT_MSG             = "Libro añadido correctamente";
const QUERY_NO_INSERT_MSG          = "No se ha podido añadir libro";

const QUERY_NO_EJECUTADA_MSG       = "Hubo un error al ejecutar la consulta";
const QUERY_SIN_DATOS_MSG          = "No se encontró ningún registro";

const QUERY_IMPORT_MSG             = "Copia de seguridad importada";
const QUERY_NO_IMPORT_MSG          = "No se pudo importar la copia de seguridad";

