<?php
include '../db_connect.php';
?>

<!-- Include Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Include Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<form id="printerAssetForm">
  <input type="hidden" name="category" value="Printer">
  <div class="row">
    <div class="col-md-6">
      <label>Printer Model</label>
      <input type="text" name="model" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label>Printer Serial Number</label>
      <input type="text" name="serial_number" class="form-control" required>
    </div>

    <div class="col-md-6 mt-3">
      <label>Location (Department)</label>
      <select name="location" class="form-control select2" required>
        <option value="">-- Select Department --</option>
        <?php
          $result = $conn->query("SELECT name FROM departments ORDER BY name ASC");
          while ($row = $result->fetch_assoc()):
        ?>
          <option value="<?= htmlspecialchars($row['name']) ?>"><?= htmlspecialchars($row['name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="col-md-6 mt-3">
      <label>Assigned To (Client Email)</label>
      <select name="assigned_to" class="form-control select2" required>
        <option value="">-- Select Client Email --</option>
        <?php
          $result = $conn->query("SELECT email FROM client_emails ORDER BY email ASC");
          while ($row = $result->fetch_assoc()):
        ?>
          <option value="<?= htmlspecialchars($row['email']) ?>"><?= htmlspecialchars($row['email']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
  </div>

  <div class="mt-3">
    <label>Status</label>
    <select name="status" class="form-control" required>
      <option value="Active">Active</option>
      <option value="In Repair">In Repair</option>
      <option value="Retired">Retired</option>
      <option value="Disposed">Disposed</option>
    </select>
  </div>

  <!-- Action Buttons -->
  <div class="d-flex justify-content-end gap-2 mt-4">
    <button type="button" class="btn btn-secondary" id="clearPrinterFormBtn"><i class="fas fa-eraser"></i> Clear Details</button>
    <button type="button" class="btn btn-danger" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancel</button>
    <button class="btn btn-success" type="submit"><i class="fas fa-save"></i> Save Printer</button>
  </div>
</form>

<!-- Include jQuery & Select2 JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    $('.select2').select2({
      width: '100%',
      dropdownParent: $('#assetModal').length ? $('#assetModal') : $('body')
    });

    $('#printerAssetForm').submit(function(e) {
      e.preventDefault();
      $.post('ajax.php?action=save_asset', $(this).serialize(), function(resp) {
        if (resp == 1) {
          alert('Printer saved successfully!');
         window.location.href = 'view_printers.php';
        } else if (resp == 2) {
          alert('Serial number already exists.');
        } else {
          alert('Failed to save Printer.');
        }
      });
    });

    // Clear form button
    $('#clearPrinterFormBtn').click(function() {
      $('#printerAssetForm')[0].reset();
      $('.select2').val(null).trigger('change');
    });
  });
</script>
