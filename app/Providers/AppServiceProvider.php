<?php

namespace App\Providers;

use App\Models\Iavic114PbmcReport;
use App\Models\Pbmc;
use App\Models\SampleDispatch;
use App\Models\User;
use App\Observers\Iavic114PbmcReportObserver;
use App\Observers\PbmcObserver;
use App\Observers\SampleDispatchObserver;
use App\Observers\UserObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Pbmc::observe(PbmcObserver::class);
        User::observe(UserObserver::class);
        Iavic114PbmcReport::observe(Iavic114PbmcReportObserver::class);
        SampleDispatch::observe(SampleDispatchObserver::class);
    }
}
