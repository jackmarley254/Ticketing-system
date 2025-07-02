<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $borrower_id = $_POST['borrower_id'];
    $full_name = $_POST['full_name'];
    $contact_number = $_POST['contact_number'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("INSERT INTO borrowers (borrower_id, full_name, contact_number, email) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $borrower_id, $full_name, $contact_number, $email);

    if ($stmt->execute()) {
        echo "<script>alert('Borrower added successfully!'); window.location.href='index.php?page=borrower_list';</script>";
    } else {
        echo "<script>alert('Error: Could not add borrower.');</script>";
    }
    $stmt->close();
}
?>

<div class="container mt-4">
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Add New Borrower</h3>
        </div>
        <form method="POST">
            <div class="card-body">
                <div class="form-group">
                    <label for="borrower_id">Borrower ID</label>
                    <input type="text" name="borrower_id" class="form-control" id="borrower_id" required>
                </div>
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" name="full_name" class="form-control" id="full_name" required>
                </div>
                <div class="form-group">
                    <label for="contact_number">Contact Number</label>
                    <input type="text" name="contact_number" class="form-control" id="contact_number" required>
                </div>
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" name="email" class="form-control" id="email" required>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Save Borrower</button>
                <a href="index.php?page=borrower_list" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
