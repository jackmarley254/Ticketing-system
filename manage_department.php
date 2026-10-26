<?php include 'db_connect.php'; ?>
<?php
if (isset($_GET['id'])) {
    $qry = $conn->query("SELECT * FROM departments WHERE id = " . $_GET['id'])->fetch_array();
    foreach ($qry as $k => $v) {
        $$k = $v;
    }
}
?>

<!-- Container -->
<div class="container my-4">
    <div class="card shadow-sm rounded">
        <div class="card-body">
            <form id="manage-department">
                <input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">

                <div class="form-group mb-3">
                    <label class="form-label">Office Name</label>
                    <input type="text" class="form-control form-control-sm" name="name" value="<?php echo isset($name) ? $name : '' ?>" required>
                </div>

                <div class="form-group mb-3">
                    <label class="form-label">Location(Floor)</label>
                    <textarea name="description" class="form-control" rows="1"><?php echo isset($description) ? $description : '' ?></textarea>
                </div>

                <div id="msg"></div>

                <!-- Action Buttons -->
                <div class="d-flex justify-content-between mt-4">
                    <div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Save
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="location.href='index.php?page=department_list'">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </div>

                    <!-- Removed Edit and Delete buttons -->
                    <!-- 
                    <?php if (isset($id)) : ?>
                        <div>
                            <a href="index.php?page=manage_department&id=<?php echo $id ?>" class="btn btn-info">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <button type="button" class="btn btn-danger" onclick="delete_department(<?php echo $id ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    <?php endif; ?>
                    -->
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script>
    $('#manage-department').submit(function (e) {
        e.preventDefault();
        $('input').removeClass("border-danger");
        start_load();
        $('#msg').html('');
        $.ajax({
            url: 'ajax.php?action=save_department',
            data: new FormData($(this)[0]),
            cache: false,
            contentType: false,
            processData: false,
            method: 'POST',
            success: function (resp) {
                if (resp == 1) {
                    alert_toast('Office successfully saved.', "success");
                    setTimeout(() => {
                        location.replace('index.php?page=department_list');
                    }, 750);
                } else if (resp == 2) {
                    $('#msg').html("<div class='alert alert-danger'>Office already exists.</div>");
                    $('[name="name"]').addClass("border-danger");
                    end_load();
                }
            }
        });
    });

    function delete_department(id) {
        if (confirm('Are you sure you want to delete this office?')) {
            start_load();
            $.ajax({
                url: 'ajax.php?action=delete_department',
                method: 'POST',
                data: { id: id },
                success: function (resp) {
                    if (resp == 1) {
                        alert_toast('Office successfully deleted.', 'success');
                        setTimeout(() => {
                            location.replace('index.php?page=department_list');
                        }, 750);
                    }
                }
            });
        }
    }
</script>
