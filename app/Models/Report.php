<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    // ---------------------------------------------------------------------- //

    // protected $attributes = [
    //     //
    // ];

    // protected $fillable = [
    //     //
    // ];

    // protected $hidden = [
    //     //
    // ];

    protected $casts = [
        'data' => 'array',
    ];

    // protected $appends = [
    //     //
    // ];

    // Attributes
    // ---------------------------------------------------------------------- //

    // put here getSomeAttribute & setSomeAttribute methods

    // Scopes
    // ---------------------------------------------------------------------- //

    // put here different scopes here to use them to build query

    // Relations
    // ---------------------------------------------------------------------- //

    // put here relation methods

    // Non-static methods
    // ---------------------------------------------------------------------- //

    // put here non-static methods

    // Static methods
    // ---------------------------------------------------------------------- //

    // public static function create(array $data)
    // {
    //     $thing = new self;
    //     $thing->fill($data);
    //     $thing->save();

    //     return $thing;
    // }
}
