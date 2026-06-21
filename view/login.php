<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk - WhiskerHub</title>
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

        /* SPLIT SCREEN MAIN CONTAINER */
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

        .forgot-link-container {
            text-align: right;
            margin-top: -10px;
            margin-bottom: 25px;
        }

        .forgot-link-container a {
            font-size: 13px;
            font-weight: 600;
            color: #0052cc;
            text-decoration: none;
        }

        .forgot-link-container a:hover {
            text-decoration: underline;
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
        }

        .btn-auth:hover {
            background: #0d141b;
            transform: translateY(-1px);
        }

        /* Divider "Or" Line */
        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #98a2b3;
            font-size: 13px;
            margin: 25px 0;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e4e7ec;
        }

        .divider:not(:empty)::before { margin-right: .75em; }
        .divider:not(:empty)::after { margin-left: .75em; }

        /* Social Buttons Mockup */
        .social-btn {
            width: 100%;
            padding: 12px;
            border: 1px solid #d0d5dd;
            border-radius: 12px;
            background: white;
            font-size: 14px;
            font-weight: 600;
            color: #344054;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 12px;
            cursor: pointer;
            transition: 0.2s;
        }

        .social-btn:hover {
            background: #f9fafb;
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

        /* RIGHT SIDE: LARGE RECTANGLE IMAGE (ROUNDED) */
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
        <div class="brand-name"></div>

        <div class="auth-content">
            <h2>Welcome Back </h2>
            <p class="auth-subtitle">Today is a new day. It's your day. You shape it. Sign in to start managing your bookings.</p>

            <form id="loginForm">
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" id="email" placeholder="Example@email.com" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" id="password" placeholder="At least 8 characters" required>
                </div>


                <button type="submit" class="btn-auth">Sign in</button>
            </form>

    
            <div class="auth-footer">
                Don't you have an account? <a href="signup.php">Sign up</a>
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
import { getAuth, signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";

const firebaseConfig = {
    apiKey: "AIzaSyAzKctHKMlufU_daqSC7xPfVw0JZqMVf1w",
    authDomain: "whiskerhub-67889.firebaseapp.com",
    projectId: "whiskerhub-67889"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

document.getElementById("loginForm").addEventListener("submit", async (e) => {
    e.preventDefault();

    const email = document.getElementById("email").value.trim();
    const password = document.getElementById("password").value;

    try {
        await signInWithEmailAndPassword(auth, email, password);
        alert("Login successful!");
        if (email.toLowerCase() === "admin@whiskerhub.com") {
            window.location.href = "admin_dashboard.php";
        } else {
            window.location.href = "mainmenu.php";
        }
    } catch (error) {
        console.log(error.code);
        if (error.code === "auth/user-not-found" || error.code === "auth/invalid-credential") {
            alert("Maklumat log masuk salah atau pengguna tidak wujud.");
        } else if (error.code === "auth/wrong-password") {
            alert("Kata laluan salah.");
        } else {
            alert(error.message);
        }
    }
});
</script>

</body>
</html>