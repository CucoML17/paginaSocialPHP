<?php
// Empezar la sesión para gestionar la autenticación
session_start();

// Verifica si hay un error pasado a través de GET
$error = isset($_GET['error']) ? $_GET['error'] : 'no_error'; // Si no hay error, asignamos 'no_error'

// Recuperar los datos ingresados si están en la sesión
$usuarioIngresado = isset($_SESSION['usuario_tmp']) ? $_SESSION['usuario_tmp'] : '';
$contraseñaIngresada = isset($_SESSION['contraseña_tmp']) ? $_SESSION['contraseña_tmp'] : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="css/estiloIndex.css">
    <link rel="stylesheet" href="css/modal1.css">
</head>
<body>
    <div class="login-container">
        <form class="login-box" id="formInicio" action="php/login.php" method="POST">

            <!-- Imagen circular -->
            <img src="img/red_logo.png" alt="Usuario" class="profile-img">
            <br>
            <!-- Inputs -->
            <div class="tam60">
                <label for="txtUsuario">Ingrese su usuario:</label>
                <input id="txtUsuario" name="txtUsuario" type="text" placeholder="Usuario"
                       class="login-input" value="<?php echo htmlspecialchars($usuarioIngresado); ?>" required>
            </div>

            <div class="tam60">
                <label for="txtContra">Ingrese su contraseña:</label>
                <input id="txtContra" name="txtContra" type="password" placeholder="Contraseña"
                       class="login-input" value="<?php echo htmlspecialchars($contraseñaIngresada); ?>" required>
            </div>

            <!-- Link para registro -->
            <a href="nuevoUsuario.php" class="register-link">Registrar nuevo usuario</a>

            <!-- Botón de iniciar sesión -->
            <button type="submit" class="login-button">Iniciar sesión</button>
        </form>
    </div>

    <!-- Modal de error -->
    <div id="modalError" class="modal <?php echo $error !== 'no_error' ? 'active' : ''; ?>"> <!-- Solo activa si hay un error -->
        <div class="modal-content">
            <p id="modalMessage">
                <?php
                if ($error == 'usuario') {
                    echo 'El usuario no existe.';
                } elseif ($error == 'contraseña') {
                    echo 'La contraseña es incorrecta.';
                }
                ?>
            </p>
            <div class="modal-footer">
                <button id="closeModal" class="modal-button">Aceptar</button>
            </div>
        </div>
    </div>

    <script>
        // Cerrar el modal
        document.getElementById('closeModal').addEventListener('click', function() {
            document.getElementById('modalError').style.display = 'none';
        });

        // Enfocar el campo con error
        const error = "<?php echo $error; ?>";
        if (error === 'usuario') {
            document.getElementById('txtUsuario').focus();
        } else if (error === 'contraseña') {
            document.getElementById('txtContra').focus();
        }
    </script>
</body>
</html>
