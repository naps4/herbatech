$(document).ready(function() {
    // Real-time updates for dashboard
    function updateDashboard() {
        $.get('/api/dashboard/stats', function(data) {
            if (data.stats) {
                // Update stats cards
                $('#total-cpbs').text(data.stats.total);
                $('#active-cpbs').text(data.stats.active);
                $('#overdue-cpbs').text(data.stats.overdue);
                $('#today-cpbs').text(data.stats.today);
            }
            
            // Update CPB table if needed
            if (data.cpbs) {
                // You can implement dynamic table update here
                console.log('CPB data updated');
            }
        }).fail(function() {
            console.log('Failed to update dashboard');
        });
    }
    
    // Initial update
    updateDashboard();
    
    // Update every 30 seconds
    setInterval(updateDashboard, 30000);
    
    // CPB status color coding
    $('.cpb-status').each(function() {
        const status = $(this).data('status');
        const overdue = $(this).data('overdue') === 'true';
        
        if (overdue) {
            $(this).closest('tr').addClass('table-danger');
        } else if (status === 'released') {
            $(this).closest('tr').addClass('table-success');
        }
    });
    
    // Handover modal auto-fill
    $('.handover-btn').click(function() {
        const cpbId = $(this).data('cpb-id');
        const cpbName = $(this).data('cpb-name');
        
        $('#handoverCpbId').val(cpbId);
        $('#handoverCpbName').text(cpbName);
        
        // Load available receivers
        $.get(`/api/cpb/${cpbId}/receivers`, function(data) {
            const select = $('#receiver_id');
            select.empty();
            
            if (data.receivers && data.receivers.length > 0) {
                data.receivers.forEach(function(receiver) {
                    select.append(new Option(
                        `${receiver.name} - ${receiver.department}`,
                        receiver.id
                    ));
                });
            } else {
                select.append(new Option('Tidak ada penerima tersedia', ''));
            }
        });
    });
    
    // Quick handover confirmation
    $('.quick-handover').click(function(e) {
        e.preventDefault();
        
        const url = $(this).attr('href');
        const cpbName = $(this).data('cpb-name');
        
        if (confirm(`Serahkan ${cpbName} ke departemen berikutnya?`)) {
            $.post(url, function(response) {
                if (response.success) {
                    window.location.reload();
                } else {
                    alert(response.message || 'Gagal melakukan handover');
                }
            }).fail(function() {
                alert('Terjadi kesalahan. Silakan coba lagi.');
            });
        }
    });
});