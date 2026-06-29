<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Shop Orders</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .order-table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-radius: 10px; overflow: hidden; }
        .order-table th, .order-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .order-table th { background: #000; color: white; font-size: 14px; text-transform: uppercase; }
        .btn-update { padding: 5px 10px; border: none; background: #333; color: white; border-radius: 5px; cursor: pointer; font-size: 12px; margin-right: 5px; }
        .btn-update:hover { background: #000; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 11px; font-weight: bold; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">WhiskerHub Admin</div>
    <div class="nav-links">
        <a href="admin_dashboard.php">Admin Dashboard</a>
        <a href="admin_shop.php" class="active">Shop Orders</a>
        <a href="#" id="logoutBtn">Logout</a>
    </div>
</div>

<div class="admin-container">
    <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 30px;">Manage Shop Orders</h1>
    <table class="order-table">
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Items</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="adminOrderList">
            <tr><td colspan="6" style="text-align: center;">Loading orders...</td></tr>
        </tbody>
    </table>
</div>

<script type="module">
import { auth, db } from "../js/firebase.js";
import { collection, getDocs, doc, updateDoc } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { signOut } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";

const adminOrderList = document.getElementById('adminOrderList');

auth.onAuthStateChanged((user) => {
    if (!user || user.email !== 'admin@whiskerhub.com') {
        alert("Access Denied. Admins only.");
        window.location.href = "login.php";
    } else {
        loadAllOrders();
    }
});

document.getElementById('logoutBtn').addEventListener('click', async (e) => {
    e.preventDefault();
    await signOut(auth);
    window.location.href = "login.php";
});

async function loadAllOrders() {
    try {
        const querySnapshot = await getDocs(collection(db, "pesanan"));
        
        if(querySnapshot.empty) {
            adminOrderList.innerHTML = "<tr><td colspan='6' style='text-align:center;'>No orders found.</td></tr>";
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
                        <button class="btn-update" onclick="updateStatus('${id}', 'Shipped')">Ship</button>
                        <button class="btn-update" onclick="updateStatus('${id}', 'Delivered')">Deliver</button>
                    </td>
                </tr>
            `;
        });
    } catch(err) {
        console.error("Error loading all orders:", err);
        adminOrderList.innerHTML = "<tr><td colspan='6' style='text-align:center;'>Error loading orders.</td></tr>";
    }
}

window.updateStatus = async function(orderId, newStatus) {
    if(!confirm(`Change order status to ${newStatus}?`)) return;
    try {
        await updateDoc(doc(db, "pesanan", orderId), { fld_status: newStatus });
        alert(`Order marked as ${newStatus}`);
        loadAllOrders();
    } catch(err) {
        console.error("Update error:", err);
        alert("Failed to update status.");
    }
}
</script>
</body>
</html>
