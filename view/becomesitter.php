<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Become a Sitter - WhiskerHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/becomestyle.css">
</head>
<body>

<div class="navbar">
    <div class="logo">WhiskerHub</div>
    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="findcatsitter.php">Search Sitters</a>
        <a href="becomesitter.php" id="becomeSitterLink" style="display:none">Become a Sitter</a>
        <a href="shopping.php">Shopping</a>
    </div>
</div>

<section class="sitter-hero">
    <h1>Earn money doing what you love</h1>
    <p>Join our community of trusted pet sitters and start your pet-sitting journey today.</p>
</section>

<div class="form-container">
    <div class="register-card">
        <h2>Sitter Registration</h2>
        <p>Fill in your details to create your sitter profile.</p>
        
        <form id="sitterForm">
            <div class="input-row">
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="fullname" id="fullname" placeholder="Enter your full name" required>
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" id="email" placeholder="email@example.com" required>
                </div>
            </div>

            <div class="input-group">
                <label>Phone Number</label>
                <input type="text" name="phone" id="phone" placeholder="012-3456789" required>
            </div>

            <div class="input-group">
                <label>City</label>
                <input type="text" name="bandar" id="bandar" placeholder="e.g. Bangi" required>
            </div>

            <div class="input-group">
                <label>State</label>
                <input type="text" name="negeri" id="negeri" placeholder="e.g. Selangor" required>
            </div>

            <div class="input-row">
            <div class="input-group" style="flex: 2;">
                 <label>Services Offered</label>
                     <div class="checkbox-group">
                    <label><input type="checkbox" value="boarding" class="service"> Boarding</label>
                    <label><input type="checkbox" value="daycare" class="service"> Daycare</label>
                    <label><input type="checkbox" value="grooming" class="service"> Grooming</label>
        </div>
    </div>

    <div class="input-group" style="flex: 1; display: flex; flex-direction: column; gap: 15px;">
        <div>
            <label style="font-size: 14px; margin-bottom: 5px; display: block; font-weight: bold;">Boarding Rate (RM / day)</label>
            <div class="price-input-wrapper">
                <span>RM</span>
                <input type="number" id="rate_boarding" placeholder="0.00" step="0.50">
            </div>
        </div>
        <div>
            <label style="font-size: 14px; margin-bottom: 5px; display: block; font-weight: bold;">Daycare Rate (RM / visit)</label>
            <div class="price-input-wrapper">
                <span>RM</span>
                <input type="number" id="rate_daycare" placeholder="0.00" step="0.50">
            </div>
        </div>
        <div>
            <label style="font-size: 14px; margin-bottom: 5px; display: block; font-weight: bold;">Grooming Rate (RM / session)</label>
            <div class="price-input-wrapper">
                <span>RM</span>
                <input type="number" id="rate_grooming" placeholder="0.00" step="0.50">
            </div>
        </div>
    </div>
</div>
            <div class="input-group" style="margin-top: 20px; border-top: 1px solid #eee; padding-top: 15px;">
                <label style="font-weight: bold; margin-bottom: 5px; display: block;">Add-on Services (Optional)</label>
                <p style="font-size: 12px; color: #888; margin-bottom: 10px;">Define custom services you can offer and set your own prices.</p>
                <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                    <input type="text" id="addon_name" placeholder="e.g. Deep Cleaning, Medication" style="flex: 2; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    <input type="number" id="addon_price" placeholder="0.00" step="0.50" style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    <button type="button" id="btnAddAddon" style="background: #ff7a8a; color: white; border: none; padding: 10px 15px; border-radius: 8px; cursor: pointer; font-weight: bold;">Add</button>
                </div>
                <div id="addonsList" style="display: flex; flex-direction: column; gap: 8px;">
                    <!-- Added addons will appear here -->
                </div>
            </div>
            <button type="submit" class="btn-register">Apply to Become a Sitter</button>
        </form>
    </div>
</div>
<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-app.js";
import { getFirestore, doc, setDoc, getDoc } 
from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { getAuth, onAuthStateChanged } 
from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";
import { getStorage, ref, uploadBytes, getDownloadURL }
from "https://www.gstatic.com/firebasejs/12.12.1/firebase-storage.js";

const firebaseConfig = {
    apiKey: "AIzaSyAzKctHKMlufU_daqSC7xPfVw0JZqMVf1w",
    authDomain: "whiskerhub-67889.firebaseapp.com",
    projectId: "whiskerhub-67889"
};

const app = initializeApp(firebaseConfig);
const db = getFirestore(app);
const auth = getAuth(app);
const storage = getStorage(app);

let cachedUserData = null;
let customAddons = [];

// Handle adding custom addon
document.getElementById("btnAddAddon").addEventListener("click", () => {
    const nameInput = document.getElementById("addon_name");
    const priceInput = document.getElementById("addon_price");
    const name = nameInput.value.trim();
    const price = parseFloat(priceInput.value) || 0;

    if (!name) {
        alert("Please enter add-on service name");
        return;
    }
    if (price <= 0) {
        alert("Please enter a valid price");
        return;
    }

    customAddons.push({ name, price });
    nameInput.value = "";
    priceInput.value = "";
    renderAddonsList();
});

function renderAddonsList() {
    const list = document.getElementById("addonsList");
    list.innerHTML = "";
    customAddons.forEach((addon, idx) => {
        const item = document.createElement("div");
        item.style = "display: flex; justify-content: space-between; align-items: center; background: #f9f9f9; padding: 8px 12px; border: 1px solid #eee; border-radius: 8px; font-size: 14px;";
        item.innerHTML = `
            <span><strong>${addon.name}</strong> (+RM ${addon.price.toFixed(2)})</span>
            <button type="button" style="background: #ff5c5c; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 12px;" onclick="removeAddon(${idx})">Remove</button>
        `;
        list.appendChild(item);
    });
}

window.removeAddon = function(idx) {
    customAddons.splice(idx, 1);
    renderAddonsList();
};

// 🔥 AUTO-FILL USER DATA
onAuthStateChanged(auth, async (user) => {

    if (!user) return;

    try {
        const userRef = doc(db, "pengguna", user.uid);
        const userSnap = await getDoc(userRef);

        if (userSnap.exists()) {
            const data = userSnap.data();
            cachedUserData = data;

            document.getElementById("fullname").value = data.fld_user_name || "";
            document.getElementById("email").value = data.fld_user_email || user.email || "";
            document.getElementById("phone").value = data.fld_user_phone || "";
        }

        // Hide "Become a Sitter" nav link if already a sitter
        const sitterSnap = await getDoc(doc(db, "penjaga_kucing", user.uid));
        if (sitterSnap.exists()) {
            const link = document.getElementById("becomeSitterLink");
            if (link) link.style.display = "none";
        }

    } catch (err) {
        console.error("Auto-fill error:", err);
    }

});


// 🔥 SUBMIT FORM
document.getElementById("sitterForm").addEventListener("submit", async (e) => {

    e.preventDefault();

    const user = auth.currentUser;

    if (!user) {
        alert("Please login first!");
        return;
    }

    // get services
    let services = [];
    document.querySelectorAll(".service:checked").forEach(cb => {
        services.push(cb.value);
    });

    try {

        navigator.geolocation.getCurrentPosition(async (pos) => {

            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            // Carry over existing avatar & bio from pengguna collection if available
            const avatarUrl = cachedUserData ? (cachedUserData.fld_user_avatar || cachedUserData.fld_user_profilePic || "") : "";
            const bioText = cachedUserData ? (cachedUserData.fld_user_desc || "") : "";

            // ✅ SAVE SITTER (use UID as doc ID)
            await setDoc(doc(db, "penjaga_kucing", user.uid), {

                fld_user_ID: user.uid,
                fld_user_fullname: document.getElementById("fullname").value,
                fld_user_email: document.getElementById("email").value,
                fld_user_phone: document.getElementById("phone").value,

                fld_user_bandar: document.getElementById("bandar").value,
                fld_user_negeri: document.getElementById("negeri").value,

                fld_rate_boarding: document.getElementById("rate_boarding").value || "0",
                fld_rate_daycare: document.getElementById("rate_daycare").value || "0",
                fld_rate_grooming: document.getElementById("rate_grooming").value || "0",
                fld_user_kadarBayaran: document.getElementById("rate_daycare").value || "0",
                fld_user_pengalaman: bioText,

                fld_user_jenisPerkhidmatan: services,
                fld_user_ketersediaan: "Available",
                fld_user_addons: customAddons,

                // Save picture URLs from existing profile
                fld_user_avatar: avatarUrl,
                fld_user_profilePic: avatarUrl,

                fld_lat: lat,
                fld_lng: lng

            });

            alert("Successfully registered as a sitter!");
            window.location.href = "profile.php";

        }, (err) => {
            alert("Please allow location access to register as a sitter");
        });

    } catch (error) {
        console.error(error);
        alert(error.message);
    }

});
</script>

</body>
</html>