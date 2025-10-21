 // JavaScript para controlar la visibilidad de las barras laterales
        const menuToggle = document.getElementById('menuToggle');
        const leftSidebar = document.getElementById('leftSidebar');
        const rightSidebar = document.getElementById('rightSidebar');
        const toggleLeft = document.getElementById('toggleLeft');
        const toggleRight = document.getElementById('toggleRight');
        
        // Alternar barra izquierda
        menuToggle.addEventListener('click', () => {
            leftSidebar.classList.toggle('active');
            if (rightSidebar.classList.contains('active')) {
                rightSidebar.classList.remove('active');
            }
        });
        
        toggleLeft.addEventListener('click', () => {
            leftSidebar.classList.toggle('active');
            if (rightSidebar.classList.contains('active')) {
                rightSidebar.classList.remove('active');
            }
        });
        
        // Alternar barra derecha
        toggleRight.addEventListener('click', () => {
            rightSidebar.classList.toggle('active');
            if (leftSidebar.classList.contains('active')) {
                leftSidebar.classList.remove('active');
            }
        });
        
        // Cerrar barras al hacer clic fuera de ellas
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                const isLeftSidebar = leftSidebar.contains(e.target);
                const isRightSidebar = rightSidebar.contains(e.target);
                const isMenuToggle = menuToggle.contains(e.target);
                const isToggleLeft = toggleLeft.contains(e.target);
                const isToggleRight = toggleRight.contains(e.target);
                
                if (!isLeftSidebar && !isMenuToggle && !isToggleLeft && leftSidebar.classList.contains('active')) {
                    leftSidebar.classList.remove('active');
                }
                
                if (!isRightSidebar && !isToggleRight && rightSidebar.classList.contains('active')) {
                    rightSidebar.classList.remove('active');
                }
            }
        });

        