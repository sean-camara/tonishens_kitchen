
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Manage About Page</title>
  <link rel="stylesheet" href="admin-about-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>
  <button id="back-btn">← Back</button>
  <div class="container animate__animated animate__fadeIn">
    <h1 class="animate__animated animate__fadeInDown">Manage About Page</h1>

    <!-- Store History -->
    <div class="card animate__animated animate__fadeInUp">
      <h2 class="section-title">Store History</h2>
      <textarea id="store-history-content" placeholder="Enter store history here..."></textarea>
      <button class="orange-btn" id="save-history">Save</button>
      <div id="save-status" class="save-status hidden">Saved successfully!</div>
    </div>

    <!-- Contact Information -->
    <div class="card animate__animated animate__fadeInUp">
      <h2 class="section-title">Contact Information</h2>
      <table id="contact-table">
        <thead>
          <tr><th>Type</th><th>Value</th><th>Actions</th></tr>
        </thead>
        <tbody></tbody>
      </table>
      <button class="orange-btn" id="add-contact">Add New Contact</button>
    </div>

    <!-- Social Media Contacts -->
    <div class="card animate__animated animate__fadeInUp">
      <h2 class="section-title">Social Media Contacts</h2>
      <table id="social-table">
        <thead>
          <tr><th>Platform</th><th>URL</th><th>Actions</th></tr>
        </thead>
        <tbody></tbody>
      </table>
      <button class="orange-btn" id="add-social">Add New Social</button>
    </div>

    <!-- Frequently Asked Questions -->
    <div class="card animate__animated animate__fadeInUp">
      <h2 class="section-title">Frequently Asked Questions</h2>
      <table id="faq-table">
        <thead>
          <tr><th>Question</th><th>Answer</th><th>Actions</th></tr>
        </thead>
        <tbody></tbody>
      </table>
      <button class="orange-btn" id="add-faq">Add New FAQ</button>
    </div>
  </div>

  <script src="admin-about.js"></script>
</body>
</html>
