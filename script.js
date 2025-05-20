// Elements
const sections = {
    profile: document.getElementById('profile_con'),
    settings: document.getElementById('settings_con'),
    logout: document.getElementById('logout_con')
};

const buttons = {
    profile: document.getElementById('profile'),
    settings: document.getElementById('settings'),
    logout: document.getElementById('logout')
};

// Function to toggle visibility
const toggleVisibility = (activeSection) => {
    for (const section in sections) {
        if (sections.hasOwnProperty(section) && sections[section]) {
            sections[section].style.display = (section === activeSection) ? 'block' : 'none';
        }
    }
};

// Event listeners for buttons
for (const button in buttons) {
    if (buttons.hasOwnProperty(button) && buttons[button]) {
        buttons[button].addEventListener('click', () => {
            toggleVisibility(button);
        });
    }
}

// Logout button handler
const logoutBtn = document.getElementById('logoutBtn');
if (logoutBtn) {
    logoutBtn.addEventListener('click', (e) => {
        e.preventDefault();
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = 'logout.php';
        }
    });
}

// Profile picture change button handler
const changePicBtn = document.getElementById('changePicBtn');
const profilePicInput = document.getElementById('profile_pic');
const profilePreview = document.getElementById('profilePreview');

if (changePicBtn && profilePicInput) {
    changePicBtn.addEventListener('click', () => {
        profilePicInput.click();
    });
}

if (profilePicInput && profilePreview) {
    profilePicInput.addEventListener('change', (event) => {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => {
                profilePreview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    });
}

document.getElementById("logoutBtn").addEventListener("click", function() {
    window.location.href = "logout.php";
});