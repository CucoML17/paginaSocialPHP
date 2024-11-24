// Array para guardar los archivos seleccionados
let selectedFiles_coment = [];

// Función para abrir el explorador de archivos
function openFileDialog_coment(type) {
    const fileInput = document.getElementById('fileInput_coment');
    fileInput.accept = type === 'image' ? 'image/*' : 'video/*'; // Filtrar por tipo de archivo
    fileInput.click(); // Abrir el explorador de archivos
}

// Función para manejar el cambio en el input de archivos
document.getElementById('fileInput_coment').addEventListener('change', function(e) {
    const files = e.target.files;
    if (files.length + selectedFiles_coment.length > 4) {
        alert("Solo puedes cargar un máximo de 4 archivos.");
        return;
    }

    // Previsualizar los archivos seleccionados
    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const reader = new FileReader();
        reader.onload = function(event) {
            const previewItem = document.createElement('div');
            previewItem.classList.add('preview-item_coment');

            // Crear una imagen o video según el tipo de archivo
            const media = file.type.startsWith('image') ? document.createElement('img') : document.createElement('video');
            media.src = event.target.result;
            media.setAttribute('alt', file.name);

            // Botón para eliminar el archivo
            const removeBtn = document.createElement('button');
            removeBtn.classList.add('remove-btn_coment');
            removeBtn.innerHTML = 'X';
            removeBtn.onclick = function() {
                // Eliminar el archivo de la lista
                selectedFiles_coment = selectedFiles_coment.filter(f => f !== file);
                previewItem.remove(); // Eliminar la previsualización
            };

            previewItem.appendChild(media);
            previewItem.appendChild(removeBtn);
            document.getElementById('previews_coment').appendChild(previewItem);

            // Guardar el archivo completo en el array
            selectedFiles_coment.push(file);
        };
        reader.readAsDataURL(file); // Leer el archivo
    }
});

// Evento para el botón de Foto
document.getElementById('photoBtn_coment').addEventListener('click', function() {
    openFileDialog_coment('image');
});

// Evento para el botón de Vídeo
document.getElementById('videoBtn_coment').addEventListener('click', function() {
    openFileDialog_coment('video');
});

// Mostrar un toast
function showToast(message, element = null) {
    const toast = document.createElement('div');
    toast.classList.add('toast');
    toast.textContent = message;
    document.body.appendChild(toast);

    toast.style.display = 'block';
    setTimeout(() => {
        toast.style.display = 'none';
        toast.remove(); // Eliminar el toast del DOM después de que desaparezca
    }, 3000);

    if (element) element.focus();
}

function cerrarModal(modal) {
    modal.style.display = 'none';
}
const modalFot2 = document.getElementById('fotoModal_coment');

// Validación al hacer click en "Publicar"
document.getElementById('publishBtn_coment').addEventListener('click', function() {
    const textarea = document.querySelector('.modal-textarea_coment');
    const commentText = textarea.value.trim();
    
    // Obtener el valor seleccionado del select
    const selectElement = document.querySelector('.modal-select_coment');
    const selectedOption = selectElement.value; // Obtiene el valor seleccionado

    // Si no hay texto ni archivos, mostrar un toast
    if (commentText === '' && selectedFiles_coment.length === 0) {
        showToast('No deje el comentario vacío o suba un archivo multimedia', textarea);
        return; // Evitar que continúe con el envío
    }

    // Crear un formulario para enviar los datos al servidor
    const formData = new FormData();
    formData.append('comentario', commentText); // Añadir el comentario
    formData.append('selectOption', selectedOption); // Añadir el valor seleccionado

    // Añadir los archivos seleccionados al FormData
    for (let i = 0; i < selectedFiles_coment.length; i++) {
        formData.append('files[]', selectedFiles_coment[i]);
    }

    // Obtener la fecha del sistema
    const currentDate = new Date();
    const formattedDate = currentDate.toLocaleString(); // Esto devuelve la fecha y hora local
    formData.append('fecha', formattedDate);

    fetch('inicioRed.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json(); // Leer la respuesta JSON
    })
    .then(data => {
        if (data.status === 'success') {
            showToast('¡Publicación realizada exitosamente!');
            window.location.reload(); // Ahora recarga la página
        } else {
            showToast('Error en la publicación. Intente nuevamente.');
        }
    })
    .catch(error => {
        console.error('Error en el proceso:', error);
        showToast('Hubo un problema al publicar.');
    });
});