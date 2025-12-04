<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleForCorrection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // 未ログインはauthミドルウェアが弾くのでここでは想定しない
        // ここで「一般／管理者」を区別してリクエストにフラグを埋め込む
        $request->attributes->set('is_admin_request', (bool) ($user->is_admin ?? false));

        // もし将来「この画面は管理者か一般のどちらかだけ許可」など制限したくなったら、
        // ここで abort(403) などの制御も可能。
        return $next($request);
    }
}