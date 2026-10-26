<?php include 'db_connect.php' ?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="col-lg-12">
  <div class="card border-light shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
      <button class="btn btn-sm btn-outline-primary" onclick="printTable()">
        <i class="fas fa-print me-1"></i> Print Technical Staff List
      </button>
      <h5 class="mb-0 text-primary"></h5>
    </div>
    <div class="card-body">
      <div id="printableArea">
        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle" id="list">
            <thead class="table-light">
              <tr>
                <th style="width: 50px;">#</th>
                <th>Name</th>
                <th>Office</th>
                <th>Email</th>
                <th class="text-center" style="width: 130px;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $i = 1;
              $qry = $conn->query("SELECT s.*, d.name as office, CONCAT(s.lastname, ', ', s.firstname, ' ', s.middlename) as name 
                                  FROM staff s 
                                  LEFT JOIN departments d ON s.department_id = d.id 
                                  ORDER BY name ASC");
              while ($row = $qry->fetch_assoc()):
              ?>
              <tr>
                <td class="text-center"><?php echo $i++ ?></td>
                <td><strong><?php echo ucwords($row['name']) ?></strong></td>
                <td><?php echo ucwords($row['office']) ?></td>
                <td><?php echo $row['email'] ?></td>
                <td class="text-center">
                  <div class="d-flex justify-content-center gap-1">
                    <button class="btn btn-sm btn-outline-primary edit_staff" 
                            data-id="<?php echo $row['id'] ?>" 
                            data-name="<?php echo ucwords($row['name']) ?>" 
                            title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete_staff" 
                            data-id="<?php echo $row['id'] ?>" 
                            title="Delete">
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
</div>

<script>
function printTable() {
    var printableContent = document.getElementById('printableArea').cloneNode(true);

    let ths = printableContent.querySelectorAll('th:last-child');
    let tds = printableContent.querySelectorAll('td:last-child');
    ths.forEach(th => th.remove());
    tds.forEach(td => td.remove());

    var newWin = window.open('', '', 'height=800,width=1000');
    newWin.document.write(`
        <html>
        <head>
            <title>Technical Staff List</title>
            <style>
                body {
                    margin: 40px;
                    font-family: Arial, sans-serif;
                }
                h2 {
                    text-align: center;
                    margin-bottom: 20px;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                th, td {
                    border: 1px solid black;
                    padding: 8px;
                    text-align: left;
                    font-size: 14px;
                }
                th {
                    background-color: #f2f2f2;
                }
            </style>
        </head>
        <body>
            <center><h2>ICT UNIT</h2></center>
            <h2>Technical Staff List</h2>
            ${printableContent.innerHTML}
        </body>
        </html>
    `);

    newWin.document.close();
    newWin.focus();
    newWin.print();
    newWin.close();
}

// Handle edit button with confirmation
$(document).on('click', '.edit_staff', function(){
    var id = $(this).data('id');
    var name = $(this).data('name');

    Swal.fire({
        title: `Edit details for ${name}?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Edit',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#aaa'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `./index.php?page=edit_staff&id=${id}`;
        }
    });
});

// Handle delete button
$(document).on('click', '.delete_staff', function(){
    var id = $(this).data('id');
    var row = $(this).closest('tr');

    Swal.fire({
        title: 'Are you sure?',
        text: "This action will permanently delete the staff member.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'ajax.php?action=delete_staff',
                method: 'POST',
                data: { id: id },
                success: function(resp) {
                    if(resp == 1) {
                        row.fadeOut(500, function(){
                            $(this).remove();
                            reindexTable();
                        });
                        Swal.fire('Deleted!', 'Staff deleted successfully.', 'success');
                    } else {
                        Swal.fire('Error!', 'An error occurred while deleting.', 'error');
                    }
                }
            });
        }
    });
});

function reindexTable() {
    $('#list tbody tr').each(function(index){
        $(this).find('td:first, th:first').text(index + 1);
    });
}
</script>
