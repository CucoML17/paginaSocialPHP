<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Recuperar el ID del usuario actual
$idUsuarioActual = $_SESSION['id'];

// Cargar todos los usuarios desde el archivo
$archivoUsuarios = 'php/usuarios.txt';
$usuarios = file($archivoUsuarios, FILE_IGNORE_NEW_LINES);

// Eliminar la cabecera (primera línea del archivo)
array_shift($usuarios);

// Crear un array para almacenar los usuarios, excluyendo al usuario actual
$usuariosData = [];
foreach ($usuarios as $usuario) {
    $campos = explode(" | ", $usuario);
    if ($campos[0] != $idUsuarioActual) { // Excluir el usuario actual
        $usuariosData[] = [
            'id' => $campos[0],
            'nombre' => $campos[1],
            'usuario' => $campos[2],
            'correo' => $campos[3],
            'sexo' => $campos[4],
            'fechaNacimiento' => $campos[5],
            'imagen' => $campos[6],
        ];
    }
}

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
$imagenPerfil = obtenerImagenUsuario($idUsuarioActual);



function verificarSolicitudEnviada($idEnvia, $idRecibe) {
    $archivo = 'php/solPendientes.txt'; // Ruta al archivo de solicitudes
    if (!file_exists($archivo)) {
        return false; // Si no existe el archivo, no hay solicitudes enviadas
    }

    $solicitudes = file($archivo, FILE_IGNORE_NEW_LINES); // Lee todas las líneas del archivo

    foreach ($solicitudes as $solicitud) {
        $campos = explode(" | ", $solicitud);

        // Verificar si los IDs coinciden en cualquier orden
        if (($campos[0] == $idEnvia && $campos[1] == $idRecibe) || ($campos[0] == $idRecibe && $campos[1] == $idEnvia)) {
            return true; // Ya existe la solicitud
        }
    }

    return false; // No existe la solicitud
}

function verificarAmistad($id1, $id2) {
    $archivo = 'php/amistades.txt'; // Ruta al archivo de amistades
    $amistades = file($archivo, FILE_IGNORE_NEW_LINES); // Lee todas las líneas del archivo

    foreach ($amistades as $amistad) {
        $campos = explode(" | ", $amistad);

        // Verificar si son amigos (en cualquier orden)
        if (($campos[1] == $id1 && $campos[2] == $id2) || ($campos[1] == $id2 && $campos[2] == $id1)) {
            return true; // Son amigos
        }
    }

    return false; // No son amigos
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

    <link rel="stylesheet" href="css/todosPerfis.css">
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

    </div>




        
        <div class="center-column">
        <div class="filaCartasUsers">
            <?php foreach ($usuariosData as $usuario): ?>
                <div class="carta">
                    <div class="carta-imagen" style="background-image: url('php/<?php echo $usuario['imagen']; ?>');"></div>

                    <div class="carta-info">
                        <h3><?php echo $usuario['nombre']; ?></h3>
                        <div class="botones">
                            <button class="btn-ver-perfil" onclick="window.location.href='verPerfilAjeno.php?idAjeno=<?php echo $usuario['id']; ?>'">Ver perfil</button>

                            <?php
                            // Verificar si ya son amigos
                            if (verificarAmistad($idUsuarioActual, $usuario['id'])):
                            ?>
                                <button class="btn-amigo" disabled>Ya son amigos</button>
                            <?php
                            // Si no son amigos, verificar si ya se envió la solicitud
                            elseif (verificarSolicitudEnviada($idUsuarioActual, $usuario['id'])):
                            ?>
                                <button class="btn-agregar" disabled>Ya enviado</button>
                            <?php else: ?>
                                <button class="btn-agregar" data-id="<?php echo $usuario['id']; ?>">Agregar</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>









    
    <div class="right-column">


    </div>
</div>



<script>
document.addEventListener('DOMContentLoaded', () => {
    const verPerfilBtns = document.querySelectorAll('.btn-ver-perfil');
    const agregarBtns = document.querySelectorAll('.btn-agregar');



    agregarBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const idUsuario = e.target.dataset.id;
            console.log('Agregar usuario con ID:', idUsuario);
            // Aquí puedes implementar la funcionalidad de agregar al usuario, por ejemplo
        });
    });
});

</script>


<!-- En tu archivo HTML/PHP donde se encuentran los botones de agregar -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Capturamos el evento click en el botón "Agregar"
        $('.btn-agregar').click(function() {
            var idRecibe = $(this).data('id');  // ID del usuario al que queremos agregar
            var idEnvia = <?php echo $_SESSION['id']; ?>; // ID del usuario actual (desde PHP)
            
            // Enviar la solicitud AJAX al servidor
            $.ajax({
                url: 'agregarAmigo.php', // El archivo PHP donde manejaremos la solicitud
                type: 'POST',
                data: {
                    idEnvia: idEnvia,
                    idRecibe: idRecibe
                },
                success: function(response) {
                    alert(response);  // Mostrar mensaje de éxito o error
                    location.reload();  // Recargar la página después de agregar la solicitud
                },
                error: function() {
                    alert('Hubo un error al procesar la solicitud.');
                }
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
