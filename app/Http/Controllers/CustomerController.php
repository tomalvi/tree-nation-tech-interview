<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::withCount('visits')
            ->orderBy('trees_planted', 'desc')
            ->get();

        return response()->json($customers);
    }
}
