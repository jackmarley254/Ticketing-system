<div class="col-lg-12">
  <div class="card shadow-sm">
    <div class="card-body">
      <form action="" id="manage_customer">
        <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
        <div class="row">
          <!-- Personal Information Section -->
          <div class="col-md-6 border-right">
            <h5 class="text-muted">Personal Information</h5>
            <div class="form-group">
              <label for="firstname" class="control-label">First Name</label>
              <input type="text" name="firstname" class="form-control form-control-sm" required value="<?php echo isset($firstname) ? $firstname : '' ?>">
            </div>
            <!-- <div class="form-group">
              <label for="middlename" class="control-label">Middle Name</label>
              <input type="text" name="middlename" class="form-control form-control-sm" value="<?php echo isset($middlename) ? $middlename : '' ?>">
            </div> -->
            <div class="form-group">
              <label for="lastname" class="control-label">Last Name</label>
              <input type="text" name="lastname" class="form-control form-control-sm" required value="<?php echo isset($lastname) ? $lastname : '' ?>">
            </div>
            <!-- <div class="form-group">
              <label for="contact" class="control-label">Contact No.</label>
              <input type="text" name="contact" class="form-control form-control-sm" required value="<?php echo isset($contact) ? $contact : '' ?>">
            </div> -->
            <div class="form-group">
              <label for="address" class="control-label">Office</label>
              <textarea name="address" class="form-control" rows="4" required><?php echo isset($address) ? $address : '' ?></textarea>
            </div>
          </div>

          <!-- System Credentials Section -->
          <div class="col-md-6">
            <h5 class="text-muted">System Credentials</h5>
            <div class="form-group">
              <label for="email" class="control-label">Email</label>
              <input type="email" class="form-control form-control-sm" name="email" required value="<?php echo isset($email) ? $email : '' ?>">
              <small id="msg" class="form-text text-danger"></small>
            </div>
            <div class="form-group">
              <label for="password" class="control-label">Password</label>
              <input type="password" class="form-control form-control-sm" name="password" <?php echo isset($id) ? '' : 'required' ?>>
              <small><i><?php echo isset($id) ? "Leave this blank if you don't want to change your password" : '' ?></i></small>
            </div>
            <div class="form-group">
              <label for="cpass" class="control-label">Confirm Password</label>
              <input type="password" class="form-control form-control-sm" name="cpass" <?php echo isset($id) ? 'required' : '' ?>>
              <small id="pass_match" class="form-text text-muted"></small>
            </div>
          </div>
        </div>

        <hr>

        <!-- Buttons Section -->
        <div class="col-lg-12 text-center">
          <button type="submit" class="btn btn-primary">Add Client</button>
          <button type="reset" class="btn btn-secondary">Clear Details</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
  // Password match validation
  $('[name="password"],[name="cpass"]').keyup(function(){
    var pass = $('[name="password"]').val();
    var cpass = $('[name="cpass"]').val();
    if(cpass === '' || pass === '') {
      $('#pass_match').attr('data-status', '');
    } else {
      if(cpass === pass) {
        $('#pass_match').attr('data-status', '1').html('<i class="text-success">Password Matched.</i>');
      } else {
        $('#pass_match').attr('data-status', '2').html('<i class="text-danger">Password does not match.</i>');
      }
    }
  });

  // Image display function (if needed for profile pic upload)
  function displayImg(input, _this) {
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function (e) {
        $('#cimg').attr('src', e.target.result);
      }
      reader.readAsDataURL(input.files[0]);
    }
  }

  // Submit form via AJAX
  $('#manage_customer').submit(function(e){
    e.preventDefault();
    $('input').removeClass("border-danger");
    start_load();
    $('#msg').html('');

    if($('#pass_match').attr('data-status') !== '1') {
      if($("[name='password']").val() !== '') {
        $('[name="password"],[name="cpass"]').addClass("border-danger");
        end_load();
        return false;
      }
    }

    $.ajax({
      url: 'ajax.php?action=save_customer',
      data: new FormData($(this)[0]),
      cache: false,
      contentType: false,
      processData: false,
      method: 'POST',
      success: function(resp) {
        if(resp == 1) {
          alert_toast('Data successfully saved.', "success");
          setTimeout(function(){
            location.replace('index.php?page=customer_list');
          }, 750);
        } else if(resp == 2) {
          $('#msg').html("<div class='alert alert-danger'>Email already exists.</div>");
          $('[name="email"]').addClass("border-danger");
          end_load();
        }
      }
    });
  });
</script>
