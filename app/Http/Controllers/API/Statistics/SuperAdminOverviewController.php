<?php


namespace App\Http\Controllers\API\Statistics;


use App\CarPark;
use App\User;

class SuperAdminOverviewController
{
    public function __invoke()
    {
        $users = User::count();
        $new_users = User::query()->where('created_at', '>', now()->subWeek()->toDateTimeString())->count();
        $issues = 0;
        $parks = $this->getParkData();

        return response()->json(compact('users', 'new_users', 'issues', 'parks'));
    }

    private function getParkData()
    {
        $parks = CarPark::all();
        return [
            'count' => $parks->count(),
            'data' => $parks,
        ];
    }

}
