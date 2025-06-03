// .js/auth.js
require('dotenv').config();

document.addEventListener("DOMContentLoaded", () => {
    const supabaseUrl = process.env.SUPABASE_URL;
    const supabaseKey = process.env.ANON_KEY;
    const supabase = supabase.createClient(supabaseUrl, supabaseKey);

    // Function to handle successful sign-in and redirect
    async function handleSignInSuccess() {
        alert("Signed in successfully!");
        window.location.href = "user.php"; // Redirect to user.php
    }

    // Function to handle sign-in errors
    function handleSignInError(error) {
        alert("Sign-in failed: " + error.message);
        console.error("Sign-in error:", error);
    }

    // Google OAuth functionality - Initiate the flow
    const googleSignInButton = document.getElementById("google-signin");
    if (googleSignInButton) {
        googleSignInButton.addEventListener("click", async () => {
            const { error } = await supabase.auth.signInWithOAuth({
                provider: "google",
                options: {
                    redirectTo: window.location.origin + "/callback.html" // Configure this callback URL
                }
            });

            if (error) {
                handleSignInError(error);
            }
            // After initiating, Supabase will redirect the user to Google for authentication.
            // Google will then redirect back to the callback URL.
        });
    }

    // Handle the OAuth callback (if the user is redirected back with auth parameters)
    async function handleOAuthCallback() {
        const params = new URLSearchParams(window.location.hash.substring(1));
        const accessToken = params.get('access_token');
        const refreshToken = params.get('refresh_token');
        const error_description = params.get('error_description');

        if (accessToken) {
            // Exchange the access token for a Supabase session
            const { data, error } = await supabase.auth.setSession({
                access_token: accessToken,
                refresh_token: refreshToken,
            });

            if (error) {
                handleSignInError(error);
            } else {
                handleSignInSuccess();
            }
        } else if (error_description) {
            handleSignInError({ message: error_description });
        }
    }

    // Check if it's a callback URL
    if (window.location.hash) {
        handleOAuthCallback();
    }

    // Normal Login functionality (remains mostly the same)
    const loginForm = document.querySelector('form button[name="login"]');
    if (loginForm) {
        document.querySelector('form').addEventListener("submit", async (e) => {
            e.preventDefault();
            const email = document.getElementById("username").value;
            const password = document.getElementById("password").value;

            const { data, error } = await supabase.auth.signInWithPassword({
                email,
                password,
            });

            if (error) {
                handleSignInError(error);
            } else {
                handleSignInSuccess(); // Redirect on normal login as well
                console.log("Logged in user:", data.user);
            }
        });
    }

    // Register functionality (remains mostly the same)
    const registerForm = document.querySelector('form button[name="register"]');
    if (registerForm) {
        document.querySelector('form').addEventListener("submit", async (e) => {
            e.preventDefault();
            const name = document.getElementById("username").value;
            const email = document.querySelector('input[type="email"]').value;
            const password = document.getElementById("password").value;

            const { data, error } = await supabase.auth.signUp({
                email,
                password,
                options: {
                    data: { name }, // Add custom user data
                },
            });

            if (error) {
                handleSignInError(error);
            } else {
                alert("User registered successfully! Please check your email to confirm.");
                // Optionally redirect after successful registration
                // window.location.href = "login.html";
            }

            // Optionally store user data in a separate 'users' table
            if (data.user) {
                await supabase.from("users").insert([{ name, email }]);
            }
        });
    }

    // --- Sign Out Functionality (Example - Add a button in your UI) ---
    const signOutButton = document.getElementById("sign-out-button"); // Add this button to your UI
    if (signOutButton) {
        signOutButton.addEventListener("click", async () => {
            const { error } = await supabase.auth.signOut();
            if (error) {
                console.error("Sign out error:", error);
                alert("Failed to sign out.");
            } else {
                alert("Signed out successfully!");
                window.location.href = "login.html"; // Redirect to login page after sign out
            }
        });
    }
});