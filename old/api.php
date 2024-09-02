<?php 
    
    include 'db.php';

    $method = $_SERVER['REQUEST_METHOD'];
    $input  = json_decode(file_get_contents('php://input'), true);

    $host   = 'localhost';
    $user   = 'root';
    $pass   = '';
    $db     = 'crud_api_test';

    $conn   = connect($host, $user, $pass, $db);

    $table  = 'users';

    switch ($method) {
        case 'GET' :
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            if ($id) {
                $sql = "SELECT * FROM $table WHERE id = $id";
                $result = $conn->query($sql);
                echo json_encode($result->fetch_assoc());
            } else {
                $sql = "SELECT * FROM $table";
                $result = $conn->query($sql);
                $rows = [];
                while($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                echo json_encode($rows);
            }
        break;

        case 'POST' :
            $column = implode(", ", array_keys($input));
            $values  = implode("', '", array_values($input));
            $sql     = "INSERT INTO $table ($column) VALUES ('$values')";
            if ($conn->query($sql) === true) {
                echo json_encode(['id' => $conn->insert_id]);
            } else {
                echo json_encode(['error' => $conn->error]);
            }
        break;

        case 'PUT':
            $id = intval($_GET['id']);
            $updates = [];
            foreach ($input as $key => $value) {
                $updates[] = "$key='$value'";
            }
            $sql = "UPDATE $table SET " . implode(", ", $updates) . " WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['error' => $conn->error]);
            }
        break;
    
        case 'DELETE':
            $id = intval($_GET['id']);
            $sql = "DELETE FROM $table WHERE id = $id";
            if ($conn->query($sql) === TRUE) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['error' => $conn->error]);
            }
        break;
    
        default:
            echo json_encode(['error' => 'Invalid Request Method']);
        break;

    }

    $conn->close();

?>