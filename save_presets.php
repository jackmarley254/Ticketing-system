<?php
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize POST values
    $book_id        = trim($_POST['book_id'] ?? '');
    $book_title     = trim($_POST['title'] ?? '');
    $book_author    = trim($_POST['author'] ?? '');
    $book_genre     = trim($_POST['genre'] ?? '');
    $publisher      = trim($_POST['publisher'] ?? '');
    $location       = trim($_POST['location'] ?? '');
    $year_published = trim($_POST['year_published'] ?? '');
    $isbn           = trim($_POST['isbn'] ?? '');
    $quantity       = trim($_POST['quantity'] ?? '');
    $status         = trim($_POST['status'] ?? '');

    // Optional: Skip insertion if required fields are empty
    if ($book_id && $book_title) {
        $stmt = $conn->prepare("INSERT INTO bookpreset 
            (book_id, book_title, book_author, book_genre, publisher, location, year_published, isbn, quantity, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", 
            $book_id, $book_title, $book_author, $book_genre, 
            $publisher, $location, $year_published, $isbn, 
            $quantity, $status);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: index.php?page=book_presets&saved=1");
    exit();
}
?>
