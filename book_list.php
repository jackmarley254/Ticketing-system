<?php include 'db_connect.php'; ?>

<?php
// Initialize filter variables
$filters = [
    'title'     => $_GET['title'] ?? '',
    'genre'     => $_GET['genre'] ?? '',
    'location'  => $_GET['location'] ?? '',
    'author'    => $_GET['author'] ?? '',
    'status'    => $_GET['status'] ?? '',
    'year'      => $_GET['year'] ?? '',
    'publisher' => $_GET['publisher'] ?? '',
    'isbn'      => $_GET['isbn'] ?? ''
];

// Build WHERE clause
$where = [];
foreach ($filters as $key => $value) {
    if (!empty($value)) {
        $field = match ($key) {
            'year' => 'year_published',
            'title' => 'book_title',
            'author' => 'book_author',
            'genre' => 'book_genre',
            default => $key
        };
        $where[] = "$field = '" . $conn->real_escape_string($value) . "'";
    }
}
$where_sql = count($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Pagination
$limit = 100;
$page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int) $_GET['p'] : 1;
$offset = ($page - 1) * $limit;

$total_result = $conn->query("SELECT COUNT(*) as total FROM bookpreset $where_sql");
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Total books by genre
$genre_totals_sql = "SELECT book_genre AS genre, COUNT(*) as total FROM bookpreset $where_sql GROUP BY book_genre ORDER BY total DESC";
$genre_totals = $conn->query($genre_totals_sql);

// All genres for buttons
$genres_res = $conn->query("SELECT DISTINCT book_genre AS name FROM bookpreset ORDER BY book_genre ASC");
?>

<!-- Select2 CSS/JS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="col-lg-12">
    <div class="genre-buttons d-flex flex-wrap mb-3">
        <a href="index.php?page=book_list" class="btn btn-sm <?= empty($filters['genre']) ? 'btn-dark' : 'btn-outline-dark' ?>">All Genres</a>
        <?php while ($genre = $genres_res->fetch_assoc()):
            $gname = htmlspecialchars($genre['name']);
            $active = ($filters['genre'] === $gname) ? 'btn-primary' : 'btn-outline-primary';
        ?>
            <a href="index.php?page=book_list&genre=<?= urlencode($gname) ?>" class="btn btn-sm <?= $active ?>"><?= $gname ?></a>
        <?php endwhile; ?>
    </div>

    <?php if ($genre_totals && $genre_totals->num_rows > 0): ?>
        <div class="alert alert-info">
            <strong>Total Books by Genre:</strong>
            <ul class="mb-0 pl-3">
                <?php while($g = $genre_totals->fetch_assoc()): ?>
                    <li><strong><?= htmlspecialchars($g['genre']) ?>:</strong> <?= $g['total'] ?> book(s)</li>
                <?php endwhile; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card border-0 shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-book"></i> Book List</h4>
            <a href="index.php?page=new_book" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Add Book</a>
        </div>
        <div class="card-body">
            <form method="GET" class="mb-3">
                <input type="hidden" name="page" value="book_list">
                <div class="row">
                    <?php
                    function buildSelect($conn, $name, $column, $label, $selected) {
                        $q = $conn->query("SELECT DISTINCT $column AS name FROM bookpreset ORDER BY $column ASC");
                        echo "<div class='col-md-2'><div class='form-group'><label>$label</label><select name='$name' class='form-control select2'>";
                        echo "<option value=''>All</option>";
                        while ($r = $q->fetch_assoc()) {
                            $val = htmlspecialchars($r['name']);
                            $sel = ($val == $selected) ? 'selected' : '';
                            echo "<option value='$val' $sel>$val</option>";
                        }
                        echo "</select></div></div>";
                    }

                    buildSelect($conn, 'title', 'book_title', 'Title', $filters['title']);
                    buildSelect($conn, 'isbn', 'isbn', 'ISBN', $filters['isbn']);
                    buildSelect($conn, 'genre', 'book_genre', 'Genre', $filters['genre']);
                    buildSelect($conn, 'location', 'location', 'Location', $filters['location']);
                    buildSelect($conn, 'author', 'book_author', 'Author', $filters['author']);
                    buildSelect($conn, 'publisher', 'publisher', 'Publisher', $filters['publisher']);

                    $years = $conn->query("SELECT DISTINCT year_published AS year FROM bookpreset ORDER BY year_published DESC");
                    echo "<div class='col-md-2'><div class='form-group'><label>Year</label><select name='year' class='form-control select2'><option value=''>All</option>";
                    while ($r = $years->fetch_assoc()) {
                        $val = htmlspecialchars($r['year']);
                        $sel = ($val == $filters['year']) ? 'selected' : '';
                        echo "<option value='$val' $sel>$val</option>";
                    }
                    echo "</select></div></div>";

                    $statuses = ['Available', 'Not Available'];
                    echo "<div class='col-md-2'><div class='form-group'><label>Status</label><select name='status' class='form-control select2'><option value=''>All</option>";
                    foreach ($statuses as $status) {
                        $sel = ($status == $filters['status']) ? 'selected' : '';
                        echo "<option value='$status' $sel>$status</option>";
                    }
                    echo "</select></div></div>";
                    ?>
                </div>
                <div class="text-right">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Apply Filters</button>
                    <a href="index.php?page=book_list" class="btn btn-secondary">Reset</a>
                </div>
            </form>

            <!-- Book Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Book ID</th>
                            <th>ISBN</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Genre</th>
                            <th>Publisher</th>
                            <th>Year</th>
                            <th>Location</th>
                            <th>Quantity</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = $offset + 1;
                        $books = $conn->query("SELECT * FROM bookpreset $where_sql ORDER BY CAST(SUBSTRING(book_id, 3) AS UNSIGNED) ASC LIMIT $limit OFFSET $offset");
                        while ($row = $books->fetch_assoc()):
                        ?>
                        <tr>
                            <td><?= $i++; ?></td>
                            <td><?= htmlspecialchars($row['book_id']); ?></td>
                            <td><?= htmlspecialchars($row['isbn']); ?></td>
                            <td><?= htmlspecialchars($row['book_title']); ?></td>
                            <td><?= htmlspecialchars($row['book_author']); ?></td>
                            <td><?= htmlspecialchars($row['book_genre']); ?></td>
                            <td><?= htmlspecialchars($row['publisher']); ?></td>
                            <td><?= htmlspecialchars($row['year_published']); ?></td>
                            <td><?= htmlspecialchars($row['location']); ?></td>
                            <td><?= htmlspecialchars($row['quantity']); ?></td>
                            <td><?= htmlspecialchars($row['status']); ?></td>
                            <td>
                                <a href="edit_book.php?id=<?= urlencode($row['book_id']) ?>" class="btn btn-sm btn-warning">Edit</a>

                                <a href="delete_book.php?id=<?= urlencode($row['book_id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                        <?php if ($books->num_rows == 0): ?>
                        <tr>
                            <td colspan="12" class="text-center text-muted">No books found for selected filters.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav>
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['p' => $page - 1])) ?>">Previous</a></li>
                        <?php endif; ?>
                        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                        <li class="page-item <?= $p == $page ? 'active' : '' ?>"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['p' => $p])) ?>"><?= $p ?></a></li>
                        <?php endfor; ?>
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['p' => $page + 1])) ?>">Next</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Initialize Select2 -->
<script>
$(document).ready(function() {
    $('.select2').select2({
        width: '100%',
        placeholder: "Select an option",
        allowClear: true
    });
});
</script>