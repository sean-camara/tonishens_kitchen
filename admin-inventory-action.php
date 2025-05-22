<?php
require 'connect.php';
header('Content-Type: application/json');

$action = $_REQUEST['action'];

function respond($success, $data = [], $message = '') {
    echo json_encode(compact('success','data','message'));
    exit;
}

// Load categories
if ($action === 'load_categories') {
    $cats = $conn->query("SELECT id,name FROM ingredient_categories");
    $out = [];
    while ($r = $cats->fetch_assoc()) $out[] = $r;
    respond(true, $out);
}

// Load inventory (join ingredients + categories + inventory)
if ($action === 'load_inventory') {
    $cat = intval($_GET['category'] ?? 0);
    $q   = $conn->real_escape_string($_GET['q'] ?? '');

    $sql = "
      SELECT
        i.id,
        i.name,
        c.name AS category,
        i.unit,
        IFNULL(inv.stock_count, 0) AS quantity,
        i.reorder_level,
        i.cost_per_unit
      FROM ingredients i
      JOIN ingredient_categories c ON i.category_id = c.id
      LEFT JOIN inventory inv ON inv.item_id = i.id
      WHERE i.name LIKE '%$q%'
    ";
    if ($cat) {
        $sql .= " AND i.category_id = $cat";
    }

    $res = $conn->query($sql);
    $out = [];
    while ($r = $res->fetch_assoc()) {
        $out[] = $r;
    }
    respond(true, $out);
}

// Add ingredient (insert into ingredients, then inventory using posted quantity)
// ────────────────────────────────────────────────────────────────────────────────
// **The only change here is that “unit” is bound as a string (type 's'), not a double.

if ($action === 'add_ingredient') {
    $p = $_POST;
    $stmt = $conn->prepare("INSERT INTO ingredients
      (name, category_id, unit, reorder_level, cost_per_unit)
      VALUES (?, ?, ?, ?, ?)
    ");
    // Bind: name (s), category_id (s or i), unit (s), reorder_level (d), cost_per_unit (d)
    $stmt->bind_param(
      "sssdd",
      $p['name'],
      $p['category_id'],
      $p['unit'],
      $p['reorder_level'],
      $p['cost_per_unit']
    );
    if ($stmt->execute()) {
        // Get new ingredient ID
        $newId = $conn->insert_id;
        // Use posted quantity (as float) when creating the inventory row
        $quantity = floatval($p['quantity']);
        $stmt2 = $conn->prepare("
          INSERT INTO inventory (item_id, item_name, stock_count)
          VALUES (?, ?, ?)
        ");
        $stmt2->bind_param("isd", $newId, $p['name'], $quantity);
        $stmt2->execute();
        respond(true);
    } else {
        respond(false, [], $stmt->error);
    }
}

// Update ingredient info (only reorder_level & cost_per_unit)
if ($action === 'update_ingredient_info') {
    $p = $_POST;
    $stmt = $conn->prepare("
      UPDATE ingredients 
      SET reorder_level = ?, cost_per_unit = ?
      WHERE id = ?
    ");
    $stmt->bind_param("ddi",
      $p['reorder_level'],
      $p['cost_per_unit'],
      $p['id']
    );
    if ($stmt->execute()) respond(true);
    else respond(false, [], $stmt->error);
}

// Delete ingredient (also delete matching inventory row)
if ($action === 'delete_ingredient') {
    $id = intval($_POST['id']);
    // Delete from ingredients
    $conn->query("DELETE FROM ingredients WHERE id=$id");
    // Delete from inventory
    $conn->query("DELETE FROM inventory WHERE item_id=$id");
    respond(true);
}

// Add new category
if ($action === 'add_category') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') respond(false, [], 'Category name is required');

    $stmt = $conn->prepare("INSERT INTO ingredient_categories (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    if ($stmt->execute()) respond(true);
    else respond(false, [], $stmt->error);
}

respond(false, [], 'Unknown action');
