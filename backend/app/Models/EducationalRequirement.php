<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationalRequirement extends Model
{
    public $timestamps = false;

    protected $fillable = ['subject_id', 'code', 'description', 'education_level'];
}
