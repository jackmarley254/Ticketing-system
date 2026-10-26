<?php
if (!isset($conn)) {
    include 'db_connect.php';
}
?>
<div id="email_banner_alert" class="alert alert-danger d-none" role="alert" style="font-size: 1.1rem;"></div>

<form id="register_staff_email_form">
    <div class="form-group">
        <label for="new_staff_email">Email Address</label>
        <input type="email" class="form-control" id="new_staff_email" name="email" required placeholder="Enter technical staff email">
    </div>
    <div class="text-right">
        <button type="submit" class="btn btn-primary">Submit</button>
    </div>
</form>
<script>
$('#register_staff_email_form').submit(function(e) {
	e.preventDefault();
	let email = $('#new_staff_email').val().trim();
	const banner = $('#email_banner_alert');

	banner.addClass('d-none').text(''); // Reset banner

	if (!email) {
		banner.removeClass('d-none').text("Please enter an email address.");
		return;
	}

	if (!email.endsWith('@cultureheritage.go.ke')) {
		banner.removeClass('d-none').text("Invalid Email Format.");
		return;
	}

	start_load();

	$.ajax({
		url: 'ajax.php?action=save_staff_email',
		method: 'POST',
		data: { email: email },
		success: function(resp) {
			if (resp == 1) {
				alert_toast("Email registered successfully.", "success");
				setTimeout(() => {
					$('.modal').modal('hide');
					location.reload();
				}, 1000);
			} else if (resp == 2) {
				banner.removeClass('d-none').text("Email already exists.");
				end_load();
			} else if (resp == 3) {
				banner.removeClass('d-none').text("Invalid Email Format.");
				end_load();
			} else {
				banner.removeClass('d-none').text("An unexpected error occurred.");
				end_load();
			}
		},
		error: function(err) {
			console.error("AJAX Error", err);
			banner.removeClass('d-none').text("Server error. Try again later.");
			end_load();
		}
	});
});
</script>