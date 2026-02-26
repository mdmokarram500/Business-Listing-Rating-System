<?php
require_once __DIR__ . '/db_connect.php';

// Log request for debugging
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - Request: " . json_encode($_REQUEST) . "\n", FILE_APPEND);

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];
    header('Content-Type: application/json'); // Ensure all responses are JSON

    // Fetch Businesses with Average Rating
    if ($action == 'fetch_businesses') {
        $sql = "SELECT b.*, (SELECT AVG(r.rating) FROM ratings r WHERE r.business_id = b.id) as avg_rating 
                FROM businesses b ORDER BY b.id DESC";
        $result = $conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $row['avg_rating'] = $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
            $data[] = $row;
        }
        echo json_encode($data);
    }

    // Add New Business
    if ($action == 'add_business') {
        $name = trim($_REQUEST['name'] ?? '');
        $address = trim($_REQUEST['address'] ?? '');
        $phone = trim($_REQUEST['phone'] ?? '');
        $email = trim($_REQUEST['email'] ?? '');

        if (empty($name) || empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Name and Email are required.']);
            exit;
        }

        $sql = "INSERT INTO businesses (name, address, phone, email) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $address, $phone, $email);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    }

    // Get Single Business for Editing
    if ($action == 'get_business') {
        $id = $_REQUEST['id'];
        $sql = "SELECT * FROM businesses WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_assoc());
    }

    // Update Business
    if ($action == 'edit_business') {
        $id = intval($_REQUEST['business_id'] ?? 0);
        $name = trim($_REQUEST['name'] ?? '');
        $address = trim($_REQUEST['address'] ?? '');
        $phone = trim($_REQUEST['phone'] ?? '');
        $email = trim($_REQUEST['email'] ?? '');

        if ($id <= 0 || empty($name) || empty($email)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
            exit;
        }

        $sql = "UPDATE businesses SET name = ?, address = ?, phone = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $address, $phone, $email, $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $conn->error]);
        }
    }

    // Delete Business
    if ($action == 'delete_business') {
        $id = $_REQUEST['id'];
        $sql = "DELETE FROM businesses WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        }
    }

    // Save/Update Rating
    if ($action == 'save_rating') {
        $b_id = intval($_REQUEST['business_id'] ?? 0);
        $r_name = trim($_REQUEST['r_name'] ?? '');
        $r_email = trim($_REQUEST['r_email'] ?? '');
        $r_phone = trim($_REQUEST['r_phone'] ?? '');
        $r_val = floatval($_REQUEST['rating_value'] ?? 0);

        if ($b_id <= 0 || empty($r_name) || empty($r_email) || $r_val <= 0) {
            echo json_encode(['status' => 'error', 'message' => 'All fields and a valid rating are required.']);
            exit;
        }

        // Rule 1: Check if same Email OR Phone already exist for this business
        $check_sql = "SELECT id FROM ratings WHERE business_id = ? AND (email = ? OR phone = ?)";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("iss", $b_id, $r_email, $r_phone);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update Existing Rating
            $row = $check_result->fetch_assoc();
            $update_sql = "UPDATE ratings SET name = ?, rating = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sdi", $r_name, $r_val, $row['id']);
            if ($update_stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        } else {
            // New Rating
            $insert_sql = "INSERT INTO ratings (business_id, name, email, phone, rating) VALUES (?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("isssd", $b_id, $r_name, $r_email, $r_phone, $r_val);
            if ($insert_stmt->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => $conn->error]);
            }
        }
    }
}