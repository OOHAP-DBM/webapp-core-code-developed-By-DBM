<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class NotificationRedirectController extends Controller
{
    public function open($id)
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            abort(403);
        }

        $notification = $user->notifications()->findOrFail($id);

        $type = (string) data_get($notification->data, 'type', '');
        $isCommissionAgreementNotification = in_array($type, ['commission_set', 'commission_updated'], true);

        if (!$isCommissionAgreementNotification && !$notification->read_at) {
            $notification->markAsRead();
        }

        $url = $this->resolveRedirectUrl($notification, $type, $isCommissionAgreementNotification);

        return redirect($url);
    }

    protected function resolveRedirectUrl($notification, string $type, bool $isCommissionAgreementNotification): string
    {
        if ($isCommissionAgreementNotification) {
            return '/vendor/commission/my-commission';
        }

        if (in_array($type, ['pos_booking_created', 'pos_booking_hold_expired'], true)) {
            $bookingId = (int) data_get($notification->data, 'booking_id');

            if ($bookingId > 0) {
                if (Route::has('pos.bookings.redirect')) {
                    return route('pos.bookings.redirect', ['id' => $bookingId], false);
                }

                return $this->resolveLegacyPosFallback($bookingId);
            }
        }

        $rawUrl = (string) (data_get($notification->data, 'action_url')
            ?? data_get($notification->data, 'actionUrl')
            ?? '/');

        return $this->normalizeNotificationUrl($rawUrl);
    }

    protected function resolveLegacyPosFallback(int $bookingId): string
    {
        $user = Auth::user();

        if ($this->hasAnyRole($user, ['admin', 'superadmin', 'super_admin'])) {
            return '/admin/pos/bookings/' . $bookingId;
        }

        if ($this->hasAnyRole($user, ['vendor'])) {
            return '/vendor/pos/bookings/' . $bookingId;
        }

        if ($this->hasAnyRole($user, ['customer'])) {
            return '/customer/pos/bookings/' . $bookingId;
        }

        return '/';
    }

    protected function normalizeNotificationUrl(string $url): string
    {
        $url = trim($url);

        if ($url === '') {
            return '/';
        }

        if (preg_match('#^https?://#i', $url) === 1) {
            $parts = parse_url($url);

            if (!is_array($parts)) {
                return '/';
            }

            $host = strtolower((string) ($parts['host'] ?? ''));
            $requestHost = strtolower(request()->getHost());
            $isLocalHost = in_array($host, ['localhost', '127.0.0.1', '::1'], true);

            if ($host !== '' && $host !== $requestHost && !$isLocalHost) {
                return '/';
            }

            $path = (string) ($parts['path'] ?? '/');
            $path = $path === '' ? '/' : (str_starts_with($path, '/') ? $path : '/' . $path);
            $normalizedPath = $this->normalizeLegacyPath($path);

            if (!empty($parts['query'])) {
                $normalizedPath .= '?' . $parts['query'];
            }

            if (!empty($parts['fragment'])) {
                $normalizedPath .= '#' . $parts['fragment'];
            }

            return $normalizedPath;
        }

        $path = str_starts_with($url, '/') ? $url : '/' . ltrim($url, '/');

        return $this->normalizeLegacyPath($path);
    }

    protected function normalizeLegacyPath(string $path): string
    {
        if (preg_match('#^/vendor/pos/(\d+)$#', $path, $matches) === 1) {
            return '/vendor/pos/bookings/' . $matches[1];
        }

        if (preg_match('#^/admin/pos/(\d+)$#', $path, $matches) === 1) {
            return '/admin/pos/bookings/' . $matches[1];
        }

        return $path;
    }

    protected function hasAnyRole($user, array $roles): bool
    {
        if (!is_object($user)) {
            return false;
        }

        if (method_exists($user, 'hasAnyRole')) {
            return (bool) $user->hasAnyRole($roles);
        }

        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
