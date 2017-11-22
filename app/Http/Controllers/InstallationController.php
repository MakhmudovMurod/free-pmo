<?php

namespace App\Http\Controllers;

use App\Entities\Users\User;
use App\Http\Requests\Accounts\RegisterRequest;
use Auth;
use DB;

/**
 * Installation Controller
 *
 * @author Nafies Luthfi <nafiesl@gmail.com>
 */
class InstallationController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function index()
    {
        if (User::count()) {
            return redirect()->route('auth.login');
        }
        return view('auth.app-install');
    }

    public function store(RegisterRequest $request)
    {
        $agencyData = collect($request->only('agency_name', 'agency_website', 'email'))
            ->map(function ($value, $key) {

                if ($key == 'email') {
                    $key = 'agency_email';
                }

                return [
                    'key'   => $key,
                    'value' => $value,
                ];
            })->toArray();

        DB::beginTransaction();
        DB::table('site_options')->insert($agencyData);

        $adminData = $request->only('name', 'email', 'password');

        $adminData['api_token'] = str_random(32);

        $admin = User::create($adminData);
        $admin->assignRole('admin');
        $admin->assignRole('worker');

        Auth::login($admin);
        DB::commit();

        flash()->success(trans('auth.welcome', ['name' => $admin->name]));
        return redirect()->route('home');
    }
}
