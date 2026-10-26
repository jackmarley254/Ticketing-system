<?php include 'db_connect.php' ?>
<div class="col-lg-12">
	<div class="card card-outline card-primary">
		<div class="card-header">
			<div class="card-tools">
				<button class="btn btn-sm btn-primary btn-block" type="button" id="new_department">
					<i class="fa fa-plus"></i> Click Here To Add New Office
				</button>
			</div>
		</div>
		<div class="card-body">
			<table class="table table-hover table-bordered" id="list">
				<thead>
					<tr>
						<th>#</th>
						<th>Name</th>
						<th>Location</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$i = 1;
					$qry = $conn->query("SELECT * FROM departments ORDER BY name ASC");
					while ($row = $qry->fetch_assoc()):
					?>
						<tr>
							<th class="text-center"><?php echo $i++ ?></th>
							<td><b><?php echo ucwords($row['name']) ?></b></td>
							<td><b><?php echo $row['description'] ?></b></td>
							<td class="text-center">
								<button class="btn btn-sm btn-info edit_department" data-id="<?php echo $row['id'] ?>">
									<i class="fa fa-edit"></i> Edit
								</button>
								<button class="btn btn-sm btn-danger delete_department" data-id="<?php echo $row['id'] ?>">
									<i class="fa fa-trash"></i> Delete
								</button>
							</td>
						</tr>
					<?php endwhile; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<!-- jQuery, Bootstrap & DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />

<script>
	$(document).ready(function () {
		$('#list').DataTable({
			pageLength: 10
		});

		$('#new_department').click(function () {
			uni_modal("New Department", "manage_department.php");
		});

		// Use event delegation for dynamically generated buttons
		$('#list tbody').on('click', '.edit_department', function () {
			const id = $(this).data('id');
			uni_modal("Edit Department", "manage_department.php?id=" + id);
		});

		$('#list tbody').on('click', '.delete_department', function () {
			const id = $(this).data('id');
			_conf("Are you sure to delete this department?", "delete_department", [id]);
		});
	});

	function delete_department(id) {
		start_load();
		$.ajax({
			url: 'ajax.php?action=delete_department',
			method: 'POST',
			data: { id: id },
			success: function (resp) {
				if (resp == 1) {
					alert_toast("Data successfully deleted", 'success');
					setTimeout(function () {
						location.reload();
					}, 1500);
				}
			}
		});
	}
</script>
