<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Current Booking - WhiskerHub</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        /* Modal Styles for Report */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 10px; width: 400px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .close { float: right; font-size: 28px; font-weight: bold; cursor: pointer; color: #aaa; }
        .close:hover { color: #000; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], .form-group input[type="file"], .form-group select, .form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-submit-report { width: 100%; padding: 10px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
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
        <a href="history.php" class="sidebar-link">History</a>
        <a href="bookingterkini.php" class="sidebar-link active" style="display:flex; align-items:center;">
            Current Booking
            <span id="sideBookingDot" style="display:none; width:10px; height:10px; background:#e74c3c; border-radius:50%; margin-left:8px;"></span>
        </a>
    </div>

    <div class="main-content">
        <h2>Active Bookings</h2>
        <div id="bookings_list">
            <p>Fetching your cat's schedules... 🐾</p>
        </div>
    </div>

</div>

<!-- Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('reportModal').style.display='none'">&times;</span>
        <h2 style="color: #e74c3c;">Report Issue</h2>
        <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Please provide proof so our admin can take action.</p>
        
        <div class="form-group">
            <label>Issue Type</label>
            <select id="reportType">
                <option value="Booking No-Show">Booking No-Show</option>
                <option value="Scam / Fraud">Scam / Fraud</option>
                <option value="Payment Issue">Payment Issue</option>
                <option value="Progress Updates Missing">Progress Updates Missing</option>
                <option value="Harassment">Harassment</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea id="reportDesc" rows="3" placeholder="Explain what happened..."></textarea>
        </div>
        <div class="form-group">
            <label>Proof (Image/Screenshot)</label>
            <input type="file" id="reportProof" accept="image/*">
        </div>
        <button class="btn-submit-report" id="submitReportBtn" onclick="submitReport()">SUBMIT REPORT</button>
    </div>
</div>

<!-- Completion Proof Modal -->
<div id="completionModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('completionModal').style.display='none'">&times;</span>
        <h2 style="color: #27ae60;">Upload Proof of Service</h2>
        <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Please upload a photo to prove the service was completed. The owner must verify this before the booking is fully completed.</p>
        
        <div class="form-group">
            <label>Proof Image</label>
            <input type="file" id="completionProof" accept="image/*">
        </div>
        <div class="form-group">
            <label>Note to Owner (Optional)</label>
            <textarea id="completionNote" rows="3" placeholder="Tell the owner how the cats are doing..."></textarea>
        </div>
        <button class="btn-submit-report" style="background:#27ae60;" id="submitCompletionBtn" onclick="submitCompletion()">SUBMIT PROOF</button>
    </div>
</div>

<script type="module">
import { auth, db, storage } from "../js/firebase.js";
import { 
    collection, 
    query, 
    where, 
    getDocs, 
    doc, 
    getDoc, 
    updateDoc,
    addDoc,
    serverTimestamp
} from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { ref, uploadBytesResumable, getDownloadURL } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-storage.js";
import { signOut } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";

const navRight = document.getElementById("navRight");
const listContainer = document.getElementById("bookings_list");

let currentReportedUserId = null;
let currentCompletionBookingId = null;
let currentCompletionOwnerId = null;

auth.onAuthStateChanged(async (user) => {
    if (!user) {
        navRight.innerHTML = `
            <a href="signup.php">Sign Up</a>
            <a href="login.php">Log In</a>
        `;
        window.location.href = "signin.php";
        return;
    }

    // --- 1. UPDATE NAVBAR (Same as History) ---
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
            const navDot = document.getElementById("navBookingDot"); // This dot is on the main icon
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

    document.getElementById("logoutBtn").addEventListener("click", async (e) => {
        e.preventDefault();
        if(confirm("Are you sure you want to logout?")) {
            await signOut(auth);
            alert("Logout successful!");
            window.location.href = "mainmenu.php";
        }
    });

    // --- 2. FETCH BOOKINGS ---
    try {
        const qOwner = query(collection(db, "tempahan"), where("fld_pemilik_ID", "==", user.uid));
        const qSitter = query(collection(db, "tempahan"), where("fld_penjaga_ID", "==", user.uid));

        const [snapOwner, snapSitter] = await Promise.all([getDocs(qOwner), getDocs(qSitter)]);

        const docsMap = new Map();
        snapOwner.docs.forEach(d => docsMap.set(d.id, d));
        snapSitter.docs.forEach(d => docsMap.set(d.id, d));
        
        const allDocs = Array.from(docsMap.values());

        if (allDocs.length === 0) {
            listContainer.innerHTML = "<p>No current bookings found 😢</p>";
            return;
        }

        listContainer.innerHTML = ""; 
        let hasActiveBooking = false;

        for (const bookingDoc of allDocs) {
            const booking = bookingDoc.data();
            const bId = bookingDoc.id;

            // --- CLEAR UNREAD NOTIFICATION ---
            if (booking.fld_unread_by && booking.fld_unread_by.includes(user.uid)) {
                await updateDoc(doc(db, "tempahan", bId), {
                    fld_unread_by: booking.fld_unread_by.filter(id => id !== user.uid)
                });
            }

            let status = booking.fld_tempahan_status;

            // --- 48-HOUR AUTO-CANCEL LAZY CHECK ---
            if (status === "Cancellation Requested" && booking.fld_cancel_requested_at) {
                const requestTime = new Date(booking.fld_cancel_requested_at).getTime();
                const now = Date.now();
                const hours48 = 48 * 60 * 60 * 1000;

                if (now - requestTime > hours48) {
                    await updateDoc(doc(db, "tempahan", bId), {
                        fld_tempahan_status: "Cancelled",
                        fld_cancel_reason: "Auto-cancelled after 48h timeout"
                    });
                    status = "Cancelled";
                }
            }

            if (status === "Completed" || status === "Cancelled") {
                continue; 
            }

            hasActiveBooking = true;

            let displayName = "Unknown";
            let displayRole = "SITTER";
            let otherPartyId = "";

            if (booking.fld_pemilik_ID === user.uid) {
                // User is the customer, so display the Sitter's name
                const sitterDoc = await getDoc(doc(db, "penjaga_kucing", booking.fld_penjaga_ID));
                displayName = sitterDoc.exists() ? sitterDoc.data().fld_user_fullname : "Unknown Sitter";
                displayRole = "SITTER";
                otherPartyId = booking.fld_penjaga_ID;
            } else {
                // User is the sitter, so display the Customer's name
                const ownerDoc = await getDoc(doc(db, "pengguna", booking.fld_pemilik_ID));
                displayName = ownerDoc.exists() ? ownerDoc.data().fld_user_name : "Unknown Owner";
                displayRole = "CUSTOMER";
                otherPartyId = booking.fld_pemilik_ID;
            }

            const card = document.createElement("div");
            card.className = "booking-box";
            card.innerHTML = `
                <table class="detail-table">
                    <tr>
                        <td width="40%"><strong>DATE:</strong></td>
                        <td>${booking.fld_tempahan_tkhMula} to ${booking.fld_tempahan_tkhTamat}</td>
                    </tr>
                    <tr>
                        <td><strong>${displayRole}:</strong></td>
                        <td>${displayName}</td>
                    </tr>
                    <tr>
                        <td><strong>SERVICE:</strong></td>
                        <td>${booking.fld_tempahan_servis || "-"}</td>
                    </tr>
                    <tr>
                        <td><strong>CATS:</strong></td>
                        <td>${booking.fld_tempahan_bilKucing} Cat(s)</td>
                    </tr>
                    <tr>
                        <td><strong>TOTAL:</strong></td>
                        <td><strong>RM ${parseFloat(booking.fld_tempahan_jumlah).toFixed(2)}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>TIME:</strong></td>
                        <td>${booking.fld_tempahan_masaMula || "-"} to ${booking.fld_tempahan_masaTamat || "-"}</td>
                    </tr>
                    <tr>
                        <td><strong>ADDRESS:</strong></td>
                        <td>${booking.fld_tempahan_alamat || "-"}</td>
                    </tr>
                    <tr>
                        <td><strong>STATUS:</strong></td>
                        <td><span class="status-tag">${status}</span></td>
                    </tr>
                </table>

                <div style="text-align:center; margin-top:25px; display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
            `;

            const isSitter = (booking.fld_penjaga_ID === user.uid);
            const isOwner = (booking.fld_pemilik_ID === user.uid);

            if (status === "Cancellation Requested") {
                if (booking.fld_cancel_requested_by === user.uid) {
                    card.innerHTML += `
                        <div style="background:#fff3cd; padding:10px; border-radius:8px; text-align:center; width:100%; font-size:14px; color:#856404;">
                            Awaiting Partner Approval (Auto-cancels in 48 hours)
                        </div>
                    `;
                } else {
                    card.innerHTML += `
                        <div style="background:#f8d7da; padding:15px; border-radius:8px; text-align:center; width:100%;">
                            <p style="margin-bottom:15px; color:#721c24;"><strong>Partner requested cancellation.</strong></p>
                            <button class="btn-done" style="background:#dc3545; padding:8px 15px; font-size:12px; margin-right:10px;" onclick="acceptCancel('${bId}')">ACCEPT</button>
                            <button class="btn-done" style="background:#6c757d; padding:8px 15px; font-size:12px;" onclick="rejectCancel('${bId}', '${booking.fld_previous_status}')">REJECT</button>
                        </div>
                    `;
                }
            } else if (status === "Completion Requested") {
                if (isSitter) {
                    card.innerHTML += `
                        <div style="background:#d4edda; padding:10px; border-radius:8px; text-align:center; width:100%; font-size:14px; color:#155724;">
                            Proof Uploaded. Awaiting Owner's Verification.
                        </div>
                    `;
                } else if (isOwner) {
                    let proofImg = booking.fld_proof_image ? `<img src="${booking.fld_proof_image}" style="width:100%; max-height:200px; object-fit:contain; border-radius:8px; margin-bottom:10px; background:#eee;">` : "";
                    let proofNote = booking.fld_proof_note ? `<p style="font-size:13px; margin-bottom:10px; color:#333;"><strong>Note:</strong> ${booking.fld_proof_note}</p>` : "";

                    card.innerHTML += `
                        <div style="background:#e8f4fd; padding:15px; border-radius:8px; text-align:center; width:100%;">
                            <h4 style="margin-bottom:10px; color:#0056b3;">Verify Completion</h4>
                            ${proofImg}
                            ${proofNote}
                            <div style="display:flex; gap:10px; justify-content:center; margin-top:10px;">
                                <button class="btn-done" style="background:#28a745; flex:1; padding:10px; font-size:12px;" onclick="approveCompletion('${bId}', '${booking.fld_penjaga_ID}')">APPROVE</button>
                                <button class="btn-done" style="background:#dc3545; flex:1; padding:10px; font-size:12px;" onclick="rejectCompletion('${bId}')">REJECT</button>
                            </div>
                        </div>
                    `;
                }
            } else {
                card.innerHTML += `
                    <button class="btn-done" style="background:#dc3545; flex:1; min-width:80px; padding:10px 5px; font-size:11px;" onclick="requestCancel('${bId}', '${status}', '${otherPartyId}')">
                        CANCEL
                    </button>`;
                
                if (isSitter) {
                    card.innerHTML += `
                        <button class="btn-done" style="background:#27ae60; flex:2; min-width:120px; padding:10px 5px; font-size:11px;" onclick="openCompletionModal('${bId}', '${otherPartyId}')">
                             UPLOAD PROOF
                        </button>
                    `;
                } else if (isOwner) {
                    card.innerHTML += `
                        <div style="flex:2; min-width:120px; padding:10px 5px; font-size:11px; text-align:center; color:#777; background:#f0f0f0; border-radius:5px; border:1px dashed #ccc; display:flex; align-items:center; justify-content:center;">
                            Waiting Proof
                        </div>
                    `;
                }

                card.innerHTML += `
                    <button class="btn-done" style="background:#4a90e2; flex:1; min-width:80px; padding:10px 5px; font-size:11px;" onclick="openChat('${otherPartyId}')">
                        CHAT
                    </button>
                    <button class="btn-done" style="background:#f39c12; flex:1; min-width:80px; padding:10px 5px; font-size:11px;" onclick="openReportModal('${otherPartyId}')">
                        🚨 REPORT
                    </button>
                `;
            }

            card.innerHTML += `</div>`;
            listContainer.appendChild(card);
        }

        if (!hasActiveBooking) {
            listContainer.innerHTML = "<p>All bookings are completed! Check your History. 🐾</p>";
        }

    } catch (err) {
        console.error("Firestore Error:", err);
        listContainer.innerHTML = "<p>Error loading data. Check console for details.</p>";
    }
});

// --- CANCELLATION FUNCTIONS ---
window.requestCancel = async (bookingId, currentStatus, partnerId) => {
    if (!confirm("Are you sure you want to request a cancellation? Your partner must accept it, or it will auto-cancel in 48 hours.")) return;
    try {
        await updateDoc(doc(db, "tempahan", bookingId), {
            fld_tempahan_status: "Cancellation Requested",
            fld_cancel_requested_by: auth.currentUser.uid,
            fld_cancel_requested_at: new Date().toISOString(),
            fld_previous_status: currentStatus,
            fld_unread_by: [partnerId]
        });
        alert("Cancellation requested. Waiting for partner approval.");
        window.location.reload();
    } catch (err) {
        console.error(err);
        alert("Error requesting cancellation.");
    }
};

window.acceptCancel = async (bookingId) => {
    if (!confirm("Accept cancellation? This will permanently cancel the booking and move it to History.")) return;
    try {
        await updateDoc(doc(db, "tempahan", bookingId), {
            fld_tempahan_status: "Cancelled"
        });
        alert("Booking cancelled.");
        window.location.reload();
    } catch (err) {
        console.error(err);
        alert("Error cancelling.");
    }
};

window.rejectCancel = async (bookingId, previousStatus) => {
    if (!confirm("Reject cancellation? The booking will remain active.")) return;
    try {
        await updateDoc(doc(db, "tempahan", bookingId), {
            fld_tempahan_status: previousStatus || "Active",
            fld_cancel_requested_by: null,
            fld_cancel_requested_at: null
        });
        alert("Cancellation rejected.");
        window.location.reload();
    } catch (err) {
        console.error(err);
        alert("Error rejecting cancellation.");
    }
};

// --- PROOF OF SERVICE COMPLETION ---

window.openCompletionModal = function(bookingId, ownerId) {
    currentCompletionBookingId = bookingId;
    currentCompletionOwnerId = ownerId;
    document.getElementById("completionProof").value = "";
    document.getElementById("completionNote").value = "";
    document.getElementById("completionModal").style.display = "block";
};

window.submitCompletion = async function() {
    const file = document.getElementById("completionProof").files[0];
    const note = document.getElementById("completionNote").value;
    const btn = document.getElementById("submitCompletionBtn");

    if (!file) {
        alert("Please upload a proof image.");
        return;
    }

    btn.innerText = "Uploading...";
    btn.disabled = true;

    try {
        const storageRef = ref(storage, `completion_proofs/${Date.now()}_${file.name}`);
        const snapshot = await uploadBytesResumable(storageRef, file);
        const downloadURL = await getDownloadURL(snapshot.ref);

        await updateDoc(doc(db, "tempahan", currentCompletionBookingId), {
            fld_tempahan_status: "Completion Requested",
            fld_proof_image: downloadURL,
            fld_proof_note: note,
            fld_unread_by: [currentCompletionOwnerId]
        });

        alert("Proof uploaded successfully! Awaiting owner's verification.");
        document.getElementById("completionModal").style.display = "none";
        window.location.reload();
    } catch (err) {
        console.error("Completion error:", err);
        alert("Failed to submit proof.");
    } finally {
        btn.innerText = "SUBMIT PROOF";
        btn.disabled = false;
    }
};

window.approveCompletion = async (bookingId, sitterId) => {
    if (!confirm("Are you sure the service was completed satisfactorily? This will finish the booking.")) return;

    try {
        await updateDoc(doc(db, "tempahan", bookingId), {
            fld_tempahan_status: "Completed",
            fld_unread_by: [sitterId]
        });

        alert("Service Verified! Redirecting to review page... 🐾");
        window.location.href = `review.php?sitterId=${sitterId}`;
    } catch (err) {
        console.error("Update error:", err);
        alert("Could not complete booking. Try again.");
    }
};

window.rejectCompletion = async (bookingId) => {
    if (!confirm("Reject this proof? The booking will revert to Active.")) return;

    try {
        await updateDoc(doc(db, "tempahan", bookingId), {
            fld_tempahan_status: "Active",
            fld_proof_image: null,
            fld_proof_note: null
        });

        alert("Proof rejected. The booking is active again.");
        window.location.reload();
    } catch (err) {
        console.error("Update error:", err);
        alert("Could not reject completion.");
    }
};

window.openChat = async (sitterId) => {

    const user = auth.currentUser;

    if (!user) {
        alert("Please login first.");
        return;
    }

    // create unique chat id
    const chatId = [user.uid, sitterId].sort().join("_");

    // direct to message page
    window.location.href = `message.php?chatId=${chatId}&receiver=${sitterId}`;
};

// --- REPORT FUNCTIONS ---
window.openReportModal = function(otherUserId) {
    currentReportedUserId = otherUserId;
    document.getElementById("reportType").value = "Booking No-Show";
    document.getElementById("reportDesc").value = "";
    document.getElementById("reportProof").value = "";
    document.getElementById("reportModal").style.display = "block";
};

window.submitReport = async function() {
    const type = document.getElementById("reportType").value;
    const desc = document.getElementById("reportDesc").value;
    const file = document.getElementById("reportProof").files[0];
    const btn = document.getElementById("submitReportBtn");

    if (!desc || !file) {
        alert("Please provide a description and upload a proof image.");
        return;
    }

    btn.innerText = "Submitting...";
    btn.disabled = true;

    try {
        const storageRef = ref(storage, `reports_proof/${Date.now()}_${file.name}`);
        const snapshot = await uploadBytesResumable(storageRef, file);
        const downloadURL = await getDownloadURL(snapshot.ref);

        await addDoc(collection(db, "reports"), {
            reporterId: auth.currentUser.uid,
            reportedUserId: currentReportedUserId,
            type: type,
            description: desc,
            proofImage: downloadURL,
            status: "Pending",
            createdAt: serverTimestamp()
        });

        alert("Report submitted successfully. Admin will review this issue.");
        document.getElementById("reportModal").style.display = "none";
    } catch (err) {
        console.error("Report error:", err);
        alert("Failed to submit report.");
    } finally {
        btn.innerText = "SUBMIT REPORT";
        btn.disabled = false;
    }
};

</script>

</body>
</html>