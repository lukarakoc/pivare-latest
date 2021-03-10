<?php

namespace App;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class BeerQA extends Model
{
    use SoftDeletes, HasTranslations;

    protected $guarded = [];
    protected $table = 'beer_qas';
    public $translatable = ['question', 'answer'];

    public static function searchBeerQA($keyword): LengthAwarePaginator
    {
        return self::query()
            ->with('category')
            ->join('beer_qa_categories as c', 'beer_qas.beer_qa_category_id', '=', 'c.id')
            ->whereRaw('lower(beer_qas.question) like (?)', ['%' . strtolower($keyword) . '%'])
            ->orWhereRaw('lower(beer_qas.answer) like (?)', ['%' . strtolower($keyword) . '%'])
            ->orWhereRaw('lower(c.name) like (?)', ['%' . strtolower($keyword) . '%'])
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
        return $this->belongsTo(BeerQACategory::class, 'beer_qa_category_id', 'id');
    }
}
