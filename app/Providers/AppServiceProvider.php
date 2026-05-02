<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('th');

        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('wallets', Wallet::where('user_id', auth()->id())->active()->get());
                $view->with('categories', Category::where('user_id', auth()->id())->active()->ordered()->get());
            }
        });
    }
}
