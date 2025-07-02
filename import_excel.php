<?php
include 'db_connect.php';
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excel_file']['tmp_name'])) {
    $file = $_FILES['excel_file']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        for ($i = 1; $i < count($data); $i++) {  // Skip header row
            $row = $data[$i];

            $book_id        = trim($row[0] ?? '');
            $location       = trim($row[1] ?? '');
            $genre          = trim($row[2] ?? '');
            $author         = trim($row[3] ?? '');
            $title          = trim($row[4] ?? '');
            $publisher      = trim($row[5] ?? '');
            $year_published = trim($row[6] ?? '');
            $isbn           = trim($row[7] ?? '');
            $quantity       = trim($row[8] ?? 1); // default to 1 if not provided
            $status         = trim($row[9] ?? 'Available'); // default to 'Available'

            // Skip completely empty rows
            if (empty($book_id) && empty($title)) continue;

            // Insert into bookpreset table
            $stmt = $conn->prepare("INSERT INTO bookpreset 
                (book_id, book_title, book_author, book_genre, publisher, location, year_published, isbn, quantity, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssssss", 
                $book_id, $title, $author, $genre, $publisher, $location, $year_published, $isbn, $quantity, $status);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: book_presets.php?saved=1");
        exit();
    } catch (Exception $e) {
        die("Failed to import: " . $e->getMessage());
    }
} else {
    die("No file uploaded.");
}
?>
