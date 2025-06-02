<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Auth;

class FirebaseService {
    private Auth $auth;

    public function __construct() {
        $factory = (new Factory)->withServiceAccount(storage_path('app/laravel-firebase-app-26131-firebase-adminsdk-fbsvc-fcd7e972f3.json'));
        $this->auth = $factory->createAuth();
    }

    public function verifyIdToken(string $idToken) {
        try {
            return $this->auth->verifyIdToken($idToken);
        }
        catch(\Exception $e) {
            throw new \Exception('Invalid Firebase token: '.$e->getMessage());
        }
    }

    public function isValidDomain(string $email): bool {
        return str_ends_with($email, '@bongbong.my.id');
    }
}
