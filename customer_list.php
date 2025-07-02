<?php include'db_connect.php' ?>
<div class="col-lg-12">
  <div class="card border-light shadow-sm">
    <div class="card-header bg-white border-bottom">
      <h4 class="mb-0 text-primary"></h4>
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
              $qry = $conn->query("SELECT *, CONCAT(lastname, ', ', firstname, ' ', middlename) AS name FROM customers ORDER BY id DESC");
              while($row = $qry->fetch_assoc()):
            ?>
            <tr>
              <td class="text-center"><?php echo $i++ ?></td>
              <td><strong><?php echo ucwords($row['name']) ?></strong></td>
              <td><?php echo $row['contact'] ?></td>
              <td><?php echo $row['address'] ?></td>
              <td><?php echo $row['email'] ?></td>
              <td class="text-center">
                <a href="./index.php?page=edit_customer&id=<?php echo $row['id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                  <i class="fas fa-edit"></i>
                </a>
                <button class="btn btn-sm btn-outline-danger delete_customer" data-id="<?php echo $row['id'] ?>" title="Delete">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
  $(document).ready(function(){
    $('#list').DataTable({
      "pageLength": 10,
      "lengthChange": false,
      "ordering": true
    });

    $('.delete_customer').click(function(){
      _conf("Are you sure you want to delete this customer?", "delete_customer", [$(this).attr('data-id')]);
    });
  });

  function delete_customer($id){
    start_load();
    $.ajax({
      url: 'ajax.php?action=delete_customer',
      method: 'POST',
      data: {id: $id},
      success: function(resp){
        if(resp == 1){
          alert_toast("Customer deleted successfully", 'success');
          setTimeout(function(){
            location.reload();
          }, 1500);
        }
      }
    });
  }
</script>
