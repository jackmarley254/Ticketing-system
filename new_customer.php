<?php
if (!isset($conn)) {
  include 'db_connect.php';
}
?>
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="col-lg-12">
  <div class="card shadow-sm rounded">
    <div class="card-header bg-primary text-white">
      <h5 class="mb-0">Manage Client</h5>
    </div>
    <div class="card-body">

      <!-- Banner alert -->
      <!-- <div id="client_email_banner_alert" class="alert alert-danger d-none" role="alert" style="font-size: 1.1rem;"></div> -->

      <form action="" id="manage_customer">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">

        <div class="row">
          <div class="col-md-6 border-right">
            <h6 class="text-muted mb-3">Personal Information</h6>
            <div class="form-group">
              <label for="firstname">First Name</label>
              <input type="text" name="firstname" id="firstname" class="form-control" required value="<?php echo isset($firstname) ? $firstname : '' ?>">
            </div>
            <div class="form-group">
              <label for="lastname">Last Name</label>
              <input type="text" name="lastname" id="lastname" class="form-control" required value="<?php echo isset($lastname) ? $lastname : '' ?>">
            </div>
            <div class="form-group">
              <label for="department_id">Office</label>
              <select name="department_id" id="department_id" class="form-control select2" required>
                <option value="">Select Office</option>
                <?php
                $departments = $conn->query("SELECT * FROM departments ORDER BY name ASC");
                while ($row = $departments->fetch_assoc()):
                ?>
                  <option value="<?php echo $row['id'] ?>" <?php echo isset($department_id) && $department_id == $row['id'] ? 'selected' : '' ?>>
                    <?php echo ucwords($row['name']) ?>
                  </option>
                <?php endwhile; ?>
              </select>
            </div>
          </div>

          <div class="col-md-6">
            <h6 class="text-muted mb-3">System Credentials</h6>
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" class="form-control" name="email" id="email" required value="<?php echo isset($email) ? $email : '' ?>">
              <small id="email_feedback" class="form-text font-weight-bold"></small>
            </div>

            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" class="form-control" name="password" id="password" <?php echo isset($id) ? '' : 'required' ?>>
              <small class="text-muted"><i><?php echo isset($id) ? "Leave this blank if you don't want to change the password." : '' ?></i></small>
            </div>
            <div class="form-group">
              <label for="cpass">Confirm Password</label>
              <input type="password" class="form-control" name="cpass" id="cpass" <?php echo isset($id) ? 'required' : '' ?>>
              <small id="pass_match" data-status=''></small>
            </div>
          </div>
        </div>

        <hr>
        <div class="text-center">
          <button class="btn btn-primary mr-2">Save Details</button>
          <button type="reset" class="btn btn-secondary mr-2">Clear Details</button>
          <button type="button" class="btn btn-success" id="register_client_email_btn">
            Register Staff Email Here
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Reusable Modal -->
<div class="modal fade" id="uni_modal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-md modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="uni_modal_title">Modal Title</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body" id="uni_modal_body">
        <!-- Loaded dynamically -->
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function() {
    $('.select2').select2({
      width: '100%'
    });

    // Password match validation
    $('[name="password"], [name="cpass"]').on('keyup', function() {
      const pass = $('[name="password"]').val();
      const cpass = $('[name="cpass"]').val();
      if (!pass || !cpass) {
        $('#pass_match').attr('data-status', '').html('');
      } else if (pass === cpass) {
        $('#pass_match').attr('data-status', '1').html('<i class="text-success">Password Matched.</i>');
      } else {
        $('#pass_match').attr('data-status', '2').html('<i class="text-danger">Password does not match.</i>');
      }
    });

    // 🔍 Live client email check
    let emailCheckTimer;

    $('#email').on('input', function() {
      clearTimeout(emailCheckTimer);

      const email = $(this).val().trim();
      const feedback = $('#email_feedback');
      const input = $(this);

      feedback.removeClass('text-danger text-success').text('');
      input.removeClass('border-danger border-success');

      if (email === '') return;

      if (!email.endsWith('@cultureheritage.go.ke')) {
        feedback.addClass('text-danger').text("Invalid Email Format.");
        input.addClass('border-danger');
        return;
      }

      emailCheckTimer = setTimeout(() => {
        $.ajax({
          url: 'ajax.php?action=check_client_email',
          method: 'POST',
          data: {
            email: email
          },
          success: function(resp) {
            if (resp == '1') {
              feedback.addClass('text-success').text('Email Exists.');
              input.removeClass('border-danger').addClass('border-success');
            } else {
              feedback.addClass('text-danger').text('Email does not Exist.');
              input.addClass('border-danger');
            }
          }
        });
      }, 500); // wait 500ms after typing
    });

    let email = $(this).val().trim();
    let banner = $('#client_email_banner_alert');

    banner.addClass('d-none').text('');
    $(this).removeClass('border-danger');

    if (email === '') return;

    // Check domain first
    if (!email.endsWith('@cultureheritage.go.ke')) {
      banner.removeClass('d-none').text("Email does not Exist");
      $(this).addClass('border-danger');
      return;
    }

    // Check approval via AJAX
    $.ajax({
      url: 'ajax.php?action=check_client_email',
      method: 'POST',
      data: {
        email: email
      },
      success: function(resp) {
        if (resp == '1') {
          banner.addClass('d-none').text('');
          $('#email').removeClass('border-danger');
        } else {
          banner.removeClass('d-none').text("Email does not Exist.");
          $('#email').addClass('border-danger');
        }
      }
    });
  });

  // Form submit
  $('#manage_customer').submit(function(e) {
    e.preventDefault();
    let email = $('#email').val().trim();
    let feedback = $('#email_feedback');

    if (!email.endsWith('@cultureheritage.go.ke')) {
      feedback.removeClass('text-success').addClass('text-danger').text("Only @cultureheritage.go.ke emails are allowed.");
      $('#email').addClass('border-danger');
      return;
    }

    if ($('#email').hasClass('border-danger')) {
      feedback.removeClass('text-success').addClass('text-danger').text("Email must be approved before registration.");
      return;
    }


    $('input').removeClass("border-danger");
    start_load();

    if ($('#pass_match').attr('data-status') !== '1' && $("[name='password']").val() !== '') {
      $('[name="password"], [name="cpass"]').addClass("border-danger");
      end_load();
      return false;
    }

    $.ajax({
      url: 'ajax.php?action=save_customer',
      data: new FormData(this),
      cache: false,
      contentType: false,
      processData: false,
      method: 'POST',
      success: function(resp) {
        if (resp == 1) {
          alert_toast('Data successfully saved.', "success");
          setTimeout(() => location.replace('index.php?page=customer_list'), 750);
        } else if (resp == 2) {
          banner.removeClass('d-none').text("Email already exists.");
          $('#email').addClass("border-danger");
          end_load();
        } else if (resp == 3) {
          banner.removeClass('d-none').text("Email does not Exist.");
          $('#email').addClass("border-danger");
          end_load();
        } else {
          banner.removeClass('d-none').text("An unexpected error occurred.");
          end_load();
        }
      }
    });
  });

  // Modal
  $('#register_client_email_btn').click(function() {
    uni_modal("Register Staff Email", "register_client_email.php");
  });


  function uni_modal(title, url) {
    $('#uni_modal_title').text(title);
    $.ajax({
      url: url,
      error: err => {
        alert("An error occurred");
        console.error(err);
      },
      success: function(resp) {
        $('#uni_modal_body').html(resp);
        $('#uni_modal').modal('show');
      }
    });
  }
</script>