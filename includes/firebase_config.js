/**
 * Firebase Authentication Setup & Configuration Template
 *
 * Instructions:
 * 1. Go to Firebase Console (https://console.firebase.google.com/)
 * 2. Create a new project or select an existing project.
 * 3. Go to Project Settings -> General -> Your apps -> Add Web App.
 * 4. Copy your Firebase Configuration object and paste it below.
 * 5. Rename this file to `firebase_config.js` or copy `firebaseConfig` into `login.php`.
 * 6. Enable Phone Authentication and Email/Password under Firebase Console -> Authentication -> Sign-in method.
 */

var firebaseConfig = {
    apiKey: "AIzaSyDDpIijUWTB-I4ggo2sQUHezlQowd_7qLg",
    authDomain: "invoice-system-515d0.firebaseapp.com",
    projectId: "invoice-system-515d0",
    storageBucket: "invoice-system-515d0.firebasestorage.app",
    messagingSenderId: "234330508926",
    appId: "1:234330508926:web:41172e7501c156efaa522f",
    measurementId: "G-BNC5KPK546"
};

// Initialize Firebase
if (typeof firebase !== 'undefined' && !firebase.apps.length) {
    firebase.initializeApp(firebaseConfig);
}
