document.addEventListener('DOMContentLoaded', () => {
    // --- Control de barras laterales ---
    const menuToggle = document.getElementById('menuToggle');
    const leftSidebar = document.getElementById('leftSidebar');

    if (menuToggle && leftSidebar) {
        menuToggle.addEventListener('click', () => {
            leftSidebar.classList.toggle('active');
        });
    }

    // Cerrar barras al hacer clic fuera de ellas en modo móvil
    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            const isClickInsideSidebar = leftSidebar && leftSidebar.contains(e.target);
            const isClickOnMenuToggle = menuToggle && menuToggle.contains(e.target);

            if (!isClickInsideSidebar && !isClickOnMenuToggle && leftSidebar && leftSidebar.classList.contains('active')) {
                leftSidebar.classList.remove('active');
            }
        }
    });

    // --- Lógica de filtrado de publicaciones ---
    const filterContainer = document.querySelector('.filter-container');
    if (filterContainer) {
        const filterButtons = filterContainer.querySelectorAll('.filter-btn');
        const cardsGrid = document.querySelector('.cards-grid');

        filterButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Actualizar botón activo
                filterButtons.forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                const sortBy = button.getAttribute('data-sort');
                sortCards(sortBy);
            });
        });

        function sortCards(criteria) {
            const cards = Array.from(cardsGrid.querySelectorAll('.card-link'));

            cards.sort((a, b) => {
                let valA, valB;

                if (criteria === 'chronological') {
                    // Ordenar por fecha (más reciente primero)
                    valA = new Date(a.dataset.date);
                    valB = new Date(b.dataset.date);
                    return valB - valA;
                } else if (criteria === 'likes') {
                    // Ordenar por 'me gusta' (de mayor a menor)
                    valA = parseInt(a.dataset.likes, 10);
                    valB = parseInt(b.dataset.likes, 10);
                    return valB - valA;
                } else if (criteria === 'comments') {
                    // Ordenar por comentarios (de mayor a menor)
                    valA = parseInt(a.dataset.comments, 10);
                    valB = parseInt(b.dataset.comments, 10);
                    return valB - valA;
                }
                return 0;
            });

            // Limpiar el grid y añadir las tarjetas ordenadas
            cardsGrid.innerHTML = '';
            cards.forEach(card => {
                cardsGrid.appendChild(card);
            });
        }

        // Orden inicial por defecto (Recientes)
        if (document.querySelector('.filter-btn.active[data-sort="chronological"]')) {
            sortCards('chronological');
        }
    }
});