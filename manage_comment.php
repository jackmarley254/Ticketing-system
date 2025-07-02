<?php include 'db_connect.php' ?>
<?php
if(isset($_GET['id'])){
	$qry = $conn->query("SELECT * FROM comments where id = ".$_GET['id'])->fetch_array();
	foreach($qry as $k => $v){
		$$k = $v;
	}
}
?>
<div class="container-fluid">
	<form action="" id="update-comment">
		<input type="hidden" name="id" value="<?php echo isset($id) ? $id : '' ?>">
		<div class="form-group mb-3">
			<label for="" class="control-label">Comment</label>
			<textarea name="comment" id="comment" cols="30" rows="4" class="form-control summernote2"><?php echo isset($comment) ? $comment : '' ?></textarea>
		</div>
		<div class="text-end">
			<button type="submit" class="btn btn-primary">
				<i class="fas fa-save me-1"></i> Update Comment
			</button>
		</div>
	</form>
</div>

<script>
$(function () {
	$('.summernote2').summernote({
		height: 150,
		toolbar: [
			['style', ['style']],
			['font', ['bold', 'italic', 'strikethrough', 'superscript', 'subscript', 'clear']],
			['fontsize', ['fontsize']],
			['para', ['ol', 'ul', 'paragraph', 'height']],
			['view', ['undo', 'redo']]
		]
	});

	$('#update-comment').submit(function(e){
		e.preventDefault();
		start_load();
		$.ajax({
			url:'ajax.php?action=save_comment',
			data: new FormData(this),
			cache: false,
			contentType: false,
			processData: false,
			method: 'POST',
			success:function(resp){
				end_load();
				if(resp == 1){
					Swal.fire({
						icon: 'success',
						title: 'Comment Updated!',
						text: 'Your comment was successfully updated.',
						timer: 2000,
						showConfirmButton: false
					}).then(() => {
						location.reload();
					});
				} else {
					Swal.fire({
						icon: 'error',
						title: 'Update Failed',
						text: 'There was an issue updating your comment.',
						timer: 3000,
						showConfirmButton: false
					});
				}
			}
		});
	});
});
</script>

