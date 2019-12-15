@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="text-center">Perfil de {{auth()->user()->name}}</h2>

    <a href="{{ route('inicio') }}" class="btn btn-info mb-3">Volver</a>
    <h3>Películas favoritas</h3>
    <div class="m-auto">
        {{ $infoPeliculasFav->links() }}
    </div>
    <table class="table">
        <thead class="thead-dark">
        <tr>
            <th scope="col">Puntuación</th>
            <th scope="col">Título</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
            @foreach ($infoPeliculasFav as $peli)
                <tr>
                    <th scope="row">{{$peli->vote_average}}</th>
                    <td>{{$peli->title}}</td>
                    <td>
                        <button type="button" class="btn btn-primary" onclick="mostrarMas({{$peli->idMovieDB}}, {{$peli->id}})">
                            Ver información
                        </button>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="text-center">
        {{ $infoPeliculasFav->links() }}
    </div>
</div>


<script>
    function mostrarMas(movieId, indice){
            
            let apiKey ='77a05aec0e235c4962c40565514582e8';
            let URL = "https://api.themoviedb.org/3/movie/"+ movieId +"?language=en-US&api_key="+ apiKey + "&append_to_response=credits";
            let peliculasRedisCache = JSON.parse({!! json_encode(Cache::get('pelisEnRedis')[0]) !!});
            let infoPelicula = [];
            @auth
                let user = {!! json_encode(auth()->user()) !!}
                const peliculasFavoritas = {!!  json_encode(auth()->user()->peliculas) !!};
                let pelisIds = []
                peliculasFavoritas.forEach(peliFav => {
                    // const info = JSON.parse(peliFav['infoPelicula']);
                    pelisIds.push(peliFav.idMovieDB)
                });
                let tablePeliId;

                if (peliculasRedisCache) {
                    peliculasRedisCache.forEach(peli => {
                        if (peli.idMovieDB === movieId) {
                            myJson = peli
                            infoPelicula = peli;
                            console.log(myJson);
                            
                        }
                    });
                    if (pelisIds.includes(movieId)) {
                        peliculasFavoritas.forEach(peliFav => {
                            if (movieId === peliFav.idMovieDB) {
                                tablePeliId = peliFav.id
                            }
                        });

                        Swal.fire({
                            title: '<h5 class="modal-title" id="exampleModalLabel">' + myJson.title + ' - ' + myJson.release_date + '</h5>',
                            html:
                                '<button type="button" class="chauFav btn btn-danger btn-block mb-3"">' +
                                    'Quitar de favoritos' +
                                '</button>' +
                                '<div class="card mb-3">' +
                                    '<img src="https://image.tmdb.org/t/p/original/'+myJson.poster_path+'" class="card-img-top" alt="...">' +
                                    '<div class="card-body">' +
                                        '<p class="card-text text-muted">' + myJson.overview + '</p>' +
                                        '<p class="card-text">Puntuación ' + myJson.vote_average + '</p>' +
                                        '<p class="card-text">Cantidad de votos: ' + myJson.vote_count +'</p>' +
                                        '<p class="card-text">Popularidad: ' + myJson.popularity +'</p>' +
                                    '</div>' +
                                '</div>',
                            showCloseButton: true,
                            scrollbarPadding: true,
                            showConfirmButton: false
                        })
                            
                    } else {
                        Swal.fire({
                            title: '<h5 class="modal-title" id="exampleModalLabel">' + myJson.title + ' - ' + myJson.release_date + '</h5>',
                            html:
                                '<button type="button" class="btnFav btn btn-primary btn-block mb-3">' +
                                    'Agregar a favoritas' +
                                '</button>' +
                                '<div class="card mb-3">' +
                                    '<img src="https://image.tmdb.org/t/p/original/'+myJson.poster_path+'" class="card-img-top" alt="...">' +
                                    '<div class="card-body">' +
                                        '<p class="card-text text-muted">' + myJson.overview + '</p>' +
                                        '<p class="card-text">Puntuación ' + myJson.vote_average + '</p>' +
                                        '<p class="card-text">Cantidad de votos: ' + myJson.vote_count +'</p>' +
                                        '<p class="card-text">Popularidad: ' + myJson.popularity +'</p>' +
                                    '</div>' +
                                '</div>',
                            showCloseButton: true,
                            scrollbarPadding: true,
                            showConfirmButton: false
                        })
                    }
                } else {
                    // si no hay cache 
                    fetch(URL)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(myJson) {
                        infoPelicula = myJson;
                        if (pelisIds.includes(movieId)) {
                            peliculasFavoritas.forEach(peliFav => {
                                if (movieId === peliFav.idMovieDB) {
                                    tablePeliId = peliFav.id
                                }
                            });

                            Swal.fire({
                                title: '<h5 class="modal-title" id="exampleModalLabel">' + myJson.title + ' - ' + myJson.release_date + '</h5>',
                                html:
                                    '<button type="button" class="chauFav btn btn-danger btn-block mb-3"">' +
                                        'Quitar de favoritos' +
                                    '</button>' +
                                    '<div class="card mb-3">' +
                                        '<img src="https://image.tmdb.org/t/p/original/'+myJson.poster_path+'" class="card-img-top" alt="...">' +
                                        '<div class="card-body">' +
                                            '<p class="card-text text-muted">' + myJson.overview + '</p>' +
                                            '<p class="card-text">Puntuación ' + myJson.vote_average + '</p>' +
                                            '<p class="card-text">Genero principal: ' + myJson.production_companies[0].name + '</p>' +
                                            '<p class="card-text">Producción principal: ' + myJson.genres[0].name + '</p>' +
                                            '<p class="card-text">Cantidad de votos: ' + myJson.vote_count +'</p>' +
                                            '<p class="card-text">Presupuesto: ' + myJson.budget +'</p>' +
                                        '</div>' +
                                    '</div>',
                                showCloseButton: true,
                                scrollbarPadding: true,
                                showConfirmButton: false
                            })
                            
                        } else {
                            Swal.fire({
                                title: '<h5 class="modal-title" id="exampleModalLabel">' + myJson.title + ' - ' + myJson.release_date + '</h5>',
                                html:
                                    '<button type="button" class="btnFav btn btn-primary btn-block mb-3">' +
                                        'Agregar a favoritas' +
                                    '</button>' +
                                    '<div class="card mb-3">' +
                                        '<img src="https://image.tmdb.org/t/p/original/'+myJson.poster_path+'" class="card-img-top" alt="...">' +
                                        '<div class="card-body">' +
                                            '<p class="card-text text-muted">' + myJson.overview + '</p>' +
                                            '<p class="card-text">Puntuación ' + myJson.vote_average + '</p>' +
                                            '<p class="card-text">Genero principal: ' + myJson.production_companies[0].name + '</p>' +
                                            '<p class="card-text">Producción principal: ' + myJson.genres[0].name + '</p>' +
                                            '<p class="card-text">Cantidad de votos: ' + myJson.vote_count +'</p>' +
                                            '<p class="card-text">Presupuesto: ' + myJson.budget +'</p>' +
                                        '</div>' +
                                    '</div>',
                                showCloseButton: true,
                                scrollbarPadding: true,
                                showConfirmButton: false
                            })
                        }                        
                    });
                    // hasta aca sin cache
                }

                // botones agregar y quitar de fav
                setTimeout(() => {
                    let swalConteiner = document.querySelector('.swal2-container');
                    swalConteiner.setAttribute('style', 'overflow-y: auto;')

                    if (document.querySelector('.chauFav')) {
                        let chauFav = document.querySelector('.chauFav');
                        chauFav.addEventListener('click', () => {
                            console.log(tablePeliId);
                            
                            var deleteUrl = '/deletePeliFavorita/'+tablePeliId;

                            fetch(deleteUrl, {
                                method: 'DELETE', // or 'PUT'
                                headers:{
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            }).then(res => res.text())
                            .catch(error => console.error('Error:', error))
                            .then(response => {
                                console.log(response);

                                setTimeout(() => {
                                    window.location.reload()
                                }, 500);
                            });
                            
                        })
                    }
                }, 1000);
            @else
            fetch(URL)
                .then(function(response) {
                    return response.json();
                })
                .then(function(myJson) {
                    Swal.fire({
                        title: '<h5 class="modal-title" id="exampleModalLabel">' + myJson.title + ' - ' + myJson.release_date + '</h5>',
                        html:
                            '<a href="{{ route('login') }}" class="btn btn-primary btn-block mb-3">' +
                                'Agregar a favoritas' +
                            '</a>' +
                            '<div class="card mb-3">' +
                                '<img src="https://image.tmdb.org/t/p/original/'+myJson.poster_path+'" class="card-img-top" alt="...">' +
                                '<div class="card-body">' +
                                    '<p class="card-text text-muted">' + myJson.overview + '</p>' +
                                    '<p class="card-text">Puntuación ' + myJson.vote_average + '</p>' +
                                    '<p class="card-text">Genero principal: ' + myJson.production_companies[0].name + '</p>' +
                                    '<p class="card-text">Producción principal: ' + myJson.genres[0].name + '</p>' +
                                    '<p class="card-text">Cantidad de votos: ' + myJson.vote_count +'</p>' +
                                    '<p class="card-text">Presupuesto: ' + myJson.budget +'</p>' +
                                '</div>' +
                            '</div>',
                        showCloseButton: true,
                        scrollbarPadding: true,
                        showConfirmButton: false
                    })

                    
                });
            @endauth
        }
</script>
@endsection
