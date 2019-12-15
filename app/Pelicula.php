<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Pelicula extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        'idMovieDB',
        'title',
        'posicion_toprated',
        'popularity',
        'vote_count',
        'video',
        'poster_path',
        'adult',
        'backdrop_path',
        'original_language',
        'original_title',
        'vote_average',
        'overview',
        'release_date',
        'cast'
    ];
    protected $dates = ['deleted_at', 'created_at', 'updated_at'];
    
    public function users()
	{
        return $this->belongsToMany('\App\User');
    }

}
