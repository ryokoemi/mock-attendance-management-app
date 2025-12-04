<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        // 追加: admin 権限は is_admin フラグで判定
        Gate::define('admin', function ($user) {

             // ★ 一時的にデバッグ
        //dd('Gate admin', $user, $user ? $user->is_admin : null);

            return (bool) ($user->is_admin ?? false);
        });
    }
}
