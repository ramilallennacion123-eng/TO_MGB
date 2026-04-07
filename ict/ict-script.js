function showPopup(message, type) {
    const popup = document.getElementById('popupMessage');
    const overlay = document.getElementById('popupOverlay');
    const text = document.getElementById('popupText');
    text.textContent = message;
    popup.className = 'popup-message ' + type;
    overlay.style.display = 'block';
    popup.style.display = 'block';
}

function closePopup() {
    document.getElementById('popupMessage').style.display = 'none';
    document.getElementById('popupOverlay').style.display = 'none';
}

document.getElementById('createAccForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    fetch('create-account.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            sessionStorage.setItem('accountMessage', data.message);
            location.reload();
        } else {
            showPopup(data.message, 'error');
        }
    });
});

window.addEventListener('DOMContentLoaded', function() {
    const message = sessionStorage.getItem('accountMessage');
    if(message) {
        showPopup(message, 'success');
        sessionStorage.removeItem('accountMessage');
    }
});
