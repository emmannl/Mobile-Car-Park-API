<?php


namespace App\Notifications;


use App\User;

trait ResolveChannelsTrait
{
    /**
     * @param User $user
     * @return array
     */
    private function resolveChannel(User $user)
    {
        $settings = $user->settings;
        if (empty($settings)) {
            return ['database'];
        }

        $channels = [];
        if ($settings->app_notifications) {
            $channels[] = 'database';
        }

        if ($settings->push_notifications) {
            $channels[] = 'fcm';
        }
        return $channels;
    }
}
