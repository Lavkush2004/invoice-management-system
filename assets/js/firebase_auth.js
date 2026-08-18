/**
 * Firebase Phone & Email Authentication Handler
 */

var windowRecaptchaVerifier = null;
var windowConfirmationResult = null;

function resetFirebaseRecaptcha(containerId) {
    var cId = containerId || 'firebase-recaptcha';
    if (windowRecaptchaVerifier) {
        try {
            windowRecaptchaVerifier.clear();
        } catch (e) {
            console.warn('Error clearing reCAPTCHA verifier:', e);
        }
        windowRecaptchaVerifier = null;
    }
    var el = document.getElementById(cId);
    if (el) {
        el.innerHTML = '';
    }
}

function initFirebaseRecaptcha(containerId) {
    if (typeof firebase === 'undefined' || !firebase.auth) {
        console.warn('Firebase Auth SDK is not loaded.');
        return null;
    }

    var cId = containerId || 'firebase-recaptcha';

    // If a verifier already exists, try resetting the widget
    if (windowRecaptchaVerifier) {
        try {
            if (typeof grecaptcha !== 'undefined') {
                windowRecaptchaVerifier.render().then(function(widgetId) {
                    grecaptcha.reset(widgetId);
                }).catch(function() {
                    resetFirebaseRecaptcha(cId);
                });
            }
            return windowRecaptchaVerifier;
        } catch (e) {
            resetFirebaseRecaptcha(cId);
        }
    }

    resetFirebaseRecaptcha(cId);

    try {
        windowRecaptchaVerifier = new firebase.auth.RecaptchaVerifier(cId, {
            'size': 'invisible',
            'callback': function(response) {
                console.log('reCAPTCHA solved');
            },
            'expired-callback': function() {
                console.warn('reCAPTCHA expired, resetting...');
                if (typeof grecaptcha !== 'undefined' && windowRecaptchaVerifier) {
                    windowRecaptchaVerifier.render().then(function(widgetId) {
                        grecaptcha.reset(widgetId);
                    });
                }
            }
        });
    } catch (e) {
        console.error('Failed to create RecaptchaVerifier:', e);
        resetFirebaseRecaptcha(cId);
    }
    return windowRecaptchaVerifier;
}

function sendFirebaseOtp(phoneNumber, recaptchaContainerId, onSuccess, onError) {
    if (typeof firebase === 'undefined' || !firebase.apps || !firebase.apps.length) {
        if (typeof onError === 'function') onError(new Error('Firebase is not initialized. Please verify includes/firebase_config.js.'));
        return;
    }

    try {
        var appVerifier = initFirebaseRecaptcha(recaptchaContainerId);
        if (!appVerifier) {
            if (typeof onError === 'function') onError(new Error('reCAPTCHA initialization failed.'));
            return;
        }

        firebase.auth().signInWithPhoneNumber(phoneNumber, appVerifier)
            .then(function (confirmationResult) {
                windowConfirmationResult = confirmationResult;
                if (typeof onSuccess === 'function') onSuccess(confirmationResult);
            })
            .catch(function (error) {
                console.error('Firebase OTP Error:', error);
                if (windowRecaptchaVerifier) {
                    try { windowRecaptchaVerifier.clear(); } catch(e) {}
                    windowRecaptchaVerifier = null;
                }
                if (typeof onError === 'function') onError(error);
            });
    } catch (e) {
        console.error('Firebase Exception:', e);
        if (windowRecaptchaVerifier) {
            try { windowRecaptchaVerifier.clear(); } catch(err) {}
            windowRecaptchaVerifier = null;
        }
        if (typeof onError === 'function') onError(e);
    }
}

function verifyFirebaseOtp(otpCode, onSuccess, onError) {
    if (!windowConfirmationResult) {
        if (typeof onError === 'function') onError(new Error('Please request an OTP code first.'));
        return;
    }

    windowConfirmationResult.confirm(otpCode)
        .then(function (result) {
            var user = result.user;
            if (typeof onSuccess === 'function') onSuccess(user);
        })
        .catch(function (error) {
            console.error('Firebase Verify OTP Error:', error);
            if (typeof onError === 'function') onError(error);
        });
}
