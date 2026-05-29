<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Visit;
use Illuminate\Support\Carbon;

class VisitController extends Controller
{
     public function store(Request $request)
    {

        // $request->validate([
        //     'customer_id' => 'required|exists:customers,id'
        // ]);


        $customer = Customer::find($request->customer_id);


        Visit::create([
            'customer_id' => $customer->id,
            'visited_at'  => Carbon::now()
        ]);

        $customer->last_visit_at = Carbon::now();

        $visitsPerTree = config('app.visits_per_tree', 5);
        $totalVisits = $customer->visits()->count();

        if ($totalVisits % $visitsPerTree === 0) {
            $customer->trees_planted++;
        }

        $customer->save();

        return response()->json([
            'message'       => 'Visit registered',
            'total_visits'  => $totalVisits,
            'trees_planted' => $customer->trees_planted
        ], 201);
    }

    public function hourly()
    {
        $visits = Visit::selectRaw('HOUR(visited_at) as hour, COUNT(*) as total')
            ->whereDate('visited_at', Carbon::today())
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return response()->json($visits);
    }


}
