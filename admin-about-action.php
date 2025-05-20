<?php
session_start();
header('Content-Type: application/json');
include 'connect.php';

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

function respond($data) {
    echo json_encode($data);
    exit;
}

switch ($action) {
    case 'add-contact':
        $ctype = trim($_POST['contact_type'] ?? '');
        $cvalue = trim($_POST['contact_value'] ?? '');
        if (!$ctype || !$cvalue) respond(['error' => 'Missing fields']);
        $stmt = $conn->prepare("INSERT INTO about_contacts (type, value) VALUES (?, ?)");
        $stmt->bind_param("ss", $ctype, $cvalue);
        if ($stmt->execute()) {
            respond([
                'success' => true,
                'id'      => $stmt->insert_id,
                'type'    => $ctype,
                'value'   => $cvalue
            ]);
        } else {
            respond([
                'error'     => 'Insert failed',
                'sql_error' => $stmt->error
            ]);
        }
        break;

    case 'edit-contact':
        $id = intval($_POST['id'] ?? 0);
        $ctype = trim($_POST['contact_type'] ?? '');
        $cvalue = trim($_POST['contact_value'] ?? '');
        if (!$id || !$ctype || !$cvalue) respond(['error' => 'Missing fields']);
        $stmt = $conn->prepare("UPDATE about_contacts SET type=?, value=? WHERE id=?");
        $stmt->bind_param("ssi", $ctype, $cvalue, $id);
        if ($stmt->execute()) {
            respond(['success' => true]);
        } else {
            respond([
                'error'     => 'Update failed',
                'sql_error' => $stmt->error
            ]);
        }
        break;

    case 'delete-contact':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) respond(['error' => 'Missing ID']);
        $stmt = $conn->prepare("DELETE FROM about_contacts WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            respond(['success' => true]);
        } else {
            respond([
                'error'     => 'Delete failed',
                'sql_error' => $stmt->error
            ]);
        }
        break;

    case 'add-faq':
        $question = trim($_POST['question'] ?? '');
        $answer   = trim($_POST['answer'] ?? '');
        if (!$question || !$answer) respond(['error' => 'Missing fields']);
        $stmt = $conn->prepare("INSERT INTO about_faqs (question, answer) VALUES (?, ?)");
        $stmt->bind_param("ss", $question, $answer);
        if ($stmt->execute()) {
            respond([
                'success'  => true,
                'id'       => $stmt->insert_id,
                'question' => $question,
                'answer'   => $answer
            ]);
        } else {
            respond([
                'error'     => 'Insert failed',
                'sql_error' => $stmt->error
            ]);
        }
        break;

    case 'edit-faq':
        $id = intval($_POST['id'] ?? 0);
        $question = trim($_POST['question'] ?? '');
        $answer   = trim($_POST['answer'] ?? '');
        if (!$id || !$question || !$answer) respond(['error' => 'Missing fields']);
        $stmt = $conn->prepare("UPDATE about_faqs SET question=?, answer=? WHERE id=?");
        $stmt->bind_param("ssi", $question, $answer, $id);
        if ($stmt->execute()) {
            respond(['success' => true]);
        } else {
            respond([
                'error'     => 'Update failed',
                'sql_error' => $stmt->error
            ]);
        }
        break;

    case 'delete-faq':
        $id = intval($_POST['id'] ?? 0);
        if (!$id) respond(['error' => 'Missing ID']);
        $stmt = $conn->prepare("DELETE FROM about_faqs WHERE id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            respond(['success' => true]);
        } else {
            respond([
                'error'     => 'Delete failed',
                'sql_error' => $stmt->error
            ]);
        }
        break;

    default:
        respond(['error' => 'Invalid action']);
}
