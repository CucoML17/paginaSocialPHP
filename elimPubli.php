<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idPublicacion = $_POST['idPublicacion'];
    $archivo = 'php/Publicaciones.txt';

    // Leer el archivo línea por línea
    $lineas = file($archivo, FILE_IGNORE_NEW_LINES);

    // Abrir el archivo en modo de escritura
    $archivoNuevo = fopen($archivo, 'w');

    $eliminada = false;

    foreach ($lineas as $linea) {
        $campos = explode(' | ', $linea);
        if ($campos[0] != $idPublicacion) {
            fwrite($archivoNuevo, $linea . PHP_EOL);
        } else {
            $eliminada = true;
        }
    }

    fclose($archivoNuevo);

    if ($eliminada) {
        echo "Publicación eliminada exitosamente.";
    } else {
        echo "No se encontró la publicación.";
    }
}
?>
