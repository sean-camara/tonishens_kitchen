<?php
session_start();

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: sign-in.php");
    exit();
}

include 'connect.php';

// Handle form submissions (add/edit/delete) for History, Contacts, FAQs

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update History
    if (isset($_POST['history'])) {
        $history = $_POST['history'];
        // Upsert into about_history table (assuming only one row)
        $stmt = $conn->prepare("INSERT INTO about_history (id, content) VALUES (1, ?) ON DUPLICATE KEY UPDATE content=?");
        $stmt->bind_param("ss", $history, $history);
        $stmt->execute();
        $stmt->close();
    }

    // Contacts CRUD - add/edit/delete handled via ajax (later)

    // FAQs CRUD - add/edit/delete handled via ajax (later)

    // For now, after POST redirect to avoid form resubmission
    header("Location: admin-about.php");
    exit();
}

// Fetch current History
$history = "";
$result = $conn->query("SELECT content FROM about_history WHERE id = 1");
if ($result && $row = $result->fetch_assoc()) {
    $history = $row['content'];
}

// Fetch Contacts
$contacts = [];
$res = $conn->query("SELECT * FROM about_contacts ORDER BY id ASC");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $contacts[] = $row;
    }
}

// Fetch FAQs
$faqs = [];
$res2 = $conn->query("SELECT * FROM about_faqs ORDER BY id ASC");
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        $faqs[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin About Page Management</title>
    <link rel="stylesheet" href="admin-about-style.css" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
    <style>
        /* Add minimal inline style for layout */
        .section { margin-bottom: 2rem; padding: 1rem; border: 1px solid #ccc; border-radius: 6px; }
        label { font-weight: bold; display: block; margin-bottom: 0.5rem; }
        textarea, input[type="text"], input[type="url"], input[type="email"], input[type="tel"] {
            width: 100%; padding: 0.5rem; margin-bottom: 1rem;
            border: 1px solid #aaa; border-radius: 4px; font-size: 1rem;
        }
        table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
        th, td { border: 1px solid #2d2d2d; padding: 0.5rem; }
        th { background: #f0f0f0; }
        button { cursor: pointer; padding: 0.4rem 1rem; border: none; border-radius: 4px; }
        .btn-add { background: #FF7750; color: white; }
        .btn-edit { background: #007bff; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        .inline-form { display: flex; gap: 0.5rem; margin-bottom: 1rem; }
        .inline-form input { flex: 1; }
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 4px;
            text-decoration: none;
            margin-bottom: 1.5rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin.php" id="back-dh-btn" class="btn-back"><i class="fa fa-arrow-left"></i> Back to Dashboard</a>

        <h1>Manage About Page Content</h1>

        <!-- History Section -->
        <div class="section" id="history-section">
            <h2>Store History</h2>
            <form method="POST" action="admin-about.php">
                <label for="history">Edit History Text:</label>
                <textarea id="history" name="history" rows="6" required><?php echo htmlspecialchars($history); ?></textarea>
                <button type="submit" class="btn-add">Save History</button>
            </form>
        </div>

        <!-- Contact Info Section -->
        <div class="section" id="contacts-section">
            <h2>Contact Information</h2>
            <button id="add-contact-btn" class="btn-add"><i class="fa fa-plus"></i> Add New Contact</button>

            <table id="contacts-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Value</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contacts as $c): ?>
                    <tr data-id="<?php echo $c['id']; ?>">
                        <td><?php echo htmlspecialchars($c['type']); ?></td>
                        <td><?php echo htmlspecialchars($c['value']); ?></td>
                        <td>
                            <button class="btn-edit contact-edit-btn"><i class="fa fa-pen"></i> Edit</button>
                            <button class="btn-delete contact-delete-btn"><i class="fa fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- FAQ Section -->
        <div class="section" id="faq-section">
            <h2>Frequently Asked Questions</h2>
            <button id="add-faq-btn" class="btn-add"><i class="fa fa-plus"></i> Add New FAQ</button>

            <table id="faq-table">
                <thead>
                    <tr>
                        <th>Question</th>
                        <th>Answer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faqs as $f): ?>
                    <tr data-id="<?php echo $f['id']; ?>">
                        <td><?php echo htmlspecialchars($f['question']); ?></td>
                        <td><?php echo htmlspecialchars($f['answer']); ?></td>
                        <td>
                            <button class="btn-edit faq-edit-btn"><i class="fa fa-pen"></i> Edit</button>
                            <button class="btn-delete faq-delete-btn"><i class="fa fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="admin-about.js"></script>
</body>
</html>
