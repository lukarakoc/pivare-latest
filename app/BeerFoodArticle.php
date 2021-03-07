<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class BeerFoodArticle extends Model
{
    use SoftDeletes, HasTranslations;

    protected $guarded = [];
    public $translatable = ['title', 'text'];

    public function createUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user_id', 'id');
    }

    public function updateUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'update_user_id', 'id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BeerFoodCategory::class, 'beer_food_category_id', 'id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(BeerFoodArticlePhoto::class);
    }
}
