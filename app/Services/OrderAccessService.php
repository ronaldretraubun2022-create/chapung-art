<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Throwable;

class OrderAccessService
{
    public function canView(Order $order, ?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($order->canBeViewedBy($user)) {
            return true;
        }

        try {
            return $user->isSuperAdmin()
                || $user->isLegacyAdminWithoutRoles()
                || Gate::forUser($user)->allows('view', $order);
        } catch (Throwable) {
            return false;
        }
    }

    public function authorize(Order $order, ?User $user): void
    {
        abort_unless($this->canView($order, $user), 403);
    }
}
