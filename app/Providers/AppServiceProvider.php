<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\LazyLoadingViolationException;
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
        Model::preventLazyLoading(! app()->isProduction());
        Model::handleLazyLoadingViolationUsing(function ($model, $relation) {
            if (app()->environment('testing')) {
                throw new LazyLoadingViolationException($model, $relation);
            }
            logger()->warning('Lazy loading violation: '.$model::class.'->'.$relation);
        });
        Model::preventAccessingMissingAttributes(! app()->isProduction());
        Carbon::setLocale('th');

        View::composer('*', function ($view) {
            if (auth()->check()) {
                $view->with('wallets', Wallet::where('user_id', auth()->id())->active()->with(['transactions' => function ($query) {
                    $query->latest()->limit(3);
                }])->orderBy('sort_order')->orderBy('name')->get());
                $view->with('categories', Category::where('user_id', auth()->id())->active()->ordered()->get());
            }
        });
    }
}
