<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Location extends Model
{
    use SoftDeletes, HasTranslations;

    protected $guarded = [];
    public $translatable = ['name', 'description', 'address'];

    public function createUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user_id', 'id');
    }

    public function updateUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'update_user_id', 'id');
    }

    public function category()
    {
        return $this->belongsTo(LocationCategory::class, 'location_category_id', 'id');
    }
}
