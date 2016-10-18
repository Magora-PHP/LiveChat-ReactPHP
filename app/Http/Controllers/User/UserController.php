<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Auth\Guard;
use App\Http\Requests\ProfileUpdateRequest;
use Session;

class UserController extends Controller
{

    /**
     * @param Guard $auth
     *
     * @return \Illuminate\Http\Response
     */
    public function showProfileForm(Guard $auth)
    {
        return view('user.profile', [
            'user' => $auth->user()
        ]);
    }

    /**
     * @param ProfileUpdateRequest $request
     * @param Guard                $auth
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateProfile(ProfileUpdateRequest $request, Guard $auth)
    {
        $auth->user()->update($request->all());

        Session::flash('success', 'Profile updated successfully.');
        return redirect('/profile');
    }
}
