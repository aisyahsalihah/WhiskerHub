<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - WhiskerHub</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <style>
        /* Override beberapa style supaya tak ikut layout chat */
        body { overflow-y: auto; background-color: #fdfafc; }
        
        .profile-wrapper {
            max-width: 900px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-card {
            background: white;
            border-radius: 30px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.05);
            overflow: hidden;
            padding-bottom: 40px;
        }

        /* 1. Top Section: Banner & Avatar */
        .profile-banner {
            height: 150px;
            background: linear-gradient(135deg, #ffb6c1 0%, #ff9aa2 100%);
            position: relative;
        }

        .avatar-container {
            position: absolute;
            bottom: -60px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
        }

        .avatar-preview {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            background-color: white;
            background-size: cover;
            background-position: center;
            border: 6px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: 0.3s;
        }

        .avatar-preview:hover { opacity: 0.8; }

        .profile-info-top {
            margin-top: 70px;
            text-align: center;
            margin-bottom: 40px;
        }

        .profile-info-top h2 { font-family: 'Playfair Display', serif; font-size: 28px; }
        .role-badge { 
            background: #fff5f6; 
            color: #ffb6c1; 
            padding: 5px 15px; 
            border-radius: 20px; 
            font-size: 13px; 
            font-weight: 600; 
            text-transform: uppercase;
        }

        /* 2 & 3 & 4. Form Sections */
        .form-container {
            padding: 0 60px;
        }

        .section-header {
            font-size: 18px;
            font-weight: 600;
            margin: 30px 0 15px;
            color: #444;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-header::after { content: ""; flex: 1; height: 1px; background: #eee; }

        .input-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .input-group { display: flex; flex-direction: column; margin-bottom: 15px; }
        .input-group label { font-size: 13px; color: #777; margin-bottom: 8px; margin-left: 5px; }
        
        .input-group input, .input-group textarea {
            padding: 12px 18px;
            border: 1px solid #f0f0f0;
            background: #fafafa;
            border-radius: 15px;
            outline: none;
            font-size: 14px;
            transition: 0.3s;
        }

        .input-group input:focus, .input-group textarea:focus {
            background: white;
            border-color: #ffb6c1;
            box-shadow: 0 0 0 4px rgba(255, 182, 193, 0.1);
        }

        /* 5. Buttons */
        .button-group {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 40px;
        }

        .btn {
            padding: 13px 35px;
            border-radius: 15px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.3s;
        }

        .btn-save { background: #333; color: white; }
        .btn-save:hover { background: #000; transform: translateY(-2px); }
        .btn-cancel { background: #eee; color: #666; text-decoration: none; display: inline-block; text-align: center; }

        /* 6. Gallery */
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-top: 5px;
        }

        .gallery-item {
            position: relative;
            aspect-ratio: 1/1;
            border-radius: 12px;
            overflow: hidden;
            background: #f0f0f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: transform 0.2s;
        }
        .gallery-item:hover { transform: scale(1.02); }

        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .gallery-item .delete-btn {
            position: absolute;
            top: 6px;
            right: 6px;
            width: 26px;
            height: 26px;
            background: rgba(0,0,0,0.55);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 14px;
            line-height: 26px;
            text-align: center;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.2s;
            padding: 0;
        }
        .gallery-item:hover .delete-btn { opacity: 1; }

        .gallery-add-btn {
            aspect-ratio: 1/1;
            border-radius: 12px;
            border: 2px dashed #ffb6c1;
            background: #fff5f6;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            color: #ffb6c1;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s;
            padding: 10px;
            text-align: center;
            min-height: 110px;
        }
        .gallery-add-btn:hover { background: #ffecee; border-color: #ff9aa2; color: #ff7a8a; }
        .gallery-add-btn svg { width: 36px; height: 36px; opacity: 0.8; }
        .gallery-add-btn:hover svg { opacity: 1; }
        .gallery-add-btn .add-label { font-size: 12px; font-weight: 700; letter-spacing: 0.5px; }
        .gallery-add-btn .add-sub { font-size: 10px; font-weight: 400; opacity: 0.7; }

        /* Upload button row */
        .gallery-upload-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }
        .btn-upload-gallery {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, #ffb6c1, #ff9aa2);
            color: white;
            border: none;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 4px 14px rgba(255,154,162,0.35);
            letter-spacing: 0.3px;
        }
        .btn-upload-gallery:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(255,154,162,0.5); }
        .btn-upload-gallery svg { width: 18px; height: 18px; }
        .gallery-count-badge {
            font-size: 12px;
            color: #aaa;
        }

        /* Blocked Dates Tracker */
        .blocked-dates-container { margin-top: 10px; }
        .blocked-dates-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        .blocked-date-tag {
            background: #fff0f2;
            color: #d81b60;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            border: 1px solid #ffccd5;
        }
        .blocked-date-tag button {
            background: none;
            border: none;
            color: #d81b60;
            font-size: 16px;
            cursor: pointer;
            padding: 0;
            display: flex;
            align-items: center;
        }
        .add-date-wrapper {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .add-date-wrapper input[type="date"] {
            flex: 1;
        }
        .add-date-btn {
            background: #ffb6c1;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 15px;
            cursor: pointer;
            font-weight: 600;
            transition: 0.3s;
        }
        .add-date-btn:hover { background: #ff9aa2; }

        @media (max-width: 768px) {
            .profile-wrapper {
                padding: 0 10px;
                margin: 20px auto;
            }
            .form-container {
                padding: 0 20px;
            }
            .input-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            .gallery-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .button-group {
                flex-direction: column;
                gap: 10px;
            }
            .btn {
                width: 100%;
            }
        }

    </style>
</head>
<body>

<div class="navbar">
    <div class="logo" onclick="location.href='mainmenu.php'" style="cursor:pointer">WhiskerHub</div>
</div>

<div class="profile-wrapper">
    <div class="profile-card">
        <div class="profile-banner">
            <div class="avatar-container">
                <div class="avatar-preview" id="imagePreview" style="background-image: url('https://cdn-icons-png.flaticon.com/512/3135/3135715.png');" onclick="document.getElementById('imageInput').click()" title="Click to edit photo"></div>
                <input type="file" id="imageInput" style="display:none" accept="image/*">
                <p style="font-size: 11px; color: #ffb6c1; margin-top: 5px; font-weight: 600; cursor: pointer;" onclick="document.getElementById('imageInput').click()">CHANGE PHOTO</p>
            </div>
        </div>

        <div class="profile-info-top">
            <h2 id="userNameDisplay">Loading...</h2>
            <span class="role-badge" id="userRoleBadge">Pet Owner</span>
        </div>

        <form id="profileForm" class="form-container">
            <div class="section-header">Basic Information</div>
            <div class="input-grid">
                <div class="input-group">
                    <label>Display Name</label>
                    <input type="text" id="fullName" required>
                </div>
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" id="email" disabled>
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="text" id="phone">
                </div>
            </div>

            <div class="section-header">About Me / Experience</div>
            <div class="input-group">
                <textarea id="bio" rows="3" placeholder="Tell the community about your furry friends or your experience..."></textarea>
            </div>

            <div id="sitterFields" style="display: none;">
                <div class="section-header">Sitter Settings</div>
                <div class="input-grid">
                    <div class="input-group">
                        <label>City</label>
                        <input type="text" id="bandar">
                    </div>
                    <div class="input-group">
                        <label>State</label>
                        <input type="text" id="negeri">
                    </div>
                    <div class="input-group">
                        <label>Boarding Rate (RM / day)</label>
                        <input type="number" id="rate_boarding" step="0.50">
                    </div>
                    <div class="input-group">
                        <label>Daycare Rate (RM / hour)</label>
                        <input type="number" id="rate_daycare" step="0.50">
                    </div>
                    <div class="input-group">
                        <label>Grooming Rate (RM / session)</label>
                        <input type="number" id="rate_grooming" step="0.50">
                    </div>
                </div>

                <div class="input-group" style="margin-top: 15px;">
                    <label>Services Offered</label>
                    <div style="display: flex; gap: 20px; margin-top: 5px;">
                        <label><input type="checkbox" value="boarding" class="service-checkbox"> Boarding</label>
                        <label><input type="checkbox" value="daycare" class="service-checkbox"> Daycare</label>
                        <label><input type="checkbox" value="grooming" class="service-checkbox"> Grooming</label>
                    </div>
                </div>

                <!-- Gallery Upload Section -->
                <div class="section-header" style="margin-top: 30px;">📸 Gallery / Progress Photos</div>
                <p style="font-size: 13px; color: #aaa; margin-bottom: 14px;">Upload photos to prove your cat sitting experience. Visible on your public profile.</p>

                <input type="file" id="galleryInput" style="display:none" accept="image/*" multiple>

                <!-- Upload button row -->
                <div class="gallery-upload-row">
                    <button type="button" class="btn-upload-gallery" onclick="document.getElementById('galleryInput').click()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                        </svg>
                        Upload Progress Photos
                    </button>
                    <span class="gallery-count-badge" id="galleryCount"></span>
                </div>

                <div class="gallery-grid" id="galleryGrid">
                    <!-- gallery items rendered by JS -->
                    <div class="gallery-add-btn" id="galleryAddBtn" onclick="document.getElementById('galleryInput').click()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                            <circle cx="12" cy="13" r="4"/>
                            <line x1="12" y1="10" x2="12" y2="16"/>
                            <line x1="9" y1="13" x2="15" y2="13"/>
                        </svg>
                        <span class="add-label">Add Photo</span>
                        <span class="add-sub">Click to upload</span>
                    </div>
                </div>

                <!-- Availability Tracker Section -->
                <div class="section-header" style="margin-top: 40px;">🗓️ Availability / Blocked Dates</div>
                <p style="font-size: 13px; color: #aaa; margin-bottom: 14px;">Block out dates when you are sick, on holiday, or fully booked. Owners cannot book you on these dates.</p>
                
                <div class="blocked-dates-container">
                    <div class="add-date-wrapper">
                        <input type="date" id="blockDateInput">
                        <button type="button" class="add-date-btn" onclick="addBlockedDate()">Block Date</button>
                    </div>
                    <div class="blocked-dates-list" id="blockedDatesList">
                        <!-- Dates rendered here by JS -->
                    </div>
                </div>
            </div>

            <div class="section-header">My Cats 🐾</div>
            <p style="font-size: 13px; color: #aaa; margin-bottom: 14px;">Manage your cat profiles below. You can add multiple cats.</p>
            
            <div id="catsListContainer" style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 25px;">
                <p style="font-size: 13px; color: #aaa;">No cats added yet. Add your cats below!</p>
            </div>

            <div style="background: #fff5f6; border: 1px dashed #ffb6c1; padding: 20px; border-radius: 15px; margin-bottom: 30px;">
                <h4 style="margin-top: 0; margin-bottom: 15px; color: #ff7a8a; font-size: 14px;">Add a Cat</h4>
                <div class="input-grid">
                    <div class="input-group">
                        <label>Cat's Name</label>
                        <input type="text" id="newCatName" placeholder="e.g., Oyen">
                    </div>
                    <div class="input-group">
                        <label>Cat's Age (years)</label>
                        <input type="number" id="newCatAge" placeholder="e.g., 2" min="0">
                    </div>
                    <div class="input-group">
                        <label>Cat's Breed</label>
                        <input type="text" id="newCatBreed" placeholder="e.g., Persian">
                    </div>
                    <div class="input-group">
                        <label>Medical/Dietary Notes</label>
                        <input type="text" id="newCatNotes" placeholder="e.g., Sensitive stomach">
                    </div>
                    <div class="input-group" style="grid-column: span 2;">
                        <label>Cat's Photo (Optional)</label>
                        <input type="file" id="newCatPhoto" accept="image/*">
                    </div>
                </div>
                <button type="button" class="btn" style="background: #ffb6c1; color: white; margin-top: 15px; width: 100%;" onclick="addCatToList()">Add Cat to List</button>
            </div>

            <div class="section-header">Security</div>
            <div class="input-grid">
                <div class="input-group">
                    <label>New Password</label>
                    <input type="password" id="newPass" placeholder="Leave blank to keep current">
                </div>
                <div class="input-group">
                    <label>Confirm Password</label>
                    <input type="password" id="confirmPass">
                </div>
            </div>

            <div class="button-group">
                <a href="mainmenu.php" class="btn btn-cancel">Cancel</a>
                <button type="submit" class="btn btn-save">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script type="module">
    import { auth, db, storage } from "../js/firebase.js";
    import { doc, getDoc, updateDoc } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
    import { updatePassword } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";
    import { ref, uploadBytes, getDownloadURL } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-storage.js";

    let currentRole = "pengguna";
    let myCats = []; // Array of { name, age, breed, notes, photoUrl, photoFile }

    // galleryImages: array of { type: 'url'|'file', value: string|File, preview: string }
    let galleryImages = [];
    let unavailableDates = []; // Track blocked dates

    // Set min date for date picker
    document.getElementById("blockDateInput").min = new Date().toISOString().split("T")[0];

    window.addBlockedDate = function() {
        const input = document.getElementById("blockDateInput");
        const dateVal = input.value;
        if (!dateVal) { alert("Please select a date first."); return; }
        if (unavailableDates.includes(dateVal)) { alert("Date already blocked."); return; }
        
        unavailableDates.push(dateVal);
        unavailableDates.sort(); // Keep chronologically sorted
        input.value = "";
        renderBlockedDates();
    };

    window.removeBlockedDate = function(dateStr) {
        unavailableDates = unavailableDates.filter(d => d !== dateStr);
        renderBlockedDates();
    };

    window.addCatToList = function() {
        const name = document.getElementById("newCatName").value.trim();
        const age = document.getElementById("newCatAge").value.trim();
        const breed = document.getElementById("newCatBreed").value.trim();
        const notes = document.getElementById("newCatNotes").value.trim();
        const photoFile = document.getElementById("newCatPhoto").files[0];

        if (!name) {
            alert("Cat's Name is required.");
            return;
        }

        let photoUrl = "";
        if (photoFile) {
            photoUrl = URL.createObjectURL(photoFile);
        }

        myCats.push({
            name: name,
            age: age,
            breed: breed,
            notes: notes,
            photoUrl: photoUrl,
            photoFile: photoFile
        });

        // Clear inputs
        document.getElementById("newCatName").value = "";
        document.getElementById("newCatAge").value = "";
        document.getElementById("newCatBreed").value = "";
        document.getElementById("newCatNotes").value = "";
        document.getElementById("newCatPhoto").value = "";

        renderCatsList();
    };

    window.removeCatFromList = function(index) {
        myCats.splice(index, 1);
        renderCatsList();
    };

    function renderCatsList() {
        const container = document.getElementById("catsListContainer");
        if (!container) return;

        container.innerHTML = "";
        
        if (myCats.length === 0) {
            container.innerHTML = `<p style="font-size: 13px; color: #aaa;">No cats added yet. Add your cats below!</p>`;
            return;
        }

        myCats.forEach((cat, index) => {
            const catCard = document.createElement("div");
            catCard.style = "display: flex; justify-content: space-between; align-items: center; background: white; border: 1px solid #f0f0f0; border-radius: 12px; padding: 12px 18px; box-shadow: 0 2px 5px rgba(0,0,0,0.02);";
            
            let photoHTML = cat.photoUrl ? `<img src="${cat.photoUrl}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 15px;">` : `<div style="width: 50px; height: 50px; border-radius: 50%; background: #eee; margin-right: 15px; display: flex; align-items: center; justify-content: center; font-size: 20px;">🐱</div>`;
            
            catCard.innerHTML = `
                <div style="display: flex; align-items: center;">
                    ${photoHTML}
                    <div style="font-size: 13px; color: #444;">
                        <strong style="font-size: 15px; color: #ff7a8a;">${cat.name}</strong> (${cat.breed || 'Unknown Breed'}, ${cat.age || '0'} y/o)<br>
                        <span style="color: #888;">Notes: ${cat.notes || 'None'}</span>
                    </div>
                </div>
                <button type="button" class="btn" style="background: #e74c3c; color: white; padding: 6px 12px; font-size: 12px; border-radius: 8px; margin-left: 10px; width: auto;" onclick="removeCatFromList(${index})">Remove</button>
            `;
            container.appendChild(catCard);
        });
    }

    function renderBlockedDates() {
        const list = document.getElementById("blockedDatesList");
        list.innerHTML = "";
        if (unavailableDates.length === 0) {
            list.innerHTML = `<span style="font-size:13px; color:#aaa;">No blocked dates. You are available every day!</span>`;
            return;
        }
        unavailableDates.forEach(dateStr => {
            const span = document.createElement("span");
            span.className = "blocked-date-tag";
            span.innerHTML = `
                ${dateStr}
                <button type="button" onclick="removeBlockedDate('${dateStr}')">&times;</button>
            `;
            list.appendChild(span);
        });
    }

    // ── Render gallery grid ──────────────────────────────────────────────────────
    function renderGallery() {
        const grid = document.getElementById("galleryGrid");
        const countBadge = document.getElementById("galleryCount");

        // Rebuild grid with add-button last
        grid.innerHTML = `
            <div class="gallery-add-btn" id="galleryAddBtn" onclick="document.getElementById('galleryInput').click()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                    <circle cx="12" cy="13" r="4"/>
                    <line x1="12" y1="10" x2="12" y2="16"/>
                    <line x1="9" y1="13" x2="15" y2="13"/>
                </svg>
                <span class="add-label">Add Photo</span>
                <span class="add-sub">Click to upload</span>
            </div>`;

        galleryImages.forEach((item, idx) => {
            const div = document.createElement("div");
            div.className = "gallery-item";
            div.innerHTML = `
                <img src="${item.preview}" alt="Gallery photo ${idx + 1}">
                <button class="delete-btn" title="Remove photo">×</button>`;
            div.querySelector(".delete-btn").onclick = () => {
                galleryImages.splice(idx, 1);
                renderGallery();
            };
            grid.insertBefore(div, grid.firstChild);
        });

        // Update count badge
        if (countBadge) {
            countBadge.textContent = galleryImages.length > 0
                ? `${galleryImages.length} photo${galleryImages.length > 1 ? 's' : ''} added`
                : "No photos yet";
        }
    }

    // ── Gallery file input handler ───────────────────────────────────────────────
    document.getElementById("galleryInput").onchange = function(e) {
        const files = Array.from(e.target.files);
        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (ev) => {
                galleryImages.push({ type: "file", value: file, preview: ev.target.result });
                renderGallery();
            };
            reader.readAsDataURL(file);
        });
        // Reset so same file can be re-added if removed
        e.target.value = "";
    };

    auth.onAuthStateChanged(async (user) => {
        if (!user) { window.location.href = "login.php"; return; }
        
        document.getElementById("email").value = user.email;

        // Check if user is a sitter first, since becoming a sitter is an upgrade
        let userSnap = await getDoc(doc(db, "penjaga_kucing", user.uid));
        if (userSnap.exists()) {
            currentRole = "penjaga_kucing";
        } else {
            userSnap = await getDoc(doc(db, "pengguna", user.uid));
            currentRole = "pengguna";
        }

        if (userSnap.exists()) {
            const data = userSnap.data();
            document.getElementById("fullName").value = data.fld_user_name || data.fld_user_fullname || "";
            document.getElementById("userNameDisplay").innerText = data.fld_user_name || data.fld_user_fullname || "User";
            document.getElementById("phone").value = data.fld_user_phone || "";
            
            if (currentRole === "penjaga_kucing") {
                document.getElementById("bio").value = data.fld_user_pengalaman || data.fld_user_desc || "";
                document.getElementById("sitterFields").style.display = "block";
                document.getElementById("bandar").value = data.fld_user_bandar || "";
                document.getElementById("negeri").value = data.fld_user_negeri || "";
                document.getElementById("rate_boarding").value = data.fld_rate_boarding || "";
                document.getElementById("rate_daycare").value = data.fld_rate_daycare || data.fld_user_kadarBayaran || "";
                document.getElementById("rate_grooming").value = data.fld_rate_grooming || "";

                // Load existing services
                const services = data.fld_user_jenisPerkhidmatan || [];
                document.querySelectorAll(".service-checkbox").forEach(cb => {
                    cb.checked = services.includes(cb.value);
                });

                // Load existing gallery
                if (Array.isArray(data.fld_user_gallery)) {
                    galleryImages = data.fld_user_gallery.map(url => ({
                        type: "url",
                        value: url,
                        preview: url
                    }));
                    renderGallery();
                }

                // Load existing unavailable dates
                if (Array.isArray(data.fld_user_unavailableDates)) {
                    // Filter out dates that have already passed (optional cleanup)
                    const todayStr = new Date().toISOString().split("T")[0];
                    unavailableDates = data.fld_user_unavailableDates.filter(d => d >= todayStr);
                }
                renderBlockedDates();
            } else {
                document.getElementById("bio").value = data.fld_user_desc || "";
            }

            // Load Cat details list
            const rawCats = data.fld_cats || [];
            myCats = rawCats.map(cat => ({
                name: cat.name || "",
                age: cat.age || "",
                breed: cat.breed || "",
                notes: cat.notes || "",
                photoUrl: cat.photoUrl || "",
                photoFile: null
            }));
            renderCatsList();

            document.getElementById("userRoleBadge").innerText = currentRole === "pengguna" ? "Pet Owner" : "Cat Sitter";
            
            // Load existing avatar if present
            if (data.fld_user_avatar) {
                document.getElementById("imagePreview").style.backgroundImage = `url('${data.fld_user_avatar}')`;
            } else if (data.fld_user_profilePic) {
                document.getElementById("imagePreview").style.backgroundImage = `url('${data.fld_user_profilePic}')`;
            }
        }
    });

    // ── Handle Form Submit ───────────────────────────────────────────────────────
    document.getElementById("profileForm").onsubmit = async (e) => {
        e.preventDefault();
        const user = auth.currentUser;
        const name = document.getElementById("fullName").value;
        const phone = document.getElementById("phone").value;
        const bio = document.getElementById("bio").value;
        const nPass = document.getElementById("newPass").value;
        const cPass = document.getElementById("confirmPass").value;
        const imageFile = document.getElementById("imageInput").files[0];
        const btnSave = document.querySelector(".btn-save");

        btnSave.disabled = true;
        btnSave.innerText = "Saving...";

        // Auto-add cat to list if fields are filled but button wasn't clicked
        const newCatName = document.getElementById("newCatName").value.trim();
        if (newCatName) {
            window.addCatToList();
        }

        // Upload any new cat images and construct final cats list
        let finalCats = [];
        try {
            let avatarUrl = null;

            // If user selected a new profile image, upload to Storage
            if (imageFile) {
                const storageRef = ref(storage, `avatars/${user.uid}_${Date.now()}`);
                const snapshot = await uploadBytes(storageRef, imageFile);
                avatarUrl = await getDownloadURL(snapshot.ref);
            }

            // Upload any new gallery files and collect final URL list
            let finalGalleryUrls = [];
            for (const item of galleryImages) {
                if (item.type === "url") {
                    // Already uploaded — keep it
                    finalGalleryUrls.push(item.value);
                } else {
                    // New file — upload to Storage
                    const gRef = ref(storage, `gallery/${user.uid}_${Date.now()}_${item.value.name}`);
                    const gSnap = await uploadBytes(gRef, item.value);
                    const gUrl = await getDownloadURL(gSnap.ref);
                    finalGalleryUrls.push(gUrl);
                    // Convert to url type so re-renders show correct preview
                    item.type = "url";
                    item.value = gUrl;
                }
            }

            // Upload cat photos
            for (let i = 0; i < myCats.length; i++) {
                const cat = myCats[i];
                if (cat.photoFile) {
                    const catImgRef = ref(storage, `cats/${user.uid}_${Date.now()}_${i}_${cat.photoFile.name}`);
                    const snap = await uploadBytes(catImgRef, cat.photoFile);
                    const downloadUrl = await getDownloadURL(snap.ref);
                    
                    finalCats.push({
                        name: cat.name,
                        age: cat.age,
                        breed: cat.breed,
                        notes: cat.notes,
                        photoUrl: downloadUrl
                    });
                } else {
                    finalCats.push({
                        name: cat.name,
                        age: cat.age,
                        breed: cat.breed,
                        notes: cat.notes,
                        photoUrl: cat.photoUrl || ""
                    });
                }
            }

            // Get selected services
            let services = [];
            document.querySelectorAll(".service-checkbox:checked").forEach(cb => {
                services.push(cb.value);
            });

            // 1. Update Firestore
            const docRef = doc(db, currentRole, user.uid);
            let updateObj = currentRole === "pengguna" 
                ? { 
                    fld_user_name: name, 
                    fld_user_phone: phone, 
                    fld_user_desc: bio,
                    fld_cats: finalCats
                  }
                : { 
                    fld_user_fullname: name, 
                    fld_user_phone: phone, 
                    fld_user_pengalaman: bio,
                    fld_user_bandar: document.getElementById("bandar").value,
                    fld_user_negeri: document.getElementById("negeri").value,
                    fld_rate_boarding: document.getElementById("rate_boarding").value || "0",
                    fld_rate_daycare: document.getElementById("rate_daycare").value || "0",
                    fld_rate_grooming: document.getElementById("rate_grooming").value || "0",
                    fld_user_kadarBayaran: document.getElementById("rate_daycare").value || "0",
                    fld_user_jenisPerkhidmatan: services,
                    fld_user_gallery: finalGalleryUrls,
                    fld_user_unavailableDates: unavailableDates,
                    fld_cats: finalCats
                  };
            
            if (avatarUrl) {
                updateObj.fld_user_avatar = avatarUrl;
                if (currentRole === "penjaga_kucing") {
                    updateObj.fld_user_profilePic = avatarUrl; // fallback for backwards compatibility
                }
            }

            await updateDoc(docRef, updateObj);

            // 2. Update Password if filled
            if (nPass !== "") {
                if (nPass !== cPass) throw new Error("Passwords do not match!");
                await updatePassword(user, nPass);
            }

            alert("Profile successfully updated! 🐾");
            location.reload();
        } catch (err) {
            alert(err.message);
            btnSave.disabled = false;
            btnSave.innerText = "Save Changes";
        }
    };

    // Image Preview locally before save
    document.getElementById("imageInput").onchange = function(e) {
        if (e.target.files && e.target.files[0]) {
            const reader = new FileReader();
            reader.onload = () => { document.getElementById("imagePreview").style.backgroundImage = `url(${reader.result})`; };
            reader.readAsDataURL(e.target.files[0]);
        }
    };
</script>

</body>
</html>