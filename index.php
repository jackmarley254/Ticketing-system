<?php
session_start();

if (!isset($_SESSION['login_id'])) {
  header('Location: login.php');
  exit;
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">

<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
  <div class="wrapper">

    <?php include 'topbar.php'; ?>
    <?php include 'sidebar.php'; ?>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <div class="toast" id="alert_toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-body text-white"></div>
      </div>
      <div id="toastsContainerTopRight" class="toasts-top-right fixed"></div>

      <!-- Content Header -->
      <div class="content-header">
        <div class="container-fluid">



          <?php if (!isset($_SESSION['password_changed'])): ?>
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
              <strong>Important:</strong> For your security, please
              <a href="index.php?page=manage_user&id=<?php echo $_SESSION['login_id'] ?>" class="alert-link">change your password</a> before proceeding.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php elseif (isset($_SESSION['password_changed']) && $_SESSION['password_changed']): ?>
            <script>
              $(function() {
                alert_toast("Thank you, your password changes have been saved.", 'success');
              })
            </script>
            <?php unset($_SESSION['password_changed']); ?>
          <?php endif; ?>




          <div class="row mb-2">
            <div class="col-sm-6">
              <?php
              // Dynamically set the page title
              $page = isset($_GET['page']) ? $_GET['page'] : 'home';
              switch ($page) {
                case 'home':
                  $title = 'Dashboard';
                  break;
                case 'new_customer':
                  $title = 'To add a new client, complete the following fields:';
                  break;
                case 'customer_list':
                  $title = 'All Clients List';
                  break;
                case 'new_staff':
                  $title = 'To add a new staff, complete the following fields:';
                  break;
                case 'staff_list':
                  $title = 'All Staff List';
                  break;
                case 'department_list':
                  $title = 'All Offices List';
                  break;
                case 'new_ticket':
                  $title = 'Raise a new ticket by filling the following details.';
                  break;
                case 'ticket_list':
                  $title = 'All Tickets List';
                  break;
                case 'resources':
                  $title = 'All Resources List';
                  break;
                case 'assets_dashboard':
                  $title = 'Assets Dashboard';
                  break;
                case 'manage_user':
                  $title = 'Manage Account';
                  break;
                default:
                  $title = ucwords(str_replace('_', ' ', $page)); // fallback for any future pages
                  break;
              }
              ?>

              <h1 class="m-0 text-primary"><?php echo isset($title) ? $title : "Dashboard"; ?></h1>
            </div>
            <div class="col-sm-6 text-right">
              <!-- <a href="manage_users.php" class="btn btn-primary">Manage Users</a> -->
            </div>
          </div>
          <hr class="border-primary">
        </div>
      </div>


      <!-- Main content -->
      <section class="content">
        <div class="container-fluid">
          <?php
          $page = isset($_GET['page']) ? $_GET['page'] : 'home';
          $file = $page . '.php';
          if (file_exists($file)) {
            include $file;
          } else {
            echo "<div class='alert alert-danger'>Page not found!</div>";
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
    <!-- /.content-wrapper -->

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark"></aside>

    <!-- Main Footer -->
    <footer class="main-footer text-center">
      <strong>&copy; 2025 ICT. All Rights Reserved.</strong>
      <div class="float-right d-none d-sm-inline-block">
        <b>ICT Ticketing Support System</b>
      </div>
    </footer>
  </div>

  <?php include 'footer.php'; ?>

</body>

</html>