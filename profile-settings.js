// Preview selected image when a new file is picked
document.getElementById('profile_pic').addEventListener('change', function (event) {
  const file = event.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function () {
      const output = document.getElementById('profilePreview');
      output.src = reader.result;
    };
    reader.readAsDataURL(file);

    // Optional: show the filename (if you have a label)
    const label = document.querySelector('.custom-file-upload');
    if (label) {
      label.textContent = file.name;
    }
  }
});

// Sidebar navigation functionality
document.addEventListener("DOMContentLoaded", function () {
  const sidebarItems = document.querySelectorAll(".sidebar ul li");
  const sections = document.querySelectorAll(".settings-section");

  sidebarItems.forEach((item, index) => {
    item.addEventListener("click", () => {
      const isLogout = item.textContent.includes("Logout");

      if (isLogout) {
        const confirmLogout = confirm("Are you sure you want to logout?");
        if (confirmLogout) {
          window.location.href = "logout.php";
        }
        return; // Don't switch section or do anything else if logout
      }

      // Update active item
      sidebarItems.forEach(i => i.classList.remove("active"));
      item.classList.add("active");

      // Show only selected section
      sections.forEach((section, i) => {
        section.style.display = i === index ? "block" : "none";
      });
    });
  });

  // Default: show only the first section
  sections.forEach((section, i) => {
    section.style.display = i === 0 ? "block" : "none";
  });
});

// Dropdown toggle for Update Email / Password form
document.addEventListener("DOMContentLoaded", function () {
  const toggleBtn = document.querySelector(".dropdown-toggle");
  const dropdownForm = document.querySelector(".dropdown-form");

  if (toggleBtn && dropdownForm) {
    toggleBtn.addEventListener("click", () => {
      dropdownForm.style.display = dropdownForm.style.display === "none" ? "block" : "none";
    });
  }
});