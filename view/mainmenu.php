
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Menu - WhiskerHub</title>
    <link rel="stylesheet" href="../css/style.css">
    
</head>
<body>

<div class="navbar">
    <a href="mainmenu.php" class="logo">WhiskerHub</a>

    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="findcatsitter.php">Search Sitters</a>
        <a href="becomesitter.php" id="becomeSitterLink" style="display:none">Become a Sitter</a>
        <a href="shopping.php">Shopping</a>
    </div>

    <div class="nav-right" id="navRight">


</div>

</div>
</div>

<!-- HERO -->
<section class="hero">
    <div>
        <div class="trust-badge" style="background: rgba(255,255,255,0.2); display: inline-block; padding: 6px 15px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 15px; backdrop-filter: blur(5px);">🇲🇾 Trusted by 1,000+ Cat Parents</div>
        <h1>Because your cat deserves a vacation too.</h1>
        <p>Connect with vetted, local cat lovers who treat your furry royalty exactly how you would.</p>
        <br>
        <a href="findcatsitter.php" class="btn-primary" style="padding: 15px 30px; font-size: 16px; box-shadow: 0 10px 20px rgba(108, 6, 82, 0.3);">Find a Sitter Nearby</a>
    </div>
</section>

<section class="services">
    <h1>What does your cat need today?</h1>
    <p class="subtitle">Whether you're away for a week or just stuck at the office, we've got you covered.</p>

    <div class="tabs">
        <button class="tab-btn active" onclick="openService(event, 'overnight')">Overnight care</button>
        <button class="tab-btn" onclick="openService(event, 'daytime')">Daytime care</button>
        <button class="tab-btn" onclick="openService(event, 'all')">All services</button>
    </div>

    <div id="overnight" class="tab-content active">
        <div class="service-grid"> <div class="service-card">
                <img src="../photos/catboarding.jpg" alt="Cat Boarding">
                <div class="info">
                    <h2>Boarding</h2>
                    <p><strong>Perfect for:</strong> Your vacations or long trips away.</p>
                    <p>Give your cat a "pawsome" vacation! Your feline friend will stay in a sitter's home with personalized attention.</p>
                </div>
            </div>
            
            <div class="service-card">
                <img src="../photos/housesitting.avif" alt="Cat House Sitting">
                <div class="info">
                    <h2>House Sitting</h2>
                    <p><strong>Perfect for:</strong> Anxious cats who love their own space.</p>
                    <p>Keep your cat in the comfort of their own kingdom. A dedicated sitter will stay at your home.</p>
                </div>
            </div>
        </div>
    </div>

    <div id="daytime" class="tab-content">
        <div class="service-grid">
            <div class="service-card">
                <img src="../photos/dropin.jpeg" alt="Cat Drop-in">
                <div class="info">
                    <h2>Drop-in Visits</h2>
                    <p><strong>Perfect for:</strong> Busy work days or short day-trips.</p>
                    <p>Need someone to check in? Our sitters pop by to refresh water, meals, and scoop the litter box.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="how-it-works">
    <h2 class="section-title">How WhiskerHub works</h2>
    <p class="section-subtitle">Finding a loving sitter for your feline friend is as easy as 1-2-3.</p>

    <div class="steps-container">
        <div class="step-card">
            <div class="image-circle">
                <img src="../photos/catsitter.jpeg" alt="Searching cat"> 
            </div>
            <h3>Find a Sitter</h3>
                <p>Browse verified profiles of cat lovers in your area. Read honest reviews and find the perfect match for your cat's personality.</p>
            </div>

        <div class="step-card">
            <div class="image-circle">
                <img src="../photos/bookcat.jpeg" alt="Booking cat"> 
            </div>
            <h3>Book with Confidence</h3>
                <p>Chat with sitters and book directly through our secure platform. No messy paperwork—just quick, safe, and easy payments.</p>
            </div>

        <div class="step-card">
            <div class="image-circle">
                <img src="../photos/peace.jpg" alt="Relaxing cat"> 
            </div>
            <h3>Peace of Mind</h3>
                <p>Get daily photo updates and enjoy 24/7 support with <strong>WhiskerProtect</strong> while your cat enjoys their "vacation".</p>
            </div>
    </div>
</section>
<!-- TESTIMONIAL -->
<section class="testimonials">
    <h2>Real stories from happy cats (and their humans)</h2>
    <div class="testimonial-grid" style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap; margin-top: 40px;">
        <div class="testimonial-card">
            <p>"I was so anxious leaving Luna for the first time, but my sitter sent me daily photo updates! She even watered my plants. WhiskerHub is a lifesaver."</p>
            <div style="display: flex; align-items: center; margin-top: 20px;">
                <img src="https://ui-avatars.com/api/?name=Farah+A&background=random" style="width: 40px; border-radius: 50%; margin-right: 15px;">
                <h4 style="margin:0; text-align: left;">Farah A.<br><span style="font-size: 12px; color:#888; font-weight: normal;">Kuala Lumpur</span></h4>
            </div>
        </div>
        <div class="testimonial-card">
            <p>"The best part is knowing Oyen is comfortable in his own home. My sitter dropped by exactly when promised and played with him until he was exhausted!"</p>
            <div style="display: flex; align-items: center; margin-top: 20px;">
                <img src="https://ui-avatars.com/api/?name=Hafiz+R&background=random" style="width: 40px; border-radius: 50%; margin-right: 15px;">
                <h4 style="margin:0; text-align: left;">Hafiz R.<br><span style="font-size: 12px; color:#888; font-weight: normal;">Selangor</span></h4>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer style="background: #fff; padding: 40px 20px; text-align: center; border-top: 1px solid #eee; margin-top: 40px;">
    <h2 style="font-family: 'Playfair Display', serif; color: rgb(176, 2, 124); margin-bottom: 10px;">WhiskerHub</h2>
    <p style="color: #777; font-size: 14px; margin-bottom: 20px;">Connecting cat lovers across Malaysia.</p>
    <div style="display: flex; justify-content: center; gap: 20px; margin-bottom: 20px;">
        <a href="#" style="color: #555; text-decoration: none; font-size: 14px;">About Us</a>
        <a href="#" style="color: #555; text-decoration: none; font-size: 14px;">Safety Guidelines</a>
        <a href="#" style="color: #555; text-decoration: none; font-size: 14px;">Contact Support</a>
    </div>
    <p style="color: #aaa; font-size: 12px;">&copy; 2026 WhiskerHub. All rights reserved.</p>
</footer>


<script>
function openService(evt, serviceName) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tab-content");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
        tabcontent[i].classList.remove("active");
    }
    tablinks = document.getElementsByClassName("tab-btn");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    if(serviceName === 'all') {
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].style.display = "block";
        }
    } else {
        document.getElementById(serviceName).style.display = "block";
        document.getElementById(serviceName).classList.add("active");
    }
    evt.currentTarget.className += " active";
}
</script>
<script type="module">

import { initializeApp } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-app.js";

import { 
    getAuth, 
    onAuthStateChanged,
    signOut
} from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";
import { getFirestore, doc, getDoc } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";

const firebaseConfig = {
    apiKey: "AIzaSyAzKctHKMlufU_daqSC7xPfVw0JZqMVf1w",
    authDomain: "whiskerhub-67889.firebaseapp.com",
    projectId: "whiskerhub-67889"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);

const navRight = document.getElementById("navRight");

onAuthStateChanged(auth, async (user) => {
    if (user) {
        // Show/hide "Become a Sitter" based on sitter status
        const sitterSnap = await getDoc(doc(db, "penjaga_kucing", user.uid));
        const link = document.getElementById("becomeSitterLink");
        if (link) link.style.display = sitterSnap.exists() ? "none" : "";

        // 1. Paparkan butang untuk user yang sudah login
        navRight.innerHTML = `
            <a href="message.php" class="circle-btn" style="position:relative;">
                <img src="../photos/history.jpg">
                <span id="navBookingDot" style="display:none; position:absolute; top:0; right:0; width:12px; height:12px; background:#e74c3c; border-radius:50%; border:2px solid #fff;"></span>
            </a>
            <a href="profile.php" class="circle-btn">
                <img src="../photos/profile.jpg">
            </a>
            <a href="#" id="logoutBtn" class="circle-btn">
                <img src="../photos/logout.webp">
            </a>
        `;

        // Check for unread bookings and messages
        import("https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js").then(({ collection, query, where, onSnapshot }) => {
            let hasUnreadBooking = false;
            let hasUnreadMessage = false;

            const updateNavDot = () => {
                const navDot = document.getElementById("navBookingDot");
                if (navDot) navDot.style.display = (hasUnreadBooking || hasUnreadMessage) ? "block" : "none";
            };

            // Bookings listener
            const qBookings = query(collection(db, "tempahan"), where("fld_unread_by", "array-contains", user.uid));
            onSnapshot(qBookings, (snap) => {
                hasUnreadBooking = snap.docs.length > 0;
                updateNavDot();
            });

            // Messages listener
            const qMessages = query(collection(db, "chats"), where("participants", "array-contains", user.uid));
            onSnapshot(qMessages, (snap) => {
                hasUnreadMessage = false;
                snap.forEach(doc => {
                    const data = doc.data();
                    if (data.lastSenderId !== user.uid && data.isRead === false) {
                        hasUnreadMessage = true;
                    }
                });
                updateNavDot();
            });
        });

        // 2. DAFTAR EVENT LISTENER DI SINI (Selepas innerHTML dimasukkan)
        document.getElementById("logoutBtn").addEventListener("click", async (e) => {
            e.preventDefault(); // Elakkan page scroll ke atas bila tekan #
            try {
                await signOut(auth);
                alert("Logout successful!");
                window.location.href = "mainmenu.php";
            } catch (error) {
                console.error("Error signing out: ", error);
            }
        });

    } else {
        // 3. Paparkan butang Sign Up / Login jika belum login
        navRight.innerHTML = `
            <a href="signup.php">Sign Up</a>
            <a href="login.php">Log In</a>
        `;
    }
});
</script>

</body>
</html>