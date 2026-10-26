<?php 
include('db_connect.php');
// session_start();

$utype = array('', 'users', 'staff', 'customers');
$meta = [];

if (isset($_GET['id']) && isset($_SESSION['login_type']) && isset($utype[$_SESSION['login_type']])) {
    $table = $utype[$_SESSION['login_type']];
    $id = (int)$_GET['id'];
    $user = $conn->query("SELECT * FROM $table WHERE id = $id");

    if ($user && $user->num_rows > 0) {
        foreach ($user->fetch_array() as $k => $v) {
            $meta[$k] = $v;
        }
    }
}
?>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
    body {
        background-color: #f4f6f9;
        font-family: 'Segoe UI', sans-serif;
        padding: 2rem;
    }

    .form-card {
        background: white;
        max-width: 500px;
        margin: 0 auto;
        padding: 2rem 2rem 1.5rem;
        border-radius: 0.75rem;
        box-shadow: 0 0.75rem 2rem rgba(0, 0, 0, 0.1);
        border-top: 4px solid #198754;
    }

    .form-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #198754;
        margin-bottom: 1rem;
        text-align: center;
    }

    .toggle-password {
        cursor: pointer;
    }

    #msg {
        margin-bottom: 1rem;
    }

    .btn-success {
        background-color: #198754;
        border-color: #198754;
    }

    .btn-success:hover {
        background-color: #157347;
        border-color: #146c43;
    }
</style>

<div class="form-card mt-0">
    <div class="form-title">Update Your Password</div>
    <div id="msg"></div>

    <form action="" id="manage-user">
        <input type="hidden" name="id" value="<?php echo isset($meta['id']) ? $meta['id'] : '' ?>">

        <div class="mb-3">
            <label for="new_password" class="form-label">New Password</label>
            <div class="input-group">
                <input type="password" name="new_password" id="new_password" class="form-control" autocomplete="off" required>
                <span class="input-group-text toggle-password" data-target="#new_password">Show</span>
            </div>
            <div id="new-password-strength" class="form-text text-muted">
                Must be 8+ characters, include upper/lowercase, number, and symbol.
            </div>
        </div>

        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <div class="input-group">
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" autocomplete="off" required>
                <span class="input-group-text toggle-password" data-target="#confirm_password">Show</span>
            </div>
            <div id="password-match" class="form-text"></div>
        </div>

        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success">Update Password</button>
            <button type="button" class="btn btn-secondary ms-2" onclick="cancelForm()">Cancel</button>
        </div>
    </form>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function () {
    const passwordInput = $('#new_password');
    const confirmInput = $('#confirm_password');
    const strengthFeedback = $('#new-password-strength');
    const matchFeedback = $('#password-match');
    const msgBox = $('#msg');

    function validatePasswordStrength(password) {
        const regex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#^+=])[A-Za-z\d@$!%*?&#^+=]{8,}$/;
        if (regex.test(password)) {
            strengthFeedback.text('Strong password.').removeClass('text-danger').addClass('text-success');
            return true;
        } else {
            strengthFeedback.text('Must be 8+ characters, include uppercase, lowercase, number, and symbol.').removeClass('text-success').addClass('text-danger');
            return false;
        }
    }

    function validateMatch() {
        const newPass = passwordInput.val();
        const confirmPass = confirmInput.val();
        if (confirmPass.length === 0) {
            matchFeedback.text('').removeClass('text-success text-danger');
            return;
        }
        if (newPass === confirmPass) {
            matchFeedback.text('Passwords match.').removeClass('text-danger').addClass('text-success');
        } else {
            matchFeedback.text('Passwords do not match.').removeClass('text-success').addClass('text-danger');
        }
    }

    passwordInput.on('input', function () {
        validatePasswordStrength($(this).val());
        validateMatch();
    });

    confirmInput.on('input', function () {
        validateMatch();
    });

    $('#manage-user').submit(function (e) {
        e.preventDefault();
        const newPassword = passwordInput.val().trim();
        const confirmPassword = confirmInput.val().trim();

        if (!validatePasswordStrength(newPassword)) {
            showError('Password requirements not met.');
            return;
        }

        if (newPassword !== confirmPassword) {
            showError('Passwords do not match.');
            return;
        }

        $.ajax({
            url: 'ajax.php?action=update_password_only',
            method: 'POST',
            data: $(this).serialize(),
            success: function (resp) {
                if (resp == 1) {
                    msgBox.html(`<div class="alert alert-success">Password successfully updated.</div>`);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showError('An error occurred. Please try again.');
                }
            },
            error: function () {
                showError('An error occurred while saving. Please try again.');
            }
        });
    });

    $(document).on('click', '.toggle-password', function () {
        const input = $($(this).data("target"));
        const isPassword = input.attr("type") === "password";
        input.attr("type", isPassword ? "text" : "password");
        $(this).text(isPassword ? "Hide" : "Show");
    });

    function showError(message) {
        msgBox.html(`<div class="alert alert-danger">${message}</div>`);
    }
});

function cancelForm() {
    window.history.back();
}
</script>
