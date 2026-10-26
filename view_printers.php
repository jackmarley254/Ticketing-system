<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Printers</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- DataTables CSS -->
  <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-5">
  <h3 class="mb-4"><i class="fas fa-print text-warning me-2"></i>Printer Assets</h3>

  <div class="table-responsive">
    <table id="printersTable" class="table table-striped table-bordered">
      <thead class="table-dark">
        <tr>
          <th>Model</th>
          <th>Serial Number</th>
          <th>Location</th>
          <th>Assigned To</th>
          <th>Status</th>
          <th style="width: 120px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $printers = $conn->query("SELECT * FROM assets WHERE category = 'Printer'");
        while ($row = $printers->fetch_assoc()):
        ?>
        <tr>
          <td><?= htmlspecialchars($row['model']) ?></td>
          <td><?= htmlspecialchars($row['serial_number']) ?></td>
          <td><?= htmlspecialchars($row['location']) ?></td>
          <td><?= htmlspecialchars($row['assigned_to']) ?></td>
          <td><?= htmlspecialchars($row['status']) ?></td>
          <td>
            <a href="edit_asset.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary mb-1"><i class="fas fa-edit"></i></a>
            <button class="btn btn-sm btn-danger delete-btn" data-id="<?= $row['id'] ?>"><i class="fas fa-trash-alt"></i></button>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<!-- DataTables Export Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
  $(document).ready(function () {
    $('#printersTable').DataTable({
      dom: 'Bfrtip',
      buttons: [
        { extend: 'excelHtml5', className: 'btn btn-outline-success btn-sm', text: '<i class="fas fa-file-excel"></i> Excel' },
        { extend: 'pdfHtml5', className: 'btn btn-outline-danger btn-sm', text: '<i class="fas fa-file-pdf"></i> PDF' },
        { extend: 'print', className: 'btn btn-outline-primary btn-sm', text: '<i class="fas fa-print"></i> Print' },
      ],
      pageLength: 10,
      lengthMenu: [5, 10, 25, 50, 100],
    });

    // Delete button action
    $('.delete-btn').click(function () {
      const id = $(this).data('id');
      if (confirm('Are you sure you want to delete this printer asset?')) {
        $.ajax({
          url: 'delete_asset.php',
          method: 'POST',
          data: { id: id },
          success: function (resp) {
            if (resp == 1) {
              alert('Asset deleted successfully.');
              location.reload();
            } else {
              alert('Failed to delete asset.');
            }
          }
        });
      }
    });
  });
</script>
</body>
</html>
