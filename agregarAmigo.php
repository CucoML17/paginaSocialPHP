<?php
// Iniciar la sesión para acceder al ID del usuario actual
session_start();

// Verificar si se ha recibido una solicitud válida
if (isset($_POST['idEnvia']) && isset($_POST['idRecibe'])) {
    $idEnvia = $_POST['idEnvia'];  // ID del usuario actual
    $idRecibe = $_POST['idRecibe'];  // ID del usuario al que se quiere agregar

    // Ruta del archivo donde se guardarán las solicitudes pendientes
    $archivoSolicitudes = 'php/solPendientes.txt';

    // Verificar si el archivo existe, si no, crearlo
    if (!file_exists($archivoSolicitudes)) {
        file_put_contents($archivoSolicitudes, "");  // Crear archivo vacío si no existe
    }

    // Crear una nueva solicitud
    $nuevaSolicitud = "$idEnvia | $idRecibe\n";

    // Guardar la solicitud en el archivo
    file_put_contents($archivoSolicitudes, $nuevaSolicitud, FILE_APPEND);

    // Enviar una respuesta de éxito
    echo 'Solicitud de amistad enviada correctamente.';
} else {
    // Si no se recibieron los parámetros, enviar un error
    echo 'Faltan parámetros.';
}
?>
