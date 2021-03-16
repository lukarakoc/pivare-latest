<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class LocationDiscount extends Model
{
    use SoftDeletes, HasTranslations;

    protected $guarded = [];
    public $translatable = ['name', 'description'];

    public function createUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'create_user_id', 'id');
    }

    public function updateUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'update_user_id', 'id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    public function setDateToAttribute($value)
    {
        $this->attributes['date_to'] = Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
    }

    public function getDateToAttribute()
    {
        return Carbon::createFromFormat('Y-m-d', $this->attributes['date_to'])->format('d.m.Y');
    }

    public function setDateFromAttribute($value)
    {
        $this->attributes['date_from'] = Carbon::createFromFormat('d.m.Y', $value)->format('Y-m-d');
    }

    public function getDateFromAttribute()
    {
        return Carbon::createFromFormat('Y-m-d', $this->attributes['date_from'])->format('d.m.Y');
    }
}
