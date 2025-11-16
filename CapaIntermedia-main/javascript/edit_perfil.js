document.addEventListener('DOMContentLoaded', function() {
    const profilePhotoInput = document.getElementById('profilePhoto');
    const imagePreview = document.getElementById('imagePreview');
    const paisSelect = document.getElementById('pais_input');
    const nacionalidadSelect = document.getElementById('nacionalidad_input');

    // Cargar países desde REST Countries API
    async function cargarPaisesEditar() {
    try {
        console.log('Iniciando carga de países desde API...');
        const response = await fetch('https://restcountries.com/v3.1/all');
        
        console.log('Respuesta recibida, status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const paises = await response.json();
        console.log('Datos parseados, total países:', paises.length);
        
        if (!Array.isArray(paises) || paises.length === 0) {
            throw new Error('La respuesta no contiene países válidos');
        }
        
        // Ordenar países alfabéticamente por nombre en español
        paises.sort((a, b) => {
            const nombreA = a.translations?.spa?.common || a.name.common;
            const nombreB = b.translations?.spa?.common || b.name.common;
            return nombreA.localeCompare(nombreB);
        });
        
        // Obtener valores actuales de los selects
        const paisActual = paisSelect.dataset.current;
        const nacionalidadActual = nacionalidadSelect.dataset.current;
        
        // Limpiar opciones anteriores
        paisSelect.innerHTML = '<option value="">Selecciona tu país de nacimiento</option>';
        nacionalidadSelect.innerHTML = '<option value="">Selecciona tu nacionalidad</option>';
        
        // Llenar ambos selects con los países
        paises.forEach(pais => {
            const nombreEspanol = pais.translations?.spa?.common || pais.name.common;
            
            // Agregar al select de país
            const optionPais = document.createElement('option');
            optionPais.value = nombreEspanol;
            optionPais.textContent = nombreEspanol;
            if (nombreEspanol === paisActual) {
                optionPais.selected = true;
            }
            paisSelect.appendChild(optionPais);
            
            // Agregar al select de nacionalidad
            const optionNac = document.createElement('option');
            optionNac.value = nombreEspanol;
            optionNac.textContent = nombreEspanol;
            if (nombreEspanol === nacionalidadActual) {
                optionNac.selected = true;
            }
            nacionalidadSelect.appendChild(optionNac);
        });
        
        console.log('✅ Países cargados exitosamente desde API para edición:', paises.length);
    } catch (error) {
        console.error('❌ Error al cargar países desde API:', error);
        console.warn('🔄 Usando lista de países completa como fallback...');
        
        // Fallback con lista completa de países
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
        
        const paisActual = paisSelect.dataset.current;
        const nacionalidadActual = nacionalidadSelect.dataset.current;
        
        paisSelect.innerHTML = '<option value="">Selecciona tu país de nacimiento</option>';
        nacionalidadSelect.innerHTML = '<option value="">Selecciona tu nacionalidad</option>';
        
        paisesFallback.forEach(pais => {
            const optionPais = document.createElement('option');
            optionPais.value = pais;
            optionPais.textContent = pais;
            if (pais === paisActual) {
                optionPais.selected = true;
            }
            paisSelect.appendChild(optionPais);
            
            const optionNac = document.createElement('option');
            optionNac.value = pais;
            optionNac.textContent = pais;
            if (pais === nacionalidadActual) {
                optionNac.selected = true;
            }
            nacionalidadSelect.appendChild(optionNac);
        });
        
        console.log('✅ Países del fallback cargados:', paisesFallback.length);
    }
}

    // Cargar países al iniciar
    if (paisSelect && nacionalidadSelect) {
        cargarPaisesEditar();
    }

    if (profilePhotoInput && imagePreview) {
        profilePhotoInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    }
});