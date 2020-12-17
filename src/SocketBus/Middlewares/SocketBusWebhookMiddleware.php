<?php

namespace SocketBus\Middlewares;

class SocketBusWebhookMiddleware {
    
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $socketBus = app()->make('socketbus');
        
        if (!$socketBus->authWebhook($request)) {
            abort(401, 'Invalid webhook token');
        }

        return $next($request);
    }
}