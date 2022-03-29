<?php

namespace App\Http\Middleware;

use Closure;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $preferredLocale = $this->getPreferredLocale($request);

        if (!in_array($preferredLocale, config('app.allowed_locales'))) {
            $preferredLocale = app()->getLocale();
        }

        app()->setLocale($preferredLocale);

        return $next($request);
    }

    /**
     * Get preferred locale for the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    public function getPreferredLocale($request)
    {
        if (auth()->check()) {
            return auth()->user()->locale;
        }

        if ($request->input('locale')) {
            return $request->input('locale');
        }

        if ($request->server('HTTP_ACCEPT_LANGUAGE')) {
            return explode('-', explode(',', $request->server('HTTP_ACCEPT_LANGUAGE'))[0])[0];
        }

        return app()->getLocale();
    }
}
