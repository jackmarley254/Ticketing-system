<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get values from form
    $book_id = $conn->real_escape_string($_POST['book_id']);
    $book_title = $conn->real_escape_string($_POST['book_title']);
    $book_author = $conn->real_escape_string($_POST['book_author']);
    $book_genre = $conn->real_escape_string($_POST['book_genre']);
    $isbn = $conn->real_escape_string($_POST['isbn']);
    $publisher = $conn->real_escape_string($_POST['publisher']);
    $year_published = $conn->real_escape_string($_POST['year_published']);
    $quantity = intval($_POST['quantity']);
    $location = $conn->real_escape_string($_POST['location']);
    $status = $conn->real_escape_string($_POST['status']);

    // Check if the book exists in `bookpreset` by `book_id`
    $check = $conn->query("SELECT * FROM bookpreset WHERE book_id = '$book_id'");
    if (!$check || $check->num_rows === 0) {
        die("Error: Book with Book ID '$book_id' not found.");
    }

    // Proceed with the update
    $sql = "UPDATE bookpreset SET
                book_title = '$book_title',
                book_author = '$book_author',
                book_genre = '$book_genre',
                isbn = '$isbn',
                publisher = '$publisher',
                year_published = '$year_published',
                quantity = $quantity,
                location = '$location',
                status = '$status'
            WHERE book_id = '$book_id'";

    if ($conn->query($sql)) {
        header("Location: index.php?page=book_list&updated=1");
        exit;
    } else {
        echo "Error updating book: " . $conn->error;
    }
} else {
    echo "Invalid request method.";
}
?>
