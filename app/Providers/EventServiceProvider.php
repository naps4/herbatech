<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

use App\Events\CPBCreated;
use App\Events\CPBHandover;
use App\Events\CPBOverdue;
use App\Listeners\SendCPBNotification;
use App\Listeners\SendOverdueNotification;
use App\Listeners\LogHandoverActivity;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        CPBCreated::class => [
            SendCPBNotification::class,
        ],
        CPBHandover::class => [
            LogHandoverActivity::class,
        ],
        CPBOverdue::class => [
            SendOverdueNotification::class,
        ],
    ];

    public function boot()
    {
        parent::boot();
    }
}