// Function to close alert
function closeAlert(alertId) {
    const alert = document.getElementById(alertId);
    alert.classList.remove('show'); // Remove the show class
    alert.style.display = 'none'; // Hide the alert
}

// Automatically show alerts if they exist
window.onload = function() {
    const successAlert = document.getElementById('successAlert');
    const errorAlert = document.getElementById('errorAlert');

    if (successAlert) {
        successAlert.classList.add('show'); // Show the success alert
        setTimeout(() => {
            closeAlert('successAlert'); // Auto close after 5 seconds
        }, 5000);
    }

    if (errorAlert) {
        errorAlert.classList.add('show'); // Show the error alert
        setTimeout(() => {
            closeAlert('errorAlert'); // Auto close after 5 seconds
        }, 5000);
    }
};