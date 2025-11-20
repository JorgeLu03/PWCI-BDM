document.addEventListener('DOMContentLoaded', function () {
    const selectImageBtn = document.getElementById('selectImageBtn');
    const profilePhotoInput = document.getElementById('profilePhoto');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const fechaNacimientoInput = document.getElementById('fecha_nacimiento');
    const registroForm = document.querySelector('.form');
    const emailInput = document.getElementById('correo');
    const telefonoInput = document.getElementById('telefono');
    const nombreInput = document.getElementById('nombre');
    const contrasenaInput = document.getElementById('contrasena');
    const paisSelect = document.getElementById('pais');
    const nacionalidadSelect = document.getElementById('nacionalidad');

    // REST Countries API
    async function cargarPaises() {
        try {

            const response = await fetch('https://restcountries.com/v3.1/all');
            

            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const paises = await response.json();

            
            if (!Array.isArray(paises) || paises.length === 0) {
                throw new Error('La respuesta no contiene países válidos');
            }
            
            paises.sort((a, b) => {
                const nombreA = a.translations?.spa?.common || a.name.common;
                const nombreB = b.translations?.spa?.common || b.name.common;
                return nombreA.localeCompare(nombreB);
            });
            
            paisSelect.innerHTML = '<option value="">Selecciona tu país de nacimiento</option>';
            nacionalidadSelect.innerHTML = '<option value="">Selecciona tu nacionalidad</option>';
            
            // Llenar con los países
            paises.forEach(pais => {
                const nombreEspanol = pais.translations?.spa?.common || pais.name.common;
                
                // país
                const optionPais = document.createElement('option');
                optionPais.value = nombreEspanol;
                optionPais.textContent = nombreEspanol;
                paisSelect.appendChild(optionPais);
                
                // nacionalidad
                const optionNac = document.createElement('option');
                optionNac.value = nombreEspanol;
                optionNac.textContent = nombreEspanol;
                nacionalidadSelect.appendChild(optionNac);
            });
            

        } catch (error) {

            
            const paisesFallback = [
                'Afganistán', 'Albania', 'Alemania', 'Andorra', 'Angola', 'Antigua y Barbuda',
                'Arabia Saudita', 'Argelia', 'Argentina', 'Armenia', 'Australia', 'Austria',
                'Azerbaiyán', 'Bahamas', 'Bangladés', 'Barbados', 'Baréin', 'Bélgica', 'Belice',
                'Benín', 'Bielorrusia', 'Birmania', 'Bolivia', 'Bosnia y Herzegovina', 'Botsuana',
                'Brasil', 'Brunéi', 'Bulgaria', 'Burkina Faso', 'Burundi', 'Bután', 'Cabo Verde',
                'Camboya', 'Camerún', 'Canadá', 'Catar', 'Chad', 'Chile', 'China', 'Chipre',
                'Colombia', 'Comoras', 'Congo', 'Corea del Norte', 'Corea del Sur', 'Costa de Marfil',
                'Costa Rica', 'Croacia', 'Cuba', 'Dinamarca', 'Dominica', 'Ecuador', 'Egipto',
                'El Salvador', 'Emiratos Árabes Unidos', 'Eritrea', 'Eslovaquia', 'Eslovenia',
                'España', 'Estados Unidos', 'Estonia', 'Etiopía', 'Filipinas', 'Finlandia', 'Fiyi',
                'Francia', 'Gabón', 'Gambia', 'Georgia', 'Ghana', 'Granada', 'Grecia', 'Guatemala',
                'Guinea', 'Guinea Ecuatorial', 'Guinea-Bisáu', 'Guyana', 'Haití', 'Honduras', 'Hungría',
                'India', 'Indonesia', 'Irak', 'Irán', 'Irlanda', 'Islandia', 'Israel', 'Italia',
                'Jamaica', 'Japón', 'Jordania', 'Kazajistán', 'Kenia', 'Kirguistán', 'Kiribati',
                'Kosovo', 'Kuwait', 'Laos', 'Lesoto', 'Letonia', 'Líbano', 'Liberia', 'Libia',
                'Liechtenstein', 'Lituania', 'Luxemburgo', 'Macedonia del Norte', 'Madagascar',
                'Malasia', 'Malaui', 'Maldivas', 'Malí', 'Malta', 'Marruecos', 'Mauricio', 'Mauritania',
                'México', 'Micronesia', 'Moldavia', 'Mónaco', 'Mongolia', 'Montenegro', 'Mozambique',
                'Namibia', 'Nauru', 'Nepal', 'Nicaragua', 'Níger', 'Nigeria', 'Noruega', 'Nueva Zelanda',
                'Omán', 'Países Bajos', 'Pakistán', 'Palaos', 'Panamá', 'Papúa Nueva Guinea', 'Paraguay',
                'Perú', 'Polonia', 'Portugal', 'Puerto Rico', 'Reino Unido', 'República Centroafricana',
                'República Checa', 'República Democrática del Congo', 'República Dominicana', 'Ruanda',
                'Rumania', 'Rusia', 'Samoa', 'San Cristóbal y Nieves', 'San Marino', 'San Vicente y las Granadinas',
                'Santa Lucía', 'Santo Tomé y Príncipe', 'Senegal', 'Serbia', 'Seychelles', 'Sierra Leona',
                'Singapur', 'Siria', 'Somalia', 'Sri Lanka', 'Suazilandia', 'Sudáfrica', 'Sudán',
                'Sudán del Sur', 'Suecia', 'Suiza', 'Surinam', 'Tailandia', 'Tanzania', 'Tayikistán',
                'Timor Oriental', 'Togo', 'Tonga', 'Trinidad y Tobago', 'Túnez', 'Turkmenistán', 'Turquía',
                'Tuvalu', 'Ucrania', 'Uganda', 'Uruguay', 'Uzbekistán', 'Vanuatu', 'Vaticano', 'Venezuela',
                'Vietnam', 'Yemen', 'Yibuti', 'Zambia', 'Zimbabue'
            ];
            
            paisSelect.innerHTML = '<option value="">Selecciona tu país de nacimiento</option>';
            nacionalidadSelect.innerHTML = '<option value="">Selecciona tu nacionalidad</option>';
            
            paisesFallback.forEach(pais => {
                const optionPais = document.createElement('option');
                optionPais.value = pais;
                optionPais.textContent = pais;
                paisSelect.appendChild(optionPais);
                
                const optionNac = document.createElement('option');
                optionNac.value = pais;
                optionNac.textContent = pais;
                nacionalidadSelect.appendChild(optionNac);
            });
        }
    }
    
    // Cargar países
    cargarPaises();

    // Validación edad 
    if (fechaNacimientoInput && registroForm) {
        registroForm.addEventListener('submit', function(event) {
            const nombreValue = nombreInput.value.trim();
            if (nombreValue.length < 3 || nombreValue.length > 100) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Nombre Inválido',
                    text: 'El nombre debe tener entre 3 y 100 caracteres.',
                    confirmButtonColor: '#d33'
                });
                nombreInput.focus();
                return false;
            }
            
            const nombrePattern = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
            if (!nombrePattern.test(nombreValue)) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Nombre Inválido',
                    text: 'El nombre solo puede contener letras y espacios.',
                    confirmButtonColor: '#d33'
                });
                nombreInput.focus();
                return false;
            }

            const fechaNac = new Date(fechaNacimientoInput.value);
            const hoy = new Date();
            let edad = hoy.getFullYear() - fechaNac.getFullYear();
            const mes = hoy.getMonth() - fechaNac.getMonth();
            
            if (mes < 0 || (mes === 0 && hoy.getDate() < fechaNac.getDate())) {
                edad--;
            }
            
            if (edad < 12) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Edad No Permitida',
                    text: 'Debes tener al menos 12 años para registrarte en GolNet.',
                    confirmButtonColor: '#d33'
                });
                fechaNacimientoInput.focus();
                return false;
            }

            const emailValue = emailInput.value.trim();
            const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            if (!emailPattern.test(emailValue)) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Email Inválido',
                    text: 'Por favor ingresa un correo electrónico válido.',
                    confirmButtonColor: '#d33'
                });
                emailInput.focus();
                return false;
            }

            const telefonoValue = telefonoInput.value.trim();
            if (!/^[0-9]{10,15}$/.test(telefonoValue)) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Teléfono Inválido',
                    text: 'El teléfono debe contener entre 10 y 15 dígitos numéricos.',
                    confirmButtonColor: '#d33'
                });
                telefonoInput.focus();
                return false;
            }

            const contrasenaValue = contrasenaInput.value;
            if (contrasenaValue.length < 8) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Contraseña Débil',
                    text: 'La contraseña debe tener al menos 8 caracteres.',
                    confirmButtonColor: '#d33'
                });
                contrasenaInput.focus();
                return false;
            }
            const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/;
            if (!passwordPattern.test(contrasenaValue)) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Contraseña Débil',
                    text: 'La contraseña debe contener: mayúscula, minúscula, número y carácter especial.',
                    confirmButtonColor: '#d33'
                });
                contrasenaInput.focus();
                return false;
            }

            const fotoFile = profilePhotoInput.files[0];
            if (!fotoFile) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Foto Requerida',
                    text: 'Por favor selecciona una foto de perfil.',
                    confirmButtonColor: '#d33'
                });
                return false;
            }
            const validImageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validImageTypes.includes(fotoFile.type)) {
                event.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Formato Inválido',
                    text: 'Solo se permiten imágenes (JPEG, PNG, GIF, WebP).',
                    confirmButtonColor: '#d33'
                });
                return false;
            }
        });
        
        // Edad mínima
        const maxDate = new Date();
        maxDate.setFullYear(maxDate.getFullYear() - 12);
        const maxDateString = maxDate.toISOString().split('T')[0];
        fechaNacimientoInput.setAttribute('max', maxDateString);
    }

    if (selectImageBtn && profilePhotoInput && imagePreviewContainer) {
        selectImageBtn.addEventListener('click', function () {
            profilePhotoInput.click();
        });

        profilePhotoInput.addEventListener('change', function (event) {
            imagePreviewContainer.innerHTML = '';

            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    // Contenedor imagen
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'preview-wrapper';
                    previewDiv.style.width = '100%';
                    previewDiv.style.display = 'flex';
                    previewDiv.style.justifyContent = 'center';
                    previewDiv.style.alignItems = 'center';

                    // Crear y configurar  imagen
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    
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

                    previewDiv.appendChild(img);
                    imagePreviewContainer.appendChild(previewDiv);
                };
                reader.readAsDataURL(file);
            }
        });
    }
});