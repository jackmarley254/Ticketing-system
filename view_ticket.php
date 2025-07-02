<?php 
session_start();
include 'db_connect.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Ticket ID.");
}

$ticket_id = (int) $_GET['id'];

// Fetch Ticket Details
$stmt = $conn->prepare("SELECT t.*, CONCAT(c.lastname, ', ', c.firstname, ' ', c.middlename) AS cname, d.name AS dname 
                        FROM tickets t 
                        INNER JOIN customers c ON c.id = t.customer_id 
                        INNER JOIN departments d ON d.id = t.department_id  
                        WHERE t.id = ?");
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    die("Ticket not found.");
}
$ticket = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Ticket Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 8px; }
        .form-control, .btn-outline-secondary, .btn-outline-primary { border-radius: 25px; }
        .comment-box { background-color: #e9ecef; border-radius: 8px; padding: 15px; }
        .comment-box-header { background-color: #007bff; color: #fff; padding: 10px; border-radius: 8px 8px 0 0; }
        .comment-body { background-color: #fff; padding: 10px; border-radius: 8px; margin-top: 10px; }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="mb-4">
        <a href="index.php?page=ticket_list" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-2"></i> Back to Tickets
        </a>
    </div>

    <div class="row g-4">
        <!-- Ticket Details -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5><i class="fas fa-ticket-alt me-2"></i> Ticket Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <?php 
                        $details = [
                            ['Subject', htmlspecialchars($ticket['subject'])],
                            ['Requested By', htmlspecialchars($ticket['cname'])],
                            ['Department / Office', htmlspecialchars($ticket['dname'])],
                            ['Date Created', date("F d, Y", strtotime($ticket['date_created']))],
                        ];
                        foreach ($details as $detail): ?>
                            <div class="col-md-6">
                                <div class="text-muted small"><?= $detail[0] ?></div>
                                <div class="fs-6 fw-semibold"><?= $detail[1] ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <hr class="my-4">

                    <h6 class="text-muted mb-2"><i class="fas fa-exclamation-circle me-2"></i> Problem Description</h6>
                    <div class="bg-light border rounded p-3">
                        <?= html_entity_decode($ticket['description']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comments -->
        <div class="col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-comments me-2"></i> Comments</h6>
                </div>
                <div class="card-body" style="max-height: 450px; overflow-y: auto;" id="comments-list">
                    <?php 
                    $comments = $conn->query("SELECT * FROM comments WHERE ticket_id = $ticket_id ORDER BY date_created ASC");

                    while ($row = $comments->fetch_assoc()):
                        $uname = 'Unknown';
                        $user_tables = ['1' => 'users', '2' => 'staff', '3' => 'customers'];
                        $table = $user_tables[$row['user_type']] ?? null;

                        if ($table) {
                            $user_q = $conn->query("SELECT CONCAT(lastname, ', ', firstname, ' ', middlename) AS name FROM $table WHERE id = {$row['user_id']}");
                            if ($user_q && $user_q->num_rows) {
                                $uname = $user_q->fetch_assoc()['name'];
                            }
                        }
                    ?>
                    <div class="comment-box mb-3">
                        <div class="comment-box-header d-flex justify-content-between">
                            <strong><i class="fas fa-user-circle me-1"></i> <?= ucwords(htmlspecialchars($uname)) ?></strong>
                            <small><i class="far fa-clock me-1"></i> <?= date("M d, Y", strtotime($row['date_created'])) ?></small>
                        </div>
                        <div class="comment-body"><?= html_entity_decode($row['comment']) ?></div>

                        <?php if ($_SESSION['login_type'] == $row['user_type'] && $_SESSION['login_id'] == $row['user_id']): ?>
                        <div class="text-end mt-2">
                            <button class="btn btn-sm btn-outline-secondary edit_comment" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-edit me-1"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete_comment" data-id="<?= $row['id'] ?>">
                                <i class="fas fa-trash-alt me-1"></i> Delete
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Comment Form -->
                <div class="card-footer bg-white border-top">
                    <form id="manage-comment">
                        <input type="hidden" name="ticket_id" value="<?= $ticket_id ?>">
                        <div class="mb-3">
                            <label class="form-label fw-bold" for="comment"><i class="fas fa-plus-circle me-1"></i> Add a Comment</label>
                            <textarea name="comment" id="comment" class="form-control summernote2" required></textarea>
                        </div>
                        <div class="text-end">
                            <button class="btn btn-sm btn-primary"><i class="fas fa-paper-plane me-1"></i> SubmitYour Comment</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Edit -->
<div class="modal fade" id="uni_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">Loading...</div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs5.min.js"></script>


<!-- SweetAlert2 for modern popups -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(function () {
    $('.summernote2').summernote({
        height: 150,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'strikethrough', 'clear']],
            ['fontsize', ['fontsize']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['view', ['undo', 'redo']]
        ]
    });

    $('#manage-comment').submit(function(e) {
        e.preventDefault();
        $.post('ajax.php?action=save_comment', $(this).serialize(), function(resp) {
            if (resp == 1) {
                Swal.fire({
                    icon: 'success',
                    title: 'Comment Added',
                    text: 'Your comment has been posted successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
                setTimeout(() => location.reload(), 2000);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed',
                    text: 'Could not post the comment. Try again.',
                });
            }
        });
    });

    $('.edit_comment').click(function(){
        $('#uni_modal .modal-title').text('Edit Comment');
        $('#uni_modal .modal-body').load('manage_comment.php?id=' + $(this).data('id'), function() {
            $('#uni_modal').modal('show');
        });
    });

    $('.delete_comment').click(function(){
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "Do you really want to delete this comment?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax.php?action=delete_comment', { id }, function(resp){
                    if (resp == 1) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted',
                            text: 'The comment was deleted.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: 'Could not delete comment.'
                        });
                    }
                });
            }
        });
    });
});
</script>

</body>
</html>
