<?php
require 'connect.php';

$action = $_REQUEST['action'];

function getInput($key) {
    return trim($_POST[$key] ?? '');
}

if ($action === "save_history") {
    $content = getInput("content");
    $conn->query("DELETE FROM about_history");
    $stmt = $conn->prepare("INSERT INTO about_history (content) VALUES (?)");
    if (!$stmt) die("Prepare failed: " . $conn->error);
    $stmt->bind_param("s", $content);
    if (!$stmt->execute()) die("Execute failed: " . $stmt->error);
    exit;
}

if ($action === "save_contact") {
    $id    = isset($_POST['id']) ? intval($_POST['id']) : null;
    $type  = getInput("type");
    $value = getInput("value");
    if ($id) {
        $stmt = $conn->prepare("UPDATE about_contacts SET type=?, value=? WHERE id=?");
        if (!$stmt) die("Prepare failed: " . $conn->error);
        $stmt->bind_param("ssi", $type, $value, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO about_contacts (type, value) VALUES (?, ?)");
        if (!$stmt) die("Prepare failed: " . $conn->error);
        $stmt->bind_param("ss", $type, $value);
    }
    if (!$stmt->execute()) die("Execute failed: " . $stmt->error);
    exit;
}

if ($action === "delete_contact") {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM about_contacts WHERE id=$id");
    exit;
}

if ($action === "save_social") {
    $id       = isset($_POST['id']) ? intval($_POST['id']) : null;
    $platform = getInput("platform");
    $url      = getInput("url");
    if ($id) {
        $stmt = $conn->prepare("UPDATE social_media_contacts SET platform=?, url=? WHERE id=?");
        if (!$stmt) die("Prepare failed: " . $conn->error);
        $stmt->bind_param("ssi", $platform, $url, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO social_media_contacts (platform, url) VALUES (?, ?)");
        if (!$stmt) die("Prepare failed: " . $conn->error);
        $stmt->bind_param("ss", $platform, $url);
    }
    if (!$stmt->execute()) die("Execute failed: " . $stmt->error);
    exit;
}

if ($action === "delete_social") {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM social_media_contacts WHERE id=$id");
    exit;
}

if ($action === "save_faq") {
    $id       = isset($_POST['id']) ? intval($_POST['id']) : null;
    $question = getInput("question");
    $answer   = getInput("answer");
    if ($id) {
        $stmt = $conn->prepare("UPDATE about_faqs SET question=?, answer=? WHERE id=?");
        if (!$stmt) die("Prepare failed: " . $conn->error);
        $stmt->bind_param("ssi", $question, $answer, $id);
    } else {
        $stmt = $conn->prepare("INSERT INTO about_faqs (question, answer) VALUES (?, ?)");
        if (!$stmt) die("Prepare failed: " . $conn->error);
        $stmt->bind_param("ss", $question, $answer);
    }
    if (!$stmt->execute()) die("Execute failed: " . $stmt->error);
    exit;
}

if ($action === "delete_faq") {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM about_faqs WHERE id=$id");
    exit;
}

if ($action === "load_all") {
    $out = [];
    $his = $conn->query("SELECT content FROM about_history LIMIT 1")->fetch_assoc();
    $out['history']  = $his['content'] ?? "";
    $out['contacts'] = $conn->query("SELECT id,type,value FROM about_contacts")->fetch_all(MYSQLI_ASSOC);
    $out['socials']  = $conn->query("SELECT id,platform,url FROM social_media_contacts")->fetch_all(MYSQLI_ASSOC);
    $out['faqs']     = $conn->query("SELECT id,question,answer FROM about_faqs")->fetch_all(MYSQLI_ASSOC);
    header('Content-Type: application/json');
    echo json_encode($out);
    exit;
}
