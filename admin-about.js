document.addEventListener("DOMContentLoaded", () => {
  const backBtn = document.getElementById("back-btn");
  if (backBtn) {
    backBtn.addEventListener("click", () => {
      window.location.href = "admin.php";
    });
  }
  
  loadAll();

  // Save Store History
  document.getElementById('save-history').addEventListener('click', () => {
    const content = encodeURIComponent(
      document.getElementById('store-history-content').value
    );
    const status  = document.getElementById('save-status');

    fetch("admin-about-action.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `action=save_history&content=${content}`
    })
    .then(res => {
      if (!res.ok) throw new Error("History save failed");
      status.classList.remove('hidden');
      setTimeout(() => status.classList.add('hidden'), 2500);
    })
    .catch(err => console.error(err));
  });

  // Add buttons
  document.getElementById("add-contact").onclick = () => {
    document.querySelector("#contact-table tbody")
      .appendChild(createEditableRow("contact"));
  };
  document.getElementById("add-social").onclick = () => {
    document.querySelector("#social-table tbody")
      .appendChild(createEditableRow("social"));
  };
  document.getElementById("add-faq").onclick = () => {
    document.querySelector("#faq-table tbody")
      .appendChild(createEditableRow("faq"));
  };
});

function createEditableRow(type, data = {}) {
  const tr = document.createElement("tr");
  tr.classList.add("animate__animated","animate__fadeInUp","row-animate");

  let html = "";
  if (type === "contact") {
    html = `
      <td><input type="text" value="${data.type || ""}"></td>
      <td><input type="text" value="${data.value || ""}"></td>
    `;
  } else if (type === "social") {
    html = `
      <td><input type="text" value="${data.platform || ""}"></td>
      <td><input type="text" value="${data.url || ""}"></td>
    `;
  } else if (type === "faq") {
    html = `
      <td><input type="text" value="${data.question || ""}"></td>
      <td><input type="text" value="${data.answer || ""}"></td>
    `;
  }

  html += `
    <td>
      <button class="save-btn">Save</button>
      <button class="delete-btn">Delete</button>
    </td>
  `;
  tr.innerHTML = html;

  tr.querySelector(".save-btn").onclick = () => saveRow(type, tr, data.id);
  tr.querySelector(".delete-btn").onclick = () => {
    if (data.id && confirm("Are you sure?")) {
      deleteRow(type, data.id);
      tr.remove();
    } else if (!data.id) {
      tr.remove();
    }
  };

  return tr;
}

function saveRow(type, tr, id = null) {
  const inputs = tr.querySelectorAll("input");
  let body = `action=save_${type}`;
  if (id) body += `&id=${id}`;

  let keys = [];
  if (type === "contact") keys = ["type","value"];
  if (type === "social")  keys = ["platform","url"];
  if (type === "faq")     keys = ["question","answer"];

  inputs.forEach((input, i) => {
    body += `&${keys[i]}=${encodeURIComponent(input.value)}`;
  });

  fetch("admin-about-action.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body
  })
  .then(res => {
    if (!res.ok) throw new Error("Save failed");
    return res.text();
  })
  .then(() => loadAll())
  .catch(err => console.error(err));
}

function deleteRow(type, id) {
  fetch("admin-about-action.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `action=delete_${type}&id=${id}`
  });
}

function loadAll() {
  fetch("admin-about-action.php?action=load_all")
    .then(res => res.json())
    .then(data => {
      // history
      document.getElementById("store-history-content").value = data.history || "";

      // contacts
      const cb = document.querySelector("#contact-table tbody");
      cb.innerHTML = "";
      data.contacts.forEach(c => cb.appendChild(createEditableRow("contact", c)));

      // socials
      const sb = document.querySelector("#social-table tbody");
      sb.innerHTML = "";
      data.socials.forEach(s => sb.appendChild(createEditableRow("social", s)));

      // faqs
      const fb = document.querySelector("#faq-table tbody");
      fb.innerHTML = "";
      data.faqs.forEach(f => fb.appendChild(createEditableRow("faq", f)));
    });
}
