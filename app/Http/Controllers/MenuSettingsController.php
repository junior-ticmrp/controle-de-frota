<?php

namespace App\Http\Controllers;

use App\Models\MenuVisibility;
use App\Models\User;
use App\Services\MenuVisibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MenuSettingsController extends Controller
{
    public function index(Request $request, MenuVisibilityService $menu): View
    {
        $actor = $this->actor($request);
        $stored = MenuVisibility::query()
            ->where('role', '!=', 'admin')
            ->get()
            ->keyBy(fn (MenuVisibility $setting): string => $setting->role.'|'.$setting->menu_key);

        return view('menu-settings.index', [
            'items' => $menu->configurableItems(),
            'roles' => [
                'user' => 'Usuário',
                'supervisor' => 'Operador',
            ],
            'stored' => $stored,
            'user' => $actor,
            'guard' => $request->attributes->get('fleet.guard'),
        ]);
    }

    public function update(Request $request, MenuVisibilityService $menu): RedirectResponse
    {
        $actor = $this->actor($request);
        $items = $menu->configurableItems()->keyBy('key');
        $roles = ['user', 'supervisor'];
        $data = $request->validate([
            'settings' => ['nullable', 'array'],
            'settings.*' => ['nullable', 'array'],
            'settings.*.*' => ['nullable', 'boolean'],
        ]);

        foreach ($roles as $role) {
            foreach ($items as $key => $item) {
                if (! in_array($role, $item['roles'], true)) {
                    continue;
                }

                MenuVisibility::updateOrCreate(
                    ['menu_key' => $key, 'role' => $role],
                    ['enabled' => (bool) data_get($data, "settings.{$role}.{$key}", false)],
                );
            }
        }

        return redirect()->route('menu-settings.index')->with('status', 'Visibilidade do menu atualizada com sucesso.');
    }

    private function actor(Request $request): User
    {
        $actor = $request->attributes->get('fleet.user');
        Gate::forUser($actor)->authorize('manage-menu-settings');

        return $actor;
    }
}
