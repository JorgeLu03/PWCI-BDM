document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menuToggle');
    const leftSidebar = document.getElementById('leftSidebar');

    if (menuToggle && leftSidebar) {
        menuToggle.addEventListener('click', () => {
            leftSidebar.classList.toggle('active');
        });
    }

    document.addEventListener('click', (e) => {
        if (window.innerWidth <= 768) {
            const isClickInsideSidebar = leftSidebar && leftSidebar.contains(e.target);
            const isClickOnMenuToggle = menuToggle && menuToggle.contains(e.target);

            if (!isClickInsideSidebar && !isClickOnMenuToggle && leftSidebar && leftSidebar.classList.contains('active')) {
                leftSidebar.classList.remove('active');
            }
        }
    });
});