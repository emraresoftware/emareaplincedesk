<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // İlk kayıt ise demo şube oluştur
        $branch = Branch::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name'     => 'Ana Şube',
                'currency' => 'TRY',
                'timezone' => 'Europe/Istanbul',
                'language' => 'tr',
            ]
        );

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'branch_id' => $branch->id,
            'role'      => User::count() === 0 ? 'super_admin' : 'staff',
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect('/');
    }
}
