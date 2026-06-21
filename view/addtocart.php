<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Cart - WhiskerHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/shopstyle.css">
</head>
<body>

<div class="navbar">
    <div class="logo">WhiskerHub</div>
    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="shopping.php">Continue Shopping</a>
        <a href="myorders.php">My Orders</a>
        <a href="mysales.php">My Sales</a>
    </div>
</div>

<div class="cart-container" style="max-width: 900px; margin: 40px auto; padding: 0 20px;">
    <h1 style="font-family: 'Playfair Display', serif; margin-bottom: 30px;">Shopping Cart</h1>

    <div id="cartList">
        <p style="text-align: center;">Checking your cart...</p>
    </div>

    <div class="cart-footer" style="margin-top: 30px; text-align: right; background: white; padding: 20px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.02);">
        <h3 style="margin-bottom: 10px;">Total Amount: <span id="totalPrice" style="color: #ffb6c1;">RM 0.00</span></h3>
        <button id="btnCheckout" class="btn-buy" style="background: #333; color: white; padding: 15px 60px; border: none; border-radius: 12px; font-weight: bold; cursor: pointer; transition: 0.3s;">CHECKOUT NOW</button>
    </div>
</div>

<script type="module">
import { auth, db } from "../js/firebase.js";
import { collection, query, where, getDocs, deleteDoc, doc, updateDoc } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";

const cartList = document.getElementById('cartList');
const totalPriceEl = document.getElementById('totalPrice');

// 1. MONITOR AUTH STATE
auth.onAuthStateChanged(async (user) => {
    if (user) {
        loadCart(user.uid);
    } else {
        window.location.href = "signin.php";
    }
});

// --- 2. LOAD CART ITEMS (Updated to trigger calculation) ---
async function loadCart(uid) {
    try {
        const q = query(collection(db, "troli"), where("fld_user_id", "==", uid));
        const querySnapshot = await getDocs(q);
        cartList.innerHTML = "";

        if (querySnapshot.empty) {
            cartList.innerHTML = "<p style='text-align: center; color: #888;'>Your cart is empty. 🐾</p>";
            totalPriceEl.innerText = "RM 0.00";
            return;
        }

        querySnapshot.forEach((docSnap) => {
            const item = docSnap.data();
            const itemId = docSnap.id;
            const qty = item.fld_quantity || 1;
            const price = parseFloat(item.fld_prod_price);

            const itemHTML = `
                <div class="cart-item" style="display: flex; align-items: center; background: white; padding: 20px; border-radius: 15px; margin-bottom: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.02);">
                    <input type="checkbox" class="cart-checkbox" checked 
                        data-id="${itemId}" 
                        data-price="${price}" 
                        data-qty="${qty}" 
                        style="width: 20px; height: 20px; accent-color: #ffb6c1; margin-right: 20px;">
                    
                    <div class="cart-details" style="flex-grow: 1;">
                        <h3 style="font-size: 18px; margin-bottom: 5px;">${item.fld_prod_name}</h3>
                        <p style="color: #ffb6c1; font-weight: bold;">RM ${price.toFixed(2)}</p>
                        
                        <div style="display: flex; align-items: center; margin-top: 10px;">
                            <button onclick="changeQty('${itemId}', ${qty - 1})" style="width: 30px; height: 30px; border-radius: 50%; border: 1px solid #ddd; background: white; cursor: pointer;">-</button>
                            <span style="margin: 0 15px; font-weight: bold;">${qty}</span>
                            <button onclick="changeQty('${itemId}', ${qty + 1})" style="width: 30px; height: 30px; border-radius: 50%; border: 1px solid #ddd; background: white; cursor: pointer;">+</button>
                        </div>
                    </div>
                    <button onclick="deleteItem('${itemId}')" style="background: none; border: none; color: #ff4d4d; cursor: pointer; font-size: 14px; margin-left: 20px;">Remove</button>
                </div>
            `;
            cartList.innerHTML += itemHTML;
        });

        // Attach listeners to all checkboxes
        document.querySelectorAll('.cart-checkbox').forEach(cb => {
            cb.addEventListener('change', calculateTotal);
        });

        // TERUS KIRA TOTAL SELEPAS LOAD
        calculateTotal();

    } catch (error) {
        console.error("Error loading cart:", error);
    }
}
// --- 3. UPDATE QUANTITY (Smoother & More Responsive) ---
window.changeQty = async function(id, newQty) {
    if (newQty < 1) return;

    try {
        // A. Terus update Firestore kat belakang tab
        const itemRef = doc(db, "troli", id);
        updateDoc(itemRef, {
            fld_quantity: newQty
        });

        // B. Update UI secara manual tanpa reload seluruh list (Taknak bagi butang 'mati')
        // Cari checkbox yang berkaitan dengan ID ini
        const checkbox = document.querySelector(`.cart-checkbox[data-id="${id}"]`);
        if (checkbox) {
            checkbox.setAttribute('data-qty', newQty); // Update attribute qty baru
            
            // Cari text kuantiti (span) dalam card yang sama
            const qtySpan = checkbox.parentElement.querySelector('span');
            if (qtySpan) qtySpan.innerText = newQty;

            // Update function parameter untuk butang + dan - supaya next click betul
            const buttons = checkbox.parentElement.querySelectorAll('button');
            buttons[0].setAttribute('onclick', `changeQty('${id}', ${newQty - 1})`); // Button -
            buttons[1].setAttribute('onclick', `changeQty('${id}', ${newQty + 1})`); // Button +
        }

        // C. Kira balik total harga
        calculateTotal();

    } catch (error) {
        console.error("Update failed:", error);
    }
};

// --- 4. CALCULATE TOTAL PRICE (Updated to be more dynamic) ---
function calculateTotal() {
    let total = 0;
    const checkedBoxes = document.querySelectorAll('.cart-checkbox:checked');
    
    checkedBoxes.forEach(cb => {
        const price = parseFloat(cb.getAttribute('data-price'));
        const qty = parseInt(cb.getAttribute('data-qty'));
        total += (price * qty);
    });

    totalPriceEl.innerText = `RM ${total.toFixed(2)}`;
}
// 5. REMOVE ITEM
window.deleteItem = async function(id) {
    if (confirm("Are you sure you want to remove this item?")) {
        try {
            await deleteDoc(doc(db, "troli", id));
            loadCart(auth.currentUser.uid);
        } catch (error) {
            console.error("Delete failed:", error);
        }
    }
};

// 6. PROCEED TO CHECKOUT
document.getElementById('btnCheckout').addEventListener('click', () => {
    const selected = Array.from(document.querySelectorAll('.cart-checkbox:checked'))
                          .map(cb => cb.getAttribute('data-id'));
    
    if (selected.length === 0) {
        alert("Please select at least one item to proceed!");
        return;
    }

    // Redirect to checkout with selected IDs
    window.location.href = `checkout.php?ids=${selected.join(',')}`;
});
</script>

<style>
    .btn-buy:hover {
        background: #ffb6c1 !important;
        color: #333 !important;
        transform: translateY(-3px);
    }
    .cart-item { transition: 0.3s; }
    .cart-item:hover { transform: scale(1.01); }
</style>

</body>
</html>