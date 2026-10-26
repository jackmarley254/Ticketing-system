<!DOCTYPE html> 
<html lang="en">
<?php 
session_start();
include('./db_connect.php');
?>
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Login | ICT Ticketing Support System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #ffffff;
      height: 100vh;
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .kenyan-stripes {
      width: 100%;
      height: 20px;
    }

    .kenyan-stripes div {
      display: block;
      width: 100%;
      height: 5px;
    }

    .black { background-color: black; }
    .red { background-color: red; }
    .green { background-color: green; }
    .white { background-color: white; }

    .login-card {
      border-radius: 1rem;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      background-color: #fff;
    }

    .login-title {
      color: white;
      text-align: center;
      margin-top: 40px;
      margin-bottom: 20px;
      font-size: 2.5rem;
    }

    .login-title span {
      font-weight: bold;
    }

    .form-control {
      padding-left: 40px;
    }

    .input-group {
      position: relative;
    }

    .btn-primary {
      background-color: #4e73df;
      border-color: #4e73df;
    }

    .btn-primary:hover {
      background-color: #2e59d9;
      border-color: #2e59d9;
    }

    .form-select {
      padding-left: 40px;
    }

    .input-group-text i {
      color: #6c757d;
    }

    .toggle-password {
      cursor: pointer;
    }
  </style>
  <?php include('./header.php'); ?>
  <?php 
  if(isset($_SESSION['login_id']))
    header("location:index.php?page=home");
  ?>
</head>
<body>

  <!-- Kenyan Flag Strips at the top -->
  <div class="kenyan-stripes">
    <div class="black"></div>
    <div class="red"></div>
    <div class="green"></div>
    <div class="white"></div>
  </div>

  <!-- Logo and title container -->
  <div class="container text-center">
    <img src="images/State-department-logo.png" alt="System Logo" class="mb-3" style="max-height: 100px;">
  </div>

  <!-- Full-width Kenyan flag stripes -->
  <div class="kenyan-stripes">
    <div class="black"></div>
    <div class="red"></div>
    <div class="green"></div>
    <div class="white"></div>
  </div>

  <!-- Title container -->
  <div class="container text-center">
    <h1 class="login-title">
      <span style="color: white;">ICT </span>
      <span style="color: red;">Ticketing </span>
      <span style="color: black;">Support </span>
      <span style="color: green;">System </span>
    </h1>

    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card login-card p-4">
          <div class="card-body">
            <form id="login-form">
              <div class="mb-3 input-group">
                <span class="input-group-text"><i class="fas fa-user"></i></span>
                <input type="text" id="username" name="username" class="form-control" placeholder="Username" required>
              </div>

              <div class="mb-3 input-group">
                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                <input type="password" id="password" name="password" class="form-control" placeholder="Password" required>
                <span class="input-group-text toggle-password"><i class="fas fa-eye" id="togglePasswordIcon"></i></span>
              </div>

              <div class="mb-3">
                <label for="type" class="form-label">Login As</label>
                <select name="type" class="form-select" required>
                  <option value="3">Staff</option>
                  <option value="2">Technical Staff</option>
                  <option value="1">Admin</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS and dependencies -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    // Toggle password visibility
    document.querySelector('.toggle-password').addEventListener('click', function () {
      const passwordField = document.getElementById('password');
      const icon = document.getElementById('togglePasswordIcon');
      const isPassword = passwordField.getAttribute('type') === 'password';
      passwordField.setAttribute('type', isPassword ? 'text' : 'password');
      icon.classList.toggle('fa-eye');
      icon.classList.toggle('fa-eye-slash');
    });

    // Login form AJAX
    $('#login-form').submit(function(e){
      e.preventDefault();
      const btn = $(this).find('button[type="submit"]');
      btn.attr('disabled', true).text('Logging in...');
      $('.alert-danger').remove();

      $.ajax({
        url: 'ajax.php?action=login',
        method: 'POST',
        data: $(this).serialize(),
        error: err => {
          console.error(err);
          btn.removeAttr('disabled').text('Login');
        },
        success: function(resp){
          if(resp == 1){
            location.href = 'index.php?page=home';
          } else {
            $('#login-form').prepend('<div class="alert alert-danger">Username or password is incorrect.</div>');
            btn.removeAttr('disabled').text('Login');
          }
        }
      });
    });
  </script>
</body>
</html>
