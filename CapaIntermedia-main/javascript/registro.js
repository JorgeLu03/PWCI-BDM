document.addEventListener('DOMContentLoaded', function () {
    const selectImageBtn = document.getElementById('selectImageBtn');
    const profilePhotoInput = document.getElementById('profilePhoto');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');

    if (selectImageBtn && profilePhotoInput && imagePreviewContainer) {
        selectImageBtn.addEventListener('click', function () {
            profilePhotoInput.click();
        });

        profilePhotoInput.addEventListener('change', function (event) {
            // Limpiar la vista previa anterior
            imagePreviewContainer.innerHTML = '';

            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    // Crear un div contenedor para la imagen
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'preview-wrapper';
                    previewDiv.style.width = '100%';
                    previewDiv.style.display = 'flex';
                    previewDiv.style.justifyContent = 'center';
                    previewDiv.style.alignItems = 'center';

                    // Crear y configurar la imagen
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
                    // Aplicar estilos directamente a la imagen
                    img.style.cssText = `
                        max-width: 160px;
                        width: auto;
                        height: auto;
                        max-height: 160px;
                        object-fit: contain;
                        border-radius: 10px;
                        border: 3px solid rgba(255,255,255,0.8);
                        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
                        display: block;
                    `;

                    // Agregar la imagen al contenedor y el contenedor al preview
                    previewDiv.appendChild(img);
                    imagePreviewContainer.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            }
        });
    }
});