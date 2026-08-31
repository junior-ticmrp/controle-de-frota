<?php

namespace App\Services;

use App\Models\MenuVisibility;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;

class MenuVisibilityService
{
    public function items(): array
    {
        return (array) config('menu.items', []);
    }

    public function visibleItems(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        try {
            $stored = MenuVisibility::query()
                ->where('role', $user->role)
                ->pluck('enabled', 'menu_key');
        } catch (QueryException) {
            $stored = collect();
        }

        return collect($this->items())
            ->filter(function (array $item, string $key) use ($user, $stored): bool {
                if (! in_array($user->role, $item['roles'], true)) {
                    return false;
                }

                if (($item['always_visible'] ?? false) === true) {
                    return true;
                }

                return (bool) ($stored->get($key, $item['default'] ?? true));
            })
            ->map(fn (array $item, string $key): array => $item + ['key' => $key])
            ->values();
    }

    public function configurableItems(): Collection
    {
        return collect($this->items())
            ->filter(fn (array $item): bool => ($item['configurable'] ?? false) === true)
            ->map(fn (array $item, string $key): array => $item + ['key' => $key])
            ->values();
    }

    public function enabled(string $key, string $role): bool
    {
        $item = $this->items()[$key] ?? null;

        if (! $item || ! in_array($role, $item['roles'], true)) {
            return false;
        }

        if (($item['always_visible'] ?? false) === true) {
            return true;
        }

        try {
            $stored = MenuVisibility::query()
                ->where('menu_key', $key)
                ->where('role', $role)
                ->value('enabled');
        } catch (QueryException) {
            $stored = null;
        }

        return $stored === null ? (bool) ($item['default'] ?? true) : (bool) $stored;
    }
}
