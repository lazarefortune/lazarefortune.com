function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarBackdrop = document.getElementById('sidebarBackdrop');
    const btnSidebar = document.getElementById('btnSidebar');
    const sidebarDropdowns = document.querySelectorAll('.sidebar-dropdown-toggle')

    function toggleSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        sidebar.classList.toggle('translate-x-0');
        sidebarBackdrop.classList.toggle('hidden');
    }

    if (btnSidebar) {
        btnSidebar.addEventListener('click', toggleSidebar);
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', toggleSidebar);
    }

    if (sidebarDropdowns) {
        sidebarDropdowns.forEach(dropdown => {
            if (dropdown.classList.contains('active')) {
                dropdown.querySelector('.sidebar-dropdown-toggle-icon').classList.add('rotate-90');
                dropdown.nextElementSibling.classList.remove('hidden');
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', initSidebar);