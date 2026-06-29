<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - WhiskerHub</title>

    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .emoji-picker-menu {
            display: none;
            position: absolute;
            bottom: 70px;
            left: 20px;
            background: white;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            grid-template-columns: repeat(6, 1fr);
            gap: 5px;
            z-index: 100;
        }
        .emoji-item {
            cursor: pointer;
            font-size: 20px;
            text-align: center;
            padding: 5px;
            border-radius: 4px;
            user-select: none;
        }
        .emoji-item:hover {
            background: #f1f1f1;
        }
        .chat-input {
            position: relative;
        }
        .msg img {
            max-width: 200px;
            border-radius: 8px;
            margin-top: 5px;
            display: block;
        }
        .btn-report {
            background-color: #ffebee;
            color: #d32f2f;
            border: 1px solid #ffcdd2;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: bold;
            margin-left: auto;
        }
        .btn-report:hover { background-color: #ffcdd2; }
        
        /* Modal Styles */
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
</div>

<div class="dashboard-container">
    <div class="sidebar">
        <a href="message.php" class="sidebar-link active">
            Messages
            <span class="notification-dot" id="globalUnreadDot" style="display:none;"></span>
        </a>
        <a href="history.php" class="sidebar-link">History</a>
        <a href="bookingterkini.php" class="sidebar-link" style="display:flex; align-items:center;">
            Booking
            <span id="sideBookingDot" style="display:none; width:10px; height:10px; background:#e74c3c; border-radius:50%; margin-left:8px;"></span>
        </a>
    </div>

    <div class="chat-sidebar">
        <div class="search-box">
            <input type="text" id="searchUser" placeholder="Search user or message...">
        </div>
        <div class="contact-list" id="contactList">
            </div>
    </div>

    <div class="chat-container">
        <div class="chat-header" style="display: flex; align-items: center; width: 100%;">
            <div>
                <div class="chat-user" id="activeChatName">Select a user to start chatting</div>
                <div class="chat-status" id="activeChatStatus"><span class="online-dot"></span> Online</div>
            </div>
            <button class="btn-report" id="btnReportUser" style="display:none;" onclick="openReportModal()">🚨 Report User</button>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            </div>

        <div class="chat-input">
            <div id="emojiPicker" class="emoji-picker-menu"></div>
            <button class="emoji-btn" onclick="toggleEmojiPicker()">😊</button>
            <input type="file" id="fileUpload" accept="image/*" style="display:none;" onchange="handleFileUpload(this)">
            <button class="upload-btn" onclick="document.getElementById('fileUpload').click()">📷</button>
            <textarea id="messageInput" placeholder="Type your message here..."></textarea>
            <button class="send-btn" onclick="window.sendMessage()">Send</button>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div id="reportModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('reportModal').style.display='none'">&times;</span>
        <h2 style="color: #e74c3c;">Report User</h2>
        <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Please provide proof so our admin can take action.</p>
        
        <div class="form-group">
            <label>Issue Type</label>
            <select id="reportType">
                <option value="Scam">Scam / Fraud</option>
                <option value="Payment Issue">Payment Issue</option>
                <option value="Service Not Rendered">Service Not Rendered</option>
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

<script type="module">
import { auth, db, storage } from "../js/firebase.js";
import {
    collection, doc, getDoc, addDoc, query, where, orderBy, 
    onSnapshot, serverTimestamp, updateDoc
} from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { ref, uploadBytesResumable, getDownloadURL } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-storage.js";

let currentUser = null;
let currentChatId = null;
let currentOtherUserId = null; // Store for reporting

const contactList = document.getElementById("contactList");
const chatMessages = document.getElementById("chatMessages");
const messageInput = document.getElementById("messageInput");

// --- 1. AUTH STATE ---
auth.onAuthStateChanged(async (user) => {
    if (!user) {
        window.location.href = "login.php";
        return;
    }
    currentUser = user;
    loadChats();

    // Check for unread bookings
    const qBooking = query(collection(db, "tempahan"), where("fld_unread_by", "array-contains", currentUser.uid));
    onSnapshot(qBooking, (snap) => {
        const sideDot = document.getElementById("sideBookingDot");
        if (sideDot) sideDot.style.display = snap.docs.length > 0 ? "inline-block" : "none";
    });
});

// --- 2. LOAD CHATS (List of people) ---
async function loadChats() {
    const q = query(
        collection(db, "chats"),
        where("participants", "array-contains", currentUser.uid)
    );

    onSnapshot(q, async (snapshot) => {
        contactList.innerHTML = "";
        let totalUnread = 0;

        for (const chatDoc of snapshot.docs) {
            const data = chatDoc.data();
            const otherUserId = data.participants.find(id => id !== currentUser.uid);
            
            // Check unread for global red dot
            if (data.lastSenderId !== currentUser.uid && data.isRead === false) {
                totalUnread++;
            }

            let otherUserName = "User";
            const userSnap = await getDoc(doc(db, "pengguna", otherUserId));
            if (userSnap.exists()) {
                otherUserName = userSnap.data().fld_user_name || "User";
            } else {
                const sitterSnap = await getDoc(doc(db, "penjaga_kucing", otherUserId));
                if (sitterSnap.exists()) {
                    otherUserName = sitterSnap.data().fld_user_fullname || "Sitter";
                }
            }

            const contactDiv = document.createElement("div");
            contactDiv.className = `contact ${currentChatId === chatDoc.id ? 'active' : ''}`;
            contactDiv.onclick = () => window.openChat(chatDoc.id, otherUserName, otherUserId);

            contactDiv.innerHTML = `
                <div class="contact-name">${otherUserName}</div>
                <div class="contact-preview">${data.lastMessage || "No messages yet"}</div>
                <div class="online"><span class="online-dot"></span> Online</div>
            `;
            contactList.appendChild(contactDiv);
        }
        
        // Update Red Dot
        document.getElementById("globalUnreadDot").style.display = totalUnread > 0 ? "block" : "none";
    });
}

// --- 3. OPEN CHAT & LOAD MESSAGES ---
window.openChat = function(chatId, userName, otherUserId) {
    currentChatId = chatId;
    currentOtherUserId = otherUserId; // Used for reporting
    
    document.getElementById("activeChatName").innerText = userName;
    document.getElementById("btnReportUser").style.display = "block";
    
    // Mark chat as read when opening
    updateDoc(doc(db, "chats", chatId), { isRead: true });
    
    const q = query(
        collection(db, "chats", chatId, "messages"),
        orderBy("createdAt")
    );

    onSnapshot(q, (snapshot) => {
        chatMessages.innerHTML = "";
        snapshot.forEach(docSnap => {
            const msg = docSnap.data();
            const isSent = msg.senderId === currentUser.uid;
            
            // Mark message as read if it was received and is currently unread
            if (!isSent && msg.isRead === false) {
                updateDoc(doc(db, "chats", chatId, "messages", docSnap.id), { isRead: true });
            }

            let readStatusHtml = '';
            if (isSent) {
                readStatusHtml = msg.isRead 
                    ? '<span class="read-status">✓✓ Read</span>' 
                    : '<span class="read-status">✓ Sent</span>';
            }

            let contentHtml = msg.text ? `<div>${msg.text}</div>` : '';
            if (msg.imageUrl) {
                contentHtml += `<img src="${msg.imageUrl}" alt="Image" style="max-width: 200px; border-radius: 8px; margin-bottom: 5px; cursor:pointer;" onclick="window.open('${msg.imageUrl}', '_blank')">`;
            }

            chatMessages.innerHTML += `
                <div class="msg ${isSent ? 'sent' : 'received'}">
                    ${contentHtml}
                    ${readStatusHtml}
                </div>
            `;
        });
        
        setTimeout(() => {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }, 100);
    });
};

// --- 4. SEND MESSAGE ---
window.sendMessage = async function() {
    const message = messageInput.value.trim();
    if (message === "" || !currentChatId) return;

    try {
        await addDoc(collection(db, "chats", currentChatId, "messages"), {
            senderId: currentUser.uid,
            text: message,
            createdAt: serverTimestamp(),
            isRead: false
        });

        await updateDoc(doc(db, "chats", currentChatId), {
            lastMessage: message,
            lastMessageTime: serverTimestamp(),
            lastSenderId: currentUser.uid,
            isRead: false
        });

        messageInput.value = "";
        messageInput.focus();
    } catch (error) {
        console.error("Error:", error);
    }
};

// --- 5. SEARCH, EMOJI & HELPERS ---
document.getElementById("searchUser").addEventListener("keyup", function() {
    const filter = this.value.toLowerCase();
    document.querySelectorAll(".contact").forEach(contact => {
        const text = contact.innerText.toLowerCase();
        contact.style.display = text.includes(filter) ? "block" : "none";
    });
});

window.toggleEmojiPicker = function() {
    emojiPicker.style.display = emojiPicker.style.display === "grid" ? "none" : "grid";
};

// Populate emoji picker
const emojisList = ['😊', '😂', '🥺', '😍', '😎', '😭', '😡', '👍', '🙏', '🐱', '🐈', '🐾', '❤️', '✨', '🔥', '🎉', '👋', '👀'];
const emojiPicker = document.getElementById("emojiPicker");
emojisList.forEach(emoji => {
    const span = document.createElement("span");
    span.className = "emoji-item";
    span.innerText = emoji;
    span.onclick = () => {
        messageInput.value += emoji;
        messageInput.focus();
        emojiPicker.style.display = "none";
    };
    emojiPicker.appendChild(span);
});

messageInput.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        window.sendMessage();
    }
});

window.handleFileUpload = async function(input) {
    if (!input.files || !input.files[0] || !currentChatId) return;
    
    const file = input.files[0];
    const storageRef = ref(storage, `chat_images/${currentChatId}/${Date.now()}_${file.name}`);
    const uploadTask = uploadBytesResumable(storageRef, file);

    alert("Uploading image... Please wait.");

    uploadTask.on('state_changed', 
        (snapshot) => {}, 
        (error) => {
            console.error("Upload error:", error);
            alert("Failed to upload image.");
        }, 
        async () => {
            const downloadURL = await getDownloadURL(uploadTask.snapshot.ref);
            
            await addDoc(collection(db, "chats", currentChatId, "messages"), {
                senderId: currentUser.uid,
                text: "",
                imageUrl: downloadURL,
                createdAt: serverTimestamp(),
                isRead: false
            });

            await updateDoc(doc(db, "chats", currentChatId), {
                lastMessage: "📷 Image",
                lastMessageTime: serverTimestamp(),
                lastSenderId: currentUser.uid,
                isRead: false
            });

            // No need to alert "Image sent" as it will pop up dynamically
        }
    );
    input.value = "";
};

// --- 6. REPORT SYSTEM ---
window.openReportModal = function() {
    if (!currentOtherUserId) return;
    document.getElementById("reportType").value = "Scam";
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
        alert("Please provide a description and a proof image.");
        return;
    }

    btn.innerText = "Submitting...";
    btn.disabled = true;

    try {
        const storageRef = ref(storage, `reports_proof/${Date.now()}_${file.name}`);
        const snapshot = await uploadBytesResumable(storageRef, file);
        const downloadURL = await getDownloadURL(snapshot.ref);

        await addDoc(collection(db, "reports"), {
            reporterId: currentUser.uid,
            reportedUserId: currentOtherUserId,
            type: type,
            description: desc,
            proofImage: downloadURL,
            status: "Pending",
            createdAt: serverTimestamp()
        });

        alert("Report submitted successfully. Admin will review this case.");
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