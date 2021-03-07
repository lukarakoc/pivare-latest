<?php

namespace App;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Beer extends Model
{
    use SoftDeletes, HasTranslations;

    protected $guarded = [];
    public $translatable = ['name', 'description'];

    public static function searchBeers($keyword): LengthAwarePaginator
    {
        return self::query()
            ->whereRaw('lower(name) like (?)', ['%' . strtolower($keyword) . '%'])
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

    public function photos(): HasMany
    {
        return $this->hasMany(BeerPhoto::class);
    }
}
