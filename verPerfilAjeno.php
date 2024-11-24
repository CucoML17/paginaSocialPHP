<?php
session_start();

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario']) || !isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Recuperar el ID de usuario desde la sesión
$id = $_SESSION['id'];


if (isset($_GET['idAjeno'])) {
    $idUsuarioAjeno = $_GET['idAjeno']; // Este es el ID del usuario al que queremos ver el perfil
} else {
    // Si no se recibe el ID, redirigir o mostrar un mensaje de error
    echo "No se ha proporcionado un ID de usuario.";
    exit();
}



// Función para obtener la imagen del usuario
function obtenerImagenUsuario($id) {
    $archivo = "php/usuarios.txt"; // Ruta al archivo de usuarios
    $usuarios = file($archivo, FILE_IGNORE_NEW_LINES); // Lee todas las líneas del archivo

    // Buscar el usuario por su ID
    foreach ($usuarios as $usuario) {
        // Separar los campos por el delimitador "|"
        $campos = explode(" | ", $usuario);
        
        // Si el ID coincide, devolver la imagen
        if ($campos[0] == $id) {
            return $campos[6]; // El campo de la imagen está en la posición 6
        }
    }

    // Si no se encuentra el usuario, devolver un valor predeterminado
    return "imagenes/default.png"; // Ruta de imagen por defecto
}

// Obtener la imagen para el usuario actual
$imagenPerfil = obtenerImagenUsuario($id);

// Procesar el formulario cuando se haya enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
// Capturar el valor del select
$selectOption = $_POST['selectOption']; // El valor del select que se seleccionó

// Procesar el comentario y los archivos
$comentario = $_POST['comentario']; 

// Directorios para guardar imágenes y videos
$directorioImagenes = 'php/imagenesPubli/';
$directorioVideos = 'php/videosPubli/';

// Crear directorios si no existen
if (!is_dir($directorioImagenes)) {
    mkdir($directorioImagenes, 0777, true);
}
if (!is_dir($directorioVideos)) {
    mkdir($directorioVideos, 0777, true);
}

// Procesar los archivos (imágenes y videos)
$imagenesRuta = [];
$videosRuta = [];

// Función para generar el ID de publicación
function obtenerUltimoIDPublicacion() {
    $archivo = "php/Publicaciones.txt";
    $lineas = file($archivo, FILE_IGNORE_NEW_LINES);
    if ($lineas) {
        $ultimaLinea = end($lineas);
        $campos = explode(" | ", $ultimaLinea);
        return intval($campos[0]); // Devuelve el último ID
    }
    return 0; // Si el archivo está vacío, comienza desde 0
}

// Obtener el próximo ID de publicación
$idPublicacion = obtenerUltimoIDPublicacion() + 1; // Incrementar el ID

// Procesar los archivos
if (!empty($_FILES['files'])) {
    foreach ($_FILES['files']['tmp_name'] as $index => $tmpName) {
        $fileName = $_FILES['files']['name'][$index];
        $fileTmpPath = $_FILES['files']['tmp_name'][$index];
        $fileType = $_FILES['files']['type'][$index];

        // Asegurarse de que el archivo no sea sobreescrito, agregando el ID a su nombre
        $nombreArchivo = pathinfo($fileName, PATHINFO_FILENAME);
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $nuevoNombre = $nombreArchivo . $idPublicacion . '.' . $extension;

        if (strpos($fileType, 'image') !== false) {
            // Es una imagen
            $rutaImagen = $directorioImagenes . $nuevoNombre;
            if (move_uploaded_file($fileTmpPath, $rutaImagen)) {
                $imagenesRuta[] = $rutaImagen;
            }
        } elseif (strpos($fileType, 'video') !== false) {
            // Es un video
            $rutaVideo = $directorioVideos . $nuevoNombre;
            if (move_uploaded_file($fileTmpPath, $rutaVideo)) {
                $videosRuta[] = $rutaVideo;
            }
        }
    }
}

// Si no se subió ningún archivo, colocar "nono" en lugar de rutas de imagen o video
if (empty($imagenesRuta)) {
    $imagenesRuta[] = "nono";
}
if (empty($videosRuta)) {
    $videosRuta[] = "nono";
}

// Unir las rutas de los archivos (separados por coma)
$imagenes = implode(",", $imagenesRuta);
$videos = implode(",", $videosRuta);
$fecha = date("Y-m-d");
$hora = date("H:i:s");

// Guardar los datos en un archivo de texto
$archivo = "php/Publicaciones.txt";
$registro = "$idPublicacion | $id | $comentario | $imagenes | $videos | $selectOption | $fecha | $hora\n"; // Agregar la opción seleccionada
file_put_contents($archivo, $registro, FILE_APPEND);

// Redirigir al usuario después de guardar la publicación
header("Location: verPerfilAjeno.php");
exit();

}
?>





<?php
// Cargar las publicaciones desde el archivo
$publicaciones = file('php/Publicaciones.txt', FILE_IGNORE_NEW_LINES);

// Crear un array para almacenar las publicaciones de este usuario
$misPublicaciones = [];



// Archivo de amistades
$archivoAmistades = "php/amistades.txt";

// Iterar sobre las publicaciones
foreach ($publicaciones as $publicacion) {
    $campos2 = explode('|', $publicacion);

    // Obtener datos de la publicación
    $idAutor = trim($campos2[1]);      // ID del usuario que publicó
    $privacidad = trim($campos2[5]);  // Privacidad de la publicación

    // Verificar condiciones para mostrar la publicación
    if (
        $idAutor == $idUsuarioAjeno && 
        ($privacidad == "Público" || ($privacidad == "Amigos" && sonAmigos($id, $idUsuarioAjeno, $archivoAmistades)))
    ) {
        $misPublicaciones[] = $campos2;
    }
}



// Función para obtener los datos de un usuario
function obtenerDatosUsuario($id) {
    $archivo = "php/usuarios.txt"; // Ruta al archivo de usuarios
    $usuarios = file($archivo, FILE_IGNORE_NEW_LINES); // Lee todas las líneas del archivo

    // Buscar el usuario por su ID
    foreach ($usuarios as $usuario) {
        // Separar los campos por el delimitador "|"
        $campos = explode(" | ", $usuario);
        
        // Si el ID coincide, devolver los datos del usuario
        if ($campos[0] == $id) {
            return $campos; // Devuelve todos los datos del usuario
        }
    }

    return null; // Si no se encuentra el usuario, devolver null
}

// Obtener los datos del usuario ajeno
$datosUsuarioAjeno = obtenerDatosUsuario($idUsuarioAjeno);
if (!$datosUsuarioAjeno) {
    echo "No se encontró al usuario.";
    exit();
}

$imagenAjeno = $datosUsuarioAjeno[6];  // La ruta de la imagen del usuario
$nombreAjeno = $datosUsuarioAjeno[1];
$correoAjeno = $datosUsuarioAjeno[3];
$usuarioAjeno = $datosUsuarioAjeno[2];
$sexoAjeno = $datosUsuarioAjeno[4];



// Ordenar las publicaciones por fecha y hora de publicación (últimas primero)
usort($misPublicaciones, function($a, $b) {
    return strtotime($b[6] . ' ' . $b[7]) - strtotime($a[6] . ' ' . $a[7]); // Columna de fecha y hora ajustada
});














// Verificar si los usuarios son amigos
function sonAmigos($idUsuario1, $idUsuario2, $archivoAmistades) {
    if (!file_exists($archivoAmistades)) {
        return false; // Si no existe el archivo, no hay amistades
    }
    $amistades = file($archivoAmistades, FILE_IGNORE_NEW_LINES);

    foreach ($amistades as $amistad) {
        list($idAmistad, $idAmigo1, $idAmigo2) = explode(" | ", $amistad);

        // Verificar si ambos IDs están en la misma fila, sin importar el orden
        if (($idAmigo1 == $idUsuario1 && $idAmigo2 == $idUsuario2) || ($idAmigo1 == $idUsuario2 && $idAmigo2 == $idUsuario1)) {
            return true; // Son amigos
        }
    }
    return false; // No son amigos
}

// Verificar si hay una solicitud pendiente entre los usuarios
function solicitudPendiente($idEnvia, $idRecibe, $archivoSolicitudes) {
    if (!file_exists($archivoSolicitudes)) {
        return false; // Si no existe el archivo, no hay solicitudes
    }
    $solicitudes = file($archivoSolicitudes, FILE_IGNORE_NEW_LINES);

    foreach ($solicitudes as $solicitud) {
        list($idSolicitante, $idReceptor) = explode(" | ", $solicitud);

        // Verificar si hay una solicitud pendiente (en la dirección específica)
        if ($idSolicitante == $idEnvia && $idReceptor == $idRecibe) {
            return true; // Hay una solicitud pendiente
        }
    }
    return false; // No hay solicitudes pendientes
}

// Determinar el botón que se mostrará
$archivoAmistades = "php/amistades.txt";
$archivoSolicitudes = "php/solPendientes.txt";

if (sonAmigos($id, $idUsuarioAjeno, $archivoAmistades)) {
    // Si son amigos, mostrar el botón "Cancelar Amistad"
    $botonAccion = '<button class="btn-cancelar" onclick="cancelarAmistad(' . $id . ', ' . $idUsuarioAjeno . ')">Cancelar Amistad</button>';
} elseif (solicitudPendiente($id, $idUsuarioAjeno, $archivoSolicitudes)) {
    // Si ya se envió una solicitud, mostrar un botón bloqueado
    $botonAccion = '<button class="btn-solicitud" disabled>Ya envió solicitud</button>';
} else {
    // Si no son amigos ni hay solicitud pendiente, mostrar el botón "Enviar solicitud de amistad"
    $botonAccion = '<button class="btn-enviar">Enviar solicitud de amistad</button>';
}





// Leer las solicitudes pendientes
$solicitudesPendientes = file('php/solPendientes.txt', FILE_IGNORE_NEW_LINES);
$misSolicitudes = [];

// Buscar las solicitudes donde el usuario actual es el receptor
foreach ($solicitudesPendientes as $solicitud) {
    $campos = explode(" | ", $solicitud);

    if ($campos[1] == $id) {
        // Si es una solicitud pendiente para este usuario, obtener los datos del emisor
        $datosEmisor = obtenerDatosEmisor($campos[0]);

        if ($datosEmisor) {
            // Añadir los detalles de la solicitud a la lista
            $misSolicitudes[] = [
                'nombre' => $datosEmisor['nombre'],
                'imagen' => $datosEmisor['imagen'],
                'clave' => $datosEmisor['clave'],
                'idEnvia' => $campos[0], // El ID del emisor
            ];
        }
    }
}

function obtenerDatosEmisor($idEmisor) {
    $archivoUsuarios = 'php/usuarios.txt'; // Ruta al archivo de usuarios
    $usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES); // Lee todas las líneas del archivo

    foreach ($usuarios as $usuario) {
        $campos = explode(" | ", $usuario); // Separa los campos por "|"

        // Si el ID coincide, devolver los datos del usuario
        if ($campos[0] == $idEmisor) {
            return [
                'nombre' => $campos[1],
                'imagen' => $campos[6], // Suponiendo que la imagen está en el índice 6
                'clave' => $campos[7] // Clave del emisor
            ];
        }
    }

    return null; // Si no se encuentra el usuario, devolver null
}

//---------------------------------------------------------
// Leer el archivo php/amistades.txt
// Leer el archivo php/amistades.txt
$archivoAmistades = 'php/amistades.txt';
if (!file_exists($archivoAmistades)) {
    echo "No se encontraron amistades.";
    exit();
}

$amistades = file($archivoAmistades, FILE_IGNORE_NEW_LINES);

// Leer el archivo php/usuarios.txt para obtener los datos del usuario
$archivoUsuarios = 'php/usuarios.txt';
if (!file_exists($archivoUsuarios)) {
    echo "No se encontraron usuarios.";
    exit();
}

$usuariosData = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);

// Filtrar las amistades donde el usuario actual sea parte de la relación (en cualquier columna)
$amistadesUsuarioActual = [];
foreach ($amistades as $amistad) {
    list($idAmistad, $idAmigo1, $idAmigo2) = explode(" | ", $amistad);

    // Verificar si el usuario actual está en IDAmigo1 o IDAmigo2
    if ($idAmigo1 == $id || $idAmigo2 == $id) {
        // Determinar el ID del amigo (el que no es el usuario actual)
        $idAmigo = ($idAmigo1 == $id) ? $idAmigo2 : $idAmigo1;
        $amistadesUsuarioActual[] = $idAmigo;
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Red</title>
    <link rel="stylesheet" href="css/cssIniRed.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/cssElimModal.css">


    <style>
        /* Estilos para la estructura */
        .perfil-container {
            display: flex;
            justify-content: flex-start;
            align-items: center;
            margin-top: 50px;
            padding-left: 10px;
        }

        .imagen-usuario {
            width: 150px;
            height: 150px;
            background-size: cover;
            background-position: center;
            border-radius: 50%;
            margin-right: 20px;
        }

        .datos-usuario {
            font-size: 16px;
        }

        .datos-usuario p {
            margin: 5px 0;
        }
    </style>


</head>
<body>

    <!-- Navbar sticky -->
    <!-- Navbar sticky -->
    <nav class="navbar">
        <div class="navbar-container">
            <div class="navbar-logo">
                <img src="img/red_logo.png" alt="Logo" class="logo">
            </div>

            <div class="navbar-search">
                <div class="search-input-container" style="position: relative;">
                    <input type="text" id="searchInput" placeholder="Buscar personas..." class="search-input">
                    <i class="fa fa-search search-input-icon"></i> <!-- Ícono de la lupa -->
                    <div id="searchResults" class="search-results-container" style="position: absolute; background-color: white; width: 100%; max-height: 200px; overflow-y: auto; display: none;">
                        <!-- Aquí aparecerán las sugerencias de búsqueda -->
                    </div>
                </div>
            </div>

            <div class="navbar-links">
                <a href="inicioRed.php" class="navbar-link"><i class="fas fa-home"></i></a>
                
                <a href="todosPerfiles.php" class="navbar-link"><i class="fas fa-user-plus"></i></a>
                <a href="configPerfil.php" class="navbar-link"><i class="fas fa-cogs"></i></a>
                
            </div>
            <div class="navbar-profile">
               <a href="miPerfil.php"> <img src="php/<?php echo $imagenPerfil; ?>" alt="Perfil" class="profile-img"></a>
            </div>
        </div>
    </nav>



    <div class="main-content">
    <div class="left-column">
        <h3>Tus solicitudes</h3>
        <?php if (count($misSolicitudes) > 0): ?>
            <?php foreach ($misSolicitudes as $solicitud): ?>
                <div class="card">
                    <div class="card-content">
                        <!-- Imagen del emisor -->
                        <img src="php/<?php echo $solicitud['imagen']; ?>" alt="Imagen de solicitud" class="card-img">
                        <div class="text-content">
                            <!-- Nombre del emisor -->
                            <p class="solicitante-nombre"><?php echo $solicitud['nombre']; ?></p>
                            <div class="buttons">
                                <!-- Botones para aceptar y eliminar solicitud -->
                                <button class="btn-aceptar" data-id-enviar="<?php echo $solicitud['idEnvia']; ?>" data-clave="<?php echo $solicitud['idEnvia']; ?>">Aceptar</button>
                                <button class="btn-eliminar" data-id-enviar="<?php echo $solicitud['idEnvia']; ?>">Eliminar</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No tienes solicitudes pendientes.</p>
        <?php endif; ?>
    </div>




    
<div class="center-column">
    <!-- Formulario con imagen de perfil y mensaje -->



    <div class="perfil-container">
        <!-- Imagen del usuario ajeno -->
        <div class="imagen-usuario" style="background-image: url('php/<?php echo $imagenAjeno; ?>');"></div>

        <!-- Datos del usuario ajeno -->
        <div class="datos-usuario">
            <h2><?php echo $nombreAjeno; ?></h2>
            <p><strong>Correo:</strong> <?php echo $correoAjeno; ?></p>
            <p><strong>Usuario:</strong> <?php echo $usuarioAjeno; ?></p>
            <p><strong>Sexo:</strong> <?php echo $sexoAjeno; ?></p>
        </div>
  

    </div>
    <br>
    <div class="acciones-usuario" style="padding-left:10px;">
            <?php echo $botonAccion; ?>
        </div>







<!-- OJOOOOOOOOOOOOOOOOOOOOOOOOO -->
<?php
// Asegúrate de incluir el archivo de funciones
require_once 'php/funciones.php'; // Incluye el archivo donde están definidas las funciones
?>


<div class="publicaciones-container">
    <?php foreach ($misPublicaciones as $publicacion): ?>
        <div class="carta">
            <!-- Parte superior de la carta (nombre, fecha, comentario) -->
            <div class="carta-header">
                <div class="usuario-info">
                    <img src="php/<?php echo obtenerImagenUsuario($publicacion[1]); ?>" alt="Perfil" class="profile-img_coment">
                <div class="user-details">
                    <div><?php echo obtenerNombreUsuario($publicacion[1]); ?></div>
                    <div><?php echo $publicacion[6] . ' ' . $publicacion[7]; ?></div> <!-- Fecha y hora -->
                    <div><?php echo $publicacion[5]; ?></div> <!-- Visibilidad -->
                </div>
             </div>

            </div>
            
            <!-- Comentario -->
            <div class="comentario">
                <p><?php echo $publicacion[2]; ?></p> <!-- Comentario (columna 2) -->
            </div>

            <!-- Imágenes y Videos -->
            <?php
            $imagenes = explode(',', $publicacion[3]); // Columna 3 para imágenes
            $videos = explode(',', $publicacion[4]); // Columna 4 para videos
            $mediaCount = 0;
            ?>
            <div class="media-container">
                <!-- Imágenes -->
                <?php foreach ($imagenes as $imagen): ?>
                    <?php if (trim($imagen) != 'nono'): ?>
                        
                        <img src="<?php echo $imagen; ?>" alt="Imagen" class="media-item">
                        <?php $mediaCount++; ?>
                    <?php endif; ?>
                <?php endforeach; ?>

                <!-- Videos -->
                <?php foreach ($videos as $video): ?>
                    <?php if (trim($video) != 'nono' ): ?>
                        <video class="media-item" controls>
                            <source src="<?php echo $video; ?>" type="video/mp4">
                        </video>
                        <?php $mediaCount++; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <!-- Botones para editar y eliminar -->
            <div class="botones">

            </div>
        </div>
    <?php endforeach; ?>
</div>


<!-- OJOOOOOOOOOOOOOOOOOOOOOOOOO -->



<!-- Modal para confirmar eliminación -->
<div id="modal_elimin" class="modal-eliminar">
    <div class="modal-eliminar-contenido">
        <p>¿De verdad desea borrar dicha publicación?</p>
        <div class="modal-botones">
            <button id="btnAceptar_elimin" class="btn-eliminar">Aceptar</button>
            <button id="btnCancelar_elimin" class="btn-cancelar">Cancelar</button>
        </div>
    </div>
</div>




    
    <!-- Modal para Foto -->
    <div id="fotoModal_coment" class="modal_coment">
        <div class="modal-content_coment">
            <div class="modal-header_coment">
                <!-- Botón de Cierre (X) -->
                <button class="close-modal_coment" onclick="cerrarModal()">X</button>

                <!-- Imagen y Nombre de Usuario -->
                <img src="php/<?php echo obtenerImagenUsuario($id); ?>" alt="Perfil" class="modal-profile-img_coment">
                <div class="modal-user-info_coment">
                    <p class="modal-username_coment"><?php echo $_SESSION['usuario']; ?></p>
                    <select class="modal-select_coment">
                        <option>Amigos</option>
                        <option>Público</option>
                        <option>Privado</option>
                    </select>
                </div>
            </div>
            <textarea placeholder="¿Qué estás pensando?" rows="4" class="modal-textarea_coment"></textarea>

            <!-- Previsualización de Archivos -->
            <div id="previews_coment" class="modal-previews_coment"></div>

            <div class="modal-buttons_coment">
                <!-- Botones para seleccionar Foto o Video -->
                <button class="btn_coment" id="photoBtn_coment">Foto</button>
                <button class="btn_coment" id="videoBtn_coment">Vídeo</button>
                <button class="btn_coment" id="publishBtn_coment">Publicar</button>
            </div>

            <!-- Input oculto para seleccionar imagen o vídeo -->
            <input type="file" id="fileInput_coment" style="display: none;" accept="image/*,video/*" multiple />
        </div>
    </div>































    <!-- Modal para Vídeo -->
    <div id="videoModal_coment" class="modal_coment">
        <div class="modal-content_coment">
            <div class="modal-header_coment">
                <img src="php/<?php echo obtenerImagenUsuario($id); ?>" alt="Perfil" class="modal-profile-img_coment">
                <div class="modal-user-info_coment">
                    <p class="modal-username_coment"><?php echo $_SESSION['usuario']; ?></p>
                    <select class="modal-select_coment">
                        <option>Amigos</option>
                        <option>Público</option>
                        <option>Privado</option>
                    </select>
                </div>
            </div>
            <textarea placeholder="¿Qué estás pensando?" rows="4" class="modal-textarea_coment"></textarea>
            <div class="modal-buttons_coment">
                <button class="btn_coment">Foto</button>
                <button class="btn_coment">Vídeo</button>
                <button class="btn_coment">Publicar</button>
            </div>
        </div>
    </div>

    <!-- Modal para Emoción -->
    <div id="emocionModal_coment" class="modal_coment">
        <div class="modal-content_coment">
            <div class="modal-header_coment">
                <img src="php/<?php echo obtenerImagenUsuario($id); ?>" alt="Perfil" class="modal-profile-img_coment">
                <div class="modal-user-info_coment">
                    <p class="modal-username_coment"><?php echo $_SESSION['usuario']; ?></p>
                    <select class="modal-select_coment">
                        <option>Amigos</option>
                        <option>Público</option>
                        <option>Privado</option>
                    </select>
                </div>
            </div>
            <textarea placeholder="¿Qué estás pensando?" rows="4" class="modal-textarea_coment"></textarea>
            <div class="modal-buttons_coment">
                <button class="btn_coment">Foto</button>
                <button class="btn_coment">Vídeo</button>
                <button class="btn_coment">Publicar</button>
            </div>
        </div>
    </div>
</div>









    
    <div class="right-column">
        <h3>Tus amigos</h3>
        <?php
        foreach ($amistadesUsuarioActual as $idAmigo) {
            // Buscar los datos del amigo en php/usuarios.txt
            foreach ($usuariosData as $usuario) {
                list($idUsuario, $nombre, $usuarioNombre, $correo, $sexo, $fechaNacimiento, $imagen, $contraseña) = explode(" | ", $usuario);

                // Si el ID del usuario coincide con el ID del amigo
                if ($idUsuario == $idAmigo) {
                    echo '
                    <div class="card_frens">
                        <div class="card-content_frens">
                            <a href="verPerfilAjeno.php?idAjeno=' . $idUsuario . '">
                                <img src="php/' . $imagen . '" alt="Foto de perfil" class="card-img_frens">
                            </a>
                            <div class="text-content_frens">
                                <a href="verPerfilAjeno.php?idAjeno=' . $idUsuario . '">
                                    <p class="friend-name_frens">' . $nombre . '</p>
                                </a>
                            </div>
                        </div>
                    </div>';
                    break; // Salir del bucle después de encontrar al amigo
                }
            }
        }
        ?>
    </div>
</div>





<script>
    // Obtén los botones de acción (Foto, Vídeo, Emoción)
    const btnFoto = document.querySelector('.btn_coment[data-target="#fotoModal_coment"]');
    const btnVideo = document.querySelector('.btn_coment[data-target="#videoModal_coment"]');
    const btnEmocion = document.querySelector('.btn_coment[data-target="#emocionModal_coment"]');

    // Obtén los modales
    const modalFoto = document.getElementById('fotoModal_coment');
    const modalVideo = document.getElementById('videoModal_coment');
    const modalEmocion = document.getElementById('emocionModal_coment');

    // Función para abrir el modal
    function abrirModal(modal) {
        modal.style.display = 'flex';
    }

    // Función para cerrar el modal
    function cerrarModal(modal) {
        modal.style.display = 'none';
    }

    // Cuando se haga clic en un botón, abre el modal correspondiente
    btnFoto.addEventListener('click', function() {
        abrirModal(modalFoto);
    });

    btnVideo.addEventListener('click', function() {
        abrirModal(modalVideo);
    });

    btnEmocion.addEventListener('click', function() {
        abrirModal(modalEmocion);
    });

    // Cerramos el modal si se hace clic fuera de la ventana modal
    window.addEventListener('click', function(event) {
        if (event.target === modalFoto) {
            cerrarModal(modalFoto);
        } else if (event.target === modalVideo) {
            cerrarModal(modalVideo);
        } else if (event.target === modalEmocion) {
            cerrarModal(modalEmocion);
        }
    });

    // Abre el modal de Foto cuando se haga clic en la imagen de perfil o en el input
    const imagenPerfil = document.querySelector('.profile-img_coment');
    const inputPensando = document.querySelector('.thinking-input_coment');

    imagenPerfil.addEventListener('click', function() {
        abrirModal(modalFoto); // Abre el modal de Foto
    });

    inputPensando.addEventListener('click', function() {
        abrirModal(modalFoto); // Abre el modal de Foto
    });


    // Función para cerrar el modal
function cerrarModal(modal) {
    modal.style.display = 'none';
}

// Puedes seguir usando los mismos eventos de clic para los botones, como se mencionó antes
// Cuando se haga clic en la X, se cerrará el modal de Foto
document.querySelector('.close-modal_coment').addEventListener('click', function() {
    cerrarModal(modalFoto); // Cierra el modal de Foto
});

</script>


<script src="js/inicioPublicar.js"></script>





<script>



function editarPublicacion(idPubli) {
    // Crear el formulario dinámicamente
    var form = document.createElement('form');
    form.method = 'POST';  // Usar POST para enviar los datos
    form.action = 'editarPubli.php';  // Archivo de destino donde recibirás los datos

    // Crear el campo oculto para 'idPubli'
    var inputIdPubli = document.createElement('input');
    inputIdPubli.type = 'hidden';
    inputIdPubli.name = 'idPubli';
    inputIdPubli.value = idPubli;  // El valor de la ID de la publicación

    // Crear el campo oculto para 'id' (sesión de usuario)
    var inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'id';
    inputId.value = <?php echo $_SESSION['id']; ?>;  // El valor de la ID de la sesión en PHP

    // Agregar los campos al formulario
    form.appendChild(inputIdPubli);
    form.appendChild(inputId);

    // Enviar el formulario
    document.body.appendChild(form);  // Añadir el formulario al body
    form.submit();  // Enviar el formulario
}


function eliminarPublicacion(id) {
    // Confirmación de eliminación y redirección
    if (confirm('¿Estás seguro de eliminar esta publicación?')) {
        window.location.href = `eliminar_publicacion.php?id=${id}`;
    }
}



</script>











<script>
// Variables del modal
const modalEliminar = document.getElementById('modal_elimin');
const btnAceptar = document.getElementById('btnAceptar_elimin');
const btnCancelar = document.getElementById('btnCancelar_elimin');
let idPublicacionEliminar = null;

// Mostrar el modal
function mostrarModalEliminar(idPublicacion) {
    idPublicacionEliminar = idPublicacion;
    modalEliminar.style.display = 'block';
}

// Cerrar el modal
btnCancelar.addEventListener('click', () => {
    modalEliminar.style.display = 'none';
    idPublicacionEliminar = null;
});

// Eliminar la publicación
btnAceptar.addEventListener('click', () => {
    if (idPublicacionEliminar !== null) {
        fetch('elimPubli.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `idPublicacion=${idPublicacionEliminar}`
        })
        .then(response => response.text())
        .then(data => {
    
            window.location.reload(); // Recargar la página para reflejar los cambios
        })
        .catch(error => console.error('Error:', error));
    }
});
</script>


<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Agregar evento a los botones "Aceptar"
        const botonesAceptar = document.querySelectorAll(".btn-aceptar");

        botonesAceptar.forEach(function(boton) {
            boton.addEventListener("click", function() {
                const idEnvia = this.getAttribute("data-id-enviar");
                const claveEmisor = this.getAttribute("data-clave");

                // Realizar la solicitud para aceptar la solicitud
                fetch(`aceptarSolicitud.php?idEnvia=${idEnvia}&clave=${claveEmisor}`)
                    .then(response => response.text())
                    .then(data => {
                        alert(data); // Mostrar mensaje de éxito
                        location.reload(); // Recargar la página para reflejar los cambios
                    })
                    .catch(error => {
                        console.error("Error al aceptar la solicitud:", error);
                    });
            });
        });
    });
</script>

<script>
function cancelarAmistad(idUsuario, idAmigo) {
    // Enviar una solicitud AJAX al servidor
    fetch('php/cancelarAmistad.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `idUsuario=${idUsuario}&idAmigo=${idAmigo}`
    })
    .then(response => response.text())
    .then(data => {
        if (data.trim() === 'success') {
            alert('Amistad cancelada con éxito.');
            location.reload(); // Recargar la página para actualizar la interfaz
        } else {
            alert('Error al cancelar la amistad: ' + data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al procesar la solicitud.');
    });
}







document.addEventListener('DOMContentLoaded', () => {
    const enviarSolicitudBoton = document.querySelector('.btn-enviar');
    
    if (enviarSolicitudBoton) {
        enviarSolicitudBoton.addEventListener('click', () => {
            const idEnvia = <?php echo json_encode($id); ?>; // ID del usuario actual
            const idRecibe = <?php echo json_encode($idUsuarioAjeno); ?>; // ID del usuario ajeno
            
            fetch('enviarSolicitud.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `idEnvia=${idEnvia}&idRecibe=${idRecibe}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Solicitud enviada correctamente.');
                    location.reload(); // Opcional: Recargar la página para reflejar cambios
                } else {
                    alert(`Error al enviar solicitud: ${data.message}`);
                }
            })
            .catch(error => {
                alert(`Error en el servidor: ${error.message}`);
            });
        });
    }
});



</script>


<script>
    document.getElementById('searchInput').addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        
        if (query.length > 0) {
            // Hacer la consulta al servidor para obtener los resultados de búsqueda
            fetch('buscarUsuarios.php?query=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    // Mostrar los resultados debajo del input
                    const resultsContainer = document.getElementById('searchResults');
                    resultsContainer.innerHTML = ''; // Limpiar resultados anteriores
                    if (data.length > 0) {
                        resultsContainer.style.display = 'block';
                        data.forEach(user => {
                            const userElement = document.createElement('div');
                            userElement.classList.add('search-result-item');
                            userElement.innerHTML = `
                                <a href="verPerfilAjeno.php?idAjeno=${user.idUsuario}">
                                    <p>${user.nombre}</p>
                                </a>
                            `;
                            resultsContainer.appendChild(userElement);
                        });
                    } else {
                        resultsContainer.style.display = 'none'; // Ocultar si no hay resultados
                    }
                })
                .catch(error => {
                    console.error("Error buscando usuarios:", error);
                });
        } else {
            document.getElementById('searchResults').style.display = 'none'; // Ocultar si no hay texto
        }
    });
</script>
</body>
</html>
