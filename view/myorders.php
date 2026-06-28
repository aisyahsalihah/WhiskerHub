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
    </style>
</head>
<body>

<div class="navbar">
    <a href="mainmenu.php" class="logo">WhiskerHub</a>
    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="shopping.php">Shopping</a>
        <a href="myorders.php" class="active">My Orders</a>
        <a href="mysales.php" id="mySalesLink" style="display:none;">My Sales</a>
    </div>
</div>

<div class="orders-container">
    <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 30px;">My Orders</h1>
    <div id="ordersList">
        <p>Loading your orders...</p>
    </div>
</div>

<script type="module">
import { auth, db } from "../js/firebase.js";
import { doc, getDoc, collection, query, where, getDocs } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";

const ordersList = document.getElementById('ordersList');

auth.onAuthStateChanged(async (user) => {
    if (!user) {
        window.location.href = "signin.php";
        return;
    }

    // Check if user is seller to show My Sales Link
    try {
        let userSnap = await getDoc(doc(db, "pengguna", user.uid));
        if (!userSnap.exists()) {
            userSnap = await getDoc(doc(db, "penjaga_kucing", user.uid));
        }
        if (userSnap.exists() && userSnap.data().fld_is_seller === true) {
            const salesLink = document.getElementById("mySalesLink");
            if (salesLink) salesLink.style.display = "";
        }
    } catch (e) {
        console.error("Error checking seller status:", e);
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
                    <div style="text-align: right; border-top: 1px solid #eee; padding-top: 15px;">
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
</script>
</body>
</html>
