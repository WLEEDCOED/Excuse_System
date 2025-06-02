
        $(document).ready(function(){
            $('#requestForm').on('submit', function(event){
                event.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: '',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        var res = JSON.parse(response);
                        if(res.success) {
                            $('#message').html('<div class="success-message">'+res.success+'</div>');
                            $('#requestForm')[0].reset();
                        } else if(res.error) {
                            $('#message').html('<div class="error-message">'+res.error+'</div>');
                        }
                    }
                });
            });
        });
        $(document).ready(function(){
            // عندما يتغير اختيار السنة الدراسية
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
                        },
                        error: function() {
                            alert('حدث خطأ أثناء جلب الفصول الدراسية.');
                        }
                    });
                } else {
                    $('#semester').empty().append('<option value="">اختر الفصل الدراسي</option>');
                    $('#subject').empty().append('<option value="">اختر المادة</option>');
                }
            });

            // عندما يتغير اختيار الفصل الدراسي
            $('#semester').change(function(){
                var semesterId = $(this).val();
                var studentId = '<?php echo $student_id; ?>';
                if (semesterId) {
                    $.ajax({
                        url: 'get_courses.php',
                        type: 'POST',
                        data: { semester_id: semesterId, student_id: studentId },
                        dataType: 'json',
                        success: function(response) {
                            $('#subject').empty().append('<option value="">اختر المادة</option>');
                            $.each(response, function(index, course) {
                                $('#subject').append('<option value="'+ course.id +'">'+ course.name +'</option>');
                            });
                        },
                        error: function() {
                            alert('حدث خطأ أثناء جلب المواد.');
                        }
                    });
                } else {
                    $('#subject').empty().append('<option value="">اختر المادة</option>');
                }
            });
        });


