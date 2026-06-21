<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Review & Rating - WhiskerHub</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        .review-card {
            max-width: 600px;
            margin: 50px auto;
            background: white;
            padding: 40px;
            border-radius: 30px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.05);
        }
        .stars {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 25px 0;
            flex-direction: row-reverse;
        }
        .stars input { display: none; }
        .stars label {
            font-size: 40px;
            color: #ddd;
            cursor: pointer;
            transition: 0.3s;
        }
        .stars input:checked ~ label,
        .stars label:hover,
        .stars label:hover ~ label {
            color: #f39c12;
        }
        textarea {
            width: 100%;
            padding: 20px;
            border-radius: 15px;
            border: 1.5px solid #eee;
            font-family: 'Poppins', sans-serif;
            resize: none;
            margin-bottom: 20px;
            box-sizing: border-box;
        }
        .btn-done {
            background-color: #ffb6c1;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            transition: 0.3s;
        }
        .btn-done:hover { background-color: #ff99aa; }
    </style>
</head>
<body>
    <div class="review-card">
        <h2 style="font-family: 'Playfair Display';">REVIEW AND RATING</h2>
        <p style="color: #888; font-size: 14px;">How was your experience with this sitter?</p>

        <form id="reviewForm">
            <div class="stars">
                <input type="radio" id="star5" name="rating" value="5"><label for="star5">★</label>
                <input type="radio" id="star4" name="rating" value="4"><label for="star4">★</label>
                <input type="radio" id="star3" name="rating" value="3"><label for="star3">★</label>
                <input type="radio" id="star2" name="rating" value="2"><label for="star2">★</label>
                <input type="radio" id="star1" name="rating" value="1"><label for="star1">★</label>
            </div>

            <textarea id="comment" rows="5" placeholder="Write your feedback here..." required></textarea>

            <button type="submit" class="btn-done">SUBMIT REVIEW</button>
        </form>
    </div>

<script type="module">
import { auth, db } from "../js/firebase.js";
import { 
    collection, 
    addDoc, 
    serverTimestamp, 
    doc, 
    getDoc 
} from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";

const urlParams = new URLSearchParams(window.location.search);
const sitterID = urlParams.get('sitterId');
const reviewForm = document.getElementById('reviewForm');

reviewForm.addEventListener('submit', async (e) => {
    e.preventDefault();

    if (!auth.currentUser) {
        alert("Please sign in first!");
        return;
    }

    try {
        // 1. Tarik nama sebenar user dari koleksi 'pengguna'
        const userDocRef = doc(db, "pengguna", auth.currentUser.uid);
        const userSnap = await getDoc(userDocRef);
        
        let actualName = "Anonymous User";
        if (userSnap.exists()) {
            // Gunakan field 'fld_user_name' ikut struktur database awak
            actualName = userSnap.data().fld_user_name || "User"; 
        }

        // 2. Ambil input dari form
        const ratingValue = document.querySelector('input[name="rating"]:checked')?.value;
        const commentValue = document.getElementById('comment').value;

        if (!ratingValue) {
            alert("Please select a rating star!");
            return;
        }

        // 3. Simpan ke koleksi 'review'
        const reviewData = {
            sitterID: sitterID,
            fld_user_name: actualName, // Sekarang dia save nama dari Firestore!
            fld_user_rating: parseInt(ratingValue),
            fld_user_comment: commentValue,
            createdAt: serverTimestamp()
        };

        await addDoc(collection(db, "review"), reviewData);
        alert("Thank you for your review, " + actualName + "! 🐾");
        window.location.href = "history.php";

    } catch (error) {
        console.error("Error saving review:", error);
        alert("Failed to submit review. Please try again.");
    }
});
</script>
</body>
</html>