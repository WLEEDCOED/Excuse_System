
function loadRequests() {
    $.ajax({
        url: 'get_requests.php',
        type: 'GET',
        success: function(data) {
            if (data.trim() === '') {
                $('.container').html(`<p style='color: black; text-align: center;'>لا توجد طلبات للمراجعة في الوقت الحالي.</p>`);
            } else {
                $('.container').html(data);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error loading requests:", error);
        }
    });
}

function removeRequest(requestId) {
    $('#request-' + requestId).fadeOut(400, function() {
        $(this).remove();
        if ($('.request').length === 0) {
            $('.container').html("<p style='color: black; text-align: center;'>لا توجد طلبات للمراجعة في الوقت الحالي.</p>");
        }
    });
}

function handleApproval(requestId) {
    $.ajax({
        url: 'admin_dashboard.php',
        type: 'POST',
        data: { action: 'approve', request_id: requestId },
        dataType: 'json',
        success: function(data) {
            if (data.showDatePicker) {
                setExamDate(requestId);
            } else {
                removeRequest(requestId);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error approving request:", error);
        }
    });
}

function handleRejection(requestId) {
    Swal.fire({
        title: 'اكتب سبب الرفض',
        input: 'textarea',
        inputPlaceholder: 'ادخل سبب الرفض هنا...',
        showCancelButton: true,
        confirmButtonText: 'رفض الطلب',
        cancelButtonText: 'إلغاء',
        preConfirm: (rejectionReason) => {
            if (!rejectionReason) {
                Swal.showValidationMessage('يرجى إدخال سبب الرفض');
            }
            return rejectionReason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const rejectionReason = result.value;
            $.ajax({
                url: 'admin_dashboard.php',
                type: 'POST',
                data: {
                    action: 'reject',
                    request_id: requestId,
                    rejection_reason: rejectionReason
                },
                dataType: 'json',
                success: function(data) {
                    removeRequest(requestId);
                    showSuccess();
                },
                error: function(xhr, status, error) {
                    console.error("Error rejecting request:", error);
                }
            });
        }
    });
}

function setExamDate(requestId) {
    Swal.fire({
        title: 'حدد تاريخ الامتحان',
        html: '<input type="date" id="examDatePicker" class="swal2-input">',
        showCancelButton: true,
        confirmButtonText: 'تأكيد',
        cancelButtonText: 'إلغاء',
        preConfirm: () => {
            const examDate = $('#examDatePicker').val();
            if (!examDate) {
                Swal.showValidationMessage('يرجى اختيار تاريخ الامتحان');
            }
            return examDate;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const examDate = result.value;
            $.ajax({
                url: 'admin_dashboard.php',
                type: 'POST',
                data: {
                    action: 'set_exam_date',
                    request_id: requestId,
                    exam_date: examDate
                },
                dataType: 'json',
                success: function(response) {
                    removeRequest(requestId);
                    showSuccess();
                },
                error: function(xhr, status, error) {
                    console.error("Error setting exam date:", error);
                }
            });
        }
    });
}

function toggleDetails(id) {
    $('#details-' + id).toggleClass('show-details');
}

function showSuccess() {
    Swal.fire({
        icon: 'success',
        title: 'تم تحديث الطلب بنجاح',
        showConfirmButton: false,
        timer: 1500
    });
}

$(document).ready(function() {
    loadRequests();
    setInterval(loadRequests, 30000);
});
