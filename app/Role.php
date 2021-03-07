<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use SoftDeletes;

    const ADMIN = 1;
    const OWNER = 2;
    const EMPLOYEE = 3;
}
