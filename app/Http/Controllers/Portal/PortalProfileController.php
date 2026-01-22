<?php

namespace App\Http\Controllers\Portal;

use Illuminate\View\View;

class PortalProfileController extends PortalController
{
    public function show(): View
    {
        $user = auth()->user()->load(['instruments', 'region', 'tags']);

        return view('portal.profile', [
            'user' => $user,
        ]);
    }
}
