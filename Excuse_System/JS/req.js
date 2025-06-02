
function openTab(tabId) {
    $('.container').removeClass('active');
    $('#' + tabId).addClass('active');

    $('.tab-link').removeClass('active');
    $('[onclick="openTab(\'' + tabId + '\')"]').addClass('active');
}

const toggleSidebarBtn = document.getElementById('toggleSidebar');
const sidebar = document.getElementById('sidebar');

toggleSidebarBtn.addEventListener('click', function() {
    sidebar.classList.toggle('visible');
    if (sidebar.classList.contains('visible')) {
        toggleSidebarBtn.innerHTML = '<i class="fas fa-bars"></i> إخفاء الشريط الجانبي';
    } else {
        toggleSidebarBtn.innerHTML = '<i class="fas fa-bars"></i> إظهار الشريط الجانبي';
    }
});
