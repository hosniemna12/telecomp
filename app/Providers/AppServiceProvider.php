<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ParserInterface;
use App\Contracts\ValidatorInterface;
use App\Contracts\TransformerInterface;
use App\Contracts\LoggerInterface;
use App\Services\EnvParserService;
use App\Services\ValidatorService;
use App\Services\XmlTransformerService;
use App\Services\LogService;
use App\Services\AuditService;
use App\Services\RibValidatorService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ParserInterface::class,      EnvParserService::class);
        $this->app->bind(ValidatorInterface::class,   ValidatorService::class);
        $this->app->bind(TransformerInterface::class, XmlTransformerService::class);
        $this->app->bind(LoggerInterface::class,      LogService::class);
        $this->app->singleton(AuditService::class);
        $this->app->singleton(RibValidatorService::class);
    }

    public function boot(): void
    {
        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Login::class,
            function ($event) {
                app(AuditService::class)->loginSuccess($event->user->email);
                \App\Models\LoginHistory::create([
                    'user_id'    => $event->user->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'statut'     => 'SUCCESS',
                    'created_at' => now(),
                ]);
            }
        );

        \Illuminate\Support\Facades\Event::listen(
            \Illuminate\Auth\Events\Failed::class,
            function ($event) {
                app(AuditService::class)->loginFailed(
                    $event->credentials['email'] ?? 'inconnu'
                );
            }
        );
    }
}