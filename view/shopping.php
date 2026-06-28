<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhiskerShop - WhiskerHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/shopstyle.css">

</head>
<body>

<div class="navbar">
    <div class="logo">WhiskerShop</div>
    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="shopping.php" class="active">Shopping</a>
        <a href="myorders.php">My Orders</a>
        <a href="mysales.php">My Sales</a>
        <a href="addtocart.php">Troli 🛒</a>
    </div>
</div>

<section class="search-container">
    <h1>All Products</h1>
    <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search products...">
        <button onclick="searchProducts()">SEARCH</button>
    </div>
</section>

<section class="shop-container">
    
    <div class="product-grid" id="productGrid">
        <p style="text-align: center; grid-column: 1/-1;">Loading products...</p>
    </div>
</section>

<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-body">
            <img id="modalImg" src="" alt="Product">
            <div class="modal-text">
                <h2 id="modalTitle"></h2>
                <p style="font-size: 13px; color: #888; margin-top: -10px; margin-bottom: 15px;">Kedai: <strong id="modalSellerName" style="color: #333;">Loading...</strong></p>
                <p id="modalPrice" class="modal-price"></p>
                <p id="modalDesc"></p>
                
                <input type="hidden" id="modalProdId">
                <input type="hidden" id="modalSellerId">
                
                <button class="btn-add-cart" onclick="addToCart()">Add to Cart 🛒</button>
            </div>
        </div>
    </div>
</div>

<script type="module">
import { auth, db } from "../js/firebase.js";
import { doc, getDoc, collection, getDocs, addDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";

const productGrid = document.getElementById('productGrid');
let allProducts = [];

// 1. LOAD PRODUK DARI FIRESTORE
async function loadProducts() {
    try {
        const querySnapshot = await getDocs(collection(db, "produk"));
        allProducts = [];

        querySnapshot.forEach((doc) => {
            const p = doc.data();
            p.id = doc.id;
            allProducts.push(p);
        });

        displayProducts(allProducts);
    } catch (error) {
        console.error("Error:", error);
        productGrid.innerHTML = "<p>Failed to load products. Please check the console.</p>";
    }
}

function displayProducts(products) {
    productGrid.innerHTML = ""; 

    if (products.length === 0) {
        productGrid.innerHTML = "<p style='grid-column: 1/-1; text-align: center;'>Tiada produk dijumpai.</p>";
        return;
    }

    products.forEach((p) => {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.innerHTML = `
            <div class="img-placeholder">
                <img src="${p.fld_prod_image || 'https://via.placeholder.com/200'}" alt="Product">
            </div>
            <div class="product-info">
                <h3>${p.fld_prod_name}</h3>
                <p class="price">RM ${parseFloat(p.fld_prod_price).toFixed(2)}</p>
            </div>
        `;

        productCard.addEventListener('click', () => {
            window.openModal(p.id, p.fld_prod_name, p.fld_prod_price, p.fld_prod_desc, p.fld_prod_image, p.fld_seller_id);
        });

        productGrid.appendChild(productCard);
    });
}

window.searchProducts = function() {
    const query = document.getElementById('searchInput').value.toLowerCase();
    const filtered = allProducts.filter(p => p.fld_prod_name.toLowerCase().includes(query) || (p.fld_prod_desc && p.fld_prod_desc.toLowerCase().includes(query)));
    displayProducts(filtered);
}

document.getElementById('searchInput').addEventListener('keyup', function(e) {
    if (e.key === 'Enter') {
        window.searchProducts();
    }
});
// 2. FUNGSI MODAL
window.openModal = async function(id, title, price, desc, img, sellerId) {
    document.getElementById('modalProdId').value = id;
    document.getElementById('modalSellerId').value = sellerId || 'unknown';
    document.getElementById('modalTitle').innerText = title;
    document.getElementById('modalPrice').innerText = "RM " + parseFloat(price).toFixed(2);
    document.getElementById('modalDesc').innerText = desc;
    document.getElementById('modalImg').src = img || 'https://via.placeholder.com/400';
    document.getElementById('productModal').style.display = "block";

    const sellerNameEl = document.getElementById('modalSellerName');
    if (sellerNameEl) sellerNameEl.innerText = "Loading...";

    if (sellerId && sellerId !== 'unknown') {
        try {
            let sellerSnap = await getDoc(doc(db, "penjaga_kucing", sellerId));
            if (!sellerSnap.exists()) {
                sellerSnap = await getDoc(doc(db, "pengguna", sellerId));
            }
            if (sellerSnap.exists()) {
                const data = sellerSnap.data();
                sellerNameEl.innerText = data.fld_shop_name || data.fld_user_name || data.fld_user_fullname || "WhiskerHub Seller";
            } else {
                sellerNameEl.innerText = "WhiskerHub Seller";
            }
        } catch (e) {
            console.error("Error loading seller details:", e);
            sellerNameEl.innerText = "WhiskerHub Seller";
        }
    } else {
        sellerNameEl.innerText = "WhiskerHub Seller";
    }
}

window.closeModal = function() {
    document.getElementById('productModal').style.display = "none";
}

// 3. FUNGSI ADD TO CART
window.addToCart = async function() {
    const user = auth.currentUser;
    if (!user) {
        alert("Please login first!");
        window.location.href = "signin.php";
        return;
    }

    const cartData = {
        fld_user_id: user.uid,
        fld_prod_id: document.getElementById('modalProdId').value,
        fld_seller_id: document.getElementById('modalSellerId').value,
        fld_prod_name: document.getElementById('modalTitle').innerText,
        fld_prod_price: document.getElementById('modalPrice').innerText.replace("RM ", ""),
        fld_quantity: 1,
        fld_added_at: serverTimestamp()
    };

    try {
        await addDoc(collection(db, "troli"), cartData);
        alert("Berjaya ditambah ke troli! 🐈");
        closeModal();
    } catch (error) {
        console.error("Error adding to cart:", error);
        alert("Gagal menambah ke troli.");
    }
};

// Jalankan load data
loadProducts();
</script>

</body>
</html>