<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response;
use App\Jobs\getAPIData;

class PeliculasController extends Controller
{
    public function index()
    {
        $apiKey = '77a05aec0e235c4962c40565514582e8';
        $peliculas = [];

        if (empty(Cache::get('peliculas'))) {
            for ($i=1; $i < 6; $i++) { 
                $ch = curl_init();
    
                curl_setopt($ch, CURLOPT_URL, "https://api.themoviedb.org/3/movie/top_rated?api_key=".$apiKey."&language=en-US&page=".$i."&region=ES");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
                $pelis = json_decode(curl_exec($ch));
                curl_close($ch);
    
                foreach ($pelis->results as $indice => $peli) {
                    $peliculas[] = ['title' => $peli->title, 'idMovieDB' => $peli->id];
                }
                
            }
            Cache::put('peliculas', $peliculas);
            $peliculasCache = Cache::get('peliculas');
        } else {
            $peliculasCache = Cache::get('peliculas');
        }

        if (empty(Cache::get('pelisEnRedis'))) {
            getAPIData::dispatch();
        }

        // forma para paginar un array
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($peliculasCache);
        $perPage = 20;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        $paginatedItems->setPath(request()->url());
        // fin

        return view('peliculas', ['peliculas' => $paginatedItems]);
    }

    public function guardarPeliFav()
    {
        // $peliId = request()->id;
        // $peliId = json_encode(request()->all());
        $data = request()->all();
        $userId = auth()->user()->id;

        $peliculaFavGuardada = \App\Pelicula::create($data);
        $pelicula = \App\Pelicula::find($peliculaFavGuardada->id);
        $pelicula->users()->sync($userId);

        $message = 'La pelicula se guardo correctamente en los favoritos';

        return $response = Response::json([
            'message'=> $message,
        ], 200);
    }

    public function deletePeliFav($peliId)
    {
        $pelicula = \App\Pelicula::find($peliId);
        
        $pelicula->delete();

        $pelicula->users()->detach();

        $mensaje = 'Se eliminó la pelicula correctamente';

        return $response = Response::json(['mensaje' => $mensaje], 200);
    }

    public function perfilUsuario()
    {
        $userId = auth()->user()->id;

        $peliculasFavoritas = auth()->user()->peliculas;
        // dd($peliculasFavoritas);
        // forma para paginar un array
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        // $itemCollection = collect($infoPeliculasFav)->sortByDesc('vote_average');
        $peliculasFavoritas = $peliculasFavoritas->sortByDesc('vote_average');
        $perPage = 5;
        $currentPageItems = $peliculasFavoritas->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($peliculasFavoritas), $perPage);
        $paginatedItems->setPath(request()->url());
        // fin

        return view('home', ['infoPeliculasFav' => $paginatedItems]);
    }

    public function filtroView()
    {
        // aca va lo de abajo

        $peliculas = collect(json_decode(Cache::get('pelisEnRedis')[0]));
        $peliculas = $peliculas->sortByDesc('vote_average')->where('vote_average', '>', 8);
        // id de KR = 6384
        $peliculasSinKR = $peliculas->filter(function ($peli) {
            if (collect(json_decode($peli->cast))->where('id', '=', 6384)->isEmpty()) {
                return $peli;
            }
        });
        $peliculasSinKR->all();

        // forma para paginar un array
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $itemCollection = collect($peliculasSinKR);
        $perPage = 20;
        $currentPageItems = $itemCollection->slice(($currentPage * $perPage) - $perPage, $perPage)->all();
        $paginatedItems= new LengthAwarePaginator($currentPageItems , count($itemCollection), $perPage);
        $paginatedItems->setPath(request()->url());
        // fin

        return view('filtradas', ['peliculas' => $paginatedItems]);
    }

    // $peliculas = \App\Pelicula::orderBy('vote_average')->where('vote_average', '>', 8)->get();
        // $peliculasSinKR = [];
        // foreach ($peliculas as $peli) {
        //     $estaKR = false;
        //     $actoresIds = json_decode($peli->castIds);
        //     foreach ($actoresIds as $actorId) {
        //         // id KR 6384
        //         if ($actorId->id === 6384) {
        //             $estaKR = true;
        //         }
        //     }
        //     if (!$estaKR) {
        //         $peliculasSinKR[] = $peli;
        //     }
        // }

    // public function guardarActoresIds($peliId)
    // {
    //     $ids = request()->actoresIds;

    //     $peli = \App\Pelicula::find($peliId)->update(['castIds' => json_encode($ids)]);

    //     $message = 'Los actores se guardaron correctamente';

    //     return $response = Response::json([
    //         'message'=> $message,
    //     ], 200);
    // }
}
