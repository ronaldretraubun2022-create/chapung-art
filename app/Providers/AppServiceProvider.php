<?php

namespace App\Providers;

use App\Models\AdminNotification;
use App\Models\ActivityLog;
use App\Models\Artist;
use App\Models\Artwork;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\Collection;
use App\Models\Customer;
use App\Models\Exhibition;
use App\Models\HomepageSection;
use App\Models\MediaItem;
use App\Models\Order;
use App\Models\PageView;
use App\Models\Payment;
use App\Models\Photography;
use App\Models\Post;
use App\Models\SeoMeta;
use App\Models\Shipment;
use App\Models\SiteSetting;
use App\Models\Tag;
use App\Models\User;
use App\Observers\ActivityLogObserver;
use App\Services\ActivityLogger;
use App\Services\CartService;
use Illuminate\Auth\Events\Login as LoginEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

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
        if ($this->app->isProduction() && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        $this->registerRateLimiters();
        $this->registerAuthorizationGates();

        $trackedModels = [
            AdminNotification::class,
            Artist::class,
            Artwork::class,
            Category::class,
            Certificate::class,
            Collection::class,
            Customer::class,
            Exhibition::class,
            HomepageSection::class,
            MediaItem::class,
            Order::class,
            PageView::class,
            Payment::class,
            Photography::class,
            Post::class,
            SeoMeta::class,
            Shipment::class,
            SiteSetting::class,
            Tag::class,
            User::class,
        ];

        foreach ($trackedModels as $model) {
            $model::observe(ActivityLogObserver::class);
        }

        Event::listen(LoginEvent::class, function (LoginEvent $event): void {
            app(CartService::class)->mergeAfterLogin($event->user);

            ActivityLogger::record(
                'login',
                $event->user,
                'User '.$event->user->email.' logged in.',
                ['guard' => $event->guard]
            );
        });
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('login', fn (Request $request): Limit => Limit::perMinute(5)->by(
            Str::lower((string) $request->input('email')).'|'.$request->ip()
        ));

        RateLimiter::for('public-form', fn (Request $request): Limit => Limit::perMinute(10)->by($request->ip()));

        RateLimiter::for('global-search', fn (Request $request): Limit => Limit::perMinute(60)->by($request->ip()));

        RateLimiter::for('certificate-verification', fn (Request $request): Limit => Limit::perMinute(20)->by($request->ip()));
    }

    private function registerAuthorizationGates(): void
    {
        Gate::before(function (User $user): ?bool {
            if ($user->isSuperAdmin() || $user->isLegacyAdminWithoutRoles()) {
                return true;
            }

            return null;
        });

        Gate::define('viewAny', fn (User $user, string $modelClass): bool => $this->hasModelPermission($user, 'view_any', $modelClass));
        Gate::define('view', fn (User $user, Model $model): bool => $this->hasModelPermission($user, 'view', $model::class));
        Gate::define('create', fn (User $user, string $modelClass): bool => $this->hasModelPermission($user, 'create', $modelClass));
        Gate::define('update', fn (User $user, Model $model): bool => $this->hasModelPermission($user, 'update', $model::class));
        Gate::define('delete', fn (User $user, Model $model): bool => $this->hasModelPermission($user, 'delete', $model::class));
        Gate::define('deleteAny', fn (User $user, string $modelClass): bool => $this->hasModelPermission($user, 'delete', $modelClass));
        Gate::define('restore', fn (User $user, Model $model): bool => $this->hasModelPermission($user, 'update', $model::class));
        Gate::define('forceDelete', fn (User $user, Model $model): bool => $this->hasModelPermission($user, 'delete', $model::class));
    }

    private function hasModelPermission(User $user, string $action, string $modelClass): bool
    {
        try {
            return $user->hasPermissionTo($action.' '.$this->permissionResourceKey($modelClass));
        } catch (Throwable) {
            return false;
        }
    }

    private function permissionResourceKey(string $modelClass): string
    {
        return Str::snake(class_basename($modelClass));
    }
}
