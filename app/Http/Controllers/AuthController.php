<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CaptchaService;
use App\Services\FirebaseService;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    private FirebaseService $firebaseService;
    private OtpService $otpService;
    private CaptchaService $captchaService;

    public function __construct(FirebaseService $firebaseService, OtpService $otpService, CaptchaService $captchaService) {
        $this->firebaseService = $firebaseService;
        $this->otpService = $otpService;
        $this->captchaService = $captchaService;
    }

    public function showLogin() {
        return view('auth.login');
    }

    public function showRegister() {
        return view('auth.register');
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'provider' => 'email',
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function login(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required',
        ]);

        if($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // verify captcha
        if(!$this->captchaService->verify($request->input('g-recaptcha-response'))) {
            return redirect()->back()->withErrors(['captcha' => 'Captcha verification failed.'])->withInput();
        }

        $credentials = $request->only('email', 'password');

        if(Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        }

        return redirect()->back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput();
    }

    public function firebaseLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            'idToken' => 'required|string',
        ]);

        if($validator->fails()) {
            return response()->json(['error' => 'Invalid token'], 400);
        }

        try {
            $verifiedIdToken = $this->firebaseService->verifyIdToken($request->idToken);
            $email = $verifiedIdToken->claims()->get('email');
            $name = $verifiedIdToken->claims()->get('name');
            $uid = $verifiedIdToken->claims()->get('uid');

            // check domain restriction
            if(!$this->firebaseService->isValidDomain($email)) {
                return response()->json(['error' => 'Only @bongbong.my.id email addresses are allowed'], 403);
            }

            // find or create user
            $user = User::where('email', $email)->first();

            if(!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'provider' => 'firebase',
                    'provider_id' => $uid,
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user);

            return response()->json([
                'success' => true,
                'redirect' => route('dashboard'),
            ]);
        }
        catch(\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function sendOtp(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if($validator->fails()) {
            return response()->json(['error' => 'Invalid email'], 400);
        }

        try {
            $this->otpService->generateOtp($request->email);
            return response()->json(['message' => 'OTP sent successfully']);
        }
        catch(\Exception $e) {
            return response()->json(['error' => 'Failed to send OTP'], 500);
        }
    }

    public function verifyOtp(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        if($validator->fails()) {
            return response()->json(['error' => 'Invalid input'], 400);
        }

        if($this->otpService->verifyOtp($request->email, $request->otp)) {
            // find or create user
            $user = User::where('email', $request->email)->first();

            if(!$user) {
                $user = User::create([
                    'name' => explode('@', $request->email)[0],
                    'email' => $request->email,
                    'provider' => 'otp',
                    'email_verified_at' => now(),
                ]);
            }

            Auth::login($user);

            return response()->json([
                'success' => true,
                'redirect' => route('dashboard'),
            ]);
        }

        return response()->json(['error' => 'Invalid or expired OTP'], 400);
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
