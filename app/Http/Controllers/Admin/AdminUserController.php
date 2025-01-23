<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentSupplier;
use App\Models\Site;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rules\Unique;

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
                Password::min(6)->mixedCase()->symbols()
            ],
        ]);

        User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')->with('status', 'create');
    }

    public function editUser($id)
    {


        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('status', 'error');
        }

        return view('profile.partials.Admin.edit-user-password', [
            'user' => $user,
        ]);
    }


    public function updateUserPassword(Request $request, $id)
    {


        $validated = $request->validateWithBag('updatePassword', [
            'password' => [
                'required',
                'confirmed',
                Password::min(6)->symbols()->mixedCase(),
            ],
        ]);

        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('status', 'error');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')->with('status', 'update');
    }

    public  function updateName(Request $request, $id)
    {

        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('status', 'error');
        }

        $request->validateWithBag('updateName', [
            'name' => 'required|string|max:255|min:5',
            'username' => ['nullable', 'sometimes', 'string', 'min:6', Rule::unique('users')->ignore($user->id)]
        ]);

        $user->update([
            'name' => $request->name,
            'username' => $request->username
        ]);

        return redirect()->route('users.index')->with('status', 'update');
    }

    public function deleteUser($id)
    {

        $user = User::find($id);

        $hasPaymentRecords = Site::whereHas('user', function ($query) use ($id) {
            $query->where('user_id', $id);
        })->exists();

        dd($hasPaymentRecords);

        if ($hasPaymentRecords) {
            return redirect()->back()->with('status', 'error');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'delete');
    }
}
