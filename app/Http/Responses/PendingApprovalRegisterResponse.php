<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\RegisterResponse;

class PendingApprovalRegisterResponse implements RegisterResponse
{
    public function toResponse($request)
    {
        Auth::guard(config('fortify.guard'))->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Your staff account request has been submitted. A Super Admin must approve it before you can log in.');
    }
}
