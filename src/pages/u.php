<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>W3Schools</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #1c1c1c;
      color: #fff;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .content {
      display: flex;
      justify-content: space-between;
      align-items: center;
      max-width: 800px;
      width: 100%;
    }

    .text-content {
      text-align: right;
      margin-right: 20px;
    }

    h1 {
      font-size: 36px;
      margin-bottom: 20px;
    }

    ul {
      list-style-type: none;
      padding: 0;
      margin-bottom: 30px;
    }

    li {
      margin-bottom: 10px;
    }

    .login-box {
      background-color: #fff;
      color: #333;
      padding: 20px;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
      position: relative;
      max-width: 300px;
      width: 100%;
    }

    .login-box h2 {
      margin-top: 0;
    }

    .login-box input[type="text"],
    .login-box input[type="password"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid #ccc;
      border-radius: 3px;
      box-sizing: border-box;
    }

    .login-box button {
      background-color: #4CAF50;
      color: #fff;
      padding: 10px 20px;
      border: none;
      border-radius: 3px;
      cursor: pointer;
    }

    .auth-providers {
      display: flex;
      justify-content: center;
      margin-bottom: 20px;
    }

    .auth-providers button {
      background-color: transparent;
      border: none;
      margin: 0 10px;
      cursor: pointer;
    }

    .auth-providers img {
      width: 30px;
      height: 30px;
    }

    .close-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      font-size: 24px;
      cursor: pointer;
    }

    @media (max-width: 768px) {
      .content {
        flex-direction: column;
        align-items: center;
      }

      .text-content {
        text-align: center;
        margin-right: 0;
        margin-bottom: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="content">
    <div class="text-content">
      <h1>Become a W3Schooler &#10084;</h1>
      <ul>
        <li>&#10003; Track your progress</li>
        <li>&#10003; Set your goals</li>
        <li>&#10003; Get a personalized learning path</li>
        <li>&#10003; Test your skills</li>
        <li>&#10003; Practice coding in browser</li>
        <li>&#10003; Build and host a website</li>
        <li>&#10003; Teacher Toolbox</li>
      </ul>
    </div>
    <div class="login-box">
      <span class="close-btn">&times;</span>
      <h2>Log In</h2>
      <p>Don't have an account? <a href="#">Sign up</a></p>
      <div class="auth-providers">
        <button><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/53/Google_%22G%22_Logo.svg/768px-Google_%22G%22_Logo.svg.png" alt="Google"></button>
        <button><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/cd/Facebook_logo_%28square%29.png/768px-Facebook_logo_%28square%29.png" alt="Facebook"></button>
        <button><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/Octicons-mark-github.svg/768px-Octicons-mark-github.svg.png" alt="Github"></button>
        <button><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/59/Feide_logo.svg/768px-Feide_logo.svg.png" alt="Feide"></button>
      </div>
      <p>OR</p>
      <input type="text" placeholder="email">
      <input type="password" placeholder="password">
      <a href="#">Forgot Password?</a>
      <button>Login</button>
    </div>
  </div>

  <script>
    const closeBtn = document.querySelector('.close-btn');
    const loginBox = document.querySelector('.login-box');

    closeBtn.addEventListener('click', () => {
      loginBox.style.display = 'none';
    });
  </script>
</body>
</html>