<?php
session_start();
ini_set('display_errors', 1);

class Action
{
	private $db;

	public function __construct()
	{
		ob_start();
		include 'db_connect.php';
		$this->db = $conn;
	}

	function __destruct()
	{
		$this->db->close();
		ob_end_flush();
	}

	function login()
	{
		extract($_POST);
		$table = '';
		$identifier = '';

		if ($type == 1) {
			$table = 'users';
			$identifier = 'username';
		} elseif ($type == 2) {
			$table = 'staff';
			$identifier = 'email';
		} elseif ($type == 3) {
			$table = 'customers';
			$identifier = 'email';
		} else {
			return 3; // invalid login type
		}

		$username_escaped = $this->db->real_escape_string($username);
		$qry = $this->db->query("SELECT *, CONCAT(lastname, ', ', firstname, ' ', middlename) AS name FROM $table WHERE $identifier = '$username_escaped'");

		if ($qry && $qry->num_rows > 0) {
			$row = $qry->fetch_assoc();
			$stored_hash = $row['password'];

			// ✅ Try password_verify first (for bcrypt hashes)
			if (password_verify($password, $stored_hash)) {
				// login successful
			}
			// 🟡 Optional backward compatibility with old MD5 hashes
			elseif ($stored_hash === md5($password)) {
				// Upgrade to secure hash
				$new_hash = password_hash($password, PASSWORD_DEFAULT);
				$this->db->query("UPDATE $table SET password = '{$new_hash}' WHERE id = {$row['id']}");
			} else {
				return 3; // incorrect password
			}

			// Set session variables
			foreach ($row as $key => $value) {
				if ($key != 'password' && !is_numeric($key))
					$_SESSION['login_' . $key] = $value;
			}
			$_SESSION['login_type'] = $type;
			return 1;
		}
		return 3;
	}


	function logout()
	{
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}

	function save_user()
	{
		extract($_POST);
		$ue = $_SESSION['login_type'] == 1 ? 'username' : 'email';

		$data = "firstname = '$firstname'";
		$data .= ", middlename = '$middlename'";
		$data .= ", lastname = '$lastname'";
		$data .= ", $ue = '$username'";
		if (!empty($password)) {
			$data .= ", password = '" . md5($password) . "'";
		}

		$chk = $this->db->query("SELECT * FROM $table WHERE $ue = '$username' AND id !='$id' ")->num_rows;
		if ($chk > 0) {
			return 2;
		}

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO $table SET " . $data);
		} else {
			$save = $this->db->query("UPDATE $table SET " . $data . " WHERE id = " . $id);
		}

		if ($save) {
			$_SESSION['login_firstname'] = $firstname;
			$_SESSION['login_middlename'] = $middlename;
			$_SESSION['login_lastname'] = $lastname;
			return 1;
		}
	}

	function delete_user()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users WHERE id = " . intval($id));
		return $delete ? 1 : 0;
	}

	function save_page_img()
	{
		if ($_FILES['img']['tmp_name'] != '') {
			$fname = strtotime(date('Y-m-d H:i')) . '_' . $_FILES['img']['name'];
			$move = move_uploaded_file($_FILES['img']['tmp_name'], 'assets/uploads/' . $fname);
			if ($move) {
				$protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"], 0, 5)) == 'https' ? 'https' : 'http';
				$hostName = $_SERVER['HTTP_HOST'];
				$path = explode('/', $_SERVER['PHP_SELF']);
				$currentPath = '/' . $path[1];

				return json_encode(['link' => "$protocol://$hostName$currentPath/admin/assets/uploads/$fname"]);
			}
		}
	}
	function save_customer()
	{
		extract($_POST);
		$data = "";

		if (empty($email)) return 0;
		$email = $this->db->real_escape_string($email);

		$check = $this->db->query("SELECT id FROM customers WHERE email = '$email'" . (!empty($id) ? " AND id != {$id}" : ""));
		if ($check && $check->num_rows > 0) return 2;

		// 🔁 Convert department_id to department name and store in 'address'
		if (!empty($department_id)) {
			$dep_id = (int) $department_id;
			$dep = $this->db->query("SELECT name FROM departments WHERE id = $dep_id");
			if ($dep && $dep->num_rows > 0) {
				$_POST['address'] = $this->db->real_escape_string($dep->fetch_assoc()['name']);
			}
			unset($_POST['department_id']); // remove so it doesn't get saved as a column
		}

		foreach ($_POST as $k => $v) {
			if (!in_array($k, ['id', 'cpass'])) {
				$v = $this->db->real_escape_string($v);
				if ($k == 'password' && !empty($v)) {
					$v = md5($v);
				} elseif ($k == 'password') {
					continue;
				}
				$data .= empty($data) ? " $k = '$v' " : ", $k = '$v' ";
			}
		}

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO customers SET $data");
		} else {
			$save = $this->db->query("UPDATE customers SET $data WHERE id = $id");
		}

		return $save ? 1 : 0;
	}


	function delete_customer()
	{
		extract($_POST);
		if (!isset($id)) return 0;

		$delete = $this->db->query("DELETE FROM customers WHERE id = " . intval($id));
		return $delete ? 1 : 0;
	}

	function save_staff()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if (!in_array($k, ['id', 'cpass']) && !is_numeric($k)) {
				$v = $this->db->real_escape_string($v);
				if ($k == 'password') $v = md5($v);
				$data .= empty($data) ? " $k='$v' " : ", $k='$v' ";
			}
		}
		$check = $this->db->query("SELECT * FROM staff WHERE email = '$email'" . (!empty($id) ? " AND id != {$id}" : ''))->num_rows;
		if ($check > 0) return 2;

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO staff SET $data");
		} else {
			$save = $this->db->query("UPDATE staff SET $data WHERE id = $id");
		}
		return $save ? 1 : 0;
	}

	function delete_staff()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM staff WHERE id = " . intval($id));
		return $delete ? 1 : 0;
	}

	function save_department()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if ($k != 'id' && !is_numeric($k)) {
				$v = $this->db->real_escape_string($v);
				$data .= empty($data) ? " $k='$v' " : ", $k='$v' ";
			}
		}
		$check = $this->db->query("SELECT * FROM departments WHERE name = '$name'" . (!empty($id) ? " AND id != {$id}" : ''))->num_rows;
		if ($check > 0) return 2;

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO departments SET $data");
		} else {
			$save = $this->db->query("UPDATE departments SET $data WHERE id = $id");
		}
		return $save ? 1 : 0;
	}

	function delete_department()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM departments WHERE id = " . intval($id));
		return $delete ? 1 : 0;
	}

	function save_ticket()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if ($k != 'id' && !is_numeric($k)) {
				if ($k == 'description') {
					$v = htmlentities(str_replace("'", "&#x2019;", $v));
				}
				$v = $this->db->real_escape_string($v);
				$data .= empty($data) ? " $k='$v' " : ", $k='$v' ";
			}
		}
		if (!isset($customer_id)) {
			$data .= ", customer_id={$_SESSION['login_id']} ";
		}
		if ($_SESSION['login_type'] == 1) {
			$data .= ", admin_id={$_SESSION['login_id']} ";
		}

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO tickets SET $data");
		} else {
			$save = $this->db->query("UPDATE tickets SET $data WHERE id = $id");
		}
		return $save ? 1 : 0;
	}

	function update_ticket()
	{
		extract($_POST);
		$data = " status=$status ";
		if ($_SESSION['login_type'] == 2) {
			$data .= ", staff_id={$_SESSION['login_id']} ";
		}
		$save = $this->db->query("UPDATE tickets SET $data WHERE id = $id");
		return $save ? 1 : 0;
	}

	function delete_ticket()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM tickets WHERE id = " . intval($id));
		return $delete ? 1 : 0;
	}

	function save_comment()
	{
		extract($_POST);
		$data = "";
		foreach ($_POST as $k => $v) {
			if ($k != 'id' && !is_numeric($k)) {
				if ($k == 'comment') {
					$v = htmlentities(str_replace("'", "&#x2019;", $v));
				}
				$v = $this->db->real_escape_string($v);
				$data .= empty($data) ? " $k='$v' " : ", $k='$v' ";
			}
		}
		$data .= ", user_type={$_SESSION['login_type']} ";
		$data .= ", user_id={$_SESSION['login_id']} ";

		if (empty($id)) {
			$save = $this->db->query("INSERT INTO comments SET $data");
		} else {
			$save = $this->db->query("UPDATE comments SET $data WHERE id = $id");
		}
		return $save ? 1 : 0;
	}

	function delete_comment()
	{
		extract($_POST);
		$delete = $this->db->query("DELETE FROM comments WHERE id = " . intval($id));
		return $delete ? 1 : 0;
	}
}
