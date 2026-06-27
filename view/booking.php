<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
               <label>START TIME :</label>
               <input type="time" id="start_time" required>
            </div>

            <div class="input-group">
               <label>END DATE :</label>
               <input type="date" id="end_date" required>
            </div>



            <div class="input-group" id="hours_input_group">
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
import { doc, getDoc, collection, addDoc, serverTimestamp, query, where, getDocs } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";

const urlParams = new URLSearchParams(window.location.search);
const sitterId = urlParams.get('id');
let currentHourlyRate = 5; 
let rates = { boarding: 25, daycare: 5, grooming: 15 };
let sitterUnavailableDates = []; // Blocked dates from sitter
let activeBookings = []; // Existing active bookings for this sitter

// Set minimum dates to today to prevent past bookings
const todayStr = new Date().toISOString().split("T")[0];
document.getElementById('start_date').min = todayStr;
document.getElementById('end_date').min = todayStr;

// --- CHECK DATE AVAILABILITY ---
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

// --- CHECK OVERLAP HELPER ---
function isOverlapping(dateFrom1, timeFrom1, dateTo1, timeTo1, dateFrom2, timeFrom2, dateTo2, timeTo2) {
    if (!dateFrom1 || !dateTo1 || !dateFrom2 || !dateTo2) return false;
    const start1 = new Date(`${dateFrom1}T${timeFrom1 || "00:00"}`);
    const end1 = new Date(`${dateTo1}T${timeTo1 || "23:59"}`);
    const start2 = new Date(`${dateFrom2}T${timeFrom2 || "00:00"}`);
    const end2 = new Date(`${dateTo2}T${timeTo2 || "23:59"}`);
    if (isNaN(start1) || isNaN(end1) || isNaN(start2) || isNaN(end2)) return false;
    return start1 <= end2 && start2 <= end1;
}

// --- TOGGLE HOURS GROUP DISPLAY ---
function handleServiceChange() {
    const service = document.getElementById('service_type').value;
    const hoursGroup = document.getElementById('hours_input_group');
    if (service === 'boarding' || service === 'grooming') {
        hoursGroup.style.display = 'none';
    } else {
        hoursGroup.style.display = 'block';
    }
    calculateTotal();
}
document.getElementById('service_type').addEventListener('change', handleServiceChange);

// --- FUNGSI KIRA HARGA ---
function calculateTotal() {
    const start = new Date(document.getElementById('start_date').value);
    const end = new Date(document.getElementById('end_date').value);
    const service = document.getElementById('service_type').value;
    const isFlatRate = (service === 'boarding' || service === 'grooming');
    const hours = isFlatRate ? 24 : (parseInt(document.getElementById('hours_per_day').value) || 0);
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

        // Helper to add hours
        const addHoursToTime = (timeStr, hAdd) => {
            if (!timeStr) return "23:59";
            const [h, m] = timeStr.split(":").map(Number);
            let newH = h + hAdd;
            if (newH >= 24) newH = 23;
            return `${String(newH).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
        };

        // Validate against other active bookings
        const startTimeStr = isFlatRate ? "00:00" : document.getElementById('start_time').value;
        if (isFlatRate || startTimeStr) {
            const endTimeStr = isFlatRate ? "23:59" : addHoursToTime(startTimeStr, hours);
            const hasOverlap = activeBookings.some(b => {
                return isOverlapping(
                    startStr, startTimeStr, endStr, endTimeStr,
                    b.fld_tempahan_tkhMula, b.fld_tempahan_masaMula,
                    b.fld_tempahan_tkhTamat, b.fld_tempahan_masaTamat
                );
            });
            if (hasOverlap) {
                alert("The sitter already has another booking during this date and time slot.");
                if (!isFlatRate) document.getElementById('start_time').value = "";
                document.querySelector('.payment-summary .summary-item.total span:last-child').innerText = `RM 0.00`;
                return 0;
            }
        }

        // Kira beza hari (minimum 1 hari)
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        
        let total = 0;
        let activeRate = rates.daycare;
        if (service === 'boarding') {
            activeRate = rates.boarding;
        } else if (service === 'grooming') {
            activeRate = rates.grooming;
        }

        if (service === 'boarding') {
            const firstCatRate = activeRate;
            const additionalCatRate = activeRate * 0.5;
            total = (firstCatRate + additionalCatRate * (cats - 1)) * diffDays;

            let labelText = `RM ${activeRate.toFixed(2)}/day (1st cat)`;
            if (cats > 1) {
                labelText += ` + RM ${additionalCatRate.toFixed(2)}/day (additional ${cats - 1} cats)`;
            }
            labelText += ` x ${diffDays}days`;
            document.querySelector('.payment-summary .summary-item span:last-child').innerText = labelText;
        } else if (service === 'grooming') {
            total = activeRate * diffDays * cats;
            document.querySelector('.payment-summary .summary-item span:last-child').innerText = `RM ${activeRate.toFixed(2)}/session x ${diffDays}days x ${cats}cats`;
        } else {
            total = activeRate * hours * diffDays * cats;
            document.querySelector('.payment-summary .summary-item span:last-child').innerText = `RM ${activeRate.toFixed(2)}/hr x ${hours}hrs x ${diffDays}days x ${cats}cats`;
        }

        document.querySelector('.payment-summary .summary-item.total span:last-child').innerText = `RM ${total.toFixed(2)}`;
        return total;
    }
    return 0;
}

// Listeners untuk auto-update harga
['start_date', 'end_date', 'start_time', 'hours_per_day', 'cat_count'].forEach(id => {
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
                    rates.boarding = parseFloat(sData.fld_rate_boarding) || currentHourlyRate || 25;
                    rates.daycare = parseFloat(sData.fld_rate_daycare) || currentHourlyRate || 5;
                    rates.grooming = parseFloat(sData.fld_rate_grooming) || currentHourlyRate || 15;
                    sitterUnavailableDates = sData.fld_user_unavailableDates || [];

                    // Load active bookings for this sitter
                    const q = query(collection(db, "tempahan"), where("fld_penjaga_ID", "==", sitterId));
                    const bookingSnap = await getDocs(q);
                    activeBookings = [];
                    bookingSnap.forEach(bDoc => {
                        const bData = bDoc.data();
                        if (bData.fld_tempahan_status !== "Completed" && bData.fld_tempahan_status !== "Cancelled") {
                            activeBookings.push(bData);
                        }
                    });

                    // PRE-FILL DATES FROM SESSION STORAGE
                    const savedStartDate = sessionStorage.getItem("bookingStartDate");
                    const savedEndDate = sessionStorage.getItem("bookingEndDate");
                    const savedStartTime = sessionStorage.getItem("bookingStartTime");
                    if (savedStartDate) document.getElementById('start_date').value = savedStartDate;
                    if (savedEndDate) document.getElementById('end_date').value = savedEndDate;
                    if (savedStartTime) document.getElementById('start_time').value = savedStartTime;

                    handleServiceChange();
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

    // Helper to calculate end time
    const addHoursToTime = (timeStr, hAdd) => {
        if (!timeStr) return "23:59";
        const [h, m] = timeStr.split(":").map(Number);
        let newH = h + hAdd;
        if (newH >= 24) newH = 23;
        return `${String(newH).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
    };

    const startTimeVal = document.getElementById('start_time').value;
    const hoursVal = parseInt(document.getElementById('hours_per_day').value) || 0;
    const computedEndTimeVal = addHoursToTime(startTimeVal, hoursVal);

    const bookingData = {
        fld_pemilik_ID: auth.currentUser.uid,
        fld_penjaga_ID: document.getElementById('fld_penjaga_ID').value,
        fld_tempahan_servis: document.getElementById('service_type').value,
        fld_tempahan_alamat: document.getElementById('full_address').value,
        fld_tempahan_tkhMula: document.getElementById('start_date').value,
        fld_tempahan_masaMula: startTimeVal,
        fld_tempahan_tkhTamat: document.getElementById('end_date').value,
        fld_tempahan_masaTamat: computedEndTimeVal,
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