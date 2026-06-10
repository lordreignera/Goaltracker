<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuarterRequest;
use App\Models\Quarter;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;

class QuarterController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->isAdmin(), 403);

        return view('quarters.index', [
            'quarters' => Quarter::orderByDesc('starts_at')
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function store(StoreQuarterRequest $request)
    {
        $data = $request->validated();

        $data['ends_at'] = Carbon::parse($data['starts_at'])->addDays(89)->toDateString();

        if ($request->boolean('is_active')) {
            Quarter::query()->update(['is_active' => false]);
        }

        Quarter::create($data + ['is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'Quarter created.');
    }
}
