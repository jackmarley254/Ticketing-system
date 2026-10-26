<?php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

// session_start();
if (!isset($_SESSION['login_id'])) {
  header('Location: login.php');
  exit;
}
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <style>
    .content-header h1 {
      font-weight: 600;
      color: #0d6efd;
    }

    .alert a {
      font-weight: 600;
    }

    .main-footer {
      background: #f8f9fa;
      padding: 15px 0;
      border-top: 1px solid #dee2e6;
      font-size: 0.95rem;
    }

    .modal-header {
      background-color: #0d6efd;
      color: white;
    }

    .modal-footer .btn {
      min-width: 100px;
    }

    .toast-body {
      font-weight: 500;
    }

    .card {
      transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }

    .fa-2x {
      color: white;
    }

    .content-wrapper {
      background-color: #f4f6f9;
      padding-bottom: 20px;
    }

    hr.border-primary {
      border-top: 3px solid #0d6efd;
      width: 60px;
      margin-top: 0;
    }

    .card h6 {
      font-weight: 600;
      margin-bottom: 4px;
    }

    .card h2 {
      font-weight: bold;
    }

    .alert-dismissible .btn-close {
      padding: 1rem;
    }

    .custom-alert {
      background: #fff3cd;
      /* softer yellow */
      border-left: 6px solid #ffca2c;
      /* vibrant accent */
      color: #856404;
      border-radius: 0.5rem;
      padding: 1rem 1.5rem;
      display: flex;
      align-items: center;
      box-shadow: 0 4px 6px rgb(0 0 0 / 0.1);
      font-size: 1.1rem;
      gap: 1rem;
      animation: slideInDown 0.5s ease forwards;
    }

    .custom-alert i.fa-lock {
      font-size: 1.5rem;
      color: #d39e00;
      flex-shrink: 0;
    }

    .custom-alert strong {
      font-weight: 700;
    }

    .custom-alert a.alert-link {
      font-weight: 600;
      color: #b87a00;
      text-decoration: underline;
    }

    .custom-alert a.alert-link:hover {
      color: #855700;
    }

    .btn-close {
      margin-left: auto;
      flex-shrink: 0;
    }

    /* Simple slide in animation */
    @keyframes slideInDown {
      from {
        transform: translateY(-10px);
        opacity: 0;
      }

      to {
        transform: translateY(0);
        opacity: 1;
      }
    }
  </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <div class="wrapper">

    <?php include 'topbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
      <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body text-white bg-success p-3 rounded shadow"></div>
      </div>
      <div id="toastsContainerTopRight" class="toasts-top-right fixed"></div>

      <div class="content-header">
        <div class="container-fluid">
          <?php if (!isset($_SESSION['password_changed'])): ?>
            <div class="custom-alert alert-dismissible fade show shadow-sm" role="alert">
              <i class="fa fa-lock" aria-hidden="true"></i>
              <div>
                <strong>Important:</strong> For your security, please
                <a href="index.php?page=manage_user&id=<?= $_SESSION['login_id'] ?>" class="alert-link">change your password</a> before proceeding.
              </div>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php elseif (isset($_SESSION['password_changed']) && $_SESSION['password_changed']): ?>
            <script>
              $(function() {
                alert_toast("Thank you, your password changes have been saved.", 'success');
              });
            </script>
            <?php unset($_SESSION['password_changed']); ?>
          <?php endif; ?>

          <?php
          $page = $_GET['page'] ?? 'home';
          $titles = [
            'home' => 'Dashboard',
            'new_customer' => 'Add a Staff',
            'customer_list' => 'All Staff',
            'new_staff' => 'Add New Technical Staff',
            'staff_list' => 'All Technical Staff',
            'department_list' => 'All Offices',
            'new_ticket' => 'Create a Ticket',
            'ticket_list' => 'All Tickets',
            'resources' => 'All Resources',
            'assets_dashboard' => 'Assets Dashboard',
            'manage_user' => 'Manage Account'
          ];
          $title = $titles[$page] ?? ucwords(str_replace('_', ' ', $page));
          ?>

          <div class="row mb-3 align-items-center">
            <div class="col-md-8">
              <h1 class="m-0"><i class="fa fa-tachometer-alt me-2 text-primary"></i> <?= $title ?></h1>
            </div>
          </div>
          <hr class="border-primary mb-4">
        </div>
      </div>

      <section class="content">
        <div class="container-fluid">
          <?php
          include 'db_connect.php';
          $today = date('Y-m-d');
          $total_tickets = $conn->query("SELECT COUNT(*) AS count FROM tickets")->fetch_assoc()['count'];
          $solved_tickets = $conn->query("SELECT COUNT(*) AS count FROM tickets WHERE status = 'Solved'")->fetch_assoc()['count'];
          $pending_tickets = $conn->query("SELECT COUNT(*) AS count FROM tickets WHERE status != 'Solved'")->fetch_assoc()['count'];
          $tickets_today = $conn->query("SELECT COUNT(*) AS count FROM tickets WHERE date_created >= CURDATE() AND date_created < CURDATE() + INTERVAL 1 DAY")->fetch_assoc()['count'];

          ?>

          <?php if (in_array($_SESSION['login_type'], [1, 2])): ?>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 mb-4">

              <div class="col">
                <div class="card text-white bg-primary h-100 shadow">
                  <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                      <h6>Total Tickets</h6>
                      <h2><?= $total_tickets ?></h2>
                    </div>
                    <i class="fas fa-ticket-alt fa-2x"></i>
                  </div>
                </div>
              </div>

              <div class="col">
                <div class="card text-white bg-success h-100 shadow">
                  <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                      <h6>Pending Tickets</h6>
                      <h2><?= $solved_tickets ?></h2>
                    </div>
                    <i class="fas fa-check-circle fa-2x"></i>
                  </div>
                </div>
              </div>

              <div class="col">
                <div class="card text-white bg-warning h-100 shadow">
                  <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                      <h6>Solved Tickets</h6>
                      <h2><?= $pending_tickets ?></h2>
                    </div>
                    <i class="fas fa-hourglass-half fa-2x"></i>
                  </div>
                </div>
              </div>

              <div class="col">
                <div class="card text-white bg-info h-100 shadow">
                  <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                      <h6>Tickets Today</h6>
                      <h2><?= $tickets_today ?></h2>
                    </div>
                    <i class="fas fa-calendar-day fa-2x"></i>
                  </div>
                </div>
              </div>

            </div>
          <?php endif; ?>

          <?php
          $file = $page . '.php';
          if (file_exists($file)) {
            include $file;
          } else {
            echo "<div class='alert alert-danger shadow-sm'>⚠️ Page not found!</div>";
          }
          ?>
        </div>
      </section>

      <!-- Modals -->
      <div class="modal fade" id="confirm_modal" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title">Confirmation</h5>
            </div>
            <div class="modal-body">
              <div id="delete_content"></div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-success" id="confirm">Continue</button>
              <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="uni_modal" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"></h5>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
              <!-- <button type="button" class="btn btn-primary" id="submit" onclick="$('#uni_modal form').submit()">Save</button> -->
              <!-- <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button> -->
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="uni_modal_right" role="dialog">
        <div class="modal-dialog modal-full-height modal-md" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title"></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span class="fa fa-arrow-right"></span>
              </button>
            </div>
            <div class="modal-body"></div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="viewer_modal" role="dialog">
        <div class="modal-dialog modal-md" role="document">
          <div class="modal-content">
            <button type="button" class="btn-close" data-dismiss="modal"><span class="fa fa-times"></span></button>
            <img src="" alt="">
          </div>
        </div>
      </div>

    </div>

    <footer class="main-footer text-center">
      <div class="container">
        <strong>&copy; <?= date('Y') ?> ICT-State Department for Culture, Arts and Heritage.All Rights Reserved.</strong>
        <div class="float-right d-none d-sm-inline-block">
          <b>ICT Ticketing Support System</b>
        </div>
      </div>
    </footer>

  </div>

  <?php include 'footer.php'; ?>
</body>

</html>