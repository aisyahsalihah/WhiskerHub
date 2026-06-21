

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Sitter - WhiskerHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/findstyle.css">
</head>

<body >

<div class="navbar">
    <div class="logo" onclick="location.href='mainmenu.php'" style="cursor:pointer">WhiskerHub</div>
    <div class="nav-links">
        <a href="mainmenu.php">Main Menu</a>
        <a href="findcatsitter.php">Search Sitters</a>
        <a href="becomesitter.php" id="becomeSitterLink" style="display:none">Become a Sitter</a>
        <a href="shopping.php">Shopping</a>
    </div>
</div>

<div class="main-container">

    <!-- 🔍 SEARCH -->
    <div class="search-sidebar">

        <h2>Search Sitter</h2>

        <form onsubmit="event.preventDefault(); searchSitters();">

            <div class="input-group">
                <label>Service Type</label>
                <select id="searchService">
                    <option value="">-- All --</option>
                    <option value="boarding">Boarding</option>
                    <option value="daycare">Daycare</option>
                    <option value="grooming">Grooming</option>
                </select>
            </div>

            <!-- NEGERI -->
            <div class="input-group">
                <label>State</label>
                    <select id="searchState">
                        <option value="">Auto GPS</option>
                        <option value="selangor">Selangor</option>
                        <option value="kuala lumpur">Kuala Lumpur</option>
                        <option value="putrajaya">Putrajaya</option>
                        <option value="johor">Johor</option>
                        <option value="kedah">Kedah</option>
                        <option value="kelantan">Kelantan</option>
                        <option value="melaka">Melaka</option>
                        <option value="negeri sembilan">Negeri Sembilan</option>
                        <option value="pahang">Pahang</option>
                        <option value="perak">Perak</option>
                        <option value="perlis">Perlis</option>
                        <option value="pulau pinang">Pulau Pinang</option>
                        <option value="terengganu">Terengganu</option>
                        <option value="sabah">Sabah</option>
                        <option value="sarawak">Sarawak</option>
                </select>
            </div>

            <!-- BANDAR -->
            <div class="input-group">
                <label>City</label>
                    <select id="searchCity">
                    <option value="">All</option>
            </select>
            </div>

            <!-- SORT BY -->
            <div class="input-group">
                <label>Sort By</label>
                <select id="searchSort">
                    <option value="">Default (Distance)</option>
                    <option value="price_low">Price: Low to High</option>
                    <option value="price_high">Price: High to Low</option>
                    <option value="rating_high">Rating: Highest (5 Stars)</option>
                </select>
            </div>

            <!-- TARIKH -->
            <div class="input-group">
                <label>Date From</label>
                <input type="date" id="searchDateFrom">
            </div>
            
            <div class="input-group">
                <label>Date To</label>
                <input type="date" id="searchDateTo">
            </div>

            <button type="submit" class="btn-search">
                SEARCH
            </button>

        </form>

    </div>

    <!-- 📦 RESULTS -->
    <div class="results-content" id="results">
        <p>Loading sitters...</p>
    </div>

</div>

<script type="module">

import { auth, db, getAllSitters } from "../js/firebase.js";
import { malaysiaCities } from "../js/locationData.js";
import { collection, getDocs } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { signOut } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js"; 

let allSitters = [];
let allBookings = [];
let userLat = null;
let userLng = null;
let currentUserUID = null; // track logged-in user to hide self from list

function isOverlapping(start1, end1, start2, end2) {
    if (!start1 || !end1 || !start2 || !end2) return false;
    const s1 = new Date(start1);
    const e1 = new Date(end1);
    const s2 = new Date(start2);
    const e2 = new Date(end2);
    if (isNaN(s1) || isNaN(e1) || isNaN(s2) || isNaN(e2)) return false;
    return s1 <= e2 && s2 <= e1;
}

auth.onAuthStateChanged(async (user) => {
  if (user) {
    console.log("User logged in:", user.email);
    currentUserUID = user.uid; // ✅ save UID so we can hide self from list

    // Show/hide "Become a Sitter" based on sitter status
    try {
        const { getDoc, doc } = await import("https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js");
        const sitterSnap = await getDoc(doc(db, "penjaga_kucing", user.uid));
        const link = document.getElementById("becomeSitterLink");
        if (link) link.style.display = sitterSnap.exists() ? "none" : "";
    } catch(e) { console.warn("sitter check:", e); }
  } else {
    alert("Please login first to access this page.");
    window.location.assign("signup.php"); 
  }
});


document.getElementById("searchState").addEventListener("change", function () {

    const state = this.value;
    const citySelect = document.getElementById("searchCity");

    citySelect.innerHTML = `<option value="">All</option>`;

    if (malaysiaCities[state]) {

        malaysiaCities[state].forEach(city => {
            citySelect.innerHTML += `
                <option value="${city.toLowerCase()}">
                    ${city}
                </option>
            `;
        });
    }
});


//
// 📍 GET GPS
//
navigator.geolocation.getCurrentPosition(
    (pos) => {
        userLat = pos.coords.latitude;
        userLng = pos.coords.longitude;
        loadSitters();
    },
    () => {
        console.log("GPS denied");
        loadSitters();
    }
);

//
// 📏 DISTANCE
//
function getDistance(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;

    const a =
        Math.sin(dLat/2) ** 2 +
        Math.cos(lat1 * Math.PI/180) *
        Math.cos(lat2 * Math.PI/180) *
        Math.sin(dLon/2) ** 2;

    return R * (2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)));
}

//
// 🔥 LOAD DATA
//
async function loadSitters() {
    try {
        allSitters = await getAllSitters();
        
        // Fetch bookings to check availability
        const snap = await getDocs(collection(db, "tempahan"));
        allBookings = [];
        snap.forEach(doc => {
            const data = doc.data();
            if (data.fld_tempahan_status !== "Completed" && data.fld_tempahan_status !== "Cancelled") {
                allBookings.push(data);
            }
        });

        // Fetch all reviews to calculate average rating
        const reviewSnap = await getDocs(collection(db, "review"));
        const ratingsData = {};
        reviewSnap.forEach(doc => {
            const r = doc.data();
            const sId = r.sitterID || r.sitterId; // Handle both cases just in case
            if (sId && r.fld_user_rating) {
                if (!ratingsData[sId]) ratingsData[sId] = { total: 0, count: 0 };
                ratingsData[sId].total += Number(r.fld_user_rating);
                ratingsData[sId].count++;
            }
        });

        allSitters.forEach(s => {
            s.avgRating = ratingsData[s.id] ? (ratingsData[s.id].total / ratingsData[s.id].count) : 0;
            s.reviewCount = ratingsData[s.id] ? ratingsData[s.id].count : 0;
        });

        // Hide the logged-in user from their own search results
        const visible = allSitters.filter(s => s.id !== currentUserUID);
        display(visible);
    } catch (err) {
        console.error(err);
        document.getElementById("results").innerHTML = "Error loading data";
    }
}

//
// 🔍 SEARCH
//
window.searchSitters = function () {

    const service = (document.getElementById("searchService").value || "").toLowerCase();
    const state = (document.getElementById("searchState").value || "").toLowerCase();
    const city = (document.getElementById("searchCity").value || "").toLowerCase();
    
    let dateFrom = document.getElementById("searchDateFrom").value;
    let dateTo = document.getElementById("searchDateTo").value;
    
    if (dateFrom && !dateTo) dateTo = dateFrom;
    if (!dateFrom && dateTo) dateFrom = dateTo;

    // Save dates to session storage for booking.php
    if (dateFrom) sessionStorage.setItem("bookingStartDate", dateFrom);
    if (dateTo) sessionStorage.setItem("bookingEndDate", dateTo);

    const MAX_DISTANCE = 20;

    // Exclude logged-in user from search results too
    let filtered = allSitters
        .filter(s => s.id !== currentUserUID)
        .map(s => {

            let distance = 999;

            const lat = s.fld_lat || s.fld_user_lat;
            const lng = s.fld_lng || s.fld_user_lng;

            if (userLat && userLng && lat && lng) {
                distance = getDistance(userLat, userLng, lat, lng);
            }

            return { ...s, distance };
        })
        .filter(s => {

            // 🛎️ SERVICE (SAFE CHECK)
            if (service !== "") {

                let services = s.fld_user_jenisPerkhidmatan;

                // kalau string → convert to array
                if (typeof services === "string") {
                    services = services.split(",");
                }

                services = (services || []).map(x => x.trim().toLowerCase());

                if (!services.includes(service)) {
                    return false;
                }
            }

            // 🟢 STATE
            if (state !== "") {
                if (!(s.fld_user_negeri || "").toLowerCase().includes(state)) {
                    return false;
                }
            }

            // 🔵 CITY
            if (city !== "") {
                if (!(s.fld_user_bandar || "").toLowerCase().includes(city)) {
                    return false;
                }
            }

            // 📅 DATE AVAILABILITY
            if (dateFrom && dateTo) {
                const hasOverlap = allBookings.some(b => {
                    if (b.fld_penjaga_ID === s.id) {
                        return isOverlapping(dateFrom, dateTo, b.fld_tempahan_tkhMula, b.fld_tempahan_tkhTamat);
                    }
                    return false;
                });
                
                if (hasOverlap) {
                    return false; // Not available
                }
            }

            // 📍 GPS ONLY IF NO FILTER
            if (state === "" && city === "" && !dateFrom && !dateTo) {
                return s.distance <= MAX_DISTANCE;
            }

            return true;
        });

    const sortBy = document.getElementById("searchSort").value;

    if (sortBy === "price_low") {
        filtered.sort((a, b) => Number(a.fld_user_kadarBayaran || 0) - Number(b.fld_user_kadarBayaran || 0));
    } else if (sortBy === "price_high") {
        filtered.sort((a, b) => Number(b.fld_user_kadarBayaran || 0) - Number(a.fld_user_kadarBayaran || 0));
    } else if (sortBy === "rating_high") {
        filtered.sort((a, b) => b.avgRating - a.avgRating);
    } else {
        filtered.sort((a, b) => a.distance - b.distance);
    }

    console.log("FILTER RESULT:", filtered);

    display(filtered);
};

//
// 🎨 DISPLAY
//
function display(sitters) {

    let html = "";

    if (sitters.length === 0) {
        html = "<p>No sitter found 😢</p>";
    }

    sitters.forEach(s => {

        let distanceText = "";

        if (userLat && userLng && s.fld_lat && s.fld_lng) {
            let d = getDistance(
                userLat,
                userLng,
                s.fld_lat,
                s.fld_lng
            );

            distanceText = `<p class="sitter-distance">
                <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-top:-2px;"><path d="M12 22s-8-4.5-8-11.8A8 8 0 0 1 12 2a8 8 0 0 1 8 8.2c0 7.3-8 11.8-8 11.8z"/><circle cx="12" cy="10" r="3"/></svg>
                ${d.toFixed(1)} km away</p>`;
        }

        const FALLBACK_AVATAR = `data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' fill='%23f5f5f5'/%3E%3Ccircle cx='50' cy='38' r='20' fill='%23d9d9d9'/%3E%3Cellipse cx='50' cy='85' rx='35' ry='25' fill='%23d9d9d9'/%3E%3C/svg%3E`;
        const avatarUrl = s.fld_user_avatar || s.fld_user_profilePic || FALLBACK_AVATAR;
        
        let servicesList = "";
        if (s.fld_user_jenisPerkhidmatan) {
            let svcs = Array.isArray(s.fld_user_jenisPerkhidmatan) ? s.fld_user_jenisPerkhidmatan : s.fld_user_jenisPerkhidmatan.split(',');
            servicesList = svcs.map(svc => `<span class="service-tag">${svc.trim()}</span>`).join('');
        }

        html += `
        <div class="sitter-card">
            <div class="sitter-img">
                <img src="${avatarUrl}" alt="${s.fld_user_fullname || 'Sitter'}" onerror="this.src='${FALLBACK_AVATAR}'">
            </div>
            <div class="sitter-info">
                <h3>${s.fld_user_fullname || "No Name"}</h3>
                <p class="sitter-location">
                    <svg viewBox="0 0 24 24" width="14" height="14" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-top:-2px;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    ${s.fld_user_bandar || "-"}, ${s.fld_user_negeri || "-"}
                </p>
                ${distanceText}
                <div class="sitter-services">${servicesList}</div>
                <div style="margin-top:8px; font-size:13px; color:#ff9800; font-weight:bold;">
                    ⭐ ${s.avgRating > 0 ? s.avgRating.toFixed(1) : 'No Ratings'} <span style="color:#888; font-size:12px; font-weight:normal;">(${s.reviewCount || 0} reviews)</span>
                </div>
            </div>

            <div class="sitter-action">
                <div class="sitter-price">RM ${s.fld_user_kadarBayaran || 0}<span>/hr</span></div>
                <a href="detailcatsitter.php?id=${s.id}" class="btn-view">View Profile</a>
            </div>
        </div>
        `;
    });

    document.getElementById("results").innerHTML = html;
}

</script>

</body>
</html>