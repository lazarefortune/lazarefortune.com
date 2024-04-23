document.addEventListener("DOMContentLoaded", function () {
    const alert = document.querySelector(".alert-floating");
    if (alert) {
        setTimeout(function () {
            alert.remove();
        }, 5000);
        alert.addEventListener("click", function () {
            alert.remove();
        });
    }
})