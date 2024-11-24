<?php
session_start();

// Ruta al archivo de usuarios
$archivo = 'usuarios.txt';

// Obtener los datos del formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['txtUsuario'];
    $contraseña = $_POST['txtContra'];

    // Guardar temporalmente los datos ingresados en la sesión
    $_SESSION['usuario_tmp'] = $usuario;
    $_SESSION['contraseña_tmp'] = $contraseña;

    // Variable para indicar si encontramos el usuario
    $usuarioEncontrado = false;

    // Abrir el archivo y leerlo línea por línea
    if (file_exists($archivo)) {
        $file = fopen($archivo, "r");

        while (($linea = fgets($file)) !== false) {
            $campos = explode("|", $linea);
            $usuarioArchivo = trim($campos[2]);
            $contraseñaArchivo = trim($campos[7]);

            if ($usuario === $usuarioArchivo) {
                $usuarioEncontrado = true;
                if ($contraseña === $contraseñaArchivo) {
                    // Guardar el usuario y la ID en la sesión
                    $_SESSION['usuario'] = $usuario;
                    $_SESSION['id'] = trim($campos[0]); // Suponiendo que la ID está en el campo [0] del archivo
                    unset($_SESSION['usuario_tmp'], $_SESSION['contraseña_tmp']); // Limpiar los datos temporales
                    // Redirigir sin parámetro de error
                    header("Location: ../inicioRed.php");
                    exit();
                }
            }
        }

        fclose($file);
    }

    // Redirigir con el mensaje adecuado
    if (!$usuarioEncontrado) {
        // Aquí debería ir el parámetro de error si no encuentra el usuario
        header("Location: ../index.php?error=usuario");
        exit();
    } else {
        // Si la contraseña es incorrecta
        header("Location: ../index.php?error=contraseña");
        exit();
    }
}

?>
