<?php
session_start();
header('Content-Type: application/json');
require 'connect.php';

// -------- History --------
$history = "";
if ($r = $conn->query("SELECT content FROM about_history LIMIT 1")) {
    if ($row = $r->fetch_assoc()) {
        $history = $row['content'];
    }
    $r->free();
}

// -------- Contacts --------
$contacts = [];
if ($r = $conn->query("SELECT `type`, `value` FROM about_contacts ORDER BY id")) {
    while ($row = $r->fetch_assoc()) {
        $contacts[] = $row;
    }
    $r->free();
}

// -------- Social Media --------
$social_media = [];
if ($r = $conn->query("SELECT platform, url FROM social_media_contacts ORDER BY id")) {
    while ($row = $r->fetch_assoc()) {
        $social_media[] = $row;
    }
    $r->free();
}

// -------- FAQs --------
$faqs = [];
if ($r = $conn->query("SELECT question, answer FROM about_faqs ORDER BY id")) {
    while ($row = $r->fetch_assoc()) {
        $faqs[] = $row;
    }
    $r->free();
}

echo json_encode([
    'success' => true,
    'data'    => [
        'history'      => $history,
        'contacts'     => $contacts,
        'social_media' => $social_media,
        'faqs'         => $faqs,
    ]
]);
exit;
