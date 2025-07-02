<?php include 'db_connect.php'; ?>

<?php if (isset($_GET['saved']) && $_GET['saved'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        ✅ Preset details saved successfully!
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
<?php endif; ?>

<!-- ✅ Excel Import Form -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">📥 Import Book Presets from Excel</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="import_excel.php" enctype="multipart/form-data">
            <div class="form-group row">
                <label class="col-sm-2 col-form-label">Upload Excel File</label>
                <div class="col-sm-8">
                    <input type="file" name="excel_file" class="form-control" accept=".xlsx,.xls" required>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-success">Import</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- 📚 Book Preset Form -->
<div class="col-lg-12">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Add Book Preset Details</h5>
        </div>
        <div class="card-body">
            <form id="preset_form" method="POST" action="save_presets.php" class="form-horizontal">

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Book ID</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="book_id" placeholder="Enter Book ID" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Book Location</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="location" placeholder="Enter Book Location" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Book Genre (Category)</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="genre" placeholder="Enter Book Genre" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Book Author</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="author" placeholder="Enter Book Author" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Book Title</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="title" placeholder="Enter Book Title" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Book Publisher</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="publisher" placeholder="Enter Book Publisher" required>
                    </div>
                </div>

                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Year Published</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="year_published" placeholder="Enter Year (e.g. 2025)" min="1000" max="9999" required>
                    </div>
                </div>

                <!-- ✅ Quantity Field -->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Quantity</label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="quantity" placeholder="Enter Quantity" min="1" required>
                    </div>
                </div>

                <!-- ✅ Status Field -->
                <div class="form-group row">
                    <label class="col-sm-2 col-form-label">Status</label>
                    <div class="col-sm-10">
                        <select class="form-control" name="status" required>
                            <option value="">Select Status</option>
                            <option value="Available">Available</option>
                            <option value="Not Available">Not Available</option>
                        </select>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-sm-10 offset-sm-2">
                        <button type="submit" class="btn btn-primary">Save Presets</button>
                        <a href="index.php?page=library_management" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>

            </form>
        </div>
    </div>
</div>
