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

        // inicio un array vacio para llenarlo con los datos de las peliculas que obtenga del cURL
        $peliculas = [];

        // compruebo que no esten las peliculas en el cache para asi no realizar la llamada repetidamente
        if (empty(Cache::get('peliculas'))) {
            for ($i=1; $i < 6; $i++) { 
                $ch = curl_init();
    
                curl_setopt($ch, CURLOPT_URL, "https://api.themoviedb.org/3/movie/top_rated?api_key=".$apiKey."&language=en-US&page=".$i."&region=ES");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    
                $pelis = json_decode(curl_exec($ch));
                curl_close($ch);
    
                // lleno el array de peliculas con la data basica que necesito
                foreach ($pelis->results as $indice => $peli) {
                    $peliculas[] = ['title' => $peli->title, 'idMovieDB' => $peli->id];
                }
                
            }
            // guardo el array de el cache
            Cache::put('peliculas', $peliculas);
            // obtengo la data del cache en una variable
            $peliculasCache = Cache::get('peliculas');
        } else {
            // si no hace la llamada cURL obtengo la data del cache en una variable
            $peliculasCache = Cache::get('peliculas');
        }

        // en segundo plano, si el cache pelisEnRedis esta vacio, con Queue, ejecuto llamadas cURL para obtener en el cacho la data de las peliculas
        // mas los actores para mas adelante
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
        // a traves de una llamada POST con fetch desde JS mando los datos de la pelicula

        // requiero los datos
        $data = request()->all();
        // obtengo el id del usuario
        $userId = auth()->user()->id;

        // creo una pelicula y la guardo en la base para poder generar la relacion
        $peliculaFavGuardada = \App\Pelicula::create($data);
        // busco la pelicula guardada
        $pelicula = \App\Pelicula::find($peliculaFavGuardada->id);
        // guardo la relacio
        $pelicula->users()->sync($userId);

        // envio un mensaje por consola
        $message = 'La pelicula se guardo correctamente en los favoritos';

        // envio la respuesta
        return $response = Response::json([
            'message'=> $message,
        ], 200);
    }

    public function deletePeliFav($peliId)
    {
        // a traves de una llamada DELETE con fetch desde JS elimino la pelicula favorita

        // obtengo la pelicula con el id pasado por parametro
        $pelicula = \App\Pelicula::find($peliId);
        
        // elimino la pelicula
        $pelicula->delete();

        // elimino la relacion
        $pelicula->users()->detach();

        // envio un mensaje por consola
        $mensaje = 'Se eliminó la pelicula correctamente';

        // envio una respuesta
        return $response = Response::json(['mensaje' => $mensaje], 200);
    }

    public function perfilUsuario()
    {
        // obtengo el id del usaurio que esta autenticado
        $userId = auth()->user()->id;

        // obtengo las peliculas favoritas de ese usuario
        $peliculasFavoritas = auth()->user()->peliculas;

        // forma para paginar un array
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
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

        // obtengo, del cache, las peliculas con todos sus datos, incluidos los actores
        $peliculas = collect(json_decode(Cache::get('pelisEnRedis')[0]));

        // ordeno las peliculas por votacion y que estas sean mayores a 8
        $peliculas = $peliculas->sortByDesc('vote_average')->where('vote_average', '>', 8);

        // ejecuto la funcion filter para obtener SOLO las peliculas en las que no actura
        // Keanu Reeves (KR)
        // id de KR = 6384
        $peliculasSinKR = $peliculas->filter(function ($peli) {
            // si en los actores de la pelicula no se encuentra el id de KR, devuelvo la pelicula
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
}
