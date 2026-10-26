<?php
ob_start();
include 'admin_class.php';
include 'db_connect.php';

$action = $_GET['action'] ?? '';
$crud = new Action();

switch ($action) {
    case 'login':
        echo $crud->login();
        break;

    case 'logout':
        echo $crud->logout();
        break;

    case 'save_user':
        echo $crud->save_user();
        if (!empty($_POST['password'])) {
            $password = md5($_POST['password']);
            $id = $_SESSION['login_id'];
            $update = $conn->query("UPDATE users SET password = '$password', password_changed = 1 WHERE id = $id");
            if ($update) {
                $_SESSION['password_changed_flash'] = true;
                echo 1;
            }
        }
        break;

    case 'delete_user':
        echo $crud->delete_user();
        break;

    case 'save_page_img':
        echo $crud->save_page_img();
        break;

    case 'save_customer':
        echo $crud->save_customer();
        break;

    case 'delete_customer':
        echo $crud->delete_customer();
        break;

    case 'save_staff':
        extract($_POST);

        $firstname = $conn->real_escape_string($firstname);
        $lastname = $conn->real_escape_string($lastname);
        $department_id = intval($department_id);
        $email = $conn->real_escape_string($email);
        $id = isset($id) ? intval($id) : 0;

        // Check if email exists in approved list
        $emailCheck = $conn->query("SELECT * FROM staff_emails WHERE email = '$email'");
        if ($emailCheck->num_rows === 0) {
            echo 3;
            exit;
        }

        // Check if email already used
        $emailExists = $conn->query("SELECT * FROM staff WHERE email = '$email'" . ($id ? " AND id != $id" : ""));
        if ($emailExists->num_rows > 0) {
            echo 2;
            exit;
        }

        if ($id == 0) {
            $password = md5($password);
            $save = $conn->query("INSERT INTO staff 
                (firstname, lastname, department_id, email, password) 
                VALUES 
                ('$firstname', '$lastname', $department_id, '$email', '$password')");
        } else {
            $password_sql = '';
            if (!empty($password)) {
                $password = md5($password);
                $password_sql = ", password = '$password'";
            }
            $save = $conn->query("UPDATE staff SET 
                firstname = '$firstname', 
                lastname = '$lastname', 
                department_id = $department_id, 
                email = '$email'
                $password_sql 
                WHERE id = $id");
        }

        echo $save ? 1 : 0;
        break;

    case 'delete_staff':
        $id = $_POST['id'];
        $delete = $conn->query("DELETE FROM staff WHERE id = $id");
        echo $delete ? 1 : 0;
        break;

    case 'save_department':
        echo $crud->save_department();
        break;

    case 'delete_department':
        $id = $_POST['id'];
        $delete = $conn->query("DELETE FROM departments WHERE id = $id");
        echo $delete ? 1 : 0;
        break;

    case 'save_ticket':
        if (empty($_POST['subject']) || empty($_POST['department_id'])) {
            echo 0;
        } else {
            echo $crud->save_ticket();
        }
        break;

    case 'delete_ticket':
        echo $crud->delete_ticket();
        break;

    case 'update_ticket':
        echo $crud->update_ticket();
        break;

    case 'save_comment':
        if (
            isset($_POST['ticket_id'], $_POST['comment']) &&
            !empty($_POST['ticket_id']) &&
            !empty($_POST['comment']) &&
            isset($_SESSION['login_id'], $_SESSION['login_type'])
        ) {
            $ticket_id = intval($_POST['ticket_id']);
            $user_id = intval($_SESSION['login_id']);
            $user_type = intval($_SESSION['login_type']);
            $comment = htmlspecialchars($_POST['comment'], ENT_QUOTES);

            $stmt = $conn->prepare("INSERT INTO comments (ticket_id, user_id, user_type, comment, date_created) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("iiis", $ticket_id, $user_id, $user_type, $comment);
            echo $stmt->execute() ? 1 : 0;
        } else {
            echo 0;
        }
        break;

    case 'load_comments':
        $ticket_id = intval($_GET['id'] ?? 0);
        if ($ticket_id <= 0) {
            echo 'Invalid Ticket ID';
            exit;
        }

        $comments = $conn->query("SELECT * FROM comments WHERE ticket_id = $ticket_id ORDER BY date_created DESC");

        while ($row = $comments->fetch_assoc()) {
            $user_id = $row['user_id'];
            $user_type = $row['user_type'];
            $username = 'Unknown User';

            if ($user_type == 1) {
                $u = $conn->query("SELECT name FROM users WHERE id = $user_id");
                if ($u && $u->num_rows > 0) {
                    $username = $u->fetch_assoc()['name'];
                }
            } elseif ($user_type == 2) {
                $u = $conn->query("SELECT CONCAT(firstname, ' ', lastname) AS name FROM staff WHERE id = $user_id");
                if ($u && $u->num_rows > 0) {
                    $username = $u->fetch_assoc()['name'];
                }
            } elseif ($user_type == 3) {
                $u = $conn->query("SELECT CONCAT(firstname, ' ', lastname) AS name FROM customers WHERE id = $user_id");
                if ($u && $u->num_rows > 0) {
                    $username = $u->fetch_assoc()['name'];
                }
            }

            echo '<div class="comment" style="margin-bottom:10px;">';
            echo '<strong>' . htmlspecialchars($username) . '</strong> ';
            echo '<small style="color:gray;">(' . $row['date_created'] . ')</small>';
            echo '<p>' . nl2br(htmlspecialchars($row['comment'])) . '</p>';
            echo '</div>';
        }
        break;

    case 'delete_comment':
        echo $crud->delete_comment();
        break;

    case 'save_office':
        extract($_POST);
        $data = " name = '$name', department_id = '$department_id' ";
        if (empty($id)) {
            $check = $conn->query("SELECT * FROM offices WHERE name = '$name' AND department_id = '$department_id'")->num_rows;
            if ($check > 0) {
                echo 2;
                exit;
            }
            $save = $conn->query("INSERT INTO offices SET $data");
        } else {
            $save = $conn->query("UPDATE offices SET $data WHERE id = $id");
        }
        echo $save ? 1 : 0;
        break;

    case 'delete_office':
        $delete = $conn->query("DELETE FROM offices WHERE id = " . $_POST['id']);
        echo $delete ? 1 : 0;
        break;

    case 'update_ticket_status':
        $ticket_id = intval($_POST['ticket_id']);
        $status = intval($_POST['status']);

        $assigned_check = $conn->query("SELECT assigned_to FROM tickets WHERE id = $ticket_id");
        $assigned_row = $assigned_check->fetch_assoc();

        if (!$assigned_row || empty($assigned_row['assigned_to'])) {
            echo json_encode(['success' => false, 'message' => 'Ticket must be assigned before updating status.']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $ticket_id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        break;

    case 'assign_ticket':
        $id = $_POST['id'];
        $assign_to = $_POST['assign_to'];

        $check = $conn->query("SELECT assigned_to FROM tickets WHERE id = $id")->fetch_assoc();
        if ($check && !empty($check['assigned_to'])) {
            echo 0;
            exit;
        }

        $update = $conn->query("UPDATE tickets SET assigned_to = $assign_to WHERE id = $id");
        echo $update ? 1 : 2;
        break;

    case 'get_ticket_assignee':
        $ticket_id = intval($_POST['id'] ?? 0);
        if ($ticket_id <= 0) {
            echo 'Invalid ticket ID';
            exit;
        }

        $stmt = $conn->prepare("
            SELECT s.firstname AS staff_name 
            FROM tickets t 
            LEFT JOIN staff s ON t.assigned_to = s.id 
            WHERE t.id = ?
        ");
        $stmt->bind_param('i', $ticket_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo $row['staff_name'] ?? 'Unassigned';
        break;

    case 'save_asset':
        // Escape shared fields
        $category = $conn->real_escape_string($_POST['category'] ?? '');
        $model = $conn->real_escape_string($_POST['model'] ?? '');
        $serial_number = $conn->real_escape_string($_POST['serial_number'] ?? '');
        $location = $conn->real_escape_string($_POST['location'] ?? '');
        $assigned_to = $conn->real_escape_string($_POST['assigned_to'] ?? '');
        $status = $conn->real_escape_string($_POST['status'] ?? 'Active');

        // Category-specific
        $monitor_model = $monitor_serial = "NULL";
        $port_count = "NULL";

        if ($category === 'Computer') {
            $monitor_model = "'" . $conn->real_escape_string($_POST['monitor_model'] ?? '') . "'";
            $monitor_serial = "'" . $conn->real_escape_string($_POST['monitor_serial'] ?? '') . "'";
        }

        if ($category === 'Switch') {
            $port_count = intval($_POST['port_count'] ?? 0);
        }

        // Avoid duplicates based on serial_number + category
        $dup = $conn->query("SELECT id FROM assets WHERE serial_number = '$serial_number' AND category = '$category'");
        if ($dup && $dup->num_rows > 0) {
            echo 2; // Duplicate found
            exit;
        }

        $sql = "INSERT INTO assets (
        category, model, serial_number, location, assigned_to, status, 
        monitor_model, monitor_serial, port_count
    ) VALUES (
        '$category', '$model', '$serial_number', '$location', '$assigned_to', '$status',
        $monitor_model, $monitor_serial, " . ($category === 'Switch' ? $port_count : "NULL") . "
    )";

        $save = $conn->query($sql);
        echo $save ? 1 : 0;
        break;


    case 'check_staff_email':
        $email = $conn->real_escape_string($_POST['email']);
        $check = $conn->query("SELECT id FROM staff_emails WHERE email = '$email'");
        echo $check->num_rows > 0 ? '1' : '0';
        exit;

    case 'check_client_email':
        $email = $conn->real_escape_string($_POST['email']);
        $check = $conn->query("SELECT id FROM client_emails WHERE email = '$email'");
        echo $check->num_rows > 0 ? '1' : '0';
        exit;


    case 'save_staff_email':
        include 'db_connect.php'; // Ensure DB connection

        // Debug mode ON (disable in production)
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        $email = trim($_POST['email'] ?? '');

        if ($email === '') {
            echo 'Missing email.';
            exit;
        }

        // ✅ Only allow emails ending with @cultureheritage.go.ke
        if (!preg_match('/@cultureheritage\.go\.ke$/i', $email)) {
            echo 3; // Invalid domain
            exit;
        }

        // Check if email already exists
        $check = $conn->query("SELECT id FROM staff_emails WHERE email = '$email'");
        if (!$check) {
            echo 'Check query failed: ' . $conn->error;
            exit;
        }

        if ($check->num_rows > 0) {
            echo 2; // Email already exists
            exit;
        }

        // Insert new email
        $save = $conn->query("INSERT INTO staff_emails (email) VALUES ('$email')");
        if ($save) {
            echo 1; // Success
        } else {
            echo 'Insert failed: ' . $conn->error; // Show error for debugging
        }

        exit;
    case 'save_client_email':
        include 'db_connect.php';

        $email = trim($_POST['email'] ?? '');

        if ($email === '') {
            echo 'Missing email.';
            exit;
        }

        // ✅ Only allow emails ending with @cultureheritage.go.ke
        if (!preg_match('/@cultureheritage\.go\.ke$/i', $email)) {
            echo 3; // Invalid domain
            exit;
        }

        // Check if email already exists
        $check = $conn->query("SELECT id FROM client_emails WHERE email = '$email'");
        if (!$check) {
            echo 'Check query failed: ' . $conn->error;
            exit;
        }

        if ($check->num_rows > 0) {
            echo 2; // Email already exists
            exit;
        }

        // Insert new client email
        $save = $conn->query("INSERT INTO client_emails (email) VALUES ('$email')");
        echo $save ? 1 : 'Insert failed: ' . $conn->error;
        exit;


    case 'update_password_only':
        $id = (int)$_POST['id'];
        $new_pass = $_POST['new_password'];

        if (!empty($id) && !empty($new_pass)) {
            $hashed = password_hash($new_pass, PASSWORD_DEFAULT);
            $utype = array('', 'users', 'staff', 'customers');
            $login_type = $_SESSION['login_type'] ?? 0;

            if (!isset($utype[$login_type])) {
                echo 0;
                exit;
            }

            $table = $utype[$login_type];

            $update = $conn->query("UPDATE $table SET password = '$hashed' WHERE id = $id");

            echo $update ? 1 : 0;
        } else {
            echo 0;
        }
        break;


    case 'save_ticket_field':
        $ticket_id = intval($_POST['ticket_id'] ?? 0);
        $field     = $_POST['field'] ?? '';
        $value     = $conn->real_escape_string($_POST['value'] ?? '');

        if ($ticket_id <= 0 || !in_array($field, ['channel', 'action'], true)) {
            echo json_encode(['success' => false, 'message' => 'Invalid input']);
            exit;
        }

        // Map to real DB columns
        $db_column = $field === 'channel' ? 'complaint_channel' : 'action_taken';

        $ok = $conn->query("UPDATE tickets SET {$db_column} = '{$value}' WHERE id = {$ticket_id}");
        if ($ok) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $conn->error]);
        }

        
        break;






    default:
        echo 'Invalid action';
        break;
}

ob_end_flush();
