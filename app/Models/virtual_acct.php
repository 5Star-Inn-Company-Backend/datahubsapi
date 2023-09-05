<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class virtual_acct extends Model
{
    use HasFactory;

protected $fillable = [
    'user_id',
    'account_name',
    'account_number',
    'provider',
    'domain',
    'reference',
    'assignment',
    'status',
];
}
