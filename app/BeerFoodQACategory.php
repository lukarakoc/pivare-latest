<?php

namespace App;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class BeerFoodQACategory extends Model
{
    use SoftDeletes, HasTranslations;

    protected $guarded = [];
    protected $table = 'beer_food_qa_categories';
    public $translatable = ['name', 'description'];

    public static function searchCategories($keyword): LengthAwarePaginator
    {
        return self::query()
            ->whereRaw('lower(name) like (?)', ['%' . strtolower($keyword) . '%'])
            ->orWhereRaw('lower(description) like (?)', ['%' . strtolower($keyword) . '%'])
            ->paginate(10);
    }

    public function createUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user_id', 'id');
    }

    public function updateUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'update_user_id', 'id');
    }

    public function qas(): HasMany
    {
        return $this->hasMany(BeerFoodQA::class);
    }
}
