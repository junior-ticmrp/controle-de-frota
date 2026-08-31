<?php
namespace App\Providers;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider {
 public function register(): void {}
 public function boot(): void {
  Gate::define('manage-master-data', fn(User $user): bool => $user->is_active && $user->isSupervisor());
  Gate::define('operate-fleet', fn(User $user): bool => $user->is_active && in_array($user->role,['user','supervisor'],true));
  Gate::define('supervise-fleet', fn(User $user): bool => $user->is_active && $user->isSupervisor());
  Gate::define('manage-menu-settings', fn(User $user): bool => $user->is_active && $user->isAdministrator());
 }
}
