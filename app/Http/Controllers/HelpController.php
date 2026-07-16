<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    public function index()
    {
        return view('help.user-guide');
    }

    public function pdf(Request $request)
    {
        return Pdf::loadView('help.user-guide-pdf', [
            'generatedBy' => $request->user(),
        ])
            ->setPaper('a4', 'portrait')
            ->download('smart-goals-user-guide.pdf');
    }
}
