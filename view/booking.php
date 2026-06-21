<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Butiran Tempahan - WhiskerHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/bookingstyle.css">
</head>
<body>

<div class="navbar">
    <div class="logo">WhiskerHub</div>
    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="findcatsitter.php">Search Sitters</a>
    </div>
</div>

<div class="booking-container">
    <div class="booking-card">
        <div class="booking-header">
            <h1>BOOKING DETAILS</h1>
            <p>Please confirm your information before making a payment.</p>
        </div>
        
        <hr class="divider">

        <form id="bookingForm">
            <div class="booking-grid">
                <div class="input-group">
                    <label>OWNER NAME :</label>
                    <input type="text" id="customer_name" readonly>
                </div>

            <div class="input-group">
                    <label>SITTER NAME :</label>
                    <input type="text" id="sitter_name" readonly>
                    <input type="hidden" id="fld_penjaga_ID"> </div>

            <div class="input-group">
                    <label>SERVICE TYPE :</label>
                    <select id="service_type">
                    <option value="boarding">Boarding (Overnight)</option>
                    <option value="house_sitting">House Sitting</option>
                    <option value="drop_in">Drop-in Visits</option>
                </select>
            </div>

            <div class="input-group">
                <label>SITTER LOCATION :</label>
                    <input type="text" id="location" readonly>
            </div>

            <div class="input-group">
                <label>PHONE NUMBER :</label>
                    <input type="text" id="phone" required>
            </div>

            <div class="input-group">
                <label>NUMBER OF CATS :</label>
                    <input type="number" id="cat_count" min="1" value="1" required>
            </div>

            <div class="input-group">
               <label>START DATE :</label>
               <input type="date" id="start_date" required>
            </div>

            <div class="input-group">
               <label>END DATE :</label>
               <input type="date" id="end_date" required>
            </div>

            <div class="input-group">
               <label>HOURS (Per day) :</label>
               <input type="number" id="hours_per_day" min="1" value="1" required>
             </div>

            <div class="input-group full-width">
               <label>FULL ADDRESS (Cat Location) :</label>
               <textarea id="full_address" rows="3" placeholder="Enter complete address for sitter reference" required></textarea>
            </div>

            <div class="payment-summary">
                <div class="summary-item">
                    <span>Service Charge</span>
                    <span>RM 25.00</span>
                </div>
                <div class="summary-item total">
                    <span>ESTIMATED TOTAL</span>
                    <span>RM 25.00</span>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-bayar">PAY NOW</button>
                <a href="detailcatsitter.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>

<script type="module">
import { auth, db } from "../js/firebase.js";
import { doc, getDoc, collection, addDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";

const urlParams = new URLSearchParams(window.location.search);
const sitterId = urlParams.get('id');
let currentHourlyRate = 5; // Default RM5/jam macam dalam Firestore
let sitterUnavailableDates = []; // Blocked dates from sitter

// Set minimum dates to today to prevent past bookings
const todayStr = new Date().toISOString().split("T")[0];
document.getElementById('start_date').min = todayStr;
document.getElementById('end_date').min = todayStr;

// --- CHECK AVAILABILITY ---
function isDateRangeAvailable(startStr, endStr) {
    if (!startStr || !endStr || sitterUnavailableDates.length === 0) return true;
    
    let currentDate = new Date(startStr);
    const endDate = new Date(endStr);
    
    while (currentDate <= endDate) {
        const dateStr = currentDate.toISOString().split("T")[0];
        if (sitterUnavailableDates.includes(dateStr)) {
            return false;
        }
        currentDate.setDate(currentDate.getDate() + 1);
    }
    return true;
}

// --- FUNGSI KIRA HARGA ---
function calculateTotal() {
    const start = new Date(document.getElementById('start_date').value);
    const end = new Date(document.getElementById('end_date').value);
    const hours = parseInt(document.getElementById('hours_per_day').value) || 0;
    const cats = parseInt(document.getElementById('cat_count').value) || 1;

    if (start && end && end >= start) {
        // Validate against blocked dates
        const startStr = document.getElementById('start_date').value;
        const endStr = document.getElementById('end_date').value;
        if (!isDateRangeAvailable(startStr, endStr)) {
            alert("The sitter is unavailable on one or more selected dates. Please choose a different range.");
            document.getElementById('start_date').value = "";
            document.getElementById('end_date').value = "";
            document.querySelector('.payment-summary .summary-item.total span:last-child').innerText = `RM 0.00`;
            return 0;
        }

        // Kira beza hari (minimum 1 hari)
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        const total = currentHourlyRate * hours * diffDays * cats;

        // Update UI
        document.querySelector('.payment-summary .summary-item span:last-child').innerText = `RM ${currentHourlyRate.toFixed(2)}/hr x ${hours}hrs x ${diffDays}days`;
        document.querySelector('.payment-summary .summary-item.total span:last-child').innerText = `RM ${total.toFixed(2)}`;
        return total;
    }
    return 0;
}

// Listeners untuk auto-update harga
['start_date', 'end_date', 'hours_per_day', 'cat_count'].forEach(id => {
    document.getElementById(id).addEventListener('input', calculateTotal);
});

auth.onAuthStateChanged(async (user) => {
    if (user) {
        try {
            // A. DATA PEMILIK - Guna fld_user_fullname ikut User Summary
            const userDoc = await getDoc(doc(db, "pengguna", user.uid));
            if (userDoc.exists()) {
                document.getElementById('customer_name').value = userDoc.data().fld_user_name || "";
                document.getElementById('phone').value = userDoc.data().fld_user_phone || "";
            }

            // B. DATA PENJAGA
            if (sitterId) {
                const sitterDoc = await getDoc(doc(db, "penjaga_kucing", sitterId));
                if (sitterDoc.exists()) {
                    const sData = sitterDoc.data();
                    document.getElementById('sitter_name').value = sData.fld_user_fullname || "";
                    document.getElementById('fld_penjaga_ID').value = sitterId;
                    // Ambil lokasi (Bandar & Negeri)
                    const bandar = sData.fld_user_bandar || "";
                    const negeri = sData.fld_user_negeri || "";
                    document.getElementById('location').value = bandar && negeri ? `${bandar}, ${negeri}` : bandar || negeri || "-";
                    currentHourlyRate = parseFloat(sData.fld_user_kadarBayaran) || 5;
                    sitterUnavailableDates = sData.fld_user_unavailableDates || [];

                    // PRE-FILL DATES FROM SESSION STORAGE
                    const savedStartDate = sessionStorage.getItem("bookingStartDate");
                    const savedEndDate = sessionStorage.getItem("bookingEndDate");
                    if (savedStartDate) document.getElementById('start_date').value = savedStartDate;
                    if (savedEndDate) document.getElementById('end_date').value = savedEndDate;

                    calculateTotal();
                }
            }
        } catch (err) { console.error(err); }
    } else { window.location.assign("signin.php"); }
});

// C. SIMPAN KE FIRESTORE
document.getElementById('bookingForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const finalTotal = calculateTotal();

    if (finalTotal <= 0) {
        alert("Please ensure the dates and booking details are correct.");
        return;
    }

    const bookingData = {
        fld_pemilik_ID: auth.currentUser.uid,
        fld_penjaga_ID: document.getElementById('fld_penjaga_ID').value,
        fld_tempahan_servis: document.getElementById('service_type').value,
        fld_tempahan_alamat: document.getElementById('full_address').value,
        fld_tempahan_tkhMula: document.getElementById('start_date').value,
        fld_tempahan_tkhTamat: document.getElementById('end_date').value,
        fld_tempahan_jamSehari: document.getElementById('hours_per_day').value,
        fld_tempahan_bilKucing: document.getElementById('cat_count').value,
        fld_tempahan_jumlah: finalTotal,
        fld_tempahan_status: "Unpaid", // Tukar status awal
        fld_tempahan_masaDibuat: serverTimestamp(),
        fld_unread_by: [document.getElementById('fld_penjaga_ID').value]
    };

    try {
        const docRef = await addDoc(collection(db, "tempahan"), bookingData);
       // Ambil e-mel user yang sedang aktif
        const userEmail = auth.currentUser.email; 
        // Masa redirect ke payment.php, hantar sekali e-mel
        window.location.href = `payment.php?booking_id=${docRef.id}&amount=${finalTotal}&email=${userEmail}`;
    } catch (error) { 
        alert("Error: " + error.message); 
    }
});
</script>