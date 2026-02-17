$(document).ready(function() {
    // CPB form validation
    $('#cpb-form').validate({
        rules: {
            batch_number: {
                required: true,
                remote: {
                    url: '/api/cpb/check-batch',
                    type: 'post',
                    data: {
                        batch_number: function() {
                            return $('#batch_number').val();
                        }
                    }
                }
            },
            product_name: {
                required: true,
                minlength: 3
            },
            schedule_duration: {
                required: true,
                min: 1,
                max: 720
            }
        },
        messages: {
            batch_number: {
                required: 'Nomor batch wajib diisi',
                remote: 'Nomor batch sudah digunakan'
            },
            product_name: {
                required: 'Nama produk wajib diisi',
                minlength: 'Nama produk minimal 3 karakter'
            },
            schedule_duration: {
                required: 'Durasi produksi wajib diisi',
                min: 'Durasi minimal 1 jam',
                max: 'Durasi maksimal 720 jam'
            }
        },
        errorElement: 'span',
        errorPlacement: function (error, element) {
            error.addClass('invalid-feedback');
            element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
            $(element).addClass('is-invalid').removeClass('is-valid');
        },
        unhighlight: function (element, errorClass, validClass) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        }
    });
    
    // Auto-generate batch number
    $('#generate-batch').click(function() {
        const type = $('#type').val();
        const year = new Date().getFullYear();
        
        if (!type) {
            alert('Pilih jenis CPB terlebih dahulu');
            return;
        }
        
        const typeCode = type === 'pengolahan' ? 'P' : 'K';
        
        $.get(`/api/cpb/last-number?type=${type}`, function(data) {
            const nextNumber = (data.last_number || 0) + 1;
            const batchNumber = `CPB-${year}-${typeCode}${nextNumber.toString().padStart(3, '0')}`;
            
            $('#batch_number').val(batchNumber).trigger('change');
        }).fail(function() {
            const batchNumber = `CPB-${year}-${typeCode}001`;
            $('#batch_number').val(batchNumber).trigger('change');
        });
    });
    
    // CPB status timeline
    function updateTimeline() {
        const cpbId = $('#cpb-id').val();
        if (!cpbId) return;
        
        $.get(`/api/cpb/${cpbId}/timeline`, function(data) {
            if (data.timeline) {
                const timeline = $('.cpb-timeline');
                timeline.empty();
                
                data.timeline.forEach(function(step) {
                    const stepClass = step.active ? 'active' : (step.completed ? 'completed' : 'pending');
                    const stepHtml = `
                        <div class="timeline-step ${stepClass}">
                            <div class="timeline-step-icon">${step.icon}</div>
                            <div class="timeline-step-content">
                                <div class="timeline-step-title">${step.title}</div>
                                <div class="timeline-step-time">${step.time}</div>
                                ${step.duration ? `<div class="timeline-step-duration">${step.duration}</div>` : ''}
                            </div>
                        </div>
                    `;
                    
                    timeline.append(stepHtml);
                });
            }
        });
    }
    
    // Update timeline every minute
    setInterval(updateTimeline, 60000);
    
    // CPB handover tracking
    $('.track-handover').click(function() {
        const handoverId = $(this).data('handover-id');
        
        $.get(`/api/handover/${handoverId}/track`, function(data) {
            if (data.status) {
                // Update handover status
                $(`#handover-${handoverId}`).find('.status').text(data.status);
                
                if (data.completed) {
                    $(`#handover-${handoverId}`).addClass('completed');
                }
            }
        });
    });
    
    // CPB duration countdown
    function updateCountdown() {
        $('.cpb-countdown').each(function() {
            const remaining = parseInt($(this).data('remaining'));
            const limit = parseInt($(this).data('limit'));
            
            if (remaining > 0) {
                const hours = Math.floor(remaining / 60);
                const minutes = remaining % 60;
                
                $(this).text(`${hours} jam ${minutes} menit`);
                
                // Update color based on remaining time
                if (remaining < limit * 0.2) { // Less than 20% remaining
                    $(this).removeClass('text-success text-warning').addClass('text-danger');
                } else if (remaining < limit * 0.5) { // Less than 50% remaining
                    $(this).removeClass('text-success').addClass('text-warning');
                } else {
                    $(this).removeClass('text-warning text-danger').addClass('text-success');
                }
            } else {
                $(this).text('Waktu habis').addClass('text-danger');
            }
        });
    }
    
    // Update countdown every minute
    setInterval(updateCountdown, 60000);
    updateCountdown(); // Initial update
});