<?php
session_start();

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario']) || !isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Obtener el ID del usuario actual
$idUsuarioActual = $_SESSION['id'];

// Obtener los parámetros pasados por el botón (ID del emisor y su clave)
if (isset($_GET['idEnvia']) && isset($_GET['clave'])) {
    $idEnvia = $_GET['idEnvia'];   // ID del usuario que envió la solicitud
    $claveEmisor = $_GET['clave']; // Clave del emisor

    // 1. Agregar la nueva amistad a php/amistades.txt
    $archivoAmistades = 'php/amistades.txt';
    $amistades = file($archivoAmistades, FILE_IGNORE_NEW_LINES);
    
    // Obtener el último IDAmistad y asignar el siguiente ID
    $ultimoId = 0;
    if (count($amistades) > 0) {
        $ultimoAmistad = end($amistades);
        $ultimoId = explode(" | ", $ultimoAmistad)[0]; // Obtener el último IDAmistad
    }
    $nuevoIdAmistad = $ultimoId + 1;

    // Guardar la nueva amistad en el archivo
    $nuevaAmistad = $nuevoIdAmistad . " | " . $claveEmisor . " | " . $idUsuarioActual;
    file_put_contents($archivoAmistades, $nuevaAmistad . PHP_EOL, FILE_APPEND);

    // 2. Eliminar la solicitud de php/solPendientes.txt
    $archivoSolicitudes = 'php/solPendientes.txt';
    $solicitudes = file($archivoSolicitudes, FILE_IGNORE_NEW_LINES);

    // Crear una nueva lista de solicitudes sin la que se ha aceptado
    $nuevasSolicitudes = [];
    foreach ($solicitudes as $solicitud) {
        list($idEnviaSolicitud, $idRecibeSolicitud) = explode(" | ", $solicitud);

        // Aseguramos que eliminamos la solicitud correcta: Si es de "idEnvia" a "idRecibe" o viceversa.
        if (($idEnviaSolicitud == $idEnvia && $idRecibeSolicitud == $idUsuarioActual) || 
            ($idEnviaSolicitud == $idUsuarioActual && $idRecibeSolicitud == $idEnvia)) {
            // No agregar esta solicitud a las nuevas solicitudes (eliminamos esta)
            continue;
        }

        // Si no es la solicitud que vamos a aceptar, la mantenemos en el archivo
        $nuevasSolicitudes[] = $solicitud;
    }

    // Guardar el archivo actualizado sin la solicitud aceptada
    file_put_contents($archivoSolicitudes, implode(PHP_EOL, $nuevasSolicitudes) . PHP_EOL);

    // Redirigir o dar respuesta de éxito
    echo "Solicitud aceptada con éxito. Ahora son amigos.";
    // También podrías redirigir al usuario de vuelta a la página de solicitudes o a su perfil
} else {
    echo "Error: Parámetros incompletos.";
}
?>
