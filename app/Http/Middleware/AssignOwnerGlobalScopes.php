<?php

namespace App\Http\Middleware;

use App\Models\Appointment;
use App\Models\Pet;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssignOwnerGlobalScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Appointment::addGlobalScope(function (Builder $query) {
            $petIds = Filament::auth()->user()->pets->pluck('id');
            $query->whereIn('pet_id', $petIds);
        });
        return $next($request);
    }
}
