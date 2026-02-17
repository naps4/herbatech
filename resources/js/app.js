require('./bootstrap');
require('admin-lte');

$(document).ready(function() {
    // Enable tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Enable popovers
    $('[data-toggle="popover"]').popover();
    
    // Sidebar search
    $('[data-widget="sidebar-search"]').SidebarSearch({
        arrowSign: '<i class="fas fa-chevron-right"></i>'
    });
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
    
    // Check for unread notifications every minute
    function checkNotifications() {
        $.get('/api/notifications/unread-count', function(data) {
            const count = data.count || 0;
            $('#notification-count').text(count);
            
            if (count > 0) {
                $('#notification-count').removeClass('badge-warning').addClass('badge-danger');
            } else {
                $('#notification-count').removeClass('badge-danger').addClass('badge-warning');
            }
        });
    }
    
    // Initial check
    checkNotifications();
    
    // Check every minute
    setInterval(checkNotifications, 60000);
    
    // Confirm before deleting
    $('form[data-confirm]').submit(function(e) {
        if (!confirm($(this).data('confirm'))) {
            e.preventDefault();
        }
    });
    
    // Auto-submit filter forms on select change
    $('.auto-submit').change(function() {
        $(this).closest('form').submit();
    });
    
    // Format dates
    $('.format-date').each(function() {
        const date = new Date($(this).text());
        $(this).text(date.toLocaleDateString('id-ID', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }));
    });
    
    // Auto-refresh page if specified
    const refreshInterval = $('meta[name="refresh-interval"]').attr('content');
    if (refreshInterval) {
        setInterval(function() {
            location.reload();
        }, parseInt(refreshInterval) * 1000);
    }
});