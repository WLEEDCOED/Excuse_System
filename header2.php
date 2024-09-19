<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
       .header {
        background-color:pink ;
        padding: top;
       }
        .admin-info {
    display: flex;
    align-items: center;
    position: relative;
}

.admin-icon {
    cursor: pointer;
    background-color:white ;
    border-radius: 50%;
    padding: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    width: 40px;
    height: 40px;
    margin-left: 10px;
}

.dropdown-menu {
    display: none;
    position: absolute;
    top: 50px;
    right: 0;
    background-color: white;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 5px;
    overflow: hidden;
    z-index: 1000;
}

.dropdown-menu a {
    display: block;
    padding: 10px 20px;
    text-decoration: none;
    color: #333;
    background-color: #fff;
}

.dropdown-menu a:hover {
    background-color: #f1f1f1;
}

    </style>
   
</head>
<body>
<header class="header">
<div class="welcome">
<h1> Welcome</h1>
        </div>
    <div class="admin-info">
        <div class="admin-icon" id="admin-icon"></div>
        <div class="dropdown-menu" id="dropdown-menu">
            
            <a href="logout.php">تسجيل خروج</a>
        </div>
    </div>
    <nav class="nav">
        <a href="#">About</a>
        <a href="#">Contact us</a>
    </nav>
    <div class="logo">
        Collage
    </div>
</header>

<script>
    document.getElementById('admin-icon').addEventListener('click', function() {
        var menu = document.getElementById('dropdown-menu');
        if (menu.style.display === 'block') {
            menu.style.display = 'none';
        } else {
            menu.style.display = 'block';
        }
    });

    // إخفاء القائمة عند النقر خارجها
    document.addEventListener('click', function(event) {
        var menu = document.getElementById('dropdown-menu');
        var icon = document.getElementById('admin-icon');
        if (!icon.contains(event.target)) {
            menu.style.display = 'none';
        }
    });
</script>

</body>
</html>
