<?php
if (!isset($conn)) {
    include 'db_connect.php';
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="col-lg-12">
    <div class="card shadow border-0">
        <div class="card-header bg-primary text-white d-flex align-items-center">
            <i class="fas fa-ticket-alt me-2"></i>
            <h5 class="mb-0">Submit a Support Ticket</h5>
        </div>

        <div class="card-body">
            <form action="" id="manage_ticket">
                <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <select name="subject" class="form-select form-select-sm select2" required>
                                <option value="">-- Select Issue --</option>
                                <option value="Network Issue" <?php echo isset($subject) && $subject == "Network Issue" ? "selected" : '' ?>>Network Issue</option>
                                <option value="System Crash" <?php echo isset($subject) && $subject == "System Crash" ? "selected" : '' ?>>System Crash</option>
                                <option value="Software Installation" <?php echo isset($subject) && $subject == "Software Installation" ? "selected" : '' ?>>Software Installation</option>
                                <option value="Zimbra" <?php echo isset($subject) && $subject == "Zimbra" ? "selected" : '' ?>>Zimbra</option>
                                <option value="Ghris" <?php echo isset($subject) && $subject == "Ghris" ? "selected" : '' ?>>Ghris</option>
                                <option value="Office related problems" <?php echo isset($subject) && $subject == "Office related problems" ? "selected" : '' ?>>Office related problems</option>
                                <option value="Other" <?php echo isset($subject) && $subject == "Other" ? "selected" : '' ?>>Other (Please specify below)</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Client</label>
                            <input type="text" name="user" class="form-control form-control-sm" value="<?php echo isset($_SESSION['login_firstname']) && isset($_SESSION['login_lastname']) ? ucwords($_SESSION['login_firstname'] . ' ' . $_SESSION['login_lastname']) : ''; ?>" readonly>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">Office</label>
                            <select name="department_id" id="department_id" class="form-select form-select-sm select2">
                                <option value="">-- Select office --</option>
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
                    </div>

                    <div class="col-md-12" id="description_field">
                        <div class="form-group">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" id="description" cols="30" rows="4" class="form-control" required><?php echo isset($description) ? $description : '' ?></textarea>
                        </div>
                    </div>

                </div>

                <hr>

                <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-paper-plane me-1"></i> Submit Your Ticket
                    </button>
                    <button class="btn btn-secondary" type="reset">
                        <i class="fas fa-undo-alt me-1"></i> Clear
                    </button>
                </div>
            </form>

            <audio id="ticket-sound" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg" preload="auto"></audio>
        </div>
    </div>
</div>

<script>
   $(document).ready(function () {
    $('#manage_ticket').submit(function (e) {
        e.preventDefault();

        const subject = $('select[name="subject"]').val();
        const description = $('#description').val().trim();

        if (!subject) {
            alert_toast("Please select a subject.", "warning");
            return;
        }

        if (!description) {
            alert_toast("Please enter a description.", "warning");
            return;
        }

        start_load();
        $.ajax({
            url: 'ajax.php?action=save_ticket',
            method: 'POST',
            data: $(this).serialize(),
            success: function (resp) {
                end_load();
                if (resp == 1) {
                    document.getElementById('ticket-sound').play();
                    alert_toast('New ticket has been raised successfully!', "success");
                    setTimeout(() => location.replace('index.php?page=ticket_list'), 1500);
                } else {
                    alert_toast('Failed to save. Please try again.', "danger");
                }
            },
            error: function () {
                end_load();
                alert_toast('An error occurred. Please check your connection or try again.', "danger");
            }
        });
    });
});

</script>