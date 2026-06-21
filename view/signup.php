<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - WhiskerHub</title>
    <link rel="stylesheet" href="../css/style.css">

    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
        }

        body {
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* SPLIT SCREEN MAIN CONTAINER (Sama dengan login kau) */
        .page-wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* LEFT SIDE: AUTH FORM */
        .left-side {
            flex: 1.1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 60px 80px;
            background: white;
        }

        .brand-name {
            font-size: 24px;
            font-weight: 800;
            color: #1a2530;
            letter-spacing: -0.5px;
        }

        .auth-content {
            max-width: 400px;
            width: 100%;
            margin: auto;
        }

        .auth-content h2 {
            font-size: 32px;
            font-weight: 700;
            color: #1a2530;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .auth-subtitle {
            font-size: 14px;
            color: #667085;
            line-height: 1.5;
            margin-bottom: 35px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            font-size: 14px;
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
            color: #344054;
        }

        .input-group input {
            width: 100%;
            padding: 14px 16px;
            border-radius: 12px;
            border: 1px solid #d0d5dd;
            font-size: 15px;
            background: #fcfcfc;
            outline: none;
            transition: 0.2s ease;
        }

        .input-group input:focus {
            border-color: #1a2530;
            background: white;
            box-shadow: 0 0 0 4px rgba(26, 37, 48, 0.05);
        }

        .btn-auth {
            width: 100%;
            padding: 14px;
            background: #1a2530;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s ease;
            margin-top: 10px;
        }

        .btn-auth:hover {
            background: #0d141b;
            transform: translateY(-1px);
        }

        .auth-footer {
            margin-top: 25px;
            font-size: 14px;
            color: #667085;
            text-align: center;
        }

        .auth-footer a {
            color: #0052cc;
            font-weight: 600;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }

        .copyright {
            font-size: 12px;
            color: #98a2b3;
            text-align: center;
        }

        /* RIGHT SIDE: LARGE RECTANGLE IMAGE (ROUNDED - Sebijik macam login kau) */
        .right-side {
            flex: 0.9;
            padding: 20px;
            display: flex;
        }

        .image-banner {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 24px;
            background-color: #f2f4f7;
        }

        /* RESPONSIVE DESIGN FOR MOBILE */
        @media (max-width: 900px) {
            .right-side { display: none; }
            .left-side { padding: 40px 30px; }
        }
    </style>
</head>

<body>

<div class="page-wrapper">
    
    <div class="left-side">
        <div class="brand-name"></div> <div class="auth-content">
            <h2>Join WhiskerHub </h2>
            <p class="auth-subtitle">Create an account today to start finding trusted care or managing bookings for your cat.</p>

            <form id="signupForm">
                <div class="input-group">
                    <label>Name</label>
                    <input type="text" id="name" placeholder="Enter your full name" required>
                </div>

                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="tel" id="phone" placeholder="e.g. 0123456789" required>
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" id="email" placeholder="Example@email.com" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" id="password" placeholder="At least 6 characters" required>
                </div>

                <button type="submit" class="btn-auth">Sign up</button>
            </form>

            <div class="auth-footer">
                Already have an account? <a href="login.php">Login</a>
            </div>
        </div>

        <div class="copyright">© 2026 ALL RIGHTS RESERVED</div>
    </div>

    <div class="right-side">
        <img src="../photos/cat.jpg" class="image-banner" alt="WhiskerHub Aesthetic Side Panel">
    </div>

</div>

<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-app.js";
import { getAuth, createUserWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";
import { getFirestore, doc, setDoc, serverTimestamp } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";

const firebaseConfig = {
    apiKey: "AIzaSyAzKctHKMlufU_daqSC7xPfVw0JZqMVf1w",
    authDomain: "whiskerhub-67889.firebaseapp.com",
    projectId: "whiskerhub-67889"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);

document.getElementById("signupForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const name = document.getElementById("name").value.trim();
    const phone = document.getElementById("phone").value.trim(); 
    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    try {
        const userCredential = await createUserWithEmailAndPassword(auth, email, password);
        const user = userCredential.user;

        // Simpan data ikut table structure asal kau
        await setDoc(doc(db, "pengguna", user.uid), {
            fld_user_ID: user.uid,
            fld_user_name: name,
            fld_user_email: email,
            fld_user_phone: phone,
            createdAt: serverTimestamp()
        });

        alert("Signup successful!");
        window.location.href = "login.php";

    } catch (error) {
        console.error(error);
        if (error.code === 'auth/email-already-in-use') {
            alert("This email address is already in use.");
        } else if (error.code === 'auth/weak-password') {
            alert("Password should be at least 6 characters.");
        } else {
            alert(error.message);
        }
    }
});
</script>

</body>
</html>