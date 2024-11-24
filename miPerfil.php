<?php
session_start();

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario']) || !isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Recuperar el ID de usuario desde la sesión
$id = $_SESSION['id'];

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
header("Location: miPerfil.php");
exit();

}
?>





<?php
// Recuperar el ID de usuario desde la sesión
// $id = $_SESSION['id'];

// Cargar las publicaciones desde el archivo
$publicaciones = file('php/Publicaciones.txt', FILE_IGNORE_NEW_LINES);

// Crear un array para almacenar las publicaciones de este usuario
$misPublicaciones = [];

foreach ($publicaciones as $publicacion) {
    $campos2 = explode('|', $publicacion);
    
    // Verificar si el IDUsuario de la publicación coincide con el ID del usuario
    if ($campos2[1] == $id) {  // Cambié el índice de 2 a 1 para la columna de IDUsuario
        $misPublicaciones[] = $campos2;
    }
}

// Ordenar las publicaciones por fecha y hora de publicación (últimas primero)
usort($misPublicaciones, function($a, $b) {
    return strtotime($b[6] . ' ' . $b[7]) - strtotime($a[6] . ' ' . $a[7]); // Columna de fecha y hora ajustada
});















// Función para obtener los datos del emisor usando el ID--------------------------------
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


//Lo de los frens:
// Obtener los IDs de los amigos del usuario actual
$amigos = [];
foreach ($amistades as $amistad) {
    list($idAmistad, $idAmigo1, $idAmigo2) = explode(" | ", $amistad);

    // Si el usuario actual está en cualquier lado de la relación, agregar al amigo correspondiente
    if ($idAmigo1 == $id) {
        $amigos[] = $idAmigo2; // Agregar al amigo si el usuario actual es IDAmigo1
    } elseif ($idAmigo2 == $id) {
        $amigos[] = $idAmigo1; // Agregar al amigo si el usuario actual es IDAmigo2
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

</head>
<body>

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
        <!-- OJOOOOOOOOOOOOOOO2 --><!-- OJOOOOOOOOOOOOOOO2 --><!-- OJOOOOOOOOOOOOOOO2 -->
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
<!-- OJOOOOOOOOOOOOOOO2 --><!-- OJOOOOOOOOOOOOOOO2 --><!-- OJOOOOOOOOOOOOOOO2 -->




    
<div class="center-column">
    <!-- Formulario con imagen de perfil y mensaje -->
    <div class="form-container_coment">
        <div class="profile-header_coment">
            <a href="#">
                <img src="php/<?php echo obtenerImagenUsuario($id); ?>" alt="Perfil" class="profile-img_coment">
            </a>
            <input type="text" value="¿En qué estás pensando, <?php echo $_SESSION['usuario']; ?>?" readonly class="thinking-input_coment">
        </div>
        
        <!-- Botones de acción -->
        <div class="button-row_coment">
            <button class="btn_coment" data-toggle="modal" data-target="#fotoModal_coment">Foto</button>
            <button class="btn_coment" id="videoso" data-toggle="modal" >Vídeo</button>
        </div>
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
                <button class="editar-btn" onclick="editarPublicacion(<?php echo $publicacion[0]; ?>)">Editar publicación</button>
                <button class="eliminar-btn" onclick="mostrarModalEliminar(<?php echo $publicacion[0]; ?>)">Eliminar publicación</button>
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


    // Obtén los modales
    const modalFoto = document.getElementById('fotoModal_coment');


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



    // Cerramos el modal si se hace clic fuera de la ventana modal
    window.addEventListener('click', function(event) {
        if (event.target === modalFoto) {
            cerrarModal(modalFoto);
        } 
    });

    // Abre el modal de Foto cuando se haga clic en la imagen de perfil o en el input
    const imagenPerfil = document.querySelector('.profile-img_coment');
    const inputPensando = document.querySelector('.thinking-input_coment');
    const botonVideosoo = document.querySelector('#videoso');

    imagenPerfil.addEventListener('click', function() {
        abrirModal(modalFoto); // Abre el modal de Foto
    });

    inputPensando.addEventListener('click', function() {
        abrirModal(modalFoto); // Abre el modal de Foto
    });
    botonVideosoo.addEventListener('click', function() {
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
