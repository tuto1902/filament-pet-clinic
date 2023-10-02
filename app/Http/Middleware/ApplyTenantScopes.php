<?php

namespace App\Http\Middleware;

use App\Models\Pet;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyTenantScopes
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        Pet::addGlobalScope(
            fn (Builder $query) => 
                $query->whereHas('clinics', fn (Builder $query) => 
                    $query->where('clinics.id', Filament::getTenant()->id))
        );
        
        return $next($request);
    }
}
