$(document).ready(function() {
    $('.btn-accept, .btn-reject').click(function() {
        var button = $(this);
        var requestId = button.data('id');
        var action = button.hasClass('btn-accept') ? 'approved' : 'rejected';
        var row = button.closest('.request');

        button.prop('disabled', true);

        $.ajax({
            url: 'admin_dashboard.php', 
            type: 'POST',
            data: {
                action: action,
                request_id: requestId
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var statusMessage = (response.status === 'approved') ? 'تمت الموافقة' : 'تم الرفض';
                    var statusClass = (response.status === 'approved') ? 'approved' : 'rejected';

                    row.find('.actions').html('<p class="status ' + statusClass + '">' + statusMessage + '</p>');

                    setTimeout(function() {
                        row.fadeOut(500, function() {
                            $(this).remove();
                            if ($('.request').length === 0) {
                                $('.container').append('<div class="empty-message">لا توجد طلبات بعد.</div>');
                            }
                        });
                    }, 2000);
                } else {
                    alert(response.message);
                    button.prop('disabled', false);
                }
            },
            error: function() {
                alert('حدث خطأ أثناء الاتصال بالخادم');
                button.prop('disabled', false);
            }
        });
    });
});
