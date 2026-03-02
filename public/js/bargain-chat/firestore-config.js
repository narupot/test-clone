/*var config = {
    apiKey: "AIzaSyC3kAAIAnKpT6__FJqjF2L7HSKKiWXJyOQ",
    authDomain: "depa-minivourcher-demo.firebaseapp.com",
    databaseURL: "https://depa-minivourcher-demo.firebaseio.com",
    projectId: "depa-minivourcher-demo",
    storageBucket: "depa-minivourcher-demo.appspot.com",
    messagingSenderId: "14722883026",
    appId: "1:14722883026:web:d5e2186be0de60adb83c0b",
    measurementId: "G-NJLS77BFRY"
}*/

var config = {
  apiKey: "AIzaSyCT6k567P3Lw4wvJ4LGpxoCWM3VamYSxpc",
  authDomain: "smm-mobile-dev.firebaseapp.com",
  databaseURL: "https://smm-mobile-dev.firebaseio.com",
  projectId: "smm-mobile-dev",
  storageBucket: "smm-mobile-dev.appspot.com",
  messagingSenderId: "435652499229",
  appId: "1:435652499229:web:7d4e8e02bb61eab2c633f4"
};


firebase.initializeApp(config);

// Initialize Cloud Firestore through Firebase
var db = firebase.firestore();