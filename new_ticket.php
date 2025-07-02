<?php
if (!isset($conn)) {
    include 'db_connect.php';
}
?>
<div class="col-lg-12">
    <div class="card">
        <div class="card-body">
            <form action="" id="manage_ticket">
                <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="" class="control-label">Subject <span class="text-danger">*</span></label>
                        <select name="subject" class="custom-select custom-select-sm select2" required>
                            <option value=""></option>
                            <option value="Network Issue" <?php echo isset($subject) && $subject == "Network Issue" ? "selected" : '' ?>>Network Issue</option>
                            <option value="System Crash" <?php echo isset($subject) && $subject == "System Crash" ? "selected" : '' ?>>System Crash</option>
                            <option value="Software Installation" <?php echo isset($subject) && $subject == "Software Installation" ? "selected" : '' ?>>Software Installation</option>
                            <option value="Zimbra" <?php echo isset($subject) && $subject == "Zimbra" ? "selected" : '' ?>>Zimbra</option>
                            <option value="Office related problems" <?php echo isset($subject) && $subject == "Office related problems" ? "selected" : '' ?>>Office related problems</option>
                            <option value="Other" <?php echo isset($subject) && $subject == "Other" ? "selected" : '' ?>>Other (Please specify below)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="" class="control-label">Client</label>
                        <input type="text" name="user" class="form-control form-control-sm" value="<?php echo isset($_SESSION['login_firstname']) && isset($_SESSION['login_lastname']) ? ucwords($_SESSION['login_firstname'] . ' ' . $_SESSION['login_lastname']) : ''; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label for="" class="control-label">Office</label>
                        <select name="department_id" id="department_id" class="custom-select custom-select-sm select2">
                            <option value=""></option>
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

                <div class="col-md-12">
                    <div class="form-group" id="description_field" style="display: none;">
                        <label class="control-label">Description <span id="desc_required" class="text-danger" style="display:none">*</span></label>
                        <textarea name="description" id="description" cols="30" rows="5" class="form-control"><?php echo isset($description) ? $description : '' ?></textarea>
                    </div>
                </div>

                <hr>
                <div class="col-lg-12 text-right justify-content-center d-flex">
                    <button class="btn btn-primary mr-2" type="submit">Submit Your Ticket</button>
                    <button class="btn btn-secondary" type="reset">Clear Your Ticket</button>
                </div>
            </form>
        <audio id="ticket-sound" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg" preload="auto"></audio>




        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        function toggleDescriptionField() {
            const subject = $('select[name="subject"]').val();
            if (subject === 'Other') {
                $('#description_field').show();
                $('#desc_required').show();
            } else {
                $('#description_field').hide();
                $('#desc_required').hide();
            }
        }

        toggleDescriptionField();
        $('select[name="subject"]').on('change', toggleDescriptionField);

        $('#manage_ticket').submit(function (e) {
            e.preventDefault();

            const subject = $('select[name="subject"]').val();
            if (!subject) {
                alert_toast("Please select a subject.", "warning");
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
                        // Play the sound
                        document.getElementById('ticket-sound').play();

                        // Show visual toast notification
                        alert_toast('New ticket has been raised successfully!', "success");

                        // Redirect after short delay
                        setTimeout(() => location.replace('index.php?page=ticket_list'), 1500);
                    } else {
                        alert_toast('Failed to save. Please try again.', "danger");
                    }
                },
                error: function () {
                    end_load();
                    alert_toast('An error occurred while saving. Check your internet or try again.', "danger");
                }
            });
        });
    });
</script>

