// Import the functions you need from the SDKs you need
import { initializeApp } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-app.js";
//import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-analytics.js";
import { getAuth } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-auth.js";
import { getFirestore, doc, getDoc, getDocs, collection, query, where } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-firestore.js";
import { getStorage } from "https://www.gstatic.com/firebasejs/12.12.1/firebase-storage.js";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyAzKctHKMlufU_daqSC7xPfVw0JZqMVf1w",
  authDomain: "whiskerhub-67889.firebaseapp.com",
  projectId: "whiskerhub-67889",
  storageBucket: "whiskerhub-67889.firebasestorage.app",
  messagingSenderId: "138207141304",
  appId: "1:138207141304:web:92a4423b06ed96fa6a034d",
  measurementId: "G-01845M6L66"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
// ✅ DEFINE SEKALI JE
const auth = getAuth(app);
const db = getFirestore(app);
const storage = getStorage(app);
// ✅ EXPORT SEKALI
export { auth, db, storage };
//Get Detail Sitter

console.log("FIREBASE LOADED");

export async function getSitterById(id) {
  const ref = doc(db, "penjaga_kucing", id);
  const snap = await getDoc(ref);

  if (snap.exists()) {
    return snap.data();
  } else {
    return null;
  }
}

// 🔥 BACKEND FUNCTION (get all sitters)
export async function getAllSitters() {

  const snap = await getDocs(collection(db, "penjaga_kucing"));

  let data = [];

  snap.forEach(doc => {
    data.push({
      id: doc.id,
      ...doc.data()
    });
  });

  return data;
}

export async function getReviewsBySitterId(sitterId) {

  const q = query(
    collection(db, "review"),
    where("sitterId", "==", sitterId)
  );

  const snapshot = await getDocs(q);

  let review = [];

  snapshot.forEach(doc => {
    review.push(doc.data());
  });

  return review;
}
