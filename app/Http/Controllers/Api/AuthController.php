<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('authentication.login_page');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('login')
                ->withErrors($validator)
                ->withInput();
        }

        $credentials = $request->only('nik', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            $allowed_menu_ids = DB::table('role_menu')
                ->where('user_id', $user->id)
                ->where('can_view', 1)
                ->pluck('menu_id')
                ->toArray();

            $request->session()->put('name', $user->name);
            $request->session()->put('email', $user->email);
            $request->session()->put('allowed_menus', $allowed_menu_ids);

            if (empty($allowed_menu_ids)) {
                Auth::logout();
                return back()->withErrors(['nik' => 'Your account does not have access rights.'])->onlyInput('nik');
            }

            $allowed_menus = DB::table('menus')
                ->whereIn('id', $allowed_menu_ids)
                ->orderBy('id', 'asc')
                ->get();

            $redirectUrl = null;

            foreach ($allowed_menus as $menu) {
                if ($menu->route && Route::has($menu->route)) {
                    $redirectUrl = route($menu->route);
                    break;
                }
            }
            if (is_null($redirectUrl)) {
                Auth::logout();
                return back()->withErrors(['nik' => 'Your account does not have access to a valid page.'])->onlyInput('nik');
            }
            return redirect()->intended($redirectUrl);
        }
        return back()->withErrors(['nik' => 'The NIK or Password you entered is incorrect.'])->onlyInput('nik');
    }


    public function redirectToHomepage()
    {
        $allowed_menu_ids = session('allowed_menus');
        if (empty($allowed_menu_ids)) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['nik' => 'Your account does not have access rights.']);
        }

        $allowed_menus = DB::table('menus')
            ->whereIn('id', $allowed_menu_ids)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($allowed_menus as $menu) {
            if ($menu->route && Route::has($menu->route)) {
                return redirect()->route($menu->route);
            }
        }
        Auth::logout();
        return redirect()->route('login')->withErrors(['nik' => 'Your account does not have access to a valid page.']);
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['name', 'email', 'allowed_menus']);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
