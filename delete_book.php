<?php
session_start();
include 'db_connect.php';

// Only proceed if ID is provided and valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $book_id = (int) $_GET['id'];

    // Prepare and execute delete query
    $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
    $stmt->bind_param("i", $book_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Book deleted successfully.";
    } else {
        $_SESSION['error'] = "Failed to delete book.";
    }

    $stmt->close();
} else {
    $_SESSION['error'] = "Invalid book ID.";
}

// Redirect back to book list
header("Location: index.php?page=book_list");
exit;
