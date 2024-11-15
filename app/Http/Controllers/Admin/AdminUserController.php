<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller
{
    public function index()
    {
        $users = User::with('sites')->where('role_name', 'site_engineer')->latest()->paginate(10);
        return view('profile.partials.Admin.users', compact('users'));
    }

    public function create()
    {
        return view('profile.partials.Admin.create-user');
    }

    public function register(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255|min:5',
            'username' => 'required|string|min:6|unique:' . User::class,
            'password' => [
                'required',
                'confirmed',
                Password::min(6)->mixedCase()->symbols()],
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')->with('status', 'create');
    }

    public function editUser($id) {


        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('status', 'error');
        }

        return view('profile.partials.Admin.edit-user-password', [
            'user' => $user,
        ]);
    }


}
