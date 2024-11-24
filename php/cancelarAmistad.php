<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_POST['idUsuario'];
    $idAmigo = $_POST['idAmigo'];
    $archivoAmistades = 'amistades.txt';

    if (!file_exists($archivoAmistades)) {
        echo "El archivo de amistades no existe.";
        exit;
    }

    // Leer todas las líneas del archivo
    $amistades = file($archivoAmistades, FILE_IGNORE_NEW_LINES);

    // Buscar y eliminar la relación de amistad
    $nuevasAmistades = [];
    $amistadEncontrada = false;

    foreach ($amistades as $amistad) {
        list($idAmistad, $idAmigo1, $idAmigo2) = explode(" | ", $amistad);

        // Si coincide con ambos IDs en cualquier orden, no la añadimos a las nuevas líneas
        if (($idAmigo1 == $idUsuario && $idAmigo2 == $idAmigo) || ($idAmigo1 == $idAmigo && $idAmigo2 == $idUsuario)) {
            $amistadEncontrada = true; // Marcamos que se encontró y eliminó
        } else {
            $nuevasAmistades[] = $amistad; // Mantener las otras amistades
        }
    }

    if ($amistadEncontrada) {
        // Guardar las nuevas líneas de amistad en el archivo, con un salto de línea adicional al final
        file_put_contents($archivoAmistades, implode(PHP_EOL, $nuevasAmistades) . PHP_EOL);
        echo "success";
    } else {
        echo "No se encontró la amistad.";
    }
}
?>
