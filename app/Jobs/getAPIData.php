<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class getAPIData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->getData();
    }

    public function getData()
    {     
        $apiKey = '77a05aec0e235c4962c40565514582e8';

        // inicio un array vacio para cargarlo con la informacion completa de las peliculas
        $peliculas = [];
        // llamada cURL para obtener las 100 mejores peliculas
        for ($i=1; $i < 6; $i++) { 
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, "https://api.themoviedb.org/3/movie/top_rated?page=".$i."&language=en-US&api_key=".$apiKey."&region=ES");

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $pelis = json_decode(curl_exec($ch));
            curl_close($ch); 
            foreach ($pelis->results as $indice => $peli) {
                // obtengo y guardo los id de los actores de cada pelicula en un string para dsp poder filtrarlos
                $ch = curl_init();

                curl_setopt($ch, CURLOPT_URL, "https://api.themoviedb.org/3/movie/". $peli->id ."?language=en-US&api_key=". $apiKey . "&append_to_response=credits");

                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                $peliConActor = json_decode(curl_exec($ch));

                curl_close($ch); 

                // guardo toda la informacion obtenida en el array $data
                $data = [
                    'idMovieDB' => $peliConActor->id,
                    'title' => $peliConActor->title,
                    'posicion_toprated' => $indice,
                    'popularity' => $peliConActor->popularity,
                    'vote_count' => $peliConActor->vote_count,
                    'video' => $peliConActor->video,
                    'poster_path' => $peliConActor->poster_path,
                    'adult' => $peliConActor->adult,
                    'backdrop_path' => $peliConActor->backdrop_path,
                    'original_language' => $peliConActor->original_language,
                    'original_title' => $peliConActor->original_title,
                    'vote_average' => $peliConActor->vote_average,
                    'overview' => $peliConActor->overview,
                    'release_date' => $peliConActor->release_date,
                    'cast' => json_encode($peliConActor->credits->cast),
                ];

                // guardo, en cada posicion, la data de cada pelicula
                $peliculas[] = $data;
            }
        }
        // Guardo la data
        $this->saveData($peliculas);
    }

    public function saveData($peliculas)
    {
        // guardo la info en el cache, esto se ejecuta en segundo plano
        $pelisEnRedis = [];
        foreach ($peliculas as $peli) {
            $pelisEnRedis[] = $peli;
        }
        Cache::put('pelisEnRedis', [json_encode($pelisEnRedis)]);

        return;
    }
}
