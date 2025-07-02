<?php
include 'db_connect.php';

// Get the book ID from the URL
$book_id = $_GET['id'] ?? '';

// Validate the book_id
if (empty($book_id)) {
    die("Invalid or missing Book ID.");
}

// Fetch book data from bookpreset table using book_id
$book_stmt = $conn->query("SELECT * FROM bookpreset WHERE book_id = '$book_id'");
$book = $book_stmt->fetch_assoc();

if (!$book) {
    die("Error: Book with ID '$book_id' not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Book</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <a class="navbar-brand" href="index.php">Edit Details About Your Books From This Window As Below:</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=book_list"><i class="fas fa-book"></i> Book List</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php?page=book_presets"><i class="fas fa-plus-circle"></i> Add Book</a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="col-lg-12">
            <div class="card border-0 shadow">
                <div class="card-header bg-warning text-white">
                    <h4 class="mb-0"><i class="fas fa-edit"></i> Edit Book</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="update_book.php">
                        <input type="hidden" name="book_id" value="<?= htmlspecialchars($book['book_id']) ?>">

                        <div class="row">
                            <div class="col-md-6">
                                <!-- Title -->
                                <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($book['book_title']) ?>" required>
                                </div>

                                <!-- Author -->
                                <div class="form-group">
                                    <label>Author</label>
                                    <input type="text" class="form-control" name="author" value="<?= htmlspecialchars($book['book_author']) ?>" required>
                                </div>

                                <!-- Genre -->
                                <div class="form-group">
                                    <label>Genre</label>
                                    <input type="text" class="form-control" name="genre" value="<?= htmlspecialchars($book['book_genre']) ?>" required>
                                </div>

                                <!-- ISBN -->
                                <div class="form-group">
                                    <label>ISBN</label>
                                    <input type="text" class="form-control" name="isbn" value="<?= htmlspecialchars($book['isbn']) ?>">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <!-- Publisher -->
                                <div class="form-group">
                                    <label>Publisher</label>
                                    <input type="text" class="form-control" name="publisher" value="<?= htmlspecialchars($book['publisher']) ?>" required>
                                </div>

                                <!-- Year Published -->
                                <div class="form-group">
                                    <label>Year Published</label>
                                    <input type="text" class="form-control" name="year_published" value="<?= htmlspecialchars($book['year_published']) ?>" required>
                                </div>

                                <!-- Quantity -->
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" class="form-control" name="quantity" value="<?= htmlspecialchars($book['quantity']) ?>" min="0" required>
                                </div>

                                <!-- Location -->
                                <div class="form-group">
                                    <label>Location</label>
                                    <input type="text" class="form-control" name="location" value="<?= htmlspecialchars($book['location']) ?>" required>
                                </div>

                                <!-- Status -->
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="Available" <?= $book['status'] == 'Available' ? 'selected' : '' ?>>Available</option>
                                        <option value="Not Available" <?= $book['status'] == 'Not Available' ? 'selected' : '' ?>>Not Available</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="text-right">
                            <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update Book</button>
                            <a href="index.php?page=book_list" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({ width: '100%' });
        });
    </script>
</body>
</html>
