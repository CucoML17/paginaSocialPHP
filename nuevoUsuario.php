<?php
// Ruta del archivo donde se guardan los usuarios
$archivo = 'php/usuarios.txt';

// Leer el archivo y procesar cada línea para extraer los datos de los usuarios
$usuarios = [];
if (file_exists($archivo)) {
    $lineas = file($archivo); // Leer las líneas del archivo
    foreach ($lineas as $linea) {
        $datos = explode(' | ', trim($linea));
        if (count($datos) > 1) { // Validar que la línea contiene datos válidos
            $usuarios[] = [
                'id' => $datos[0],
                'nombre' => $datos[1],
                'usuario' => $datos[2],
                'correo' => $datos[3],
                'sexo' => $datos[4],
                'fechaNacimiento' => $datos[5],
                'imagen' => $datos[6],
                'contra' => $datos[7]
            ];
        }
    }
}

// Convertir el arreglo de usuarios a formato JSON para usarlo en JavaScript
$usuariosJson = json_encode($usuarios);

// Imprimir los usuarios recuperados (solo para depuración)

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nuevo Usuario</title>
    <link rel="stylesheet" href="css/estiloNuevoUsuario.css">
</head>
<body>
    <div class="login-container">
        <div class="toast" id="toast"></div>


        <form class="login-box" id="formRegistro" action="php/guardarUsuario.php" method="POST" enctype="multipart/form-data">
            <!-- Imagen circular -->
            <img src="img/red_logo.png" alt="Nuevo Usuario" class="profile-img">
            <br>
            
            <!-- Nombre -->
             <div class="verti">
                <div class="tam50">
                    <div class="input-group">
                        <label for="txtNombre">Nombre:</label>
                        <input id="txtNombre" name="txtNombre" type="text" placeholder="Nombre Completo" class="login-input" required>
                    </div>
                    
                    <!-- Usuario -->
                    <div class="input-group">
                        <label for="txtUsuario">Usuario:</label>
                        <input id="txtUsuario" name="txtUsuario" type="text" placeholder="Usuario" class="login-input" required>
                    </div>
                    
                    <!-- Correo -->
                    <div class="input-group">
                        <label for="txtCorreo">Correo:</label>
                        <input id="txtCorreo" name="txtCorreo" type="email" placeholder="Correo Electrónico" class="login-input" required>
                    </div>
                 </div>

                 <div class="input-group">
                    <label for="txtImg">Foto de perfil:</label>
                    <input id="txtImg" name="txtImg" type="file" accept="image/*" class="login-input" required>
                    <!-- Contenedor para mostrar la imagen seleccionada -->
                    <div id="imgDemo" style="margin-top: 10px;"></div>
                </div>
                
             </div>


            
            <!-- Contraseña -->
            <div class="input-group">
                <label for="txtContra">Contraseña:</label>
                <input id="txtContra" name="txtContra" type="password" placeholder="Contraseña" class="login-input" required>
            </div>
            
            <!-- Confirmar Contraseña -->
            <div class="input-group">
                <label for="txtReContra">Confirmar Contraseña:</label>
                <input id="txtReContra" name="txtReContra" type="password" placeholder="Confirme Contraseña" class="login-input" required>
            </div>
            
            <!-- Sexo -->
            <div class="input-group sop">
                <div class="lblSe"><label>Sexo:</label></div>
                <label class="lblSex"><input type="radio" name="sexo" value="Masculino" required> Masculino</label>
                <label class="lblSex"><input type="radio" name="sexo" value="Femenino"> Femenino</label>
                <label class="lblSex"><input type="radio" name="sexo" value="No Binario"> No Binario</label>
            </div>
            
            <br>
            <!-- Fecha de Nacimiento -->
             <div class="tam40no">
                <div class="input-group">
                <label  for="fechaNacimiento" style="color: #6b0000;">Fecha de Nacimiento:</label>
                    <input id="fechaNacimiento" name="fechaNacimiento" type="date" class="login-input" required>
                </div>

        </div>
            
            <!-- Botón de registro -->
            <button type="submit" class="login-button">Registrar Usuario</button>
        </form>
        
    </div>

    <script>
        // Selección del input y el contenedor de la imagen
        const inputImg = document.getElementById('txtImg');
        const imgDemo = document.getElementById('imgDemo');
    
        // Evento cuando el usuario selecciona una imagen
        inputImg.addEventListener('change', function (event) {
            const file = event.target.files[0]; // Obtiene el archivo seleccionado
            if (file) {
                const reader = new FileReader();
                // Cuando la imagen se cargue
                reader.onload = function (e) {
                    imgDemo.innerHTML = `<img src="${e.target.result}" alt="Vista previa" style="width: 100px; height: 100px; border-radius: 50%;">`;
                };
                reader.readAsDataURL(file); // Lee el contenido del archivo como una URL
            } else {
                imgDemo.innerHTML = ""; // Limpia el contenedor si no hay archivo
            }
        });
    </script>


<script>
    // Asumiendo que los usuarios son pasados desde PHP como una variable JSON
    const usuarios = <?php echo $usuariosJson; ?>;

    // Validaciones
    const form = document.getElementById('formRegistro');
    const toast = document.getElementById('toast');

    // Mostrar un toast
    function showToast(message, element = null) {
        toast.textContent = message;
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 3000);
        if (element) element.focus();
    }

    // Verificar si el usuario ya existe
    function usuarioExiste(usuario) {
        return usuarios.some(function(usr) {
            return usr.usuario === usuario; // Buscar por el campo 'usuario'
        });
    }

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const nombre = document.getElementById('txtNombre').value.trim();
        const usuario = document.getElementById('txtUsuario').value.trim(); // Suponiendo que el campo del nombre de usuario es 'txtUsuario'
        const contra = document.getElementById('txtContra').value.trim();
        const reContra = document.getElementById('txtReContra').value.trim();
        const fechaNacimiento = document.getElementById('fechaNacimiento').value;

        // Validar que el nombre no tenga números
        if (!/^[a-zA-Z\s]+$/.test(nombre)) {
            showToast('El nombre no debe contener números.', document.getElementById('txtNombre'));
            return;
        }

        // Verificar si el nombre de usuario ya existe
        if (usuarioExiste(usuario)) {
            showToast('El nombre de usuario ya existe. Elige otro.', document.getElementById('txtUsuario'));
            return;
        }

        // Validar que las contraseñas coincidan
        if (contra !== reContra) {
            showToast('Las contraseñas no coinciden.', document.getElementById('txtReContra'));
            return;
        }

        // Validar fecha de nacimiento
        const fechaActual = new Date();
        let fechaIngresada = new Date(fechaNacimiento);

        // Validar que la fecha ingresada sea válida
        if (isNaN(fechaIngresada.getTime())) {
            showToast('La fecha ingresada no es válida.', document.getElementById('fechaNacimiento'));
            return;
        }

        let edad = fechaActual.getFullYear() - fechaIngresada.getFullYear();
        const mes = fechaActual.getMonth() - fechaIngresada.getMonth();

        if (mes < 0 || (mes === 0 && fechaActual.getDate() < fechaIngresada.getDate())) {
            edad--;
        }

        if (edad < 15) {
            showToast('Debes tener al menos 15 años para registrarte.', document.getElementById('fechaNacimiento'));
            return;
        }

        // Validar si la fecha ingresada es en el futuro
        if (fechaIngresada > fechaActual) {
            showToast('La fecha de nacimiento no puede ser en el futuro.', document.getElementById('fechaNacimiento'));
            return;
        }

        // Si pasa todas las validaciones, enviar el formulario
        form.submit();
    });
</script>

    
</body>
</html>


