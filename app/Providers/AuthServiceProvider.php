<?php

namespace App\Providers;

use App\Models\Checklist;
use App\Models\SurgeryRequest;
use App\Policies\ChecklistPolicy;
use App\Policies\SurgeryRequestPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        SurgeryRequest::class => SurgeryRequestPolicy::class,
        Checklist::class      => ChecklistPolicy::class,
    ];

    public function boot(): void
    {
        // Se precisar Gates adicionais, dá pra defini-los aqui.
    }
}
