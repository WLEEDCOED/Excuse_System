<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
   <style>
   
body {
    font-family: 'Arial', sans-serif;
    background-color: #dff7ef;
    margin: 0;
    padding: 0;
}

.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background-color: #e5fcf2;
    box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
}

.nav-logo {
    font-size: 24px;
    font-weight: bold;
    color: #009966;
}

.nav-links {
    list-style: none;
    display: flex;
    gap: 20px;
}

.nav-links li {
    display: inline;
}

.nav-links a {
    text-decoration: none;
    color: #009966;
    font-weight: 500;
    font-size: 16px;
}

.nav-menu {
    display: none;
}

.dashboard-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: calc(100vh - 80px);
}

.dashboard-text {
    max-width: 500px;
}

.dashboard-text h1 {
    font-size: 28px;
    color: #333;
}

.buttons-container {
    margin-top: 20px;
}

.btn {
    display: inline-block;
    margin-right: 10px;
    padding: 10px 20px;
    background-color: #00cc99;
    color: white;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: #009966;
}

.dashboard-image img {
    width: 300px;
    margin-left: 50px;
}

   </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-logo"></div>
        <ul class="nav-links">
            <li><a href="#"></a></li>
            <li><a href="#">About</a></li>
            <li><a href="#">Contact us</a></li>
        </ul>
        <div class="nav-menu">
            <div class="menu-icon"></div>
            <div class="menu-icon"></div>
            <div class="menu-icon"></div>
        </div>
    </nav>

    <div class="dashboard-container">
        <div class="dashboard-text">
            <h1>Select the excuses</h1>
            <div class="buttons-container">
                <a href="req_form.php" class="btn">Track</a>
                <a href="finalForm.php" class="btn">Final Exam</a>
                <a href="lecture_excuse.php" class="btn">Lecture Excuse</a>
            </div>
        </div>
        <div class="dashboard-image">
            <img src="computer_dashboard.png" alt="Dashboard Illustration">
        </div>
    </div>

</body>
</html>
