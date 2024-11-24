<?php
if (isset($_POST['idPubli'], $_POST['contenido'], $_POST['privacidad'])) {
    $idPubli = $_POST['idPubli'];
    $idSesion = $_POST['idSesion'];
    $contenido = trim($_POST['contenido']);
    $privacidad = $_POST['privacidad']; // Capturar el valor del select

    $file = 'php/Publicaciones.txt';
    $lines = file($file, FILE_IGNORE_NEW_LINES);
    $nuevaLinea = '';
    $found = false;

    // Procesar imágenes
    $imagenes = [];
    if (isset($_FILES['imagenes']['name'])) {
        foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
            $nombreOriginal = pathinfo($_FILES['imagenes']['name'][$key], PATHINFO_FILENAME);
            $extension = pathinfo($_FILES['imagenes']['name'][$key], PATHINFO_EXTENSION);
            $nuevoNombre = $nombreOriginal . "_edit$idPubli." . $extension;
            $rutaDestino = "php/imagenesPubli/$nuevoNombre";
            move_uploaded_file($tmpName, $rutaDestino);
            $imagenes[] = $rutaDestino;
        }
    }

    // Procesar videos
    $videos = [];
    if (isset($_FILES['videos']['name'])) {
        foreach ($_FILES['videos']['tmp_name'] as $key => $tmpName) {
            $nombreOriginal = pathinfo($_FILES['videos']['name'][$key], PATHINFO_FILENAME);
            $extension = pathinfo($_FILES['videos']['name'][$key], PATHINFO_EXTENSION);
            $nuevoNombre = $nombreOriginal . "_edit$idPubli." . $extension;
            $rutaDestino = "php/videosPubli/$nuevoNombre";
            move_uploaded_file($tmpName, $rutaDestino);
            $videos[] = $rutaDestino;
        }
    }

    foreach ($lines as $index => $line) {
        $campos = explode('|', $line);
        if (trim($campos[0]) == $idPubli) {
            $fecha = date('Y-m-d');
            $hora = date('H:i:s');
            // Actualizar columna 5 con la privacidad
            $nuevaLinea = "$idPubli | $idSesion | $contenido | " . implode(',', $imagenes) . " | " . implode(',', $videos) . " | $privacidad | $fecha | $hora";
            $lines[$index] = $nuevaLinea;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $fecha = date('Y-m-d');
        $hora = date('H:i:s');
        $nuevaLinea = "$idPubli | $idSesion | $contenido | " . implode(',', $imagenes) . " | " . implode(',', $videos) . " | $privacidad | $fecha | $hora";
        $lines[] = $nuevaLinea;
    }

    file_put_contents($file, implode("\n", $lines) . "\n");

    echo "Publicación actualizada correctamente. Presiona volver";

    // Ahora obtener el nombre de usuario desde usuarios.txt
    $usuariosFile = 'php/usuarios.txt';
    $usuarios = file($usuariosFile, FILE_IGNORE_NEW_LINES);
    $usuarioNombre = '';

    foreach ($usuarios as $usuarioLine) {
        $usuarioCampos = explode('|', $usuarioLine);
        if (trim($usuarioCampos[0]) == $idSesion) {
            $usuarioNombre = trim($usuarioCampos[1]); // Nombre del usuario
            break;
        }
    }

    // Redirigir a inicioRed.php pasando el idSesion y nombre de usuario
    if ($usuarioNombre != '') {
       
        exit();
    } else {
        echo "Error: No se encontró el usuario.";
    }

} else {
    echo "Error: Datos incompletos.";
}
?>
