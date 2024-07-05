<?php

namespace App\Http\Controllers;

use App\Models\Dish;
use App\Http\Requests\StoreDishRequest;
use App\Http\Requests\UpdateDishRequest;
use App\Http\Resources\DishResource;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DishController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDishRequest $request)
    {
        $this->authorize('create', Dish::class);

        DB::beginTransaction();

        try {
            $dish = $request->user()
                ->dishes()
                ->create($request->validated());
            
            DB::commit();

            return new DishResource($dish);
        } catch(\Exception $e)  {
            DB::rollBack();

            Log::error("Error creating the dish: {$e->getMessage()}");

            return response()->json([
                'error' => 'Error creating the dish.',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Dish $dish)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishRequest $request, Dish $dish)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dish $dish)
    {
        //
    }
}
