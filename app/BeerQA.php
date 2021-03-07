<?php

namespace App;

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
        return $this->belongsTo(BeerQACategory::class, 'beer_qa_category', 'id');
    }
}
