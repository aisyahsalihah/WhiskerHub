<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - WhiskerShop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/shopstyle.css">
    <style>
        .orders-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .order-card { background: white; border-radius: 15px; padding: 25px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .order-header { display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px; }
        .order-items { margin-bottom: 15px; }
        .order-item { display: flex; justify-content: space-between; margin-bottom: 8px; color: #555; font-size: 14px;}
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
        .status-Processing { background: #fff3cd; color: #856404; }
        .status-Shipped { background: #cce5ff; color: #004085; }
        .status-Delivered { background: #d4edda; color: #155724; }

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
    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="shopping.php">Shopping</a>
        <a href="myorders.php" class="active">My Orders</a>
        <a href="mysales.php" id="navMySales">My Sales</a>
    </div>
</div>

<div class="orders-container">
    <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 30px;">My Orders</h1>
    <div id="ordersList">
        <p>Loading your orders...</p>
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
                <option value="Scam / Fraud">Scam / Fraud</option>
                <option value="Incorrect Item">Incorrect Item</option>
                <option value="Damaged Item">Damaged Item</option>
                <option value="Non-delivery">Non-delivery</option>
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
import { doc, getDoc, collection, query, where, getDocs, setDoc, addDoc, serverTimestamp, updateDoc } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { ref, uploadBytesResumable, getDownloadURL } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-storage.js";

const ordersList = document.getElementById('ordersList');

auth.onAuthStateChanged(async (user) => {
    if (!user) {
        window.location.href = "login.php";
        return;
    }

    try {
        let isSeller = false;
        let userSnap = await getDoc(doc(db, "pengguna", user.uid));
        if (userSnap.exists() && userSnap.data().fld_is_seller === true) {
            isSeller = true;
        } else {
            let sitterSnap = await getDoc(doc(db, "penjaga_kucing", user.uid));
            if (sitterSnap.exists() && sitterSnap.data().fld_is_seller === true) {
                isSeller = true;
            }
        }
        const salesLink = document.getElementById("navMySales");
        if (salesLink) {
            salesLink.innerText = isSeller ? "My Sales" : "Become a Seller";
        }
    } catch (e) {
        console.error("Error setting navbar links:", e);
    }

    try {
        const q = query(
            collection(db, "pesanan"), 
            where("fld_user_id", "==", user.uid)
        );
        const snapshot = await getDocs(q);

        if (snapshot.empty) {
            ordersList.innerHTML = "<p>You have not placed any orders yet. 🐾</p>";
            return;
        }

        ordersList.innerHTML = "";
        
        // Manual sort by date descending
        const docs = snapshot.docs.sort((a,b) => {
            const timeA = a.data().fld_created_at?.seconds || 0;
            const timeB = b.data().fld_created_at?.seconds || 0;
            return timeB - timeA;
        });

        docs.forEach(docSnap => {
            const order = docSnap.data();
            const date = order.fld_created_at ? new Date(order.fld_created_at.seconds * 1000).toLocaleDateString() : 'Unknown Date';
            
            let itemsHTML = "";
            if (order.fld_items && order.fld_items.length > 0) {
                order.fld_items.forEach(item => {
                    itemsHTML += `
                        <div class="order-item">
                            <span>${item.qty}x ${item.name}</span>
                            <span>RM ${(item.price * item.qty).toFixed(2)}</span>
                        </div>
                    `;
                });
            }

            let shippingHTML = "";
            if (order.fld_status === "Shipped" || order.fld_status === "Delivered") {
                const tracking = order.fld_tracking_number || "No tracking number provided";
                const img = order.fld_shipping_image ? `<br><a href="${order.fld_shipping_image}" target="_blank" style="color: #ffb6c1; text-decoration: underline; font-weight: bold; font-size: 13px;">View Shipping Proof Receipt 📄</a>` : "";
                shippingHTML = `
                    <div style="background: #e8f4fd; padding: 12px 15px; border-radius: 8px; margin-top: 15px; font-size: 13px; color: #0056b3; text-align: left;">
                        <strong>Shipping Info:</strong><br>
                        Tracking Number: <code>${tracking}</code>
                        ${img}
                    </div>
                `;
            }

            let orderReceivedBtnHTML = "";
            if (order.fld_status === "Shipped") {
                orderReceivedBtnHTML = `<button class="btn-done" style="margin: 0 10px 0 0; padding: 8px 15px; font-size: 13px; background: #2ecc71; color: white; border: none; border-radius: 5px; cursor: pointer; display: inline-block;" onclick="confirmOrderReceived('${docSnap.id}', '${order.fld_seller_id}')">Order Received 📦</button>`;
            }

            ordersList.innerHTML += `
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <strong>Order ID: ${docSnap.id}</strong><br>
                            <small style="color: #888;">Date: ${date}</small>
                        </div>
                        <div>
                            <span class="status-badge status-${order.fld_status || 'Processing'}">${order.fld_status || 'Processing'}</span>
                        </div>
                    </div>
                    <div class="order-items">
                        ${itemsHTML}
                    </div>
                    ${shippingHTML}
                    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 15px; margin-top: 15px;">
                        <div>
                            <button class="btn-done" style="margin: 0 10px 0 0; padding: 8px 15px; font-size: 13px; display: inline-block;" onclick="startChatWithUser('${order.fld_seller_id}', 'buyer', 'Hi! I have a question regarding my Order #${docSnap.id}. 🐾')">Chat Seller 💬</button>
                            ${orderReceivedBtnHTML}
                            <button class="btn-danger" style="margin: 0; padding: 8px 15px; font-size: 13px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer; display: inline-block;" onclick="openReportModal('${order.fld_seller_id}')">Report 🚨</button>
                        </div>
                        <strong>Total: RM ${parseFloat(order.fld_total_amount).toFixed(2)}</strong>
                    </div>
                </div>
            `;
        });

    } catch(err) {
        console.error("Error fetching orders:", err);
        ordersList.innerHTML = "<p>Error loading orders.</p>";
    }
});



window.startChatWithUser = async function(otherUserId, role, defaultMsg) {
    const user = auth.currentUser;
    if (!user) {
        alert("Please login first!");
        window.location.href = "login.php";
        return;
    }

    if (user.uid === otherUserId) {
        alert("You cannot chat with yourself.");
        return;
    }

    const buyerId = role === 'seller' ? user.uid : otherUserId;
    const sellerId = role === 'seller' ? otherUserId : user.uid;
    const chatRoomId = `${buyerId}_${sellerId}`;

    try {
        const chatRoomData = {
            fld_chat_room_id: chatRoomId,
            fld_pemilik_ID: buyerId,
            fld_penjaga_ID: sellerId,
            participants: [buyerId, sellerId],
            lastMessage: defaultMsg,
            lastMessageTime: serverTimestamp(),
            lastSenderId: user.uid,
            isRead: false
        };

        await setDoc(doc(db, "chats", chatRoomId), chatRoomData, { merge: true });
        
        await addDoc(collection(db, "chats", chatRoomId, "messages"), {
            senderId: user.uid,
            text: defaultMsg,
            createdAt: serverTimestamp(),
            isRead: false
        });

        window.location.href = `message.php?chatId=${chatRoomId}`;
    } catch(err) {
        console.error("Error starting chat:", err);
        alert("Failed to start chat room.");
    }
};

let currentReportedUserId = null;

window.openReportModal = function(otherUserId) {
    if (!otherUserId) {
        alert("Seller information is not available to report.");
        return;
    }
    currentReportedUserId = otherUserId;
    document.getElementById("reportType").value = "Scam / Fraud";
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

window.confirmOrderReceived = async function(orderId, sellerId) {
    if (!confirm("Are you sure your order has arrived? The order status will be updated to Delivered.")) return;
    try {
        await updateDoc(doc(db, "pesanan", orderId), { fld_status: "Delivered" });
        alert("Order status updated to Delivered! Please leave your review. 🐾");
        window.location.href = `review.php?orderId=${orderId}&sellerId=${sellerId}&role=buyer`;
    } catch(err) {
        console.error("Error updating order status:", err);
        alert("Failed to update order status.");
    }
};
</script>
</body>
</html>
