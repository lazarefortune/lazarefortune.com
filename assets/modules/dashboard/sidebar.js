// function initSidebar() {
//     const sidebar = document.getElementById('sidebar');
//     const sidebarBackdrop = document.getElementById('sidebar-backdrop');
//     const sidebarButton = document.getElementById('sidebar-button');
//
//     function toggleSidebar() {
//         sidebar.classList.toggle('-translate-x-full');
//         sidebar.classList.toggle('translate-x-0');
//         sidebarBackdrop.classList.toggle('hidden');
//         document.body.classList.toggle('overflow-hidden');
//     }
//
//     if (sidebarButton) {
//         sidebarButton.addEventListener('click', toggleSidebar);
//     }
//
//     if (sidebarBackdrop) {
//         sidebarBackdrop.addEventListener('click', toggleSidebar);
//     }
// }
//
// document.addEventListener('DOMContentLoaded', initSidebar);


// main.js

function initSidebar() {
    const sidebar = document.getElementById('sidebar');
    const sidebarBackdrop = document.getElementById('sidebar-backdrop');
    const sidebarButton = document.getElementById('sidebar-button');

    function toggleSidebar() {
        sidebar.classList.toggle('-translate-x-full');
        sidebar.classList.toggle('translate-x-0');
        sidebarBackdrop.classList.toggle('hidden');
        document.body.classList.toggle('overflow-hidden');
    }

    if (sidebarButton) {
        sidebarButton.addEventListener('click', toggleSidebar);
    }

    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', toggleSidebar);
    }
}

document.addEventListener('DOMContentLoaded', initSidebar);
