<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sitter Detail - WhiskerHub</title>

<link rel="stylesheet" href="../css/style.css">
<link rel="stylesheet" href="../css/detailstyle.css">

<style>
/* ── Lightbox ───────────────────────────────────────────── */
#lightbox {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.88);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    animation: lbFadeIn 0.25s ease;
    cursor: zoom-out;
}
#lightbox.open { display: flex; }

@keyframes lbFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}

#lightbox img {
    max-width: 90vw;
    max-height: 88vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 30px 80px rgba(0,0,0,0.6);
    cursor: default;
    animation: lbZoomIn 0.25s ease;
}
@keyframes lbZoomIn {
    from { transform: scale(0.92); opacity: 0; }
    to   { transform: scale(1);    opacity: 1; }
}

#lightboxClose {
    position: fixed;
    top: 20px;
    right: 28px;
    font-size: 36px;
    color: #fff;
    cursor: pointer;
    line-height: 1;
    opacity: 0.75;
    transition: opacity 0.2s;
    z-index: 10000;
    user-select: none;
}
#lightboxClose:hover { opacity: 1; }

/* ── Gallery section label ──────────────────────────────── */
#galleryLabel {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2.5px;
    text-transform: uppercase;
    color: #aaa;
    margin-bottom: 12px;
    margin-top: 5px;
    display: none;
}

/* Gallery image clickable */
.gallery-img {
    cursor: zoom-in;
    transition: transform 0.25s, box-shadow 0.25s;
    border-radius: 6px;
}
.gallery-img:hover {
    transform: scale(1.03);
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

#galleryEmpty {
    grid-column: 1 / -1;
    font-size: 13px;
    color: #bbb;
    text-align: center;
    padding: 20px 0;
    display: none;
}
</style>



</head>

<body>

<!-- NAVBAR -->
<div class="navbar">
    <div class="logo">WhiskerHub</div>

    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="findcatsitter.php">Search Sitters</a>
        <a href="becomesitter.php" id="becomeSitterLink" style="display:none">Become a Sitter</a>
        <a href="shopping.php">Shopping</a>
    </div>
</div>


<!-- DETAIL -->
<div class="detail-container">

<div class="detail-wrapper">

<!-- LEFT -->
<div class="left">

    <h1 id="sitterName">Loading...</h1>

    <p id="sitterLocation"></p>

    <p><strong id="sitterPrice"></strong></p>

    <p id="sitterBio"></p>

    <div class="review-section">
        <h3>Reviews</h3>
        <div id="reviewList"></div>
    </div>

    <a id="bookingBtn" class="btn-tempah-full">TEMPAH SEKARANG</a>

</div>

<!-- RIGHT (IMAGE + PROOF) -->
<div class="right">

    <img id="sitterImage" class="profile-img" alt="Sitter photo" style="background:#f5f5f5;">

    <p id="galleryLabel">Progress Photos</p>
    <div id="gallery">
        <p id="galleryEmpty">No progress photos yet.</p>
    </div>

</div>

<!-- LIGHTBOX MODAL -->
<div id="lightbox">
    <span id="lightboxClose" title="Close">&times;</span>
    <img id="lightboxImg" src="" alt="Gallery photo">
</div>

</div>
</div>


<!-- FIREBASE -->
<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-app.js";
import { getFirestore, doc, getDoc, collection, query, where, getDocs } 
from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { getAuth, onAuthStateChanged }
from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";

const firebaseConfig = {
    apiKey: "AIzaSyAzKctHKMlufU_daqSC7xPfVw0JZqMVf1w",
    authDomain: "whiskerhub-67889.firebaseapp.com",
    projectId: "whiskerhub-67889"
};

const app = initializeApp(firebaseConfig);
const db = getFirestore(app);
const auth = getAuth(app);

// Hide "Become a Sitter" for existing sitters
onAuthStateChanged(auth, async (user) => {
    if (!user) return;
    const sitterSnap = await getDoc(doc(db, "penjaga_kucing", user.uid));
    if (sitterSnap.exists()) {
        const link = document.getElementById("becomeSitterLink");
        if (link) link.style.display = "none";
    }
});

// URL ID
const id = new URLSearchParams(window.location.search).get("id");


// 🔥 LOAD SITTER
async function loadSitter(){

    const snap = await getDoc(doc(db, "penjaga_kucing", id));

    if(!snap.exists()){
        alert("Sitter not found");
        return;
    }

    const data = snap.data();

    document.getElementById("sitterName").innerText = data.fld_user_fullname;
    document.getElementById("sitterLocation").innerText =
        "📍 " + data.fld_user_bandar + ", " + data.fld_user_negeri;

    let priceText = "";
    if (data.fld_rate_boarding && Number(data.fld_rate_boarding) > 0) priceText += `Boarding: RM ${data.fld_rate_boarding}/day | `;
    if (data.fld_rate_daycare && Number(data.fld_rate_daycare) > 0) priceText += `Daycare: RM ${data.fld_rate_daycare}/hour | `;
    if (data.fld_rate_grooming && Number(data.fld_rate_grooming) > 0) priceText += `Grooming: RM ${data.fld_rate_grooming}/session`;
    if (priceText.endsWith(" | ")) priceText = priceText.slice(0, -3);
    if (!priceText) priceText = "RM " + (data.fld_user_kadarBayaran || "0") + "/jam";
    document.getElementById("sitterPrice").innerText = priceText;

    document.getElementById("sitterBio").innerText =
        data.fld_user_pengalaman;

    // image — fallback to a clean SVG avatar if no photo saved yet
    const FALLBACK_AVATAR = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='300' height='300' viewBox='0 0 300 300'%3E%3Crect width='300' height='300' fill='%23f5f5f5'/%3E%3Ccircle cx='150' cy='115' r='55' fill='%23d9d9d9'/%3E%3Cellipse cx='150' cy='260' rx='90' ry='60' fill='%23d9d9d9'/%3E%3C/svg%3E`;
    const sitterImg = document.getElementById("sitterImage");
    sitterImg.src   = data.fld_user_avatar || data.fld_user_profilePic || FALLBACK_AVATAR;
    sitterImg.onerror = () => { sitterImg.src = FALLBACK_AVATAR; };

    // gallery
    const gallery    = document.getElementById("gallery");
    const label      = document.getElementById("galleryLabel");
    const emptyMsg   = document.getElementById("galleryEmpty");
    const lightbox   = document.getElementById("lightbox");
    const lbImg      = document.getElementById("lightboxImg");

    if (data.fld_user_gallery && data.fld_user_gallery.length > 0) {
        label.style.display = "block";
        emptyMsg.style.display = "none";

        data.fld_user_gallery.forEach(imgUrl => {
            const img = document.createElement("img");
            img.src       = imgUrl;
            img.className = "gallery-img";
            img.alt       = "Progress photo";
            img.onclick   = () => {
                lbImg.src = imgUrl;
                lightbox.classList.add("open");
            };
            gallery.appendChild(img);
        });
    } else {
        label.style.display  = "none";
        emptyMsg.style.display = "block";
    }

    // Lightbox close handlers
    document.getElementById("lightboxClose").onclick = () => lightbox.classList.remove("open");
    lightbox.onclick = (e) => { if (e.target === lightbox) lightbox.classList.remove("open"); };
    document.addEventListener("keydown", (e) => { if (e.key === "Escape") lightbox.classList.remove("open"); });

    // booking link
    document.getElementById("bookingBtn").href = "booking.php?id=" + id;
}


console.log("SITTER ID:", id); // 🔥 CHECK 1

async function loadReviews() {

    const q = query(
        collection(db, "review"),   // ✔ ikut Firebase kau
        where("sitterID", "==", id) // ✔ ikut field kau
    );

    const snap = await getDocs(q);

    console.log("FOUND:", snap.size); // 🔥 CHECK 2

    const reviewList = document.getElementById("reviewList");

    reviewList.innerHTML = "";

    if (snap.empty) {
        reviewList.innerHTML = "<p>No reviews yet</p>";
        return;
    }

    snap.forEach(doc => {
        const r = doc.data();

        reviewList.innerHTML += `
            <div class="review-card">
                <strong>${r.fld_user_name}</strong>
                <p>⭐ ${r.fld_user_rating}</p>
                <p>${r.fld_user_comment}</p>
            </div>
        `;
    });
}

// run
loadSitter();
loadReviews();

</script>

</body>
</html>