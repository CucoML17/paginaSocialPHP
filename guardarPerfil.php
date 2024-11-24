<?php
session_start();
$id = $_SESSION['id'];
$archivoUsuarios = 'php/usuarios.txt';

// Leer todos los usuarios
$usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);

// Obtener los datos del formulario
$nombre = $_POST['nombre'];
$usuario = $_POST['usuario'];
$sexo = $_POST['sexo'];
$fechaNacimiento = $_POST['fechaNacimiento'];
$contraseña = $_POST['contraseña'];

// Procesar la imagen
$imagenRuta = $_POST['imagenActual']; // Mantener la imagen actual si no se selecciona una nueva
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $directorio = 'php/imagenes/'; // Cambiar la ruta aquí para asegurar que la imagen se suba a la carpeta correcta
    if (!is_dir($directorio)) {
        mkdir($directorio, 0777, true); // Crear directorio si no existe
    }

    // Obtener el nombre de la imagen y asegurarnos de que no haya conflictos
    $imagenRuta = $directorio . basename($_FILES['imagen']['name']);
    
    // Mover el archivo a la carpeta de destino
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $imagenRuta)) {
        // Imagen subida correctamente
    } else {
        echo "Error al subir la imagen.";
        exit();
    }
}

// Eliminar la parte "php/" de la ruta antes de guardarla en el archivo
$imagenRuta = str_replace('php/', '', $imagenRuta);

// Actualizar el archivo de usuarios
$usuariosActualizados = [];
foreach ($usuarios as $linea) {
    list($idUsuario, $nombreGuardado, $usuarioGuardado, $correo, $sexoGuardado, $fechaNacimientoGuardada, $imagenGuardada, $contraseñaGuardada) = explode(" | ", $linea);
    
    if ($idUsuario == $id) {
        // Reemplazar la línea con los nuevos datos
        $nuevaLinea = "{$idUsuario} | {$nombre} | {$usuario} | {$correo} | {$sexo} | {$fechaNacimiento} | {$imagenRuta} | {$contraseña}";
        $usuariosActualizados[] = $nuevaLinea;
    } else {
        $usuariosActualizados[] = $linea; // Mantener las demás líneas intactas
    }
}

// Guardar los cambios en el archivo
if (file_put_contents($archivoUsuarios, implode("\n", $usuariosActualizados)) === false) {
    echo "Error al actualizar el archivo.";
    exit();
}

// Redirigir con el mensaje de éxito
header("Location: configPerfil.php?message=Perfil%20actualizado%20correctamente.");
exit();
?>
