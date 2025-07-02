<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Borrower List</title>

  <!-- Bootstrap CSS (optional for styling) -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

  <!-- FontAwesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- DataTables CSS -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
</head>
<body>

<?php
include 'db_connect.php';

// Fetch all borrowers
$borrowers = $conn->query("SELECT * FROM borrowers ORDER BY id DESC");
?>

<div class="container mt-5">
  <div class="card border-primary">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0">Borrower List</h4>
      <a href="index.php?page=new_borrower" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Add New Borrower</a>
    </div>
    <div class="card-body">
      <table id="borrowersTable" class="table table-bordered table-striped table-sm">
        <thead class="thead-dark">
          <tr>
            <th>#</th>
            <th>Borrower ID</th>
            <th>Full Name</th>
            <th>Contact</th>
            <th>Email</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php $i = 1; while($row = $borrowers->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($row['borrower_id']) ?></td>
              <td><?= htmlspecialchars($row['full_name']) ?></td>
              <td><?= htmlspecialchars($row['contact_number']) ?></td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td>
                <a href="index.php?page=edit_borrower&id=<?= $row['id'] ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                <a href="delete_borrower.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this borrower?')" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables JS + Buttons + PDF -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

<script>
  $(document).ready(function () {
    $('#borrowersTable').DataTable({
      dom: 'Bfrtip',
      buttons: [
        {
          extend: 'pdfHtml5',
          text: 'Download PDF',
          title: 'Borrower List',
          exportOptions: {
            columns: [0, 1, 2, 3, 4]
          }
        }
      ]
    });
  });
</script>


</body>
</html>
