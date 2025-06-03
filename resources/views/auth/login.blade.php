@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
    <h2 class="text-2xl font-bold text-center mb-6">Login</h2>

    <!-- Email/Password Login -->
    <form method="POST" action="{{ route('login') }}" class="mb-6">
        @csrf
        <div class="mb-4">
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" id="email" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                   value="{{ old('email') }}">
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" id="password" required
                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
            @error('g-recaptcha-response')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="mb-4">
            <label class="flex items-center">
                <input type="checkbox" name="remember" class="rounded border-gray-300">
                <span class="ml-2 text-sm text-gray-600">Remember me</span>
            </label>
        </div>

        <button type="submit" class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            Login
        </button>
    </form>

    <!-- Firebase Google Login -->
    <div class="mb-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or</span>
            </div>
        </div>

        <button onclick="signInWithGoogle()" class="mt-4 w-full bg-red-600 text-white py-2 px-4 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
            Sign in with Google
        </button>
    </div>

    <!-- OTP Login -->
    <div class="mb-6">
        <div class="relative">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-300"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-white text-gray-500">Or login with OTP</span>
            </div>
        </div>

        <div id="otp-section" class="mt-4">
            <div id="email-section">
                <input type="email" id="otp-email" placeholder="Enter your email"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                <button onclick="sendOtp()" class="mt-2 w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                    Send OTP
                </button>
            </div>

            <div id="otp-verify-section" class="hidden">
                <input type="text" id="otp-code" placeholder="Enter 6-digit OTP" maxlength="6"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500">
                <button onclick="verifyOtp()" class="mt-2 w-full bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">
                    Verify OTP
                </button>
                <button onclick="resetOtp()" class="mt-2 w-full bg-gray-500 text-white py-2 px-4 rounded-md hover:bg-gray-600">
                    Back
                </button>
            </div>
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-800">Don't have an account? Register</a>
    </div>
</div>

<script>
async function signInWithGoogle() {
    try {
        const provider = new window.GoogleAuthProvider();
        const result = await window.signInWithPopup(window.firebaseAuth, provider);
        const idToken = await result.user.getIdToken();

        const response = await fetch('{{ route("firebase.login") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ idToken })
        });

        const data = await response.json();

        if(response.ok) {
            window.location.href = data.redirect;
        }
        else {
            alert(data.error);
        }
    }
    catch(error) {
        console.error('Error:', error);
        alert('Login failed: ' + error.message);
    }
}

async function sendOtp() {
    const email = document.getElementById('otp-email').value;
    if(!email) {
        alert('Please enter your email');
        return;
    }

    try {
        const response = await fetch('{{ route("otp.send") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email })
        });

        const data = await response.json();

        if(response.ok) {
            document.getElementById('email-section').classList.add('hidden');
            document.getElementById('otp-verify-section').classList.remove('hidden');
            alert('OTP sent to your email');
        }
        else {
            alert(data.error);
        }
    }
    catch(error) {
        alert('Failed to send OTP');
    }
}

async function verifyOtp() {
    const email = document.getElementById('otp-email').value;
    const otp = document.getElementById('otp-code').value;

    if(!otp || otp.length !== 6) {
        alert('Please enter a valid 6-digit OTP');
        return;
    }

    try {
        const response = await fetch('{{ route("otp.verify") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ email, otp })
        });

        const data = await response.json();

        if(response.ok) {
            window.location.href = data.redirect;
        }
        else {
            alert(data.error);
        }
    }
    catch(error) {
        alert('Verification failed');
    }
}

function resetOtp() {
    document.getElementById('email-section').classList.remove('hidden');
    document.getElementById('otp-verify-section').classList.add('hidden');
    document.getElementById('otp-code').value = '';
}
</script>
@endsection
