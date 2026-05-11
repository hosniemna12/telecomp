<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Contracts\ParserInterface;
use App\Contracts\ValidatorInterface;
use App\Contracts\TransformerInterface;
use App\Contracts\LoggerInterface;
use App\Services\Parsing\EnvParserService;
use App\Services\Validation\ValidatorService;
use App\Services\Transformation\XmlTransformerService;
use App\Services\Audit\LogService;
use App\Services\Workflow\FichierWorkflowService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ParserInterface::class,      EnvParserService::class);
        $this->app->bind(ValidatorInterface::class,   ValidatorService::class);
        $this->app->bind(TransformerInterface::class, XmlTransformerService::class);
        $this->app->bind(LoggerInterface::class,      LogService::class);
        $this->app->singleton(FichierWorkflowService::class);
    }

    public function boot(): void {}
}