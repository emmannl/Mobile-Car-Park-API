<?php


namespace App\Http\Controllers\API;


use App\Classes\Helper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Cloudder;

class UserProfileController
{
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Show user's profile info
     * @return \Illuminate\Http\JsonResponse
     */
    public function show()
    {
        return response()->json([
            'data' => $this->user,
        ]);
    }

    /**
     * Update user's profile
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            'phone' => [Rule::requiredIf(function (){
                $role = $this->user->role;
                return ($role == 'partner' || $role == 'user');
            }), 'phone:NG', Rule::unique('users')->ignore($this->user->id)],
            'first_name' => ['required', 'string', 'min:3', 'max:255'],
            'last_name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($this->user->id)],
        ]);

        try {
            $this->user->update($data);

            return response()->json(['message' => 'Profile updated', 'data' => $data]);
        } catch (\Exception $e) {
            Helper::logException($e, "Error updating User Profile");
            return response()->json(['message' => $e->getMessage()], 501);
        }
    }

    /**
     * Update user password
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
           'old_password' => ['required', function($attribute, $value, $fail) use($request) {
                if (!Hash::check($request->old_password, $this->user->password)) {
                    $fail('The old password submitted does not match the current password. Try again or reset your password.');
                }
           }],
            'new_password' => ['required', 'string', 'confirmed', 'min:6', 'max:72'],
        ]);

        $update = $this->user->update([
            'password' => Hash::make($request->new_password),
        ]);

        if (! $update) {
            return response()->json(['message' => 'An error was encountered.'], 501);
        }

        return response()->json(['message' => 'Your password has been updated']);
    }

    /**
     * Update notifications and tracking settings
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function manageProfile(Request $request)
    {
        $data = [
            'app_notifications' => $request->filled('app_notifications'),
            'push_notifications' => $request->filled('push_notifications'),
            'location_tracking' => $request->filled('location_tracking')
        ];

        // remove items not filled in the request
        array_walk($data, function ($item, $key) use (&$data) {
            if ($item == false) unset($data[$key]);
        });

        $this->user->settings()->updateOrCreate($data);

        return response()->json(['message' => 'Settings Updated', 'data' => $data]);
    }

    /**
     * Change profile picture
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editImage(Request $request)
    {
        if (!$request->hasFile('picture')) {
            return response()->json(['message' => 'The picture is missing from the request'], 400);
        }

//        if ($result)

        $name = "car-app/avatar_{$this->user->id}";
        Cloudder::upload($request->file('picture'), $name);
        $result = Cloudder::getResult();

        $this->user->update(['avatar_url' => $result['secure_url']]);

        return response()->json([
            'data' => [
                'avatar_url' => $result['secure_url'],
            ],
        ]);
    }
}
