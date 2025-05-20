<?php
session_start();
include 'connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$response = [
    'success' => false,
    'data' => []
];

// Fetch History
$sql_history = "SELECT content FROM about_history LIMIT 1";
$res_history = $conn->query($sql_history);
$history = $res_history && $res_history->num_rows > 0 ? $res_history->fetch_assoc()['content'] : '';

// Fetch Contact Info (assuming only 1 row)
$sql_contact = "SELECT * FROM about_contact LIMIT 1";
$res_contact = $conn->query($sql_contact);
$contact = $res_contact && $res_contact->num_rows > 0 ? $res_contact->fetch_assoc() : [];

// Fetch Social Media links (multiple rows)
$sql_social = "SELECT platform, url FROM about_social_media";
$res_social = $conn->query($sql_social);
$social_media = [];
if ($res_social && $res_social->num_rows > 0) {
    while ($row = $res_social->fetch_assoc()) {
        $social_media[] = $row;
    }
}

// Fetch FAQs
$sql_faq = "SELECT question, answer FROM about_faqs ORDER BY id ASC";
$res_faq = $conn->query($sql_faq);
$faqs = [];
if ($res_faq && $res_faq->num_rows > 0) {
    while ($row = $res_faq->fetch_assoc()) {
        $faqs[] = $row;
    }
}

$response['success'] = true;
$response['data'] = [
    'history' => $history,
    'contact' => $contact,
    'social_media' => $social_media,
    'faqs' => $faqs
];

echo json_encode($response);
exit();
