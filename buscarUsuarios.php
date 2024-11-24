<?php
if (isset($_GET['query'])) {
    $query = strtolower(trim($_GET['query'])); // Convertir a minúsculas para comparación sin distinción de mayúsculas

    // Leer el archivo de usuarios
    $archivoUsuarios = 'php/usuarios.txt';
    if (!file_exists($archivoUsuarios)) {
        echo json_encode([]); // Retornar vacío si no existe el archivo
        exit;
    }

    // Leer todas las líneas del archivo
    $usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);
    $resultados = [];

    // Buscar coincidencias
    foreach ($usuarios as $usuario) {
        list($idUsuario, $nombre, $usuario, $correo, $sexo, $fechaNacimiento, $imagen, $contrasena) = explode(" | ", $usuario);

        // Verificar si el nombre contiene la búsqueda (sin distinción de mayúsculas/minúsculas)
        if (strpos(strtolower($nombre), $query) !== false) {
            // Si hay coincidencia, agregar el usuario a los resultados
            $resultados[] = [
                'idUsuario' => $idUsuario,
                'nombre' => $nombre,
                'imagen' => $imagen // Puedes usar esta imagen en el frontend si quieres mostrarla
            ];
        }
    }

    // Retornar los resultados como un JSON
    echo json_encode($resultados);
} else {
    echo json_encode([]); // Si no se pasa la query, retornar vacío
}
?>
