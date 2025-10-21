const profilePhotoInput = document.getElementById('profilePhoto');
const imagePreview = document.getElementById('imagePreview');

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