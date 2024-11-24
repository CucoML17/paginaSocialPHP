<?php
// Verificar si los parámetros están presentes
if (isset($_POST['idPubli']) && isset($_POST['id'])) {
    $idPubli = $_POST['idPubli'];  // ID de la publicación
    $idSesion = $_POST['id'];      // ID de la sesión del usuario

    $id = $idSesion;
    // Leer el archivo Publicaciones.txt
    $file = "php/Publicaciones.txt"; 
    $fileContents = file_get_contents($file);
    $lines = explode("\n", $fileContents);

    // Variables para almacenar los datos de la publicación
    $imagenes = [];
    $videos = [];
    $contenido = '';
    $privacidad = '';

    // Buscar la línea correspondiente a la publicación
    foreach ($lines as $line) {
        $campos = explode(" | ", $line);
        if ($campos[0] == $idPubli) {
            // Extraer los datos de la publicación
            $contenido = $campos[2];  // Comentario
            $imagenes = explode(",", $campos[3]);  // Imágenes
            $videos = explode(",", $campos[4]);  // Videos
            $privacidad = $campos[5];  // Privacidad
            break;  // Ya encontramos la publicación, podemos salir del ciclo
        }
    }

    // Función para obtener la imagen del perfil del usuario
    function obtenerImagenUsuario($id) {
        $archivo = "php/usuarios.txt";
        $usuarios = file($archivo, FILE_IGNORE_NEW_LINES);
        foreach ($usuarios as $usuario) {
            $campos = explode(" | ", $usuario);
            if ($campos[0] == $id) {
                return $campos[6];  // Imagen de perfil está en la columna 6
            }
        }
        return "imagenes/default.png"; // Valor predeterminado si no se encuentra
    }

    // Obtener la imagen para el usuario actual
    $imagenPerfil = obtenerImagenUsuario($idSesion);




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
}
?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio Red</title>
    <link rel="stylesheet" href="css/cssIniRed.css">
    <link rel="stylesheet" href="css/edit.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


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
    <h2 class="editar-titulo">Editar publicación</h2>

    <form action="" id="formol" enctype="multipart/form-data">

        <div class="form-group">
            <label for="contenido" class="form-label">Comentario:</label>
            <div class="form-group">
                    <select name="privacidad" id="privacidad" class="modal-select_coment">
                        <option value="Amigos">Amigos</option>
                        <option value="Público">Público</option>
                        <option value="Privado">Privado</option>
                    </select>


                </div> 
            <textarea name="contenido" id="contenido" class="form-textarea" rows="4"><?php echo $contenido; ?></textarea>
        </div>

        <!-- Area de "preview" donde se mostrarán las imágenes y videos -->
        <div id="preview" class="preview-container">
            <!-- Pre-cargar imágenes -->
            <?php
                foreach ($imagenes as $imagen) {
                    if ($imagen != 'nono') {
                        echo "<div class='preview-item'><img src='$imagen' alt='imagen'></div>";
                    }
                }
                foreach ($videos as $video) {
                    if ($video != 'nono') {
                        echo "<div class='preview-item'><video src='$video' controls></video></div>";
                    }
                }
            ?>
        </div>

        <input type="file" id="photoInput" accept="image/*" multiple style="display: none;">
        <input type="file" id="videoInput" accept="video/*" multiple style="display: none;">

        <div class="modal-buttons_coment espa">
            <button class="btn_coment" id="photoBtn_coment">Foto</button>
            <button class="btn_coment" id="videoBtn_coment">Vídeo</button>
        </div>

        <div class="modal-buttons_coment cente">
            <button id="btnEditar" class="btn_coment">Guardar</button>
            <button id="btnVolver" class="btn_coment">Volver</button> 
        </div>

        <input type="hidden" id="idPubli" value="<?php echo $idPubli; ?>">
        <input type="hidden" id="idSesion" value="<?php echo $idSesion; ?>">

    </form>
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
const photoBtn = document.getElementById('photoBtn_coment');
const videoBtn = document.getElementById('videoBtn_coment');
const photoInput = document.getElementById('photoInput');
const videoInput = document.getElementById('videoInput');
const preview = document.getElementById('preview');
let imagenes = [];
let videos = [];






// Función para mostrar el preview
function actualizarPreview() {
    preview.innerHTML = ''; // Limpiar preview
    [...imagenes, ...videos].forEach((file, index) => {
        const div = document.createElement('div');
        div.className = 'preview-item';
        const media = file.type.startsWith('image/')
            ? `<img src="${URL.createObjectURL(file)}" alt="imagen">`
            : `<video src="${URL.createObjectURL(file)}" controls></video>`;
        div.innerHTML = `
            ${media}
            <button type="button" class="remove-btn" data-index="${index}">X</button>
        `;
        preview.appendChild(div);
    });
}

// Función para manejar la eliminación
preview.addEventListener('click', (e) => {
    if (e.target.classList.contains('remove-btn')) {
        const index = e.target.dataset.index;
        if (index < imagenes.length) {
            imagenes.splice(index, 1);
        } else {
            videos.splice(index - imagenes.length, 1);
        }
        actualizarPreview();
    }
});


// Agregar el evento al botón "Volver"
document.getElementById('btnVolver').addEventListener('click', function(event) {
    event.preventDefault(); // Evitar el comportamiento por defecto (si es un formulario)
    window.location.href = 'inicioRed.php'; // Redirigir a inicioRed.php
});


// Listeners para abrir selectores, evitando el envío del formulario
photoBtn.addEventListener('click', (event) => {
    event.preventDefault(); // Evitar que el formulario se envíe
    photoInput.click();
});

videoBtn.addEventListener('click', (event) => {
    event.preventDefault(); // Evitar que el formulario se envíe
    videoInput.click();
});

// Listener para añadir archivos
photoInput.addEventListener('change', () => {
    if (imagenes.length + photoInput.files.length > 4) {
        alert('Máximo 4 imágenes/videos permitidos.');
        return;
    }
    imagenes = [...imagenes, ...photoInput.files];
    actualizarPreview();
});

videoInput.addEventListener('change', () => {
    if (videos.length + videoInput.files.length > 4) {
        alert('Máximo 4 imágenes/videos permitidos.');
        return;
    }
    videos = [...videos, ...videoInput.files];
    actualizarPreview();
});

// Manejo del envío del formulario
document.getElementById('btnEditar').addEventListener('click', (event) => {
    event.preventDefault(); // Prevenir el envío predeterminado del formulario

    const formData = new FormData();
    formData.append('idPubli', document.getElementById('idPubli').value);
    formData.append('idSesion', document.getElementById('idSesion').value);
    formData.append('contenido', document.getElementById('contenido').value); // Agregar el contenido del textarea
    formData.append('privacidad', document.getElementById('privacidad').value); // Agregar el valor del select

    // Añadir imágenes y videos
    imagenes.forEach((file) => formData.append('imagenes[]', file));
    videos.forEach((file) => formData.append('videos[]', file));

    fetch('actualizar_publicacion.php', {
        method: 'POST',
        body: formData,
    })
        .then((response) => response.text())
        .then((data) => alert(data))
        .catch((error) => console.error('Error:', error));
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
