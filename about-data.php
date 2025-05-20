<?php
// about-data.php

include 'connect.php';

// -------- Store History --------
$store_history = "Tonishen's Kitchen started as a small family-owned business in 2010...";
if ($res = $conn->query("SELECT content FROM about_history WHERE id = 1 LIMIT 1")) {
    if ($row = $res->fetch_assoc()) {
        $store_history = $row['content'];
    }
    $res->free();
}

// -------- Contact Info --------
$contact_info = [
    'mobile'    => '+63 912 345 6789',
    'telephone' => '+63 (02) 8765 4321',
    'email'     => 'hello@tonishenskitchen.com',
    'address'   => '1234 Mabuhay St., Quezon City, Philippines'
];
if ($res = $conn->query("SELECT `type`, `value` FROM about_contacts")) {
    while ($row = $res->fetch_assoc()) {
        $key = strtolower($row['type']);
        if (array_key_exists($key, $contact_info)) {
            $contact_info[$key] = $row['value'];
        }
    }
    $res->free();
}

// -------- Social Media --------
// Assumes you have a separate table `about_social_media(type, url)`
$social_media = [
    ['platform' => 'Facebook',  'url' => 'https://facebook.com/tonishenskitchen'],
    ['platform' => 'Instagram', 'url' => 'https://instagram.com/tonishenskitchen'],
    ['platform' => 'Twitter',   'url' => 'https://twitter.com/tonishenskitchen'],
];
if ($res = $conn->query("SELECT `platform`, `url` FROM about_social_media ORDER BY id")) {
    $social_media = [];
    while ($row = $res->fetch_assoc()) {
        $social_media[] = [
            'platform' => $row['platform'],
            'url'      => $row['url']
        ];
    }
    $res->free();
}

// -------- FAQs --------
$faqs = [
    ['question' => 'Do you offer vegan options?',       'answer' => 'Yes! We have a dedicated vegan menu...'],
    ['question' => 'Can I book your venue for events?', 'answer' => 'Absolutely – contact us for details.'],
];
if ($res = $conn->query("SELECT `question`, `answer` FROM about_faqs ORDER BY id")) {
    $faqs = [];
    while ($row = $res->fetch_assoc()) {
        $faqs[] = [
            'question' => $row['question'],
            'answer'   => $row['answer']
        ];
    }
    $res->free();
}

// -------- Admin Note --------
$admin_note = "Admin can update all content via the admin panel."; 
// you can also pull this from a DB table if you prefer

// Close the connection if you’re done with queries
$conn->close();
