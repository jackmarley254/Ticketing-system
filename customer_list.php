<?php include 'db_connect.php'; ?>
<div class="col-lg-12">
  <div class="card border-light shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
      <h4 class="mb-0 text-primary">Staff List</h4>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered table-hover align-middle" id="list">
          <thead class="table-light">
            <tr>
              <th style="width: 50px;">#</th>
              <th>Name</th>
              <th>Contact</th>
              <th>Office</th>
              <th>Email</th>
              <th style="width: 140px;" class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php
              $i = 1;
              // Fetch only required fields
              $qry = $conn->query("SELECT id, firstname, middlename, lastname, contact, address, email FROM customers ORDER BY id DESC");
              while($row = $qry->fetch_assoc()):
                $name = ucwords($row['lastname'] . ', ' . $row['firstname'] . ' ' . $row['middlename']);
            ?>
            <tr>
              <td class="text-center"><?php echo $i++; ?></td>
              <td><strong><?php echo $name; ?></strong></td>
              <td><?php echo $row['contact']; ?></td>
              <td><?php echo $row['address']; ?></td>
              <td><?php echo $row['email']; ?></td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                  <a href="./index.php?page=edit_customer&id=<?php echo $row['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                    <i class="fas fa-edit"></i>
                  </a>
                  <button class="btn btn-sm btn-outline-danger delete_customer" data-id="<?php echo $row['id']; ?>" title="Delete">
                    <i class="fas fa-trash-alt"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Include jQuery & DataTables if not already loaded -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">

<script>
  $(document).ready(function() {
    $('#list').DataTable({
      "pageLength": 10,
      "lengthChange": false,
      "ordering": true
    });

    $(document).on('click', '.delete_customer', function() {
      const id = $(this).data('id');
      _conf("Are you sure you want to delete this customer?", "delete_customer", [id]);
    });
  });

  function delete_customer(id) {
    start_load();
    $.ajax({
      url: 'ajax.php?action=delete_customer',
      method: 'POST',
      data: { id: id },
      success: function(resp) {
        if (resp == 1) {
          alert_toast("Customer deleted successfully", 'success');
          setTimeout(function() {
            location.reload();
          }, 1500);
        }
      }
    });
  }
</script>
