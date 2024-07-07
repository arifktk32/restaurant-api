<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'dish_id', 'rating'];

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function dish() : BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }
}
