
$(document).ready(function(){
    // معالجة إرسال النموذج عبر AJAX
    $('#requestForm').on('submit', function(event){
        event.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: '', // سيتم معالجة الطلب في نفس الصفحة
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                try {
                    var res = JSON.parse(response);
                    if(res.success) {
                        $('#message').html('<div class="success-message">'+res.success+'</div>');
                        $('#requestForm')[0].reset();
                    } else if(res.error) {
                        $('#message').html('<div class="error-message">'+res.error+'</div>');
                        console.error("Error from server:", res.error);
                    }
                    console.log("Server response:", res);
                } catch (e) {
                    $('#message').html('<div class="error-message">حدث خطأ غير متوقع.</div>');
                    console.error("JSON Parse Error:", e);
                    console.error("Raw response:", response);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                $('#message').html('<div class="error-message">حدث خطأ أثناء إرسال الطلب.</div>');
                console.error("AJAX Error:", textStatus, errorThrown);
            }
        });
    });

    // جلب الفصول الدراسية بناءً على السنة الدراسية المختارة
    $('#academic_year').change(function(){
        var academicYearId = $(this).val();
        if (academicYearId) {
            $.ajax({
                url: 'get_semesters.php',
                type: 'POST',
                data: { academic_year_id: academicYearId },
                dataType: 'json',
                success: function(response) {
                    $('#semester').empty().append('<option value="">اختر الفصل الدراسي</option>');
                    $.each(response, function(index, semester) {
                        $('#semester').append('<option value="'+ semester.id +'">'+ semester.name +'</option>');
                    });
                    // إفراغ قائمة المواد
                    $('#course').empty().append('<option value="">اختر المادة</option>');
                },
                error: function() {
                    alert('حدث خطأ أثناء جلب الفصول الدراسية.');
                }
            });
        } else {
            // إذا لم يتم اختيار سنة دراسية، إفراغ الفصول والمواد
            $('#semester').empty().append('<option value="">اختر الفصل الدراسي</option>');
            $('#course').empty().append('<option value="">اختر المادة</option>');
        }
    });

    // جلب المواد بناءً على الفصل الدراسي المختار
    $('#semester').change(function(){
        var semesterId = $(this).val();
        var academicYearId = $('#academic_year').val();
        var studentId = '<?php echo $student_id; ?>';
        if (semesterId && academicYearId) {
            $.ajax({
                url: 'get_courses.php',
                type: 'POST',
                data: {
                    semester_id: semesterId,
                    student_id: studentId
                },
                dataType: 'json',
                success: function(response) {
                    $('#course').empty().append('<option value="">اختر المادة</option>');
                    $.each(response, function(index, course) {
                        $('#course').append('<option value="'+ course.id +'">'+ course.name +'</option>');
                    });
                },
                error: function() {
                    alert('حدث خطأ أثناء جلب المواد.');
                }
            });
        } else {
            // إذا لم يتم اختيار فصل دراسي أو سنة دراسية، إفراغ قائمة المواد
            $('#course').empty().append('<option value="">اختر المادة</option>');
        }
    });
});
