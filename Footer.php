<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
.footer {
    position: fixed; /* جعل الفوتر ثابت في الأسفل */
    bottom: 0; /* ضبط الفوتر في أسفل الصفحة */
    left: 0; /* بدء الفوتر من اليسار */
    width: 100%; /* ضمان امتداد الفوتر عبر كامل العرض */
    background-color: #00a86b; /* لون الخلفية */
    color: #fff; /* لون النص */
    text-align: center; /* محاذاة النص في المنتصف */
    padding: 20px 0; /* التبطين العلوي والسفلي للفوتر */
    box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.1); /* الظل */
}

.footer-content {
    max-width: 900px; /* العرض الأقصى للمحتوى داخل الفوتر */
    margin: 0 auto; /* التمركز */
    display: flex; /* استخدام flex لتنظيم العناصر داخل الفوتر */
    justify-content: space-between; /* التباعد بين العناصر */
    align-items: center; /* محاذاة العناصر في الوسط عمودياً */
    flex-wrap: wrap; /* السماح للعناصر بالانتقال إلى السطر التالي إذا لزم الأمر */
}

body {
    margin: 0; /* إزالة الهوامش الافتراضية */
    padding: 0; /* إزالة التبطين الافتراضي */
    min-height: 100vh; /* ضمان أن يكون ارتفاع الصفحة كافياً */
    padding-bottom: 60px; /* ترك مساحة في الأسفل للفوتر */
}

.footer-content p {
    margin: 0;
    font-size: 14px;
}

.footer-nav {
    display: flex;
    gap: 20px;
}

.footer-nav a {
    color: #fff;
    text-decoration: none;
    font-weight: bold;
    transition: color 0.3s ease;
}

.footer-nav a:hover {
    color: #e0f5f0;
}

  </style>
</head>
<body>
<footer class="footer">
    <div class="footer-content">
        <p>&copy; 2024  جميع الحقوق محفوظة</p>
        <div class="footer-nav">
            <a href="#">الرئيسية</a>
            <a href="#">حول</a>
            <a href="#">اتصل بنا</a>
            <a href="#">سياسة الخصوصية</a>
        </div>
    </div>
</footer>

</body>
</html>