document.addEventListener('DOMContentLoaded', function () {
    function toggleModal(modalID) {
        const modal = document.getElementById(modalID);
        if (!modal) return;
        modal.toggleAttribute('hidden')
    }

    // Modales
    const modalButtons = document.querySelectorAll('[data-modal-id]');

    modalButtons.forEach(button => {
        button.addEventListener('click', function () {
            const modalID = button.getAttribute('data-modal-id');
            toggleModal(modalID);
        });
    });
});