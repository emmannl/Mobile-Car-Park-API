<?php

namespace App\Http\Controllers\API;

use Exception;
use App\CarPark;
use App\User;
use Carbon\Carbon;
use App\CarParkBooking;
use App\CarParkHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CarParkBookingController extends Controller
{
    /**
     * Gets authenticated user's data
     *
     * @return App\User
     */
    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Method to schedule a booking
     *
     * @param $id - The car park identifier
     * @return \Illuminate\Http\Response
     */
    public function __invoke($id, Request $request)
    {
    	// Get the parking space
		$parking_space = CarPark::find($id);

		// Check if such a parking space exist
		if (!$parking_space) {
			return response()->json(['message' => 'Parking Space Not Found!'], 404);
		}

		// Validate posted request
		$this->validate($request, [
            'check_in'	  => ['required', 'date'],
            'check_out'	  => ['required', 'date'],
            'vehicle_no'  => ['required', 'string'],
        ]);

        DB::beginTransaction();

        try {
			// Save the record of the booking against the user
        	$booking = new CarParkBooking;

        	$booking->car_park_id 	= $id;
        	$booking->user_id 		= $this->user->id;
        	$booking->check_in 		= $request->check_in;
        	$booking->check_out 	= $request->check_out;
        	$booking->vehicle_no 	= $request->vehicle_no;
        	$booking->amount 		= $parking_space->fee;
        	$booking->status 		= 1;

        	if (!$booking->save()) {
        		throw new Exception;
        	}
        	else {
	            // Transaction was successful
	            DB::commit();

                // Prepare a formated response
                $booking = [
                    'booking_id'    => $booking->id,
                    'user_id'       => $booking->user_id,
                    'check_in'      => $booking->check_in,
                    'check_out'     => $booking->check_out,
                    'vehicle_no'    => $booking->vehicle_no,
                    'amount'        => $booking->amount,
                    'created_at'    => $booking->created_at,
                ];

                $parking_space = [
                    'name'      => $parking_space->name,
                    'owner'     => $parking_space->owner,
                    'address'   => $parking_space->address,
                    'phone'     => $parking_space->phone,
                    'image_link'=> $parking_space->image_link,
                    'status'    => $parking_space->status == 1 ? "activated" : "deactivated",
                ];

	            // Send response
	            return response()->json([
	                'status'  => true,
	                'message' => 'Car Park has been booked successfully',
	                'result'  => $booking,
                    'parking_space' => $parking_space
	            ], 200);
	        }
        } catch(Exception $e) {
            // Transaction was not successful
            DB::rollBack();

            return response()->json([
                'status'  => false,
	            'message' => 'Unable to book parking space',
                'hint'    => $e->getMessage()
            ], 501);
		}
    }

    /**
     * Update a booking
     *
     * @param $id - The car park identifier
     * @return \Illuminate\Http\Response
     */
    public function update($id, Request $request)
    {
    	// Verify that such a booking exists
    	$booking = CarParkBooking::find($id);

    	if($booking) {
    		// Ensure that only the right user can update the booking
    		if($this->user->id != $booking->user_id) {
	            return response()->json([
	                'status'  => false,
		            'message' => 'Unauthorized!'
	            ], 501);
    		}
    		else {
				// Validate posted request
				$this->validate($request, [
		            'check_in'	  => ['date'],
		            'check_out'	  => ['date'],
		            'vehicle_no'  => ['string'],
		            'status'	  => ['integer'],
		        ]);

		        DB::beginTransaction();

		        try {
		        	$booking->check_in 		= $request->check_in ?? $booking->check_in;
		        	$booking->check_out 	= $request->check_out ?? $booking->check_out;
		        	$booking->vehicle_no 	= $request->vehicle_no ?? $booking->vehicle_no;
		        	$booking->amount 		= CarPark::find($booking->car_park_id)->pluck('fee')->first();
		        	$booking->status 		= $request->status ?? $booking->status;

		        	if (!$booking->save()) {
		        		throw new Exception;
		        	}
		        	else {
			            // transaction was successful
			            DB::commit();

			            // send response
			            return response()->json([
			                'status'  => true,
			                'message' => 'Booking has been successfully updated',
			                'result'  => $booking
			            ], 200);
		        	}
		        } catch(Exception $e) {
		            // transaction was not successful
		            DB::rollBack();

		            return response()->json([
		                'status'  => false,
			            'message' => 'Unable to update booking',
		                'hint'    => $e->getMessage()
		            ], 501);
		        }
    		}
    	}
    }

    /**
     * Gets all bookings for a park
     * assigned to the logged in user (admin user)
     *
     * @return \Illuminate\Http\Response
     */
    // public function carParksBooking()
    // {
    //     // Get the user id
    //     $user_id = $this->user->id;

    //     // Check if the admin-user has a park; and get it's bookings
    //     $bookings = CarPark::whereUserId($user_id)
    //     	->join('car_park_bookings', 'car_park_bookings.car_park_id', 'car_parks.id')
    //     	->get('car_park_bookings.*');
        
    //     // Send response
    //     return response()->json([
    //         'status' => true,
    //         'count'  => $bookings->count(),
    //         'result' => $bookings,
    //     ], 200);
    // }

    /**
     * Get all car parks' bookings for the super-admin user
     *
     */
    public function showSuperBookings()
    {
        // Get the intended resource
        $bookings =  CarParkBooking::all();

        if ($bookings->isNotEmpty()) {
            // Output details
            return response()->json([
                'count'   => $bookings->count(),
                'status'  => true,
                'result'  => $bookings
            ], 200);
        }
        else {
            return response()->json([
                'status'  => false,
                'message' => 'There were no bookings found for a car park'
            ], 404);
        }
    }

    /**
     * Get all bookings for a single car park
     * for a SA consumption
     *
     */
    public function showSuperSingleBooking($park_id, CarPark $park)
    {
        $park = $park->find($park_id);

        // Check if such a car park do exists
        if (!$park) {
            return response()->json([
                'status'  => false,
                'message' => 'Car park not found'
            ], 404);
        }
        else {
            // Get the intended resource
            $bookings =  CarParkBooking::whereCarParkId($park_id)->get();

            if ($bookings->isNotEmpty()) {
                // Output details
                return response()->json([
                    'count'   => $bookings->count(),
                    'status'  => true,
                    'result'  => $bookings
                ], 200);
            }
            else {
                return response()->json([
                    'status'  => false,
                    'message' => 'There were no bookings found for the car park'
                ], 404);
            }
        }
    }

    /**
     * Get all current car parks' bookings
     *
     */
    public function superCurrent()
    {
        // Get current time
        $current_time = Carbon::now()->toDateTimeString();

        // Get the intended resource
        $bookings =  CarParkBooking::where('check_out', '>=', $current_time)->get();

        if ($bookings->isNotEmpty()) {
            // Output details
            return response()->json([
                'count'   => $bookings->count(),
                'status'  => true,
                'result'  => $bookings
            ], 200);
        }
        else {
            return response()->json([
                'status'  => false,
                'message' => 'There are no bookings found for a car park'
            ], 404);
        }
    }

    /**
     * Get all old/past car parks' bookings
     *
     */
    public function superHistory(CarPark $park)
    {
        // Get current time
        $current_time = Carbon::now()->toDateTimeString();

        // Get the intended resource
        $bookings =  CarParkBooking::where('check_out', '<=', $current_time)->get();

        if ($bookings->isNotEmpty()) {
            // Output details
            return response()->json([
                'count'   => $bookings->count(),
                'status'  => true,
                'result'  => $bookings
            ], 200);
        }
        else {
            return response()->json([
                'status'  => false,
                'message' => 'No past scheduled booking was found'
            ], 404);
        }
    }

    /**
     * Get all current car parks' bookings for a single car park
     * assigned to the admin user
     *
     */
    public function carParkCurrent($park_id = null, CarPark $park)
    {
        // Get current time
        $current_time = Carbon::now()->toDateTimeString();

        // Get the intended resource
        if (!is_null($park_id)) {
            $bookings =  $this->user->parks()->find($park_id)->bookings()->where('check_out', '>=', $current_time)->get();
        }
        else {
            $bookings = $this->user->bookings()->where('check_out', '>=', $current_time)->get();
        }

        if ($bookings->isNotEmpty()) {
            // Output details
            return response()->json([
                'count'   => $bookings->count(),
                'status'  => true,
                'result'  => $bookings
            ], 200);
        }
        else {
            return response()->json([
                'status'  => false,
                'message' => 'There are no current scheduled bookings'
            ], 404);
        }
    }

    /**
     * Get all current car parks' bookings for a single car park
     *
     */
    public function carParkHistory($park_id = null, CarPark $park)
    {
        // Get current time
        $current_time = Carbon::now()->toDateTimeString();

        // Get the intended resource
        if (!is_null($park_id)) {
            $bookings =  $this->user->parks()->find($park_id)->bookings()->where('check_out', '<=', $current_time)->get();
        }
        else {
            $bookings = $this->user->bookings()->where('check_out', '<=', $current_time)->get();
        }

        if ($bookings->isNotEmpty()) {
            // Output details
            return response()->json([
                'count'   => $bookings->count(),
                'status'  => true,
                'result'  => $bookings
            ], 200);
        }
        else {
            return response()->json([
                'status'  => false,
                'message' => 'No past scheduled booking was found'
            ], 404);
        }
    }

    /**
     * Get all consumers for a selected car park
     *
     */
    public function getUsers($park_id, CarParkBooking $booking, CarPark $park)
    {
        // Check if the car park exists and the admin is assigned to it
        $car_park = $this->user->parks()->find($park_id);

        if (is_null($car_park)) {
            return response()->json([
                'status'  => false,
                'message' => 'Unknown error',
                'hint'    => 'Failed due to inexistent record or insufficient write permission on the record'
            ], 404);
        }
        else {
            $car_park_consumers = $booking->join('users', 'users.id', 'car_park_bookings.user_id')
                                        ->where('car_park_bookings.car_park_id', $park_id)
                                        ->get(['car_park_bookings.car_park_id', 'users.*']);

            if ($car_park_consumers->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => "At the moment, there are no consumers for your car park: {$car_park->name}"
                ], 404);
            }
            else {
                return response()->json([
                    'status'  => true,
                    'count'   => $car_park_consumers->count(),
                    'result'  => $car_park_consumers,
                ], 200);
            }
        }
    }

    /**
     * Get all bookings for a single car park
     * assigned to the admin user
     *
     */
    public function carParksBooking($park_id, CarPark $park)
    {
        // Verify that the admin user is actually assigned to the car park
        $car_park = $this->user->parks()->find($park_id);

        if (!$car_park) {
            return response()->json([
                'status'  => false,
                'message' => 'This car park does not exist or, you are not assigned as its owner'
            ], 404);
        }
        else {
            $bookings = $car_park->bookings()->get();
            // $bookings = $booking->whereCarParkId($car_park->id)->get();

            if ($bookings->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'There are no bookings for your car park'
                ], 404);
            }
            else {
                return response()->json([
                    'status'  => true,
                    'count'   => $bookings->count(),
                    'result'  => $bookings,
                ], 200);
            }
        }
    }

    /**
     * Revoke a booking
     *
     */
    public function revoke($booking_id, CarPark $park, CarParkBooking $booking)
    {
        // Check if such a booking has been made
        $check_booking = $booking->find($booking_id);

        if (!$check_booking) {
            return response()->json([
                'status'  => false,
                'message' => 'The booking id could not be found'
            ], 404);
        }
        else {
            // Get all car parks id under the admin
            $car_park_ids = array_column($this->user->parks()->get()->toArray(), 'id');

            // Verify that the booking was made to the admin user's car park
            if (in_array($check_booking->car_park_id, $car_park_ids)) {
                // delete the booking
                if ($check_booking->delete()) {
                    return response()->json([
                        'status'  => true,
                        'message' => 'The booking has been successfully revoked'
                    ], 200);
                }
                else {
                    return response()->json([
                        'status'  => false,
                        'message' => 'The revoke was rejected by the server'
                    ], 501);
                }
            }
            else {
                return response()->json([
                    'status'  => false,
                    'message' => 'The booking id is not associated to you (admin user)'
                ], 404);
            }
        }
    }
}