<?php

namespace App;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class BeerFoodQA extends Model
{
    use SoftDeletes, HasTranslations;

    protected $guarded = [];
    public $translatable = ['question', 'answer'];
    protected $table = 'beer_food_qas';

    public static function searchQA($keyword): LengthAwarePaginator
    {
        return self::query()
            ->with('category')
            ->join('')
            ->whereRaw('lower(question) like (?)', ['%' . strtolower($keyword) . '%'])
            ->orWhereRaw('lower(answer) like (?)', ['%' . strtolower($keyword) . '%'])
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

    public function category(): BelongsTo
    {
        return $this->belongsTo(BeerFoodQACategory::class, 'beer_food_qa_category_id', 'id');
    }
}
