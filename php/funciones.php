<?php
function obtenerNombreUsuario($idUsuario) {
    $usuarios = file('php/usuarios.txt', FILE_IGNORE_NEW_LINES);
    foreach ($usuarios as $usuario) {
        $campos3 = explode('|', $usuario);
        if ($campos3[0] == $idUsuario) {  // El IDUsuario es el primer campo
            return $campos3[1]; // Nombre del usuario está en la columna 2
        }
    }
    return 'Usuario Desconocido'; // Si no encuentra, devuelve un nombre por defecto
}


?>
