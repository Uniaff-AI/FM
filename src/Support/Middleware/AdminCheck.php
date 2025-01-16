<?php
namespace Support\Middleware;

use Closure;

class AdminCheck
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
        // Разрешенные роли
        $allowedRoles = ['admin', 'helper'];

        // Проверяем, есть ли у пользователя одна из разрешенных ролей
        if (!in_array($request->user()->role, $allowedRoles)) {
            return response("У вас нет доступа к этой операции!", 403);
        }

        return $next($request);
    }
}
