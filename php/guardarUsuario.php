<?php
// Ruta del archivo donde se guardarán los datos
$archivo = 'usuarios.txt';

// Validar que el archivo existe, si no, crearlo
if (!file_exists($archivo)) {
    file_put_contents($archivo, "IDUsuario | Nombre | Usuario | Correo | Sexo | Fecha de Nacimiento | Imagen | Contraseña\n", FILE_APPEND);
}

// Leer la última línea para calcular el siguiente ID
$lineas = file($archivo);
$ultimoId = count($lineas) > 1 ? intval(explode('|', trim($lineas[count($lineas) - 1]))[0]) : 0;
$idUsuario = $ultimoId + 1;

// Obtener datos del formulario
$nombre = $_POST['txtNombre'];
$usuario = $_POST['txtUsuario'];
$correo = $_POST['txtCorreo'];
$sexo = $_POST['sexo'];
$fechaNacimiento = $_POST['fechaNacimiento'];
$contra = $_POST['txtContra'];

// Validar contraseñas
if ($contra !== $_POST['txtReContra']) {
    die("Error: Las contraseñas no coinciden.");
}

// Procesar la imagen
$directorio = 'imagenes/';
if (!is_dir($directorio)) {
    mkdir($directorio, 0777, true); // Crear directorio si no existe
}
$imagenRuta = $directorio . basename($_FILES['txtImg']['name']);
if (move_uploaded_file($_FILES['txtImg']['tmp_name'], $imagenRuta)) {
    $imagen = $imagenRuta;
} else {
    die("Error al subir la imagen.");
}

// Guardar datos en el archivo
$registro = "$idUsuario | $nombre | $usuario | $correo | $sexo | $fechaNacimiento | $imagen | $contra\n";
file_put_contents($archivo, $registro, FILE_APPEND);

// Redirigir al index.html
header("Location: ../index.php");
exit;
?>
