<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class BeerQACategory extends Model
{
    use SoftDeletes, HasTranslations;

    protected $guarded = [];
    protected $table = 'beer_qa_categories';
    public $translatable = ['name', 'description'];

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
        return $this->hasMany(BeerQA::class);
    }
}
