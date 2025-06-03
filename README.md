
# Laravel 10 Auth + OTP + Marketstack Dashboard

A Laravel 10 demo app with the following features:

## 🔧 Tech Stack

- **Laravel 10**
- **MySQL**
- **PHP 8.2**
- **Firebase Authentication (Google SSO)**
- **Email/Password Auth with reCAPTCHA**
- **PHPMailer for OTP over Gmail**
- **Marketstack Stock API (cURL only)**
- **Chart.js for visualizing stock data**

---

## ✨ Features

- 🔐 **Google SSO (Firebase)**
  - Only allows login for emails under the `@bongbong.my.id` domain.
- 🧠 **Email/Password Login with Google reCAPTCHA**
- 📩 **Email OTP via PHPMailer (Gmail SMTP)**
- 📊 **Stock Price & Volume Visualization (via Marketstack)**
- 🧵 **No Guzzle / Laravel HTTP Client used – only native cURL**

---

## 🛠️ Setup Instructions

### 1. Clone & Install Dependencies

```bash
git clone https://github.com/jokevin716/laravel-firebase.git
cd laravel-firebase
composer install
cp .env.example .env
php artisan key:generate
```

### 2. Configure `.env`

```env
# Mail (Use App Password from Gmail)
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=your_app_password

# reCAPTCHA
RECAPTCHA_SITE_KEY=your_site_key
RECAPTCHA_SECRET_KEY=your_secret_key

# Firebase Config (frontend use only)
FIREBASE_API_KEY=...
FIREBASE_AUTH_DOMAIN=...
FIREBASE_PROJECT_ID=...
FIREBASE_MESSAGING_SENDER_ID=...

# Marketstack
MARKETSTACK_KEY=your_marketstack_key
```

---

### 3. Database

```bash
php artisan migrate
```

---

### 4. Start the App

```bash
php artisan serve
```

---

## 🧪 Testing

- ✅ **Email/Password**: Login via form with reCAPTCHA
- ✅ **Firebase SSO**: Login via Google using Firebase SDK (`public/js/firebase-auth.js`)
- ✅ **OTP Email**: Triggers PHPMailer sending via Gmail SMTP
- ✅ **Marketstack**: Fetches stock data using raw `cURL` and displays via Chart.js

---

## 📁 Folder Structure (Highlights)

```
├── app
│   ├── Http/Controllers
│   │   ├── AuthController.php
│   │   └── DashboardController.php
│   └── Models
│   │   ├── Otp.php
│   │   └── User.php
│   └── Services
│   │   ├── CaptchaService.php
│   │   ├── FirebaseService.php
│   │   ├── MarketstackService.php
│   │   └── OtpService.php
├── resources/views
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   ├── layouts/
│   │   └── app.blade.php
│   ├── dashboard.blade.php
│   └── welcome.blade.php
├── routes/web.php
```

---

## 📌 Notes

- Firebase Auth must be domain-restricted server-side (`@bongbong.my.id`)
- Marketstack free tier is **rate limited (HTTP 429)** — avoid spamming it
- Use [Chart.js adapter for `date-fns`](https://www.chartjs.org/chartjs-plugin-zoom/latest/guide/integrations.html#date-fns) for proper time formatting

