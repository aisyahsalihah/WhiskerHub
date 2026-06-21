<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - WhiskerShop</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/shopstyle.css">
    <style>
        .checkout-container { max-width: 800px; margin: 40px auto; display: flex; gap: 30px; }
        .checkout-form { flex: 2; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .checkout-summary { flex: 1; background: #f9f9f9; padding: 30px; border-radius: 15px; }
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; font-weight: bold; margin-bottom: 5px; font-size: 14px; }
        .input-group input, .input-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; }
        .btn-confirm { background: #000; color: white; border: none; padding: 15px; width: 100%; font-weight: bold; border-radius: 8px; cursor: pointer; transition: 0.3s; margin-top: 20px;}
        .btn-confirm:hover { background: #333; }
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 10px;}
    </style>
</head>
<body>

<div class="navbar">
    <a href="mainmenu.php" class="logo">WhiskerHub</a>
    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="shopping.php">Shopping</a>
        <a href="myorders.php">My Orders</a>
        <a href="mysales.php">My Sales</a>
        <a href="addtocart.php">Cart 🛒</a>
    </div>
</div>

<div class="checkout-container">
    <div class="checkout-form">
        <h2>Shipping Details</h2>
        <form id="checkoutForm">
            <div class="input-group">
                <label>Full Name</label>
                <input type="text" id="ship_name" required>
            </div>
            <div class="input-group">
                <label>Phone Number</label>
                <input type="tel" id="ship_phone" required>
            </div>
            <div class="input-group">
                <label>Delivery Address</label>
                <textarea id="ship_address" rows="4" required></textarea>
            </div>
            <div class="input-group">
                <label>Payment Method</label>
                <div style="display: flex; gap: 20px; align-items: center; margin-top: 5px;">
                    <label style="font-weight: normal; cursor: pointer;"><input type="radio" name="payment_method" value="COD" checked> Cash on Delivery</label>
                    <label style="font-weight: normal; cursor: pointer;"><input type="radio" name="payment_method" value="Stripe"> Stripe (Card)</label>
                </div>
            </div>
            <button type="submit" id="btnConfirm" class="btn-confirm">CONFIRM ORDER</button>
        </form>
    </div>
    <div class="checkout-summary">
        <h3>Order Summary</h3>
        <div id="summaryList">
            <p>Loading items...</p>
        </div>
        <h2 style="margin-top: 20px; border-top: 2px solid #ddd; padding-top: 20px;">Total: <br><span id="summaryTotal">RM 0.00</span></h2>
    </div>
</div>

<script type="module">
import { auth, db } from "../js/firebase.js";
import { collection, doc, getDoc, addDoc, deleteDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";

const urlParams = new URLSearchParams(window.location.search);
const idsParam = urlParams.get('ids');
const cartIds = idsParam ? idsParam.split(',') : [];

const summaryList = document.getElementById('summaryList');
const summaryTotal = document.getElementById('summaryTotal');
const checkoutForm = document.getElementById('checkoutForm');
const btnConfirm = document.getElementById('btnConfirm');

let orderItems = [];
let orderTotal = 0;

auth.onAuthStateChanged(async (user) => {
    if (!user) {
        window.location.href = "signin.php";
        return;
    }
    
    if (cartIds.length === 0) {
        alert("No items selected for checkout.");
        window.location.href = "addtocart.php";
        return;
    }

    // Load selected items
    summaryList.innerHTML = "";
    try {
        for (const id of cartIds) {
            const itemSnap = await getDoc(doc(db, "troli", id));
            if (itemSnap.exists()) {
                const item = itemSnap.data();
                orderItems.push({
                    id: item.fld_prod_id,
                    name: item.fld_prod_name,
                    price: parseFloat(item.fld_prod_price),
                    qty: item.fld_quantity,
                    cartId: id,
                    sellerId: item.fld_seller_id || "unknown"
                });
            }
        }
        
        orderItems.forEach(item => {
            const itemTotal = item.price * item.qty;
            orderTotal += itemTotal;
            summaryList.innerHTML += `
                <div class="summary-item">
                    <div>
                        <strong>${item.name}</strong><br>
                        <small>Qty: ${item.qty}</small>
                    </div>
                    <div>RM ${itemTotal.toFixed(2)}</div>
                </div>
            `;
        });
        summaryTotal.innerText = `RM ${orderTotal.toFixed(2)}`;

    } catch (error) {
        console.error("Error loading cart items:", error);
    }
});

checkoutForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const user = auth.currentUser;
    if(!user) return;

    btnConfirm.disabled = true;
    btnConfirm.innerText = "Processing...";

    const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;

    try {
        // Group items by sellerId
        const itemsBySeller = {};
        orderItems.forEach(item => {
            if (!itemsBySeller[item.sellerId]) itemsBySeller[item.sellerId] = [];
            itemsBySeller[item.sellerId].push(item);
        });

        const orderIds = [];

        // Create separate orders for each seller
        for (const [sellerId, items] of Object.entries(itemsBySeller)) {
            const sellerTotal = items.reduce((sum, i) => sum + (i.price * i.qty), 0);
            
            const orderData = {
                fld_user_id: user.uid,
                fld_seller_id: sellerId,
                fld_shipping_name: document.getElementById('ship_name').value,
                fld_shipping_phone: document.getElementById('ship_phone').value,
                fld_shipping_address: document.getElementById('ship_address').value,
                fld_payment_method: paymentMethod,
                fld_items: items.map(i => ({ id: i.id, name: i.name, price: i.price, qty: i.qty })),
                fld_total_amount: sellerTotal,
                fld_status: paymentMethod === 'Stripe' ? "Processing" : "Processing",
                fld_buyer_email: user.email || 'customer@whiskerhub.com',
                fld_created_at: serverTimestamp()
            };

            const docRef = await addDoc(collection(db, "pesanan"), orderData);
            orderIds.push(docRef.id);
        }
        
        // 2. Clear Cart items
        for (const item of orderItems) {
            await deleteDoc(doc(db, "troli", item.cartId));
        }

        const combinedOrderIds = orderIds.join(",");

        if (paymentMethod === 'Stripe') {
            window.location.href = `payment.php?booking_id=${combinedOrderIds}&amount=${orderTotal}&email=${user.email || 'customer@whiskerhub.com'}&type=shop`;
        } else {
            // For COD, redirect directly to success_shop.php with type=cod
            window.location.href = `success_shop.php?booking_id=${combinedOrderIds}&email=${user.email || 'customer@whiskerhub.com'}&type=cod`;
        }

    } catch(err) {
        console.error("Error creating order:", err);
        alert("Failed to place order. Try again.");
        btnConfirm.disabled = false;
        btnConfirm.innerText = "CONFIRM ORDER";
    }
});
</script>
</body>
</html>
