import { initializeApp } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-app.js";
import { getAuth, onAuthStateChanged }
from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";

const firebaseConfig = {
apiKey: "YOUR KEY",
authDomain: "YOUR DOMAIN",
projectId: "YOUR PROJECT"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);

// 🚫 BLOCK PAGE SAMPAI CHECK HABIS
export function requireLogin(callback) {

```
onAuthStateChanged(auth, (user) => {

    if (!user) {
        window.location.href = "login.php";
    } else {
        callback(); // ✅ hanya run code bila dah login
    }

});
```

}
