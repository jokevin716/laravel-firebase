<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

class FirebaseService {
    private Auth $auth;

    public function __construct() {
        try {
            $credPath = storage_path('app/laravel-firebase-app-26131-firebase-adminsdk-fbsvc-fcd7e972f3.json');

            if(!file_exists($credPath)) {
                throw new \Exception('Firebase credentials file not found at: ' . $credPath);
            }

            $factory = (new Factory)->withServiceAccount($credPath);
            $this->auth = $factory->createAuth();
        }
        catch(\Exception $e) {
            \Log::error('Firebase initialization failed: ' . $e->getMessage());
            throw new \Exception('Firebase service unavailable');
        }
    }

    public function verifyIdToken(string $idToken) {
        try {
            return $this->auth->verifyIdToken($idToken);
        }
        catch(\Exception $e) {
            \Log::error('Firebase token verification failed: ' . $e->getMessage());
            throw new \Exception('Invalid Firebase token: ' . $e->getMessage());
        }
    }

    public function isValidDomain(string $email): bool {
        // return str_ends_with(strtolower($email), '@bongbong.my.id');
        return str_ends_with(strtolower($email), '@bongbong.my.id') || str_ends_with(strtolower($email), '@gmail.com');
    }
}
