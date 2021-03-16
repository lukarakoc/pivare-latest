<?php

namespace App;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Location extends Model
{
    use SoftDeletes, HasTranslations;

    protected $guarded = [];
    public $translatable = ['name', 'description', 'address'];

    public static function searchLocations($keyword): LengthAwarePaginator
    {
        return self::query()->with(['category', 'photos'])
            ->join('location_categories as c', 'locations.location_category_id', '=', 'c.id')
            ->whereRaw('lower(locations.name) like (?)', ['%' . strtolower($keyword) . '%'])
            ->orWhereRaw('lower(locations.description) like (?)', ['%' . strtolower($keyword) . '%'])
            ->orWhereRaw('lower(locations.address) like (?)', ['%' . strtolower($keyword) . '%'])
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
        return $this->belongsTo(LocationCategory::class, 'location_category_id', 'id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(LocationPhoto::class, 'location_id', 'id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id');
    }
}
