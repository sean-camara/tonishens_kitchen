// includes/admin-scripts.js
document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("userFormModal");
  const closeBtn = document.querySelector(".closeBtn");
  const addBtn = document.getElementById("addAdminBtn");
  const editButtons = document.querySelectorAll(".editBtn");
  const form = modal.querySelector("form");

  // Open modal for Add Admin
  addBtn.addEventListener("click", () => {
    clearForm();
    form.action = "includes/admin-action.php";
    form.querySelector("button[type='submit']").name = "save_admin";
    form.querySelector("#password").required = true;
    modal.style.display = "block";
  });

  // Close modal
  closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
  });
  window.addEventListener("click", e => { if (e.target === modal) modal.style.display = "none"; });

  // Load Edit Data
  editButtons.forEach(btn => {
    btn.addEventListener("click", () => {
      document.getElementById("user_id").value   = btn.dataset.id;
      document.getElementById("fname").value     = btn.dataset.fname;
      document.getElementById("lname").value     = btn.dataset.lname;
      document.getElementById("email").value     = btn.dataset.email;
      document.getElementById("password").required = false;

      form.action = "includes/admin-action.php";
      form.querySelector("button[type='submit']").name = "save_admin";
      modal.style.display = "block";
    });
  });

  function clearForm() {
    ["user_id","fname","lname","email","password","profile_pic"].forEach(id => {
      const el = document.getElementById(id);
      if(el) el.value = "";
    });
    document.getElementById("password").required = true;
  }
});
