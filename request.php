<?php
include 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if($method === 'GET'){
    $result = $conn->query("SELECT * FROM requests ORDER BY id ASC");
    $rows = [];
    while($row = $result->fetch_assoc()) { $rows[] = $row; }
    echo json_encode($rows);
}

if($method === 'POST'){
    $id = $_POST['id'] ?? '';
    $requester_name = $_POST['requester_name'];
    $request_date = $_POST['request_date'];
    $item_name = $_POST['item_name'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];

    // check inventory stock
    $stmt = $conn->prepare("SELECT quantity FROM inventory WHERE item_name=?");
    $stmt->bind_param("s", $item_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if($res->num_rows == 0){ echo json_encode(["error"=>"Item not found"]); exit; }
    $row = $res->fetch_assoc();

    if($row['quantity'] < $quantity){
        echo json_encode(["error"=>"Not enough stock"]);
        exit;
    }

    if($id){
        $stmt = $conn->prepare("UPDATE requests SET requester_name=?, request_date=?, item_name=?, quantity=?, description=? WHERE id=?");
        $stmt->bind_param("sssisi", $requester_name, $request_date, $item_name, $quantity, $description, $id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO requests (requester_name, request_date, item_name, quantity, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssds", $requester_name, $request_date, $item_name, $quantity, $description);
        $stmt->execute();

        // deduct stock automatically
        $stmt2 = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_name=?");
        $stmt2->bind_param("is", $quantity, $item_name);
        $stmt2->execute();
    }

    echo json_encode(["success"=>true]);
}

if($method === 'DELETE'){
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = $_DELETE['id'];

    // restore stock
    $stmt = $conn->prepare("SELECT item_name, quantity FROM requests WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $item = $res['item_name'];
    $qty = $res['quantity'];

    $stmt2 = $conn->prepare("UPDATE inventory SET quantity = quantity + ? WHERE item_name=?");
    $stmt2->bind_param("is", $qty, $item);
    $stmt2->execute();

    // delete request
    $stmt3 = $conn->prepare("DELETE FROM requests WHERE id=?");
    $stmt3->bind_param("i", $id);
    $stmt3->execute();

    echo json_encode(["success"=>true]);
}
?>