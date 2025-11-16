document.getElementById('mediaFile').addEventListener('change', function(event) {
    const previewContainer = document.getElementById('mediaPreview');
    previewContainer.innerHTML = ''; // Limpiar previsualizaciones anteriores
    const files = event.target.files;

    if (files.length > 0) {
        for (const file of files) {
            const fileType = file.type;
            let previewElement;

            if (fileType.startsWith('image/')) {
                previewElement = document.createElement('img');
                previewElement.src = URL.createObjectURL(file);
            } else if (fileType.startsWith('video/')) {
                previewElement = document.createElement('video');
                previewElement.src = URL.createObjectURL(file);
                previewElement.controls = true;
            } else {
                previewElement = document.createElement('p');
                previewElement.textContent = `Archivo: ${file.name}`;
            }

            if (previewElement) {
                previewContainer.appendChild(previewElement);
            }
        }
    }
});