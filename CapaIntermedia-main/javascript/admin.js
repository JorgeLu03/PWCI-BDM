const btnPublis = document.getElementById('btn-show-publis');
const btnComments = document.getElementById('btn-show-comments');
const publisSection = document.getElementById('admin-publis-section');
const commentsSection = document.getElementById('admin-comments-section');

btnPublis.addEventListener('click', () => {
    publisSection.style.display = 'block';
    commentsSection.style.display = 'none';
    btnPublis.classList.add('active');
    btnComments.classList.remove('active');
});

btnComments.addEventListener('click', () => {
    publisSection.style.display = 'none';
    commentsSection.style.display = 'block';
    btnComments.classList.add('active');
    btnPublis.classList.remove('active');
});