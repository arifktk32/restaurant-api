<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchDishesRequest;
use App\Models\Dish;
use App\Http\Requests\StoreDishRequest;
use App\Http\Requests\UpdateDishRequest;
use App\Http\Resources\DishResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DishController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(SearchDishesRequest $request): ResourceCollection
    {
        $query = Dish::query();

        if($request->filled('name')) {
            $query->where('name', 'like', "%{$request->input('name')}%");
        }

        if($request->filled('description')) {
            $query->where('description', 'like', "%{$request->input('description')}%");
        }
        
        $limit = $request->input('limit', 15);
        $offset = $request->input('offset', 1);

        $dishes = $query->paginate($limit, ['*'], 'page', $offset);

        return DishResource::collection($dishes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDishRequest $request): JsonResource|JsonResponse
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
    public function show($id): JsonResource|JsonResponse
    {
        try {
            $dish = Dish::findOrFail($id);
    
            return new DishResource($dish);
        } catch (ModelNotFoundException $e) {
            Log::error("DishController::show() A Dish with the specified ID was not found.", [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Dish not found.'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDishRequest $request, Dish $dish): JsonResource
    {
        $this->authorize('update', $dish);
        
        $dish->update($request->validated());

        return new DishResource($dish);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Dish $dish)
    {
        //
    }
}
