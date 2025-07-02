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
        echo $crud->save_staff();
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
        // ✅ Only subject and department are required; allow empty description
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

        if ($user_type == 1) { // admin
            $u = $conn->query("SELECT name FROM users WHERE id = $user_id");
            if ($u && $u->num_rows > 0) {
                $username = $u->fetch_assoc()['name'];
            }
        } elseif ($user_type == 2) { // staff
            $u = $conn->query("SELECT CONCAT(firstname, ' ', lastname) AS name FROM staff WHERE id = $user_id");
            if ($u && $u->num_rows > 0) {
                $username = $u->fetch_assoc()['name'];
            }
        } elseif ($user_type == 3) { // customer
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
        $ticket_id = $_POST['ticket_id'];
        $status = $_POST['status'];
        $stmt = $conn->prepare("UPDATE tickets SET status = ? WHERE id = ?");
        $stmt->bind_param("ii", $status, $ticket_id);
        $stmt->execute();
        echo 1;
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
        $fields = ['asset_tag', 'serial_number', 'manufacturer', 'model', 'location', 'assigned_to', 'status', 'category',
                   'monitor_model', 'monitor_serial', 'mouse_model', 'mouse_serial', 'keyboard_model', 'keyboard_serial'];

        foreach ($fields as $field) {
            $$field = $conn->real_escape_string($_POST[$field]);
        }

        $chk = $conn->query("SELECT * FROM assets WHERE asset_tag = '$asset_tag'");
        if ($chk->num_rows > 0) {
            echo 2;
            exit;
        }

        $save = $conn->query("INSERT INTO assets (
            asset_tag, serial_number, manufacturer, model, location, assigned_to, status, category,
            monitor_model, monitor_serial, mouse_model, mouse_serial, keyboard_model, keyboard_serial
        ) VALUES (
            '$asset_tag', '$serial_number', '$manufacturer', '$model', '$location', '$assigned_to', '$status', '$category',
            '$monitor_model', '$monitor_serial', '$mouse_model', '$mouse_serial', '$keyboard_model', '$keyboard_serial'
        )");

        echo $save ? 1 : 0;
        break;

    default:
        echo 'Invalid action';
}

ob_end_flush();
?>
