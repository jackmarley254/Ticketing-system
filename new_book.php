<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Book</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
<div class="col-lg-12 mt-3">
    <div class="card border-0 shadow">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-book"></i> Add New Book</h4>
        </div>
        <div class="card-body">
            <form id="add_book_form" method="POST" action="save_book.php">
                <div class="row">
                    <div class="col-md-6">
                        <!-- Book ID -->
                        <div class="form-group">
                            <label for="book_id">Book ID</label>
                            <select name="book_id" id="book_id" class="form-control select2" required>
                                <option></option>
                                <?php
                                $res = $conn->query("SELECT DISTINCT book_id FROM bookpreset ORDER BY book_id ASC");
                                while ($row = $res->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['book_id']) . '">' . htmlspecialchars($row['book_id']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Title -->
                        <div class="form-group">
                            <label for="title">Title</label>
                            <select name="title" id="title" class="form-control select2" required>
                                <option></option>
                                <?php
                                $res = $conn->query("SELECT DISTINCT book_title FROM bookpreset ORDER BY book_title ASC");
                                while ($row = $res->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['book_title']) . '">' . htmlspecialchars($row['book_title']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Author -->
                        <div class="form-group">
                            <label for="author">Author</label>
                            <select name="author" id="author" class="form-control select2" required>
                                <option></option>
                                <?php
                                $res = $conn->query("SELECT DISTINCT book_author FROM bookpreset ORDER BY book_author ASC");
                                while ($row = $res->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['book_author']) . '">' . htmlspecialchars($row['book_author']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Genre -->
                        <div class="form-group">
                            <label for="genre">Genre</label>
                            <select name="genre" id="genre" class="form-control select2" required>
                                <option></option>
                                <?php
                                $res = $conn->query("SELECT DISTINCT book_genre FROM bookpreset ORDER BY book_genre ASC");
                                while ($row = $res->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['book_genre']) . '">' . htmlspecialchars($row['book_genre']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- ISBN -->
                        <div class="form-group">
                            <label for="isbn">ISBN</label>
                            <input type="text" class="form-control" name="isbn" id="isbn">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <!-- Publisher -->
                        <div class="form-group">
                            <label for="publisher">Publisher</label>
                            <select name="publisher" id="publisher" class="form-control select2" required>
                                <option></option>
                                <?php
                                $res = $conn->query("SELECT DISTINCT publisher FROM bookpreset ORDER BY publisher ASC");
                                while ($row = $res->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['publisher']) . '">' . htmlspecialchars($row['publisher']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Year Published -->
                        <div class="form-group">
                            <label for="year_published">Year Published</label>
                            <select name="year_published" id="year_published" class="form-control select2" required>
                                <option></option>
                                <?php
                                $res = $conn->query("SELECT DISTINCT year_published FROM bookpreset ORDER BY year_published DESC");
                                while ($row = $res->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['year_published']) . '">' . htmlspecialchars($row['year_published']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Quantity -->
                        <div class="form-group">
                            <label for="quantity">Quantity Available</label>
                            <input type="number" class="form-control" name="quantity" id="quantity" min="0" required>
                        </div>

                        <!-- Location -->
                        <div class="form-group">
                            <label for="location">Library Location</label>
                            <select name="location" id="location" class="form-control select2" required>
                                <option></option>
                                <?php
                                $res = $conn->query("SELECT DISTINCT location FROM bookpreset ORDER BY location ASC");
                                while ($row = $res->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['location']) . '">' . htmlspecialchars($row['location']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="form-group">
                            <label for="status">Availability Status</label>
                            <select name="status" id="status" class="form-control select2" required>
                                <option value="Available">Available</option>
                                <option value="Not Available">Not Available</option>
                            </select>
                        </div>
                    </div>
                </div>

                <hr>
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-success mr-2"><i class="fas fa-save"></i> Save Book</button>
                    <a href="index.php?page=book_list" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JS Dependencies -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js"></script>

<!-- Select2 Init & SweetAlert -->
<script>
    $(document).ready(function () {
        $('.select2').select2({
            width: '100%',
            placeholder: 'Select an option',
            allowClear: true
        });

        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('added') === '1') {
            Swal.fire({
                icon: 'success',
                title: 'Book Added',
                text: 'The book has been added successfully.',
                timer: 3000,
                showConfirmButton: false
            });
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
</script>
</body>
</html>
