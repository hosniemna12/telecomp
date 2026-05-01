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
use App\Services\ValidationService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ParserInterface::class,      EnvParserService::class);
        $this->app->bind(ValidatorInterface::class,   ValidatorService::class);
        $this->app->bind(TransformerInterface::class, XmlTransformerService::class);
        $this->app->bind(LoggerInterface::class,      LogService::class);
        $this->app->singleton(ValidationService::class);
    }

    public function boot(): void {}
}