<?php
session_start();
include 'db_connect.php';

$user_type = $_SESSION['login_type'] ?? 0;
$user_id = $_SESSION['login_id'] ?? 0;

$total_tickets = 0;
$count_qry = $conn->query("SELECT COUNT(*) as total FROM tickets");
if ($count_qry && $row = $count_qry->fetch_assoc()) {
    $total_tickets = $row['total'];
}

$staff_qry = $conn->query("SELECT id, CONCAT(firstname, ' ', lastname) as name FROM staff");
$staff_list = [];
while ($staff = $staff_qry->fetch_assoc()) {
    $staff_list[] = $staff;
}

$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$where = [];

if ($from_date && $to_date) {
    $from = $conn->real_escape_string($from_date);
    $to = $conn->real_escape_string($to_date);
    $where[] = "DATE(t.date_created) BETWEEN '$from' AND '$to'";
}

if ($user_type == 3) {
    // Customer view only their own tickets
    $where[] = "t.customer_id = '$user_id'";
}

$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

function generateAlphanumericTicketID($index)
{
    $row = (($index - 1) % 500) + 1;
    $colIndex = intval(($index - 1) / 500);
    $letters = '';
    while ($colIndex >= 0) {
        $letters = chr(65 + ($colIndex % 26)) . $letters;
        $colIndex = intval($colIndex / 26) - 1;
    }
    return $letters . $row;
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    @media print {

        #ticket-summary,
        #print-button,
        .status-btn,
        .dropdown,
        .modal,
        .modal-backdrop,
        a.btn,
        th:nth-child(7),
        th:nth-child(8),
        td:nth-child(7),
        td:nth-child(8) {
            display: none !important;
        }

        .print-header {
            display: block;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 20px;
        }
    }
</style>

<h4 class="print-header text-center mb-3 mt-3">ICT Office - Support Task List</h4>

<div class="col-lg-12">
    <div class="card card-outline card-info">
        <div class="card-body">
            <?php if ($user_type != 3): ?>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="alert alert-info mb-0" id="ticket-summary">
                        <strong>Total Tickets Raised:</strong> <?php echo $total_tickets; ?>
                    </div>

                    <button class="btn btn-outline-primary" onclick="showDateModal()" id="print-button">
                        <i class="fas fa-print"></i> Print Task List
                    </button>
                </div>
            <?php endif; ?>

            <a href="index.php?page=ticket_list" class="btn btn-secondary mb-3">
                ← Back to All Tickets
            </a>

            <table class="table table-hover table-bordered" id="list">

                <table class="table table-hover table-bordered" id="list">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date Created</th>
                            <th>Client</th>
                            <th>Subject</th>
                            <th>Description</th>
                            <th>Assigned To</th>
                            <th>Status</th>
                            <th>View</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        $qry = $conn->query("
                    SELECT t.*, CONCAT(s.firstname, ' ', s.lastname) AS assigned_staff_name 
                    FROM tickets t 
                    LEFT JOIN staff s ON s.id = t.assigned_to 
                    $where_sql
                    ORDER BY UNIX_TIMESTAMP(t.date_created) DESC
                ") or die('MySQL Error: ' . $conn->error);

                        while ($row = $qry->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo generateAlphanumericTicketID($i++); ?></td>
                                <td><?php echo date("M d, Y", strtotime($row['date_created'])); ?></td>
                                <td><?php echo $row['user']; ?></td>
                                <td><?php echo $row['subject']; ?></td>
                                <td><?php echo strip_tags(html_entity_decode($row['description'])); ?></td>
                                <td>
                                    <?php if ($row['assigned_to']): ?>
                                        <button class="btn btn-secondary btn-sm" disabled>
                                            <?php echo $row['assigned_staff_name']; ?>
                                        </button>
                                    <?php elseif ($user_type != 3): ?>
                                        <div class="dropdown">
                                            <button class="btn btn-outline-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Assign
                                            </button>
                                            <ul class="dropdown-menu staff-dropdown" data-ticket-id="<?php echo $row['id']; ?>">
                                                <?php foreach ($staff_list as $staff): ?>
                                                    <li><a class="dropdown-item assign-staff" href="#" data-staff-id="<?php echo $staff['id']; ?>" data-ticket-id="<?php echo $row['id']; ?>"><?php echo $staff['name']; ?></a></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted">Unassigned</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                        <button class="btn btn-sm mr-1 status-btn <?php echo $row['status'] == 2 ? 'btn-success' : 'btn-outline-success'; ?>" data-id="<?php echo $row['id']; ?>" data-status="2">Solved</button>
                                        <button class="btn btn-sm status-btn <?php echo $row['status'] == 1 ? 'btn-danger' : 'btn-outline-danger'; ?>" data-id="<?php echo $row['id']; ?>" data-status="1">Not Solved</button>
                                    </div>
                                </td>

                                <td class="text-center">
                                    <a href="view_ticket.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
        </div>
    </div>
</div>

<!-- Date Range Modal -->
<div class="modal fade" id="dateRangeModal" tabindex="-1" aria-labelledby="dateRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="dateRangeForm" method="GET">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Date Range to Print</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="from_date" class="form-label">From:</label>
                        <input type="date" class="form-control" name="from_date" required>
                    </div>
                    <div class="mb-3">
                        <label for="to_date" class="form-label">To:</label>
                        <input type="date" class="form-control" name="to_date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Print</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function showDateModal() {
        const modal = new bootstrap.Modal(document.getElementById('dateRangeModal'));
        modal.show();
    }

    document.getElementById('dateRangeForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const from_date = document.querySelector('[name="from_date"]').value;
        const to_date = document.querySelector('[name="to_date"]').value;

        if (from_date && to_date) {
            window.location.href = `ticket_list.php?from_date=${from_date}&to_date=${to_date}`;
        }
    });

    <?php if ($from_date && $to_date): ?>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 1000);

        };
    <?php endif; ?>

    document.querySelectorAll('.assign-staff').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const staffId = this.dataset.staffId;
            const ticketId = this.dataset.ticketId;

            fetch('ajax1.php?action=assign_ticket', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `ticket_id=${ticketId}&staff_id=${staffId}`
                })
                .then(res => res.text())
                .then(() => {
                    Swal.fire('Assigned!', 'Ticket has been assigned.', 'success').then(() => location.reload());
                })
                .catch(() => Swal.fire('Error', 'Assignment failed.', 'error'));
        });
    });

    document.querySelectorAll('.status-btn').forEach(button => {
        button.addEventListener('click', function() {
            const ticketId = this.dataset.id;
            const newStatus = this.dataset.status;
            const row = this.closest('tr');

            fetch('ajax.php?action=update_ticket_status', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `ticket_id=${ticketId}&status=${newStatus}`
                })
                .then(() => {
                    Swal.fire('Updated!', 'Ticket status updated.', 'success');
                    const solvedBtn = row.querySelector('[data-status="2"]');
                    const notSolvedBtn = row.querySelector('[data-status="1"]');

                    if (newStatus === "2") {
                        solvedBtn.classList.add('btn-success');
                        solvedBtn.classList.remove('btn-outline-success');
                        notSolvedBtn.classList.add('btn-outline-danger');
                        notSolvedBtn.classList.remove('btn-danger');
                    } else {
                        notSolvedBtn.classList.add('btn-danger');
                        notSolvedBtn.classList.remove('btn-outline-danger');
                        solvedBtn.classList.add('btn-outline-success');
                        solvedBtn.classList.remove('btn-success');
                    }
                })
                .catch(() => Swal.fire('Error!', 'Failed to update ticket status.', 'error'));
        });
    });
</script>