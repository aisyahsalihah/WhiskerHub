<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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

    <div class="input-group" style="flex: 1;">
        <label>Rate per Hour (RM)</label>
        <div class="price-input-wrapper">
            <span>RM</span>
            <input type="number" name="hourly_rate" id="rate" placeholder="0.00" step="0.50" required>
        </div>
    </div>
</div>

            <div class="input-group">
                <label>Experience / Bio</label>
                <textarea name="bio" id="bio" rows="4"></textarea>
            </div>

            <div class="input-group">
                <label>Profile Picture</label>
                <!-- Preview -->
                <div id="picPreviewWrap" style="margin-bottom:10px; display:none;">
                    <img id="picPreview" style="width:100px;height:100px;object-fit:cover;border-radius:50%;border:3px solid #ffb6c1;" alt="Preview">
                </div>
                <input type="file" name="profile_pic" id="profilePicInput" accept="image/*">
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

// 🔥 LIVE PREVIEW for profile picture
document.getElementById("profilePicInput").addEventListener("change", function(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
        document.getElementById("picPreview").src = ev.target.result;
        document.getElementById("picPreviewWrap").style.display = "block";
    };
    reader.readAsDataURL(file);
});


// 🔥 AUTO-FILL USER DATA
onAuthStateChanged(auth, async (user) => {

    if (!user) return;

    try {
        const userRef = doc(db, "pengguna", user.uid);
        const userSnap = await getDoc(userRef);

        if (userSnap.exists()) {
            const data = userSnap.data();

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

    // ambil services
    let services = [];
    document.querySelectorAll(".service:checked").forEach(cb => {
        services.push(cb.value);
    });

    try {

        navigator.geolocation.getCurrentPosition(async (pos) => {

            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;

            // ✅ Upload profile picture if selected
            let avatarUrl = "";
            const picFile = document.getElementById("profilePicInput").files[0];
            if (picFile) {
                const storageRef = ref(storage, `avatars/${user.uid}_${Date.now()}`);
                const snapshot = await uploadBytes(storageRef, picFile);
                avatarUrl = await getDownloadURL(snapshot.ref);
            }

            // ✅ SAVE SITTER (guna UID sebagai doc ID)
            await setDoc(doc(db, "penjaga_kucing", user.uid), {

                fld_user_ID: user.uid,
                fld_user_fullname: document.getElementById("fullname").value,
                fld_user_email: document.getElementById("email").value,
                fld_user_phone: document.getElementById("phone").value,

                fld_user_bandar: document.getElementById("bandar").value,
                fld_user_negeri: document.getElementById("negeri").value,

                fld_user_kadarBayaran: document.getElementById("rate").value,
                fld_user_pengalaman: document.getElementById("bio").value,

                fld_user_jenisPerkhidmatan: services,
                fld_user_ketersediaan: "Available",

                // ✅ Save picture URLs so profile.php can load them
                fld_user_avatar: avatarUrl,
                fld_user_profilePic: avatarUrl,

                fld_lat: lat,
                fld_lng: lng

            });

            alert("Berjaya daftar sebagai sitter!");
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