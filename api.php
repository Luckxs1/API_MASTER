<?php
include 'db.php';
include 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$input  = json_decode(file_get_contents('php://input'), true);

$host   = 'localhost';
$user   = 'root';
$pass   = '';
$db     = 'crud_api_test';

$conn   = connect($host, $user, $pass, $db);

$table  = 'users';

switch ($method) {
    case 'GET':
        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM $table WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            echo json_encode($result->fetch_assoc());
        } else {
            $stmt = $conn->prepare("SELECT * FROM $table");
            $stmt->execute();
            $result = $stmt->get_result();
            $rows = [];
            while($row = $result->fetch_assoc()) {
                $rows[] = $row;
            }
            echo json_encode($rows);
        }
        break;

    case 'POST':
        $columns = implode(", ", array_keys($input));
        $placeholders = implode(", ", array_fill(0, count($input), '?'));
        $types = str_repeat('s', count($input));
        $values = array_values($input);

        $stmt = $conn->prepare("INSERT INTO $table ($columns) VALUES ($placeholders)");
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            echo json_encode(['id' => $stmt->insert_id]);
        } else {
            echo json_encode(['error' => $stmt->error]);
        }
        break;

    case 'PUT':
        $id = intval($_GET['id']);
        $updates = [];
        $types = '';
        $values = [];
        
        foreach ($input as $key => $value) {
            $updates[] = "$key = ?";
            $types .= 's';
            $values[] = $value;
        }
        
        $types .= 'i';
        $values[] = $id;

        $stmt = $conn->prepare("UPDATE $table SET " . implode(", ", $updates) . " WHERE id = ?");
        $stmt->bind_param($types, ...$values);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['error' => $stmt->error]);
        }
        break;

    case 'DELETE':
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("DELETE FROM $table WHERE id = ?");
        $stmt->bind_param('i', $id);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['error' => $stmt->error]);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid Request Method']);
        break;
}

$conn->close();
?>
