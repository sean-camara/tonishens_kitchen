<?php
require 'connect.php';

// Store History
$store_history = "Tonishen's Kitchen started as a small family-owned business in 2010...";
if ($res = $conn->query("SELECT content FROM about_history LIMIT 1")) {
    if ($row = $res->fetch_assoc()) {
        $store_history = $row['content'];
    }
    $res->free();
}

// Contact Info (dynamic)
$contacts = [];
if ($res = $conn->query("SELECT `type`, `value` FROM about_contacts ORDER BY id")) {
    while ($row = $res->fetch_assoc()) {
        $contacts[] = $row;
    }
    $res->free();
}

// Social Media
$social_media = [];
if ($res = $conn->query("SELECT platform, url FROM social_media_contacts ORDER BY id")) {
    while ($row = $res->fetch_assoc()) {
        $social_media[] = $row;
    }
    $res->free();
}

// FAQs
$faqs = [];
if ($res = $conn->query("SELECT question, answer FROM about_faqs ORDER BY id")) {
    while ($row = $res->fetch_assoc()) {
        $faqs[] = $row;
    }
    $res->free();
}

// Admin Note
$admin_note = "Admin can update all content via the admin panel."; 
