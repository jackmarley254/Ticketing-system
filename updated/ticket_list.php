<?php
// session_start();
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

$subject_qry = $conn->query("SELECT DISTINCT subject FROM tickets WHERE subject IS NOT NULL AND subject != ''");
$subject_list = [];
while ($sub = $subject_qry->fetch_assoc()) {
    $subject_list[] = $sub['subject'];
}

// Filters
$where = [];
$selected_subject = $_GET['subject'] ?? '';
$selected_staff   = $_GET['assigned_to'] ?? '';
$from_date        = $_GET['from_date'] ?? '';
$to_date          = $_GET['to_date'] ?? '';

if (!empty($selected_subject)) {
    $safe_subject = $conn->real_escape_string($selected_subject);
    $where[] = "t.subject = '$safe_subject'";
}

if (!empty($selected_staff)) {
    $safe_staff = (int)$selected_staff;
    $where[] = "t.assigned_to = '$safe_staff'";
}

if ($from_date && $to_date) {
    $from = $conn->real_escape_string($from_date);
    $to   = $conn->real_escape_string($to_date);
    $where[] = "DATE(t.date_created) BETWEEN '$from' AND '$to'";
}

if ($user_type == 3) {
    $where[] = "t.customer_id = '$user_id'";
}

$where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Function for custom ticket ID
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
    /* Print styles */
    @media print {

        #ticket-summary,
        #print-button,
        .status-btn,
        .dropdown,
        .modal,
        .modal-backdrop,
        a.btn,
        /* Hide Assigned To (6th col) and View (10th col) */
        th:nth-child(6),
        td:nth-child(6),
        th:nth-child(10),
        td:nth-child(10),
        /* Hide edit/enter buttons inside channel & action */
        #list td button {
            display: none !important;
        }

        /* Show plain-text status instead of buttons */
        .status-text {
            display: inline !important;
        }
    }


    .print-header {
        display: block;
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 20px;
    }



    /* Make table smaller */
    #list {
        max-width: 95%;
        margin: auto;
    }

    #list th,
    #list td {
        font-size: 0.85rem;
        padding: 0.4rem;
        vertical-align: middle;
    }
</style>

<h2 class="print-header text-center mb-3 mt-3">MINISTY OF GENDER, CULTURE AND CHILDREN SERVICES</h2>
<h4 class="print-header text-center mb-3 mt-3">STATE DEPARTMRNT FOR CULTURE AND HERITAGE</h4>
<h4 class="print-header text-center mb-3 mt-3">ICT INTERNAL COMPLAINS</h4>

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

            <a href="index.php?page=ticket_list" class="btn btn-secondary mb-3">← Back to All Tickets</a>

            <table class="table table-sm table-hover table-bordered" id="list">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date Received</th>
                        <th>Name of Complainant</th>
                        <th>Brief Description</th>
                        <th>Description</th>
                        <th>Assigned To</th>
                        <th>Complaint Channel</th>
                        <th>Action Taken</th>
                        
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

                    while ($row = $qry->fetch_assoc()):
                    ?>
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
                                                <li><a class="dropdown-item assign-staff" href="#"
                                                        data-staff-id="<?php echo $staff['id']; ?>"
                                                        data-ticket-id="<?php echo $row['id']; ?>"><?php echo $staff['name']; ?></a></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>

                            <!-- Complaint Channel -->
                            <td id="channel-<?php echo $row['id']; ?>">
                                <?php if (!empty($row['complaint_channel'])): ?>
                                    <div class="d-flex align-items-start justify-content-between">
                                        <span><?php echo nl2br(htmlspecialchars($row['complaint_channel'])); ?></span>
                                        <button class="btn btn-link btn-sm p-0 ms-2" onclick="openModal('channel','<?php echo $row['id']; ?>')">Edit</button>
                                    </div>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-info" onclick="openModal('channel','<?php echo $row['id']; ?>')">Enter Complaint Channel</button>
                                <?php endif; ?>
                            </td>

                            <!-- Action Taken -->
                            <td id="action-<?php echo $row['id']; ?>">
                                <?php if (!empty($row['action_taken'])): ?>
                                    <div class="d-flex align-items-start justify-content-between">
                                        <span><?php echo nl2br(htmlspecialchars($row['action_taken'])); ?></span>
                                        <button class="btn btn-link btn-sm p-0 ms-2" onclick="openModal('action','<?php echo $row['id']; ?>')">Edit</button>
                                    </div>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-warning" onclick="openModal('action','<?php echo $row['id']; ?>')">Enter Action Taken</button>
                                <?php endif; ?>
                            </td>

                            <!-- Status buttons are back -->
                            <td class="text-center">
                                <div class="d-flex justify-content-center d-print-none">
                                    <button class="btn btn-sm me-1 status-btn <?php echo $row['status'] == 2 ? 'btn-success' : 'btn-outline-success'; ?>"
                                        data-id="<?php echo $row['id']; ?>" data-status="2">Solved</button>
                                    <button class="btn btn-sm status-btn <?php echo $row['status'] == 1 ? 'btn-danger' : 'btn-outline-danger'; ?>"
                                        data-id="<?php echo $row['id']; ?>" data-status="1">Not Solved</button>
                                </div>
                                <span class="status-text d-none">
                                    <?php echo $row['status'] == 2 ? 'Solved' : 'Not Solved'; ?>
                                </span>
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


<!-- Modal for channel/action -->
<div class="modal fade" id="customModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Enter Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <textarea id="modalInput" class="form-control" rows="4" placeholder="Type here..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" onclick="saveModalData()">Save</button>
            </div>
        </div>
    </div>
</div>

<!-- Date range modal -->
<div class="modal fade" id="dateRangeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="dateRangeForm" method="GET">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Date Range to Print</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
        new bootstrap.Modal(document.getElementById('dateRangeModal')).show();
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
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
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
                    } else {
                        Swal.fire('Blocked', data.message || 'Ticket must be assigned first.', 'warning');
                    }
                });
        });
    });


    let currentType = '';
    let currentTicketId = '';
    let extraData = {};
    let customModalInstance = null;

    function getModalInstance() {
        const el = document.getElementById('customModal');
        // Create on first open; reuse afterwards
        if (!customModalInstance) {
            customModalInstance = new bootstrap.Modal(el);
        }
        return customModalInstance;
    }

    function openModal(type, ticketId) {
        currentType = type; // 'channel' or 'action'
        currentTicketId = ticketId;

        // Pre-fill with what's already in the cell if available
        const cell = document.getElementById(`${type}-${ticketId}`);
        let existing = '';
        const span = cell ? cell.querySelector('span') : null;
        if (span) existing = span.innerText.trim();

        document.getElementById('modalTitle').innerText = type === 'channel' ? 'Enter Complaint Channel' : 'Enter Action Taken';
        document.getElementById('modalInput').value = existing || (extraData[ticketId]?.[type] || '');

        getModalInstance().show();
    }

    function saveModalData() {
        const value = document.getElementById('modalInput').value.trim();
        if (!value) {
            Swal.fire('Empty', 'Please enter some text.', 'warning');
            return;
        }

        fetch('ajax.php?action=save_ticket_field', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `ticket_id=${encodeURIComponent(currentTicketId)}&field=${encodeURIComponent(currentType)}&value=${encodeURIComponent(value)}`
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.success) {
                    // Cache
                    if (!extraData[currentTicketId]) extraData[currentTicketId] = {};
                    extraData[currentTicketId][currentType] = value;

                    // Update the cell HTML to show saved value + Edit button
                    const cell = document.getElementById(`${currentType}-${currentTicketId}`);
                    if (cell) {
                        cell.innerHTML = `
                    <div class="d-flex align-items-start justify-content-between">
                        <span>${value.replace(/\n/g,'<br>')}</span>
                        <button class="btn btn-link btn-sm p-0 ms-2" onclick="openModal('${currentType}','${currentTicketId}')">Edit</button>
                    </div>
                `;
                    }

                    // Close the modal
                    getModalInstance().hide();
                    Swal.fire('Saved!', 'Your entry has been saved.', 'success');
                } else {
                    Swal.fire('Error', (data && data.message) ? data.message : 'Could not save.', 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Could not save.', 'error'));
    }
</script>