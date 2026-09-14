<?php

namespace App\Http\Controllers;

use App\Enums\CollectiveMemberRole;
use App\Models\Collective;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CollectiveMembershipController extends Controller
{
    /**
     * Leave a collective. Founders can't leave here, so a collective never ends up without one.
     */
    public function destroy(Request $request, Collective $collective): RedirectResponse
    {
        $role = $collective->roleOf($request->user());

        if ($role === CollectiveMemberRole::Founder) {
            throw ValidationException::withMessages([
                'membership' => 'Founders can’t leave their collective here. Get in touch and we will help hand it over.',
            ]);
        }

        $collective->members()->detach($request->user());

        return to_route('collectives.show', $collective)->with('status', 'collective-left');
    }
}
