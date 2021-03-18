<?php

namespace App;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'role_id', 'temporary_password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public static function searchUsers($keyword): LengthAwarePaginator
    {
        return self::query()
            ->with('role')
            ->whereRaw('LOWER(name) LIKE (?)', ['%' . strtolower($keyword) . '%'])
            ->orWhereRaw('LOWER(email) LIKE (?)', ['%' . strtolower($keyword) . '%'])
            ->paginate(10);
    }

    public static function getOwners()
    {
        return self::query()
            ->with('role')
            ->where('role_id', '=', Role::OWNER)
            ->get();
    }


    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(UserPermission::class)->with('permission');
    }
}
