<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PagelyMessage extends Model
{
    public $fillable = [
        'subject',
        'from',
        'project',
        'ticket',
        'body',
        'message_date',
    ];
}
