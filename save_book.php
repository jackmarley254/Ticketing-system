<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Helper to sanitize and default empty fields to "N/A"
    function clean_input($conn, $key) {
        return isset($_POST[$key]) && trim($_POST[$key]) !== ''
            ? $conn->real_escape_string(trim($_POST[$key]))
            : 'N/A';
    }

    $book_id        = clean_input($conn, 'book_id');
    $title          = clean_input($conn, 'title');
    $author         = clean_input($conn, 'author');
    $genre          = clean_input($conn, 'genre');
    $isbn           = clean_input($conn, 'isbn');
    $publisher      = clean_input($conn, 'publisher');
    $location       = clean_input($conn, 'location');
    $status         = clean_input($conn, 'status');

    // Default year_published and quantity if not provided or invalid
    $year_published = isset($_POST['year_published']) && is_numeric($_POST['year_published'])
        ? intval($_POST['year_published'])
        : 0;

    $quantity = isset($_POST['quantity']) && is_numeric($_POST['quantity'])
        ? intval($_POST['quantity'])
        : 0;

    // Insert query
    $query = "INSERT INTO books (book_id, title, author, genre, isbn, publisher, year_published, quantity, location, status)
              VALUES ('$book_id', '$title', '$author', '$genre', '$isbn', '$publisher', $year_published, $quantity, '$location', '$status')";

    if ($conn->query($query)) {
        header("Location: index.php?page=book_list&success=1");
        exit();
    } else {
        echo "❌ Error: " . $conn->error;
    }
} else {
    echo "Invalid request.";
}
?>
