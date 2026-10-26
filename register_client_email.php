<?php
if (!isset($conn)) {
	include 'db_connect.php';
}
?>

<!-- Banner alert -->
<div id="client_email_alert_banner" class="alert alert-danger d-none" role="alert"></div>

<form id="register_client_email_form">
	<div class="form-group">
		<label for="new_client_email">Staff Email Address</label>
		<input type="email" class="form-control" id="new_client_email" name="email" required placeholder="Enter client email">
	</div>
	<div class="text-right">
		<button type="submit" class="btn btn-primary">Submit</button>
	</div>
</form>

<script>
$(document).ready(function () {
	const banner = $('#client_email_alert_banner');
	const emailField = $('#new_client_email');

	$('#register_client_email_form').submit(function (e) {
		e.preventDefault();

		const email = emailField.val().trim();
		const domain = '@cultureheritage.go.ke';

		// Reset banner
		banner.addClass('d-none').text('');
		emailField.removeClass('border-danger');

		// Basic email format validation
		const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
		if (!emailRegex.test(email)) {
			banner.removeClass('d-none').text('Please enter a valid email format.');
			emailField.addClass('border-danger');
			return;
		}

		// Domain restriction check
		if (!email.endsWith(domain)) {
			banner.removeClass('d-none').text('Invalid Email Format.Try Again!');
			emailField.addClass('border-danger');
			return;
		}

		start_load();

		// Submit via AJAX
		$.ajax({
			url: 'ajax.php?action=save_client_email',
			method: 'POST',
			data: { email: email },
			success: function (resp) {
				if (resp == 1) {
					alert_toast("Staff email registered successfully.", "success");
					setTimeout(() => {
						$('.modal').modal('hide');
						location.reload();
					}, 1000);
				} else if (resp == 2) {
					banner.removeClass('d-none').text("This email is already registered.");
					emailField.addClass("border-danger");
					end_load();
				} else {
					banner.removeClass('d-none').text("An unexpected error occurred.");
					end_load();
				}
			},
			error: function (err) {
				console.error("AJAX Error", err);
				banner.removeClass('d-none').text("Server error. Try again later.");
				end_load();
			}
		});
	});
});
</script>
