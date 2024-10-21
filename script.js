// جاوا اسکریپت برای نمایش یا مخفی کردن رمز عبور
document.getElementById("toggle-password").addEventListener("click", function() {
    const passwordField = document.getElementById("password");
    const toggleButton = document.getElementById("toggle-password");
    
    if (passwordField.type === "password") {
        passwordField.type = "text";
        toggleButton.textContent = "Hide";
    } else {
        passwordField.type = "password";
        toggleButton.textContent = "Show";
    }
});

// اعتبارسنجی فرم لاگین قبل از ارسال
function validateForm() {
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;
    const errorMessage = document.getElementById("error-message");

    // پاک کردن پیام‌های خطا قبلی
    errorMessage.style.display = "none";
    errorMessage.textContent = "";

    // اعتبارسنجی سمت کاربر
    if (username === "" || password === "") {
        errorMessage.textContent = "Both fields are required.";
        errorMessage.style.display = "block";
        return false; // جلوگیری از ارسال فرم
    }

    if (password.length < 6) {
        errorMessage.textContent = "Password must be at least 6 characters long.";
        errorMessage.style.display = "block";
        return false; // جلوگیری از ارسال فرم
    }

    // اگر همه چیز درست باشد، فرم ارسال می‌شود
    return true;
}
