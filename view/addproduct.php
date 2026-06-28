<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - WhiskerHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/addprodstyle.css">
</head>
<body>

<div class="navbar">
    <div class="logo">WhiskerHub</div>
    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="shopping.php">Shopping</a>
        <a href="myorders.php">My Orders</a>
        <a href="mysales.php" class="active">My Sales</a>
    </div>
</div>

<div class="form-container">
    <div class="register-card" style="margin-top: 50px;">
        <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 20px;">Add New Product</h2>
        <p>Please fill in the details of your cat product below.</p>
        
        <form id="addProductForm">
            <div class="input-group">
                <label>PRODUCT NAME</label>
                <input type="text" id="prod_name" placeholder="Example: Luxury 2-Tier Cat Cage" required>
            </div>

            <div class="input-group">
                <label>PRICE (RM)</label>
                <input type="number" id="prod_price" step="0.01" placeholder="0.00" required>
            </div>

            <div class="input-group">
                <label>DETAILS</label>
                <textarea id="prod_desc" rows="5" placeholder="Describe your product's highlights..."></textarea>
            </div>

            <div class="input-group">
                <label>STOCK</label>
                <input type="number" id="prod_stock" placeholder="Available quantity" required>
            </div>

            <div class="input-group">
                <label>PRODUCT IMAGE</label>
                <div class="file-upload-wrapper" style="border: 2px dashed #eee; padding: 20px; border-radius: 10px; text-align: center;">
                    <input type="file" id="prod_image" accept="image/*">
                </div>
            </div>

            <button type="submit" id="submitBtn" class="btn-register" style="background-color: #ffb6c1; color: #333;">ADD PRODUCT</button>
        </form>
    </div>
</div>

<script type="module">
import { db, storage, auth } from "../js/firebase.js";
import { collection, addDoc, serverTimestamp, doc, getDoc } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { ref, uploadBytes, getDownloadURL } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-storage.js";

const addProductForm = document.getElementById('addProductForm');
const submitBtn = document.getElementById('submitBtn');

auth.onAuthStateChanged(async (user) => {
    if (!user) {
        window.location.href = "signin.php";
        return;
    }

    try {
        let userSnap = await getDoc(doc(db, "pengguna", user.uid));
        if (!userSnap.exists()) {
            userSnap = await getDoc(doc(db, "penjaga_kucing", user.uid));
        }
        if (!userSnap.exists() || userSnap.data().fld_is_seller !== true) {
            alert("Akses Dihalang: Anda perlu mendaftar kedai jualan anda terlebih dahulu.");
            window.location.href = "mysales.php";
            return;
        }
    } catch (err) {
        console.error("Error validating seller status:", err);
        window.location.href = "shopping.php";
        return;
    }
});

addProductForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    const name = document.getElementById('prod_name').value;
    const price = document.getElementById('prod_price').value;
    const desc = document.getElementById('prod_desc').value;
    const stock = document.getElementById('prod_stock').value;
    const imageFile = document.getElementById('prod_image').files[0];

    if (!imageFile) {
       alert("Please upload a product image!");
        return;
    }

    // Tukar status button biar user tak tekan banyak kali
    submitBtn.disabled = true;
    submitBtn.innerText = "Uploading...";

    try {
        // 1. Upload Gambar ke Firebase Storage
        const storageRef = ref(storage, `products/${Date.now()}_${imageFile.name}`);
        const snapshot = await uploadBytes(storageRef, imageFile);
        const downloadURL = await getDownloadURL(snapshot.ref);

        // 2. Simpan Data ke Firestore (Koleksi: produk)
        const productData = {
            fld_prod_name: name,
            fld_prod_price: parseFloat(price),
            fld_prod_desc: desc,
            fld_prod_stock: parseInt(stock),
            fld_prod_image: downloadURL,
            fld_seller_id: auth.currentUser ? auth.currentUser.uid : "unknown",
            fld_created_at: serverTimestamp()
        };

        await addDoc(collection(db, "produk"), productData);

        alert("Product added successfully! 🐾");
        window.location.href = "mysales.php";

    } catch (error) {
        console.error("Error adding product: ", error);
        alert("Failed to add product: " + error.message);
        submitBtn.disabled = false;
        submitBtn.innerText = "ADD PRODUCT";
    }
});
</script>

</body>
</html>