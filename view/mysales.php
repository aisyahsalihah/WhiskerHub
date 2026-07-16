<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        
        /* Tab Styles */
        .dashboard-tabs { display: flex; gap: 15px; border-bottom: 2px solid #eee; margin-bottom: 25px; }
        .tab-btn { padding: 12px 25px; border: none; background: none; font-size: 15px; font-weight: bold; cursor: pointer; color: #666; transition: 0.3s; border-bottom: 3px solid transparent; }
        .tab-btn:hover { color: #ffb6c1; }
        .tab-btn.active { color: #ffb6c1; border-bottom-color: #ffb6c1; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
    </style>
</head>
<body>

<div class="navbar">
    <a href="mainmenu.php" class="logo">WhiskerHub</a>
    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="shopping.php">Shop</a>
        <a href="mysales.php" class="active" id="navMySales">My Sales</a>
    </div>
</div>

<div class="admin-container" id="sellerDashboard" style="display:none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1 style="font-family: 'Playfair Display', serif; margin: 0;">Seller Dashboard</h1>
        <a href="addproduct.php" style="padding: 10px 20px; font-size: 14px; text-decoration: none; display: inline-block; background: #ffb6c1; color: #333; border-radius: 8px; font-weight: bold; transition: 0.3s;" onmouseover="this.style.background='#ff9aa2'" onmouseout="this.style.background='#ffb6c1'">+ Add New Product</a>
    </div>

    <!-- Dashboard Tabs -->
    <div class="dashboard-tabs">
        <button class="tab-btn active" onclick="switchTab('ordersTab', this)">Orders Received</button>
        <button class="tab-btn" onclick="switchTab('productsTab', this)">My Products</button>
    </div>

    <!-- Tab 1: Orders Received -->
    <div id="ordersTab" class="tab-content active">
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

    <!-- Tab 2: My Products -->
    <div id="productsTab" class="tab-content">
        <table class="order-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="sellerProductList">
                <tr><td colspan="6" style="text-align: center;">Loading your products...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Seller Registration Form (Hidden by default) -->
<div class="admin-container" id="sellerRegisterContainer" style="display:none;">
    <div style="max-width: 500px; margin: 40px auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); text-align: center;">
        <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 10px; color: #333;">Start Selling on WhiskerShop! 🐱</h2>
        <p style="color: #666; font-size: 14px; margin-bottom: 25px;">Register your shop now to start listing your cat products and receiving orders from buyers.</p>
        
        <div class="form-group" style="text-align: left; margin-bottom: 20px;">
            <label style="font-weight: bold; font-size: 13px; display: block; margin-bottom: 8px;">Shop Name</label>
            <input type="text" id="regShopName" placeholder="Example: Happy Cat Petshop" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box;">
        </div>
        
        <button id="btnRegisterSeller" style="width: 100%; padding: 14px; background: #ffb6c1; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; transition: 0.3s;" onmouseover="this.style.background='#ff9aa2'" onmouseout="this.style.background='#ffb6c1'">REGISTER AS SELLER</button>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('editProductModal').style.display='none'">&times;</span>
        <h2>Edit Product</h2>
        <p style="font-size: 13px; color: #666; margin-bottom: 20px;">Update your product details below.</p>
        
        <div class="form-group">
            <label>Product Name</label>
            <input type="text" id="editProdName">
        </div>
        <div class="form-group">
            <label>Price (RM)</label>
            <input type="text" id="editProdPrice">
        </div>
        <div class="form-group">
            <label>Stock</label>
            <input type="text" id="editProdStock">
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea id="editProdDesc" rows="3" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; resize: vertical;"></textarea>
        </div>
        <input type="hidden" id="editProdId">
        
        <button class="btn-submit-ship" id="confirmEditBtn" onclick="submitProductEdit()">SAVE CHANGES</button>
    </div>
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
import { collection, query, where, getDocs, doc, updateDoc, getDoc, deleteDoc, setDoc } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { ref, uploadBytes, getDownloadURL } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-storage.js";

const adminOrderList = document.getElementById('adminOrderList');
const sellerProductList = document.getElementById('sellerProductList');

window.switchTab = function(tabId, btn) {
    // Hide all tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    // Remove active class from all tab buttons
    document.querySelectorAll('.tab-btn').forEach(button => {
        button.classList.remove('active');
    });
    // Show selected tab content
    document.getElementById(tabId).classList.add('active');
    // Set active tab button
    btn.classList.add('active');
};

auth.onAuthStateChanged(async (user) => {
    if (!user) {
        window.location.href = "login.php";
        return;
    }

    try {
        let userSnap = await getDoc(doc(db, "penjaga_kucing", user.uid));
        let currentCollection = "penjaga_kucing";
        if (!userSnap.exists()) {
            userSnap = await getDoc(doc(db, "pengguna", user.uid));
            currentCollection = "pengguna";
        }

        const isSeller = userSnap.exists() && userSnap.data().fld_is_seller === true;
        const salesLink = document.getElementById("navMySales");
        if (salesLink) {
            salesLink.innerText = isSeller ? "My Sales" : "Become a Seller";
        }

        if (isSeller) {
            document.getElementById('sellerDashboard').style.display = 'block';
            loadProducts(user.uid);
        } else {
            document.getElementById('sellerRegisterContainer').style.display = 'block';
            document.getElementById('btnRegisterSeller').onclick = async () => {
                const shopName = document.getElementById('regShopName').value.trim();
                if (!shopName) {
                    alert("Please enter your shop name!");
                    return;
                }
                try {
                    document.getElementById('btnRegisterSeller').disabled = true;
                    document.getElementById('btnRegisterSeller').innerText = "Registering...";
                    
                    await updateDoc(doc(db, currentCollection, user.uid), {
                        fld_is_seller: true,
                        fld_shop_name: shopName
                    });
                    
                    alert("Shop registration successful! You can now start selling. 🎉");
                    location.reload();
                } catch (e) {
                    console.error("Error registering seller:", e);
                    alert("Failed to register shop. Please try again.");
                    document.getElementById('btnRegisterSeller').disabled = false;
                    document.getElementById('btnRegisterSeller').innerText = "REGISTER AS SELLER";
                }
            };
            return;
        }
    } catch (err) {
        console.error("Error validating seller status:", err);
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
                        <button class="btn-update" style="background:#ffb6c1; color:#333;" onclick="startChatWithUser('${order.fld_user_id}', 'seller', 'Hi! I am contacting you regarding your Order #${id}. 🐾')">Chat Buyer 💬</button>
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

async function loadProducts(sellerId) {
    try {
        const q = query(collection(db, "produk"), where("fld_seller_id", "==", sellerId));
        const querySnapshot = await getDocs(q);
        
        if (querySnapshot.empty) {
            sellerProductList.innerHTML = "<tr><td colspan='6' style='text-align:center;'>You have not listed any products yet.</td></tr>";
            return;
        }

        sellerProductList.innerHTML = "";
        querySnapshot.forEach(docSnap => {
            const product = docSnap.data();
            const id = docSnap.id;
            
            const escapedDesc = (product.fld_prod_desc || "").replace(/'/g, "\\'").replace(/"/g, "&quot;").replace(/\n/g, " ");
            const escapedName = (product.fld_prod_name || "").replace(/'/g, "\\'").replace(/"/g, "&quot;");

            sellerProductList.innerHTML += `
                <tr>
                    <td><img src="${product.fld_prod_image || ''}" alt="Product" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                    <td><strong>${product.fld_prod_name || ''}</strong></td>
                    <td>RM ${parseFloat(product.fld_prod_price).toFixed(2)}</td>
                    <td>${product.fld_prod_stock || 0}</td>
                    <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><small>${product.fld_prod_desc || '-'}</small></td>
                    <td>
                        <button class="btn-update" onclick="openEditProductModal('${id}', '${escapedName}', ${product.fld_prod_price}, ${product.fld_prod_stock}, '${escapedDesc}')">Edit</button>
                        <button class="btn-update" style="background:#d32f2f;" onclick="deleteProduct('${id}')">Delete</button>
                    </td>
                </tr>
            `;
        });
    } catch (err) {
        console.error("Error loading products:", err);
        sellerProductList.innerHTML = "<tr><td colspan='6' style='text-align:center;'>Error loading products.</td></tr>";
    }
}

window.openEditProductModal = function(id, name, price, stock, desc) {
    document.getElementById('editProdId').value = id;
    document.getElementById('editProdName').value = name;
    document.getElementById('editProdPrice').value = price;
    document.getElementById('editProdStock').value = stock;
    document.getElementById('editProdDesc').value = desc;
    document.getElementById('editProductModal').style.display = 'block';
};

window.submitProductEdit = async function() {
    const id = document.getElementById('editProdId').value;
    const name = document.getElementById('editProdName').value.trim();
    const price = parseFloat(document.getElementById('editProdPrice').value);
    const stock = parseInt(document.getElementById('editProdStock').value);
    const desc = document.getElementById('editProdDesc').value.trim();
    const btn = document.getElementById('confirmEditBtn');

    if (!name || isNaN(price) || isNaN(stock)) {
        alert("Please enter valid name, price, and stock!");
        return;
    }

    btn.innerText = "Saving...";
    btn.disabled = true;

    try {
        await updateDoc(doc(db, "produk", id), {
            fld_prod_name: name,
            fld_prod_price: price,
            fld_prod_stock: stock,
            fld_prod_desc: desc
        });

        alert("Product updated successfully!");
        location.reload();
    } catch(err) {
        console.error("Edit error:", err);
        alert("Failed to update product.");
        btn.innerText = "SAVE CHANGES";
        btn.disabled = false;
    }
};

window.deleteProduct = async function(id) {
    if (!confirm("Are you sure you want to permanently delete this product?")) return;
    try {
        await deleteDoc(doc(db, "produk", id));
        alert("Product deleted successfully!");
        location.reload();
    } catch(err) {
        console.error("Delete error:", err);
        alert("Failed to delete product.");
    }
};
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

    const buyerId = role === 'seller' ? otherUserId : user.uid;
    const sellerId = role === 'seller' ? user.uid : otherUserId;
    const chatRoomId = [user.uid, otherUserId].sort().join('_');

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
</script>
</body>
</html>
