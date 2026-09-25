// Main application JavaScript for Chege JIRA

$(document).ready(function() {
    // Initialize tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();

    // Set current date in elements with class .current-date
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    $('.current-date').text(now.toLocaleDateString('en-US', options));

    // Sidebar toggle functionality
    $('#sidebarToggle').click(function() {
        $('#sidebar').toggleClass('sidebar-collapsed');
        $('#mainContent').toggleClass('full-width');

        const icon = $(this).find('i');
        if (icon.hasClass('fa-bars')) {
            icon.removeClass('fa-bars').addClass('fa-times');
        } else {
            icon.removeClass('fa-times').addClass('fa-bars');
        }

        const isCollapsed = $('#sidebar').hasClass('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', isCollapsed);
    });

    // Check localStorage for sidebar state
    if (localStorage.getItem('sidebarCollapsed') === 'true') {
        $('#sidebar').addClass('sidebar-collapsed');
        $('#mainContent').addClass('full-width');
        $('#sidebarToggle i').removeClass('fa-bars').addClass('fa-times');
    }

    // Project card click handler
    $(document).on('click', '.project-card', function() {
        const projectName = $(this).find('strong').text();
        const projectId = $(this).data('project-id');

        if (projectId) {
            window.location.href = `/projects/${projectId}`;
        } else {
            console.log(`Project clicked: ${projectName}`);
            showToast(`Opening project: ${projectName}`);
        }
    });

    // Dark/Light theme toggle
    function toggleTheme() {
        const html = $('html');
        const currentTheme = html.attr('data-bs-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        html.attr('data-bs-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        showToast(`Theme changed to ${newTheme} mode`);
    }

    // Check for saved theme preference
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme) {
        $('html').attr('data-bs-theme', savedTheme);
    }

    // Press 't' to toggle theme (only when not typing in input/textarea)
    $(document).keypress(function(e) {
        if ((e.key === 't' || e.key === 'T') && !$(e.target).is('input, textarea, select, [contenteditable]')) {
            toggleTheme();
        }
    });

    // Theme toggle button (if exists)
    $('#themeToggle').click(function() {
        toggleTheme();
    });

    // Toast notification function
    function showToast(message, type = 'info') {
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        $('.toast-container').append(toastHtml);

        const toast = new bootstrap.Toast(document.getElementById(toastId));
        toast.show();

        $(`#${toastId}`).on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    // Create toast container if it doesn't exist
    if ($('.toast-container').length === 0) {
        $('body').append('<div class="toast-container position-fixed bottom-0 end-0 p-3"></div>');
    }

    // Search functionality
    $('#projectSearch').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();

        $('.project-card').each(function() {
            const projectText = $(this).text().toLowerCase();
            if (projectText.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
});