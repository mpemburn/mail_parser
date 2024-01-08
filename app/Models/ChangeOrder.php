<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChangeOrder extends Model
{
    use HasFactory;

    public $fillable = [
        'subject',
        'from',
        'project',
        'sponsor',
        'implementer',
        'system_changes',
        'description',
        'effect',
        'reason',
        'change_date',
        'downtime',
        'back_out_plan',
        'communication',
        'comments',
    ];
}
