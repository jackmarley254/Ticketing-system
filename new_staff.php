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
			<h5 class="mb-0">Manage Staff</h5>
		</div>
		<div class="card-body">
			<form action="" id="manage_staff">
				<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">

				<div class="row">
					<div class="col-md-6 border-right">
						<h6 class="text-muted mb-3">Personal Information</h6>
						<div class="form-group">
							<label for="firstname" class="control-label">First Name</label>
							<input type="text" name="firstname" id="firstname" class="form-control" required value="<?php echo isset($firstname) ? $firstname : '' ?>">
						</div>

						<div class="form-group">
							<label for="lastname" class="control-label">Last Name</label>
							<input type="text" name="lastname" id="lastname" class="form-control" required value="<?php echo isset($lastname) ? $lastname : '' ?>">
						</div>
					</div>

					<div class="col-md-6">
						<div class="form-group">
							<label for="department_id" class="control-label">Office</label>
							<select name="department_id" id="department_id" class="form-control select2">
								<option value="">Select Office</option>
								<?php
								$department = $conn->query("SELECT * FROM departments ORDER BY name ASC");
								while ($row = $department->fetch_assoc()):
								?>
									<option value="<?php echo $row['id'] ?>" <?php echo isset($department_id) && $department_id == $row['id'] ? "selected" : '' ?>>
										<?php echo ucwords($row['name']) ?>
									</option>
								<?php endwhile; ?>
							</select>
						</div>

						<h6 class="text-muted mb-3">System Credentials</h6>

						<div class="form-group">
							<label for="email" class="control-label">Email</label>
							<input list="staff_emails" name="email" id="email" class="form-control" required value="<?php echo isset($email) ? $email : '' ?>">
							<datalist id="staff_emails">
								<?php
								$emails = $conn->query("SELECT email FROM staff_emails ORDER BY email ASC");
								while ($row = $emails->fetch_assoc()):
								?>
									<option value="<?php echo $row['email'] ?>"></option>
								<?php endwhile; ?>
							</datalist>
							<small id="msg" class="text-danger font-weight-bold"></small>
						</div>

						<div class="form-group">
							<label for="password" class="control-label">Password</label>
							<input type="password" class="form-control" name="password" id="password" <?php echo isset($id) ? "" : 'required' ?>>
							<small class="text-muted"><i><?php echo isset($id) ? "Leave this blank if you don't want to change the password." : '' ?></i></small>
						</div>

						<div class="form-group">
							<label for="cpass" class="control-label">Confirm Password</label>
							<input type="password" class="form-control" name="cpass" id="cpass" <?php echo isset($id) ? 'required' : '' ?>>
							<small id="pass_match" data-status=''></small>
						</div>
					</div>
				</div>

				<hr>
				<!-- Action buttons and register email -->
				<div class="text-center mt-4">
					<button class="btn btn-primary mr-2">Save Details</button>
					<button class="btn btn-secondary mr-2" type="reset">Clear Details</button>
					<button type="button" class="btn btn-success" id="register_email_btn">
						Register Technical Staff Email
					</button>
				</div>
			</form>
		</div>
	</div>
</div>

<!-- MODAL HTML CONTAINER (only needed once in layout) -->
<div class="modal fade" id="uni_modal" role="dialog">
	<div class="modal-dialog modal-md modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header bg-primary text-white">
				<h5 class="modal-title" id="uni_modal_title">Modal Title</h5>
				<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body" id="uni_modal_body">
				<!-- Modal content loads here -->
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	$('.select2').select2({ width: '100%' });

	// Password confirmation
	$('[name="password"], [name="cpass"]').on('keyup', function() {
		const pass = $('[name="password"]').val();
		const cpass = $('[name="cpass"]').val();
		const $msg = $('#pass_match');

		if (!pass || !cpass) {
			$msg.attr('data-status', '').html('');
		} else if (pass === cpass) {
			$msg.attr('data-status', '1').html('<i class="text-success">Password Matched.</i>');
		} else {
			$msg.attr('data-status', '2').html('<i class="text-danger">Password does not match.</i>');
		}
	});

	// Live email validation
	$('#email').on('keyup', function() {
		const email = $(this).val().trim();
		if (email === '') {
			$('#msg').html('');
			$(this).removeClass('border-danger');
			return;
		}

		$.ajax({
			url: 'ajax.php?action=check_staff_email',
			method: 'POST',
			data: { email: email },
			success: function(resp) {
				if (resp == '1') {
					$('#msg').html('');
					$('#email').removeClass('border-danger');
				} else {
					$('#msg').html('This Email is not in the approved Technical staff list.');
					$('#email').addClass('border-danger');
				}
			}
		});
	});

	// Submit staff form
	$('#manage_staff').submit(function(e) {
		e.preventDefault();
		$('input, select').removeClass("border-danger");
		$('#msg').html('');
		start_load();

		const passwordStatus = $('#pass_match').attr('data-status');
		const password = $('[name="password"]').val();

		if (password && passwordStatus != '1') {
			$('[name="password"], [name="cpass"]').addClass("border-danger");
			end_load();
			return false;
		}

		$.ajax({
			url: 'ajax.php?action=save_staff',
			data: new FormData(this),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			success: function(resp) {
				if (resp == 1) {
					alert_toast('Data successfully saved.', "success");
					setTimeout(() => location.replace('index.php?page=staff_list'), 750);
				} else if (resp == 2) {
					$('#msg').html("<div class='text-danger'>Email already exists.</div>");
					$('#email').addClass("border-danger");
					end_load();
				} else if (resp == 3) {
					$('#msg').html("<div class='text-danger'>Email is not in the approved Technical staff list.</div>");
					$('#email').addClass("border-danger");
					end_load();
				} else {
					$('#msg').html("<div class='text-danger'>Unexpected error occurred.</div>");
					end_load();
				}
			}
		});
	});

	// Show modal on button click
	$('#register_email_btn').click(function () {
		uni_modal("Register Technical Staff Email", "register_technical_staff_email.php");
	});
});

// `uni_modal()` function definition (if not already defined globally)
function uni_modal(title, url) {
	$('#uni_modal .modal-title').text(title);
	$.ajax({
		url: url,
		error: err => {
			console.error("Modal Load Error", err);
			alert("An error occurred.");
		},
		success: function(resp) {
			$('#uni_modal .modal-body').html(resp);
			$('#uni_modal').modal('show');
		}
	});
}
</script>
