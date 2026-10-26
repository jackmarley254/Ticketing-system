<?php include __DIR__ . '/db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Asset Management Dashboard</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Select2 -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f5f7fa;
    }
    .btn-asset {
      width: 200px;
      height: 60px;
      font-size: 1rem;
    }
    .card {
      border: none;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
  </style>
</head>
<body>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h3><i class="fas fa-boxes-stacked text-primary me-2"></i> Asset Management</h3>
  </div>

  <!-- Add Assets Card -->
  <div class="card p-4 mb-4">
    <h5 class="text-muted mb-3">Choose Asset to Add</h5>
    <div class="d-flex gap-4 flex-wrap">
      <button class="btn btn-info btn-asset" id="addComputerBtn"><i class="fas fa-desktop me-2"></i> Add Computer</button>
      <button class="btn btn-warning btn-asset" id="addPrinterBtn"><i class="fas fa-print me-2"></i> Add Printer</button>
      <button class="btn btn-dark btn-asset" id="addSwitchBtn"><i class="fas fa-network-wired me-2"></i> Add Switch</button>
    </div>
  </div>

  <!-- View Assets Card -->
  <div class="card p-4">
    <h5 class="text-muted mb-3">View Existing Assets</h5>
    <div class="d-flex gap-4 flex-wrap">
      <a href="view_computers.php" class="btn btn-outline-primary btn-asset"><i class="fas fa-desktop me-2"></i> View Computers</a>
      <a href="view_printers.php" class="btn btn-outline-warning btn-asset"><i class="fas fa-print me-2"></i> View Printers</a>
      <a href="view_switches.php" class="btn btn-outline-dark btn-asset"><i class="fas fa-network-wired me-2"></i> View Switches</a>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="assetModal" tabindex="-1" aria-labelledby="assetModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="assetModalLabel"><i class="fas fa-box-open me-2"></i> Add Asset</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="assetModalBody">
        <div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Loading form...</div>
      </div>
    </div>
  </div>
</div>

<!-- JS Libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
function openAssetModal(type) {
  document.getElementById('assetModalLabel').innerHTML = `<i class="fas fa-box-open me-2"></i> Add ${type.charAt(0).toUpperCase() + type.slice(1)}`;
  document.getElementById('assetModalBody').innerHTML = '<div class="text-center text-muted py-4"><i class="fas fa-spinner fa-spin"></i> Loading form...</div>';

  let formURL = '';
  if (type === 'computer') formURL = 'forms/computer_form.php';
  else if (type === 'printer') formURL = 'forms/printer_form.php';
  else if (type === 'switch') formURL = 'forms/switch_form.php';

  const assetModal = new bootstrap.Modal(document.getElementById('assetModal'));
  assetModal.show();

  $('#assetModalBody').load(formURL, function() {
    // Re-initialize select2 after content loads
    $('.select2').select2({ width: '100%' });
  });
}

document.getElementById('addComputerBtn').addEventListener('click', () => openAssetModal('computer'));
document.getElementById('addPrinterBtn').addEventListener('click', () => openAssetModal('printer'));
document.getElementById('addSwitchBtn').addEventListener('click', () => openAssetModal('switch'));
</script>

</body>
</html>
