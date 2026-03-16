<?php
include 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

if($method === 'GET'){
    $result = $conn->query("SELECT * FROM inventory ORDER BY id ASC");
    $rows = [];
    while($row = $result->fetch_assoc()) { $rows[] = $row; }
    echo json_encode($rows);
}

if($method === 'POST'){
    $id = $_POST['id'] ?? '';
    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $quantity = $_POST['quantity'];
    $purchase_date = $_POST['purchase_date'];

    if($id){
        $stmt = $conn->prepare("UPDATE inventory SET item_name=?, category=?, quantity=?, purchase_date=? WHERE id=?");
        $stmt->bind_param("ssisi", $item_name, $category, $quantity, $purchase_date, $id);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO inventory (item_name, category, quantity, purchase_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $item_name, $category, $quantity, $purchase_date);
        $stmt->execute();
    }

    echo json_encode(["success"=>true]);
}

if($method === 'DELETE'){
    parse_str(file_get_contents("php://input"), $_DELETE);
    $id = $_DELETE['id'];
    $stmt = $conn->prepare("DELETE FROM inventory WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    echo json_encode(["success"=>true]);
}
?>