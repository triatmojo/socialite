<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Exception;

use App\Models\User;
use App\Models\SocialAccount;

class SocialiteController extends Controller
{
    public function redirectToProvider($provider)
    {
        // Menentukan provider social media
        return Socialite::driver($provider)->redirect();
    }

    // handle data credentials get from social media
    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            return redirect('/login');
        }

        $authUser = $this->findOrCreateUser($user, $provider);

        Auth::login($authUser, true);

        return redirect('/home');
    }

    public function findOrCreateUser($socialUser, $provider)
    {
        $socialAccount = SocialAccount::where('provider_id', $socialUser->id)
            ->where('provider_name', $provider)
            ->first();

        if ($socialAccount) {
            return $socialAccount->user;
        } else {
            $user = User::where('email', $socialUser->email)->first();

            if (!$user) {

                $user = User::create([
                    'name' => $socialUser->name,
                    'email' => $socialUser->email,
                ]);
            }

            $user->socialAccount()->create([
                'provider_id' => $socialUser->id,
                'provider_name' => $provider
            ]);

            return $user;
        }
    }
}
