<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking History - WhiskerHub</title>
    <link rel="stylesheet" href="../css/dashboard.css">

</head>
<body>

    <div class="navbar">
        <a href="mainmenu.php" class="logo">WhiskerHub</a>
        <div class="nav-right" id="navRight"></div>
    </div>

    <div class="dashboard-container">
        <div class="sidebar">
            <a href="message.php" class="sidebar-link" style="display:flex; align-items:center;">
                Messages
                <span id="sideMessageDot" style="display:none; width:10px; height:10px; background:#e74c3c; border-radius:50%; margin-left:8px;"></span>
            </a>
            <a href="history.php" class="sidebar-link active">History</a>
            <a href="bookingterkini.php" class="sidebar-link" style="display:flex; align-items:center;">
                Current Booking
                <span id="sideBookingDot" style="display:none; width:10px; height:10px; background:#e74c3c; border-radius:50%; margin-left:8px;"></span>
            </a>
        </div>

        <div class="main-content">
            <h2>Booking History</h2>
            <div id="history_list">
                <p>Loading your history... 🐾</p>
            </div>
        </div>
    </div>

<script type="module">
    // Consolidate everything into ONE module script
    import { auth, db } from "../js/firebase.js";
    import { 
        collection, 
        query, 
        where, 
        getDocs 
    } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
    import { signOut } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";

    const navRight = document.getElementById("navRight");
    const listContainer = document.getElementById("history_list");

    auth.onAuthStateChanged(async (user) => {
        if (!user) {
            // Update Navbar for guests
            navRight.innerHTML = `
                <a href="signup.php">Sign Up</a>
                <a href="login.php">Log In</a>
            `;
            window.location.href = "login.php";
            return;
        }

        // --- 1. UPDATE NAVBAR FOR LOGGED IN USER ---
        navRight.innerHTML = `
            <a href="message.php" class="circle-btn" style="position:relative;">
                <img src="../photos/history.jpg" alt="History">
                <span id="navBookingDot" style="display:none; position:absolute; top:0; right:0; width:12px; height:12px; background:#e74c3c; border-radius:50%; border:2px solid #fff;"></span>
            </a>
            <a href="profile.php" class="circle-btn">
                <img src="../photos/profile.jpg" alt="Profile">
            </a>
            <a href="#" id="logoutBtn" class="circle-btn">
                <img src="../photos/logout.webp" alt="Logout">
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
                const sideDot = document.getElementById("sideBookingDot");
                hasUnreadBooking = snap.docs.length > 0;
                if (sideDot) sideDot.style.display = hasUnreadBooking ? "inline-block" : "none";
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
                const sideDot = document.getElementById("sideMessageDot");
                if (sideDot) sideDot.style.display = hasUnreadMessage ? "inline-block" : "none";
                updateNavDot();
            });
        });

        // Add Logout Event Listener immediately after creating the button
        document.getElementById("logoutBtn").addEventListener("click", async (e) => {
            e.preventDefault();
            if(confirm("Are you sure you want to logout?")) {
                await signOut(auth);
                alert("Logout successful!");
                window.location.href = "mainmenu.php";
            }
        });

        // --- 2. FETCH BOOKING HISTORY ---
        try {
            const qOwner = query(collection(db, "tempahan"), where("fld_pemilik_ID", "==", user.uid));
            const qSitter = query(collection(db, "tempahan"), where("fld_penjaga_ID", "==", user.uid));

            const [snapOwner, snapSitter] = await Promise.all([getDocs(qOwner), getDocs(qSitter)]);

            const docsMap = new Map();
            snapOwner.docs.forEach(d => docsMap.set(d.id, d));
            snapSitter.docs.forEach(d => docsMap.set(d.id, d));
            
            const allDocs = Array.from(docsMap.values());

            if (allDocs.length === 0) {
                listContainer.innerHTML = "<p>No booking history found.</p>";
                return;
            }

            listContainer.innerHTML = ""; 
            let hasHistory = false;

            // Sort by creation time manually
            const sortedDocs = allDocs.sort((a, b) => {
                const timeA = a.data().fld_tempahan_masaDibuat?.seconds || 0;
                const timeB = b.data().fld_tempahan_masaDibuat?.seconds || 0;
                return timeB - timeA;
            });

            sortedDocs.forEach((doc) => {
                const data = doc.data();

                if (data.fld_tempahan_status === "Completed") {
                    hasHistory = true;

                    const card = document.createElement("div");
                    card.className = "item-card";
                    
                    const dateRange = `${data.fld_tempahan_tkhMula} - ${data.fld_tempahan_tkhTamat}`;
                    const service = data.fld_tempahan_servis || "Cat Service";
                    const total = parseFloat(data.fld_tempahan_jumlah || 0).toFixed(2);
                    
                    let roleTag = "";
                    if (data.fld_penjaga_ID === user.uid && data.fld_pemilik_ID !== user.uid) {
                        roleTag = "<span style='font-size:11px; background:#e2e3e5; padding:2px 6px; border-radius:4px;'>As Sitter</span>";
                    } else if (data.fld_pemilik_ID === user.uid) {
                        roleTag = "<span style='font-size:11px; background:#e2e3e5; padding:2px 6px; border-radius:4px;'>As Owner</span>";
                    }

                    card.innerHTML = `
                        <div>
                            <strong>${dateRange}</strong> ${roleTag}
                            <p style="color:#888; font-size:13px; margin: 5px 0;">
                                ${service} | RM ${total}
                            </p>
                            <small style="color:#ccc; font-size:11px;">Booking ID: ${doc.id}</small>
                        </div>
                        <span class="status-pill" style="background:#d4edda; color:#155724;">COMPLETED</span>
                    `;
                    listContainer.appendChild(card);
                }
            });

            if (!hasHistory) {
                listContainer.innerHTML = "<p>No completed bookings yet. 🐾</p>";
            }

        } catch (err) {
            console.error("Error loading history:", err);
            listContainer.innerHTML = "<p>Failed to load history data.</p>";
        }
    });
</script>

</body>
</html>