function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');

    sidebar.classList.toggle('sidebar-expanded');
    mainContent.classList.toggle('main-content-expanded'); // Adjust the main content margin
}