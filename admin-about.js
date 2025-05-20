document.addEventListener("DOMContentLoaded", () => {

    // Helper to send POST AJAX requests
    function postData(data) {
        return fetch("admin-about-action.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams(data)
        }).then(res => res.json());
    }

    // --- CONTACTS ---
    const contactsTable = document.querySelector("#contacts-table tbody");
    const addContactBtn = document.getElementById("add-contact-btn");

    function createContactRow(contact) {
        const tr = document.createElement("tr");
        tr.dataset.id = contact.id;
        tr.innerHTML = `
            <td class="contact-type">${contact.type}</td>
            <td class="contact-value">${contact.value}</td>
            <td>
                <button class="btn-edit contact-edit-btn"><i class="fa fa-pen"></i> Edit</button>
                <button class="btn-delete contact-delete-btn"><i class="fa fa-trash"></i> Delete</button>
            </td>
        `;
        return tr;
    }

    // Add Contact
    addContactBtn.addEventListener("click", () => {
        if (document.querySelector("#contacts-section .inline-form")) return;
        const form = document.createElement("div");
        form.className = "inline-form";
        form.innerHTML = `
            <input type="text" name="type" placeholder="Contact Type (e.g. Phone, Email)" required />
            <input type="text" name="value" placeholder="Contact Value" required />
            <button class="btn-add">Add</button>
            <button class="btn-delete cancel-btn">Cancel</button>
        `;
        contactsTable.parentElement.insertBefore(form, contactsTable);

        form.querySelector(".btn-add").addEventListener("click", async () => {
            const type = form.querySelector('input[name="type"]').value.trim();
            const value = form.querySelector('input[name="value"]').value.trim();
            if (!type || !value) return alert("Please fill in all fields");

            const res = await postData({
                action: "add-contact",
                contact_type: type,
                contact_value: value
            });
            console.log("add-contact response:", res);
            if (res.success) {
                contactsTable.appendChild(createContactRow({ id: res.id, type, value }));
                form.remove();
            } else {
                alert(res.error || "Failed to add contact");
            }
        });

        form.querySelector(".cancel-btn").addEventListener("click", () => form.remove());
    });

    // Edit/Delete Contacts
    contactsTable.addEventListener("click", async (e) => {
        // EDIT
        if (e.target.closest(".contact-edit-btn")) {
            const tr = e.target.closest("tr");
            if (document.querySelector("#contacts-section .inline-form")) return alert("Finish editing first");
            const currentType  = tr.querySelector(".contact-type").textContent;
            const currentValue = tr.querySelector(".contact-value").textContent;

            tr.innerHTML = `
                <td><input type="text" name="type" value="${currentType}" required /></td>
                <td><input type="text" name="value" value="${currentValue}" required /></td>
                <td>
                    <button class="btn-add save-btn">Save</button>
                    <button class="btn-delete cancel-btn">Cancel</button>
                </td>
            `;

            tr.querySelector(".save-btn").addEventListener("click", async () => {
                const newType  = tr.querySelector('input[name="type"]').value.trim();
                const newValue = tr.querySelector('input[name="value"]').value.trim();
                if (!newType || !newValue) return alert("Please fill in all fields");

                console.log("Sending edit-contact:", {
                  action: "edit-contact",
                  id: tr.dataset.id,
                  contact_type: newType,
                  contact_value: newValue
                });
                const res = await postData({
                    action: "edit-contact",
                    id: tr.dataset.id,
                    contact_type: newType,
                    contact_value: newValue
                });
                console.log("Response edit-contact:", res);

                if (res.success) {
                    tr.innerHTML = `
                        <td class="contact-type">${newType}</td>
                        <td class="contact-value">${newValue}</td>
                        <td>
                            <button class="btn-edit contact-edit-btn"><i class="fa fa-pen"></i> Edit</button>
                            <button class="btn-delete contact-delete-btn"><i class="fa fa-trash"></i> Delete</button>
                        </td>
                    `;
                } else {
                    alert(res.error || "Failed to save");
                }
            });

            tr.querySelector(".cancel-btn").addEventListener("click", () => {
                tr.innerHTML = `
                    <td class="contact-type">${currentType}</td>
                    <td class="contact-value">${currentValue}</td>
                    <td>
                        <button class="btn-edit contact-edit-btn"><i class="fa fa-pen"></i> Edit</button>
                        <button class="btn-delete contact-delete-btn"><i class="fa fa-trash"></i> Delete</button>
                    </td>
                `;
            });

        // DELETE
        } else if (e.target.closest(".contact-delete-btn")) {
            const tr = e.target.closest("tr");
            if (!confirm("Delete this contact?")) return;
            console.log("Sending delete-contact:", { action: "delete-contact", id: tr.dataset.id });
            const res = await postData({ action: "delete-contact", id: tr.dataset.id });
            console.log("Response delete-contact:", res);
            if (res.success) {
                tr.remove();
            } else {
                alert(res.error || "Failed to delete");
            }
        }
    });


    // --- FAQs ---
    const faqTable = document.querySelector("#faq-table tbody");
    const addFaqBtn = document.getElementById("add-faq-btn");

    function createFaqRow(faq) {
        const tr = document.createElement("tr");
        tr.dataset.id = faq.id;
        tr.innerHTML = `
            <td class="faq-question">${faq.question}</td>
            <td class="faq-answer">${faq.answer}</td>
            <td>
                <button class="btn-edit faq-edit-btn"><i class="fa fa-pen"></i> Edit</button>
                <button class="btn-delete faq-delete-btn"><i class="fa fa-trash"></i> Delete</button>
            </td>
        `;
        return tr;
    }

    // Add FAQ
    addFaqBtn.addEventListener("click", () => {
        if (document.querySelector("#faq-section .inline-form")) return;
        const form = document.createElement("div");
        form.className = "inline-form";
        form.innerHTML = `
            <input type="text" name="question" placeholder="Question" required />
            <input type="text" name="answer" placeholder="Answer" required />
            <button class="btn-add">Add</button>
            <button class="btn-delete cancel-btn">Cancel</button>
        `;
        faqTable.parentElement.insertBefore(form, faqTable);

        form.querySelector(".btn-add").addEventListener("click", async () => {
            const question = form.querySelector('input[name="question"]').value.trim();
            const answer   = form.querySelector('input[name="answer"]').value.trim();
            if (!question || !answer) return alert("Please fill in all fields");

            const res = await postData({ action: "add-faq", question, answer });
            console.log("add-faq response:", res);
            if (res.success) {
                faqTable.appendChild(createFaqRow({ id: res.id, question, answer }));
                form.remove();
            } else {
                alert(res.error || "Failed to add FAQ");
            }
        });

        form.querySelector(".cancel-btn").addEventListener("click", () => form.remove());
    });

    // Edit/Delete FAQ
    faqTable.addEventListener("click", async (e) => {
        if (e.target.closest(".faq-edit-btn")) {
            const tr = e.target.closest("tr");
            if (document.querySelector("#faq-section .inline-form")) return alert("Finish editing first");
            const currentQ = tr.querySelector(".faq-question").textContent;
            const currentA = tr.querySelector(".faq-answer").textContent;

            tr.innerHTML = `
                <td><input type="text" name="question" value="${currentQ}" required /></td>
                <td><input type="text" name="answer" value="${currentA}" required /></td>
                <td>
                    <button class="btn-add save-btn">Save</button>
                    <button class="btn-delete cancel-btn">Cancel</button>
                </td>
            `;

            tr.querySelector(".save-btn").addEventListener("click", async () => {
                const newQ = tr.querySelector('input[name="question"]').value.trim();
                const newA = tr.querySelector('input[name="answer"]').value.trim();
                if (!newQ || !newA) return alert("Please fill in all fields");

                console.log("Sending edit-faq:", {
                  action:   "edit-faq",
                  id:       tr.dataset.id,
                  question: newQ,
                  answer:   newA
                });
                const res = await postData({
                    action: "edit-faq",
                    id: tr.dataset.id,
                    question: newQ,
                    answer:   newA
                });
                console.log("Response edit-faq:", res);

                if (res.success) {
                    tr.innerHTML = `
                        <td class="faq-question">${newQ}</td>
                        <td class="faq-answer">${newA}</td>
                        <td>
                            <button class="btn-edit faq-edit-btn"><i class="fa fa-pen"></i> Edit</button>
                            <button class="btn-delete faq-delete-btn"><i class="fa fa-trash"></i> Delete</button>
                        </td>
                    `;
                } else {
                    alert(res.error || "Failed to save");
                }
            });

            tr.querySelector(".cancel-btn").addEventListener("click", () => {
                tr.innerHTML = `
                    <td class="faq-question">${currentQ}</td>
                    <td class="faq-answer">${currentA}</td>
                    <td>
                        <button class="btn-edit faq-edit-btn"><i class="fa fa-pen"></i> Edit</button>
                        <button class="btn-delete faq-delete-btn"><i class="fa fa-trash"></i> Delete</button>
                    </td>
                `;
            });

        } else if (e.target.closest(".faq-delete-btn")) {
            const tr = e.target.closest("tr");
            if (!confirm("Delete this FAQ?")) return;
            console.log("Sending delete-faq:", { action: "delete-faq", id: tr.dataset.id });
            const res = await postData({ action: "delete-faq", id: tr.dataset.id });
            console.log("Response delete-faq:", res);
            if (res.success) {
                tr.remove();
            } else {
                alert(res.error || "Failed to delete");
            }
        }
    });

});
