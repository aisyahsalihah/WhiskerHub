<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Global Admin Dashboard - WhiskerHub</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .admin-container { max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .revenue-card {
            background: linear-gradient(135deg, #1a2530, #0d141b);
            color: white;
            padding: 25px 35px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        
        .revenue-card h3 { margin: 0 0 10px 0; font-weight: 500; font-size: 16px; opacity: 0.9; }
        .revenue-card h1 { margin: 0; font-size: 36px; font-weight: 700; }

        .tabs { display: flex; gap: 15px; margin-bottom: 20px; }
        .tab-btn { padding: 12px 25px; border: none; background: #eee; border-radius: 10px; cursor: pointer; font-weight: 600; color: #555; transition: 0.2s; }
        .tab-btn.active { background: #ffb6c1; color: #fff; }

        .admin-table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-radius: 10px; overflow: hidden; display: none;}
        .admin-table.active { display: table; }
        .admin-table th, .admin-table td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        .admin-table th { background: #000; color: white; font-size: 14px; text-transform: uppercase; }
        
        .btn-danger { padding: 8px 12px; border: none; background: #e74c3c; color: white; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold;}
        .btn-danger:hover { background: #c0392b; }

        .avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; vertical-align: middle; margin-right: 10px; }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">WhiskerHub Admin</div>
    <div class="nav-links">
        <a href="#" class="active">Admin Dashboard</a>
        <a href="#" id="logoutBtn">Logout</a>
    </div>
</div>

<div class="admin-container">
    <div class="dashboard-header">
        <h1 style="font-family: 'Playfair Display', serif;">Global Admin Control Panel</h1>
        
        <div class="revenue-card">
            <h3>Total Platform Revenue</h3>
            <h1 id="totalRevenue">RM 0.00</h1>
        </div>
    </div>

    <div class="tabs">
        <button class="tab-btn active" onclick="switchTab('users', this)">Manage Users</button>
        <button class="tab-btn" onclick="switchTab('sitters', this)">Manage Sitters</button>
        <button class="tab-btn" onclick="switchTab('reports', this)" style="background-color: #ffebee; color: #d32f2f; border: 1px solid #ffcdd2;">Reports 🚨</button>
    </div>

    <table class="admin-table active" id="table-users">
        <thead>
            <tr>
                <th>User Details</th>
                <th>Phone</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="usersList">
            <tr><td colspan="3" style="text-align: center;">Loading users...</td></tr>
        </tbody>
    </table>

    <table class="admin-table" id="table-sitters">
        <thead>
            <tr>
                <th>Sitter Details</th>
                <th>Location</th>
                <th>Rate (RM)</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="sittersList">
            <tr><td colspan="4" style="text-align: center;">Loading sitters...</td></tr>
        </tbody>
    </table>

    <table class="admin-table" id="table-reports">
        <thead>
            <tr>
                <th>Reporter ID</th>
                <th>Reported User ID</th>
                <th>Issue Details</th>
                <th>Proof</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody id="reportsList">
            <tr><td colspan="5" style="text-align: center;">Loading reports...</td></tr>
        </tbody>
    </table>
</div>

<script type="module">
import { auth, db } from "../js/firebase.js";
import { collection, getDocs, doc, deleteDoc, updateDoc, query, where } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { signOut } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";

auth.onAuthStateChanged((user) => {
    if (!user || user.email !== 'admin@whiskerhub.com') {
        alert("Access Denied. Admins only.");
        window.location.href = "login.php";
    } else {
        loadDashboardData();
    }
});

document.getElementById('logoutBtn').addEventListener('click', async (e) => {
    e.preventDefault();
    await signOut(auth);
    window.location.href = "login.php";
});

window.switchTab = function(tabId, btn) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.admin-table').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(`table-${tabId}`).classList.add('active');
};

async function loadDashboardData() {
    try {
        // 1. Calculate Revenue (Bookings + Shop)
        let totalRev = 0;
        
        // Bookings (Tempahan)
        const bookingsQ = query(collection(db, "tempahan"), where("fld_tempahan_status", "in", ["Paid", "Completed"]));
        const bookingsSnap = await getDocs(bookingsQ);
        bookingsSnap.forEach(doc => { totalRev += parseFloat(doc.data().fld_tempahan_jumlah || 0); });

        // Shop Orders (Pesanan)
        const shopSnap = await getDocs(collection(db, "pesanan"));
        shopSnap.forEach(doc => { 
            // Add if it's paid or processing/shipped
            if(doc.data().fld_status !== "Cancelled") {
                totalRev += parseFloat(doc.data().fld_total_amount || 0); 
            }
        });

        document.getElementById("totalRevenue").innerText = `RM ${totalRev.toFixed(2)}`;

        // 2. Load Users
        const usersSnap = await getDocs(collection(db, "pengguna"));
        const usersList = document.getElementById("usersList");
        usersList.innerHTML = "";
        
        if(usersSnap.empty) {
            usersList.innerHTML = "<tr><td colspan='3' style='text-align:center;'>No users found.</td></tr>";
        } else {
            usersSnap.forEach(docSnap => {
                const data = docSnap.data();
                usersList.innerHTML += `
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center;">
                                <img src="${data.fld_user_avatar || 'https://via.placeholder.com/40'}" class="avatar">
                                <div>
                                    <strong>${data.fld_user_name || 'Unknown'}</strong><br>
                                    <small style="color:#888">${docSnap.id}</small>
                                </div>
                            </div>
                        </td>
                        <td>${data.fld_user_phone || '-'}</td>
                        <td><button class="btn-danger" onclick="deleteRecord('pengguna', '${docSnap.id}')">Ban / Delete</button></td>
                    </tr>
                `;
            });
        }

        // 3. Load Sitters
        const sittersSnap = await getDocs(collection(db, "penjaga_kucing"));
        const sittersList = document.getElementById("sittersList");
        sittersList.innerHTML = "";
        
        if(sittersSnap.empty) {
            sittersList.innerHTML = "<tr><td colspan='4' style='text-align:center;'>No sitters found.</td></tr>";
        } else {
            sittersSnap.forEach(docSnap => {
                const data = docSnap.data();
                sittersList.innerHTML += `
                    <tr>
                        <td>
                            <div style="display:flex; align-items:center;">
                                <img src="${data.fld_user_avatar || data.fld_user_profilePic || 'https://via.placeholder.com/40'}" class="avatar">
                                <div>
                                    <strong>${data.fld_user_fullname || 'Unknown'}</strong><br>
                                    <small style="color:#888">${docSnap.id}</small>
                                </div>
                            </div>
                        </td>
                        <td>${data.fld_user_bandar || '-'}, ${data.fld_user_negeri || '-'}</td>
                        <td>RM ${data.fld_user_kadarBayaran || '0.00'} / hr</td>
                        <td><button class="btn-danger" onclick="deleteRecord('penjaga_kucing', '${docSnap.id}')">Delete Sitter</button></td>
                    </tr>
                `;
            });
        }

        // 4. Load Reports
        const reportsQ = query(collection(db, "reports"), where("status", "==", "Pending"));
        const reportsSnap = await getDocs(reportsQ);
        const reportsList = document.getElementById("reportsList");
        reportsList.innerHTML = "";
        
        if(reportsSnap.empty) {
            reportsList.innerHTML = "<tr><td colspan='5' style='text-align:center;'>No pending reports. Yay! 🎉</td></tr>";
        } else {
            reportsSnap.forEach(docSnap => {
                const data = docSnap.data();
                reportsList.innerHTML += `
                    <tr>
                        <td><small style="color:#888">${data.reporterId}</small></td>
                        <td><small style="color:#d32f2f; font-weight:bold;">${data.reportedUserId}</small></td>
                        <td>
                            <strong>${data.type}</strong><br>
                            <span style="font-size: 13px; color: #555;">${data.description}</span>
                        </td>
                        <td>
                            ${data.proofImage ? `<a href="${data.proofImage}" target="_blank" style="color: #3498db; text-decoration: none; font-size: 13px; font-weight: bold;">📷 View Proof</a>` : 'No Proof'}
                        </td>
                        <td>
                            <button class="btn-danger" style="margin-bottom: 5px; width: 100%;" onclick="banReportedUser('${docSnap.id}', '${data.reportedUserId}')">Ban User</button><br>
                            <button style="padding: 8px 12px; border: none; background: #95a5a6; color: white; border-radius: 6px; cursor: pointer; font-size: 12px; font-weight: bold; width: 100%;" onclick="dismissReport('${docSnap.id}')">Dismiss</button>
                        </td>
                    </tr>
                `;
            });
        }

    } catch (error) {
        console.error("Error loading dashboard:", error);
    }
}

window.dismissReport = async function(reportId) {
    if(!confirm("Dismiss this report and mark it as resolved?")) return;
    try {
        await updateDoc(doc(db, "reports", reportId), { status: "Resolved" });
        alert("Report dismissed.");
        loadDashboardData();
    } catch(err) {
        alert("Error: " + err.message);
    }
}

window.banReportedUser = async function(reportId, userId) {
    if(!confirm("Are you SURE you want to BAN this user? This will delete their account from the platform.")) return;
    try {
        // We don't know if they are a pengguna or penjaga_kucing, so we try both!
        try { await deleteDoc(doc(db, "pengguna", userId)); } catch(e) {}
        try { await deleteDoc(doc(db, "penjaga_kucing", userId)); } catch(e) {}
        
        // Mark report as resolved
        await updateDoc(doc(db, "reports", reportId), { status: "Resolved" });
        
        alert("User Banned and Report Resolved.");
        loadDashboardData();
    } catch(err) {
        alert("Error: " + err.message);
    }
}

window.deleteRecord = async function(collectionName, id) {
    if(!confirm("Are you SURE you want to delete this account? This action cannot be undone.")) return;
    try {
        await deleteDoc(doc(db, collectionName, id));
        alert("Account successfully deleted.");
        loadDashboardData();
    } catch(err) {
        alert("Error deleting account: " + err.message);
    }
}

</script>
</body>
</html>
