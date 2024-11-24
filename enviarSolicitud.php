<?php
// Ruta del archivo de solicitudes pendientes
$archivoSolicitudes = 'php/solPendientes.txt';

// Verificar que se recibieron los datos necesarios
if (isset($_POST['idEnvia']) && isset($_POST['idRecibe'])) {
    $idEnvia = $_POST['idEnvia'];
    $idRecibe = $_POST['idRecibe'];

    // Validar que el archivo de solicitudes exista, si no, crearlo
    if (!file_exists($archivoSolicitudes)) {
        file_put_contents($archivoSolicitudes, '');
    }

    // Verificar que la solicitud no exista ya
    $solicitudes = file($archivoSolicitudes, FILE_IGNORE_NEW_LINES);
    foreach ($solicitudes as $solicitud) {
        list($idExistenteEnvia, $idExistenteRecibe) = explode(" | ", $solicitud);
        if (($idExistenteEnvia == $idEnvia && $idExistenteRecibe == $idRecibe) ||
            ($idExistenteEnvia == $idRecibe && $idExistenteRecibe == $idEnvia)) {
            echo json_encode(['status' => 'error', 'message' => 'Solicitud ya existente']);
            exit;
        }
    }

    // Añadir la nueva solicitud al archivo
    $nuevaSolicitud = $idEnvia . " | " . $idRecibe . PHP_EOL;
    file_put_contents($archivoSolicitudes, $nuevaSolicitud, FILE_APPEND);

    echo json_encode(['status' => 'success', 'message' => 'Solicitud enviada']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
}
?>
