<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index(): View
    {
        /** @var User $user */
        $user = Auth::user();
        $notifications = $this->notificationsFor($user)->with(['appointment.customer', 'appointment.employee', 'appointment.services'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    public function poll(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $validated = $request->validate(['after' => 'nullable|integer|min:0']);
        $after = $validated['after'] ?? 0;
        $notifications = $this->notificationsFor($user)
            ->where('notifications.id', '>', $after)
            ->orderBy('notifications.id', $after === 0 ? 'desc' : 'asc')
            ->limit(20)
            ->get();

        return response()->json([
            'data' => $notifications->map(fn (Notification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'url' => $notification->appointment_id
                    ? ($user->isCustomer()
                        ? route('customer.dashboard', ['view' => 'appointments'])
                        : route('appointments.index', ['date' => $notification->appointment?->appointment_date]))
                    : route('notifications.index'),
            ]),
            'unread_count' => $this->notificationsFor($user)->where('is_read', false)->count(),
        ]);
    }

    public function markAsRead(Notification $notification): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $canRead = $notification->user_id === $user->id
            || ($notification->user_id === null && $user->isOwnerOrAdmin())
            || ($notification->user_id === null && $user->isStaff() && $notification->type !== 'APPOINTMENT')
            || ($notification->user_id === null
                && $user->isStaff()
                && $notification->appointment?->employee_id === $user->employee_id);
        abort_unless($canRead, 403);

        $notification->update(['is_read' => true]);

        return back()->with('success', 'Notification marked as read.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $this->notificationsFor($user)->where('is_read', false)->update(['is_read' => true]);

        return back()->with('success', 'All notifications marked as read.');
    }

    private function notificationsFor(User $user): Builder
    {
        $query = Notification::query();

        if ($user->isCustomer()) {
            return $query->where('user_id', $user->id)->where('type', 'APPOINTMENT');
        }

        if ($user->isStaff()) {
            return $query->where(function (Builder $query) use ($user): void {
                $query->where('user_id', $user->id)->orWhere(function (Builder $globalQuery) use ($user): void {
                    $globalQuery->whereNull('user_id')
                        ->where(function (Builder $typeQuery) use ($user): void {
                            $typeQuery->where('type', '!=', 'APPOINTMENT')
                                ->orWhereHas('appointment', fn (Builder $appointmentQuery) => $appointmentQuery->where('employee_id', $user->employee_id));
                        });
                });
            });
        }

        if ($user->isOwnerOrAdmin()) {
            return $query->where(function (Builder $query) use ($user): void {
                $query->whereNull('user_id')->orWhere('user_id', $user->id);
            });
        }

        return $query->where('user_id', $user->id);
    }
}
