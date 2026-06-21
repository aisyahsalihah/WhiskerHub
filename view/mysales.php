<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Sales - WhiskerShop</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .order-table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-radius: 10px; overflow: hidden; }
        .order-table th, .order-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .order-table th { background: #000; color: white; font-size: 14px; text-transform: uppercase; }
        .btn-update { padding: 5px 10px; border: none; background: #333; color: white; border-radius: 5px; cursor: pointer; font-size: 12px; margin-right: 5px; }
        .btn-update:hover { background: #000; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        
        /* Modal Styles */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #fff; margin: 10% auto; padding: 20px; border-radius: 10px; width: 400px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .close { float: right; font-size: 28px; font-weight: bold; cursor: pointer; color: #aaa; }
        .close:hover { color: #000; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], .form-group input[type="file"] { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-submit-ship { width: 100%; padding: 10px; background: #ffb6c1; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<div class="navbar">
    <a href="mainmenu.php" class="logo">WhiskerHub</a>
    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="shopping.php">Shop</a>
        <a href="mysales.php" class="active">My Sales</a>
        <a href="addproduct.php">Add Product</a>
    </div>
</div>

<div class="admin-container">
    <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 30px;">Seller Dashboard - My Sales</h1>
    <table class="order-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer Details</th>
                <th>Items Sold</th>
                <th>Revenue</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="adminOrderList">
            <tr><td colspan="6" style="text-align: center;">Loading your sales...</td></tr>
        </tbody>
    </table>
</div>

<!-- Shipping Modal -->
<div id="shipModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('shipModal').style.display='none'">&times;</span>
        <h2>Ship Order</h2>
        <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Please provide shipping evidence to notify the customer.</p>
        
        <div class="form-group">
            <label>Tracking Number</label>
            <input type="text" id="trackNum" placeholder="e.g., J&T 123456789">
        </div>
        <div class="form-group">
            <label>Shipping Proof / Receipt (Image)</label>
            <input type="file" id="shipProof" accept="image/*">
        </div>
        <input type="hidden" id="shipOrderId">
        <input type="hidden" id="shipBuyerEmail">
        
        <button class="btn-submit-ship" id="confirmShipBtn" onclick="submitShipping()">CONFIRM & NOTIFY BUYER</button>
    </div>
</div>

<script type="module">
import { auth, db, storage } from "../js/firebase.js";
import { collection, query, where, getDocs, doc, updateDoc } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { ref, uploadBytes, getDownloadURL } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-storage.js";

const adminOrderList = document.getElementById('adminOrderList');

auth.onAuthStateChanged(async (user) => {
    if (!user) {
        window.location.href = "signin.php";
        return;
    }

    try {
        const q = query(collection(db, "pesanan"), where("fld_seller_id", "==", user.uid));
        const querySnapshot = await getDocs(q);
        
        if(querySnapshot.empty) {
            adminOrderList.innerHTML = "<tr><td colspan='6' style='text-align:center;'>You haven't received any orders yet. Keep promoting your products! 🐾</td></tr>";
            return;
        }

        adminOrderList.innerHTML = "";
        
        const docs = querySnapshot.docs.sort((a,b) => {
            const timeA = a.data().fld_created_at?.seconds || 0;
            const timeB = b.data().fld_created_at?.seconds || 0;
            return timeB - timeA;
        });

        docs.forEach(docSnap => {
            const order = docSnap.data();
            const id = docSnap.id;
            
            let itemsText = order.fld_items ? order.fld_items.map(i => `${i.qty}x ${i.name}`).join("<br>") : "-";
            
            let statusColor = "#ccc";
            if(order.fld_status === 'Processing') statusColor = "#ffc107";
            if(order.fld_status === 'Shipped') statusColor = "#17a2b8";
            if(order.fld_status === 'Delivered') statusColor = "#28a745";

            adminOrderList.innerHTML += `
                <tr>
                    <td style="font-size:12px; color:#555;">${id}</td>
                    <td>
                        <strong>${order.fld_shipping_name || 'Unknown'}</strong><br>
                        <small>${order.fld_shipping_phone || '-'}</small><br>
                        <small style="color:#888;">${order.fld_shipping_address || '-'}</small>
                    </td>
                    <td style="font-size:13px;">${itemsText}</td>
                    <td><strong>RM ${parseFloat(order.fld_total_amount).toFixed(2)}</strong></td>
                    <td><span class="status-badge" style="background:${statusColor}; color:white;">${order.fld_status || 'Processing'}</span></td>
                    <td>
                        <button class="btn-update" onclick="openShipModal('${id}', '${order.fld_buyer_email || ''}')">Ship</button>
                        <button class="btn-update" onclick="updateStatus('${id}', 'Delivered')">Deliver</button>
                    </td>
                </tr>
            `;
        });
    } catch(err) {
        console.error("Error loading sales:", err);
        adminOrderList.innerHTML = "<tr><td colspan='6' style='text-align:center;'>Error loading sales.</td></tr>";
    }
});

window.openShipModal = function(orderId, email) {
    document.getElementById('shipOrderId').value = orderId;
    document.getElementById('shipBuyerEmail').value = email;
    document.getElementById('trackNum').value = '';
    document.getElementById('shipProof').value = '';
    document.getElementById('shipModal').style.display = 'block';
}

window.submitShipping = async function() {
    const orderId = document.getElementById('shipOrderId').value;
    const email = document.getElementById('shipBuyerEmail').value;
    const trackNum = document.getElementById('trackNum').value;
    const file = document.getElementById('shipProof').files[0];
    const btn = document.getElementById('confirmShipBtn');

    if (!trackNum || !file) {
        alert("Please provide both Tracking Number and an Image Proof.");
        return;
    }

    btn.innerText = "Uploading...";
    btn.disabled = true;

    try {
        // 1. Upload Image to Storage
        const storageRef = ref(storage, `shipping_proofs/${orderId}_${file.name}`);
        const snapshot = await uploadBytes(storageRef, file);
        const downloadURL = await getDownloadURL(snapshot.ref);

        // 2. Update Firestore Document
        await updateDoc(doc(db, "pesanan", orderId), { 
            fld_status: "Shipped",
            fld_tracking_number: trackNum,
            fld_shipping_image: downloadURL
        });

        // 3. Trigger Email Notification via PHP
        if (email) {
            const formData = new FormData();
            formData.append('order_id', orderId);
            formData.append('email', email);
            formData.append('tracking', trackNum);
            formData.append('image_url', downloadURL);

            await fetch('send_shipping_email.php', {
                method: 'POST',
                body: formData
            });
        }

        alert("Order Shipped and Buyer Notified!");
        location.reload();
    } catch(err) {
        console.error("Shipping error:", err);
        alert("Failed to update shipping details.");
        btn.innerText = "CONFIRM & NOTIFY BUYER";
        btn.disabled = false;
    }
}

window.updateStatus = async function(orderId, newStatus) {
    if(!confirm(`Change order status to ${newStatus}?`)) return;
    try {
        await updateDoc(doc(db, "pesanan", orderId), { fld_status: newStatus });
        alert(`Order marked as ${newStatus}`);
        location.reload();
    } catch(err) {
        console.error("Update error:", err);
        alert("Failed to update status.");
    }
}
</script>
</body>
</html>
