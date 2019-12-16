@extends('layouts.app')
@section('content')
    <div class="container">
        <h2 class="text-center">Las mejores películas sin Keanu Reeves</h2>
        <a href="{{ route('inicio') }}" class="btn btn-info mb-3">Volver</a>
        <div class="m-auto">
            {{ $peliculas->links() }}
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
                @foreach ($peliculas as $indice => $peli)
                    <tr>
                        <th scope="row">{{$peli->vote_average}}</th>
                        <td>{{$peli->title}}</td>
                        <td>
                            <button type="button" class="btn btn-primary" onclick="mostrarMas({{$peli->idMovieDB}}, {{$indice + 1}})">
                                Ver información
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="text-center">
            {{ $peliculas->links() }}
        </div>
    </div>
    
    <script>
        
        // al clickear el boton Ver informacion, se ejecuta la funcion mostrarMas donde paso como parametros
        // el id de TMDB de la pelicula y el indice de la misma
        function mostrarMas(movieId, indice){
            
            let apiKey ='77a05aec0e235c4962c40565514582e8';
            let URL = "https://api.themoviedb.org/3/movie/"+ movieId +"?language=en-US&api_key="+ apiKey + "&append_to_response=credits";
            // obtengo del cache la data completa de las pelicuals
            let peliculasRedisCache = JSON.parse({!! json_encode(Cache::get('pelisEnRedis')[0]) !!});
            let infoPelicula = [];

            @auth
                // si el usuario esta logueado
                let user = {!! json_encode(auth()->user()) !!}
                const peliculasFavoritas = {!!  json_encode(auth()->user()->peliculas) !!};
                let pelisIds = []
                peliculasFavoritas.forEach(peliFav => {
                    pelisIds.push(peliFav.idMovieDB)
                });
                let tablePeliId;

                // si el cache ya esta cargado, existe
                if (peliculasRedisCache) {
                    // obtengo la informacion de la pelicula clickeada
                    peliculasRedisCache.forEach(peli => {
                        if (peli.idMovieDB === movieId) {
                            myJson = peli
                            infoPelicula = peli;
                        }
                    });

                    // compruebo si la pelicula clickeada ya es una pelicula favorita
                    if (pelisIds.includes(movieId)) {
                        // si es una pelicula favorita, doy la opcion de Quitar de los favoritos
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
                        // si no es una pelicula favorita, doy la opcion de Agregar a favoritos
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
                    // si el cache no esta cargado, no existe
                    // realizo una llamada fetch para que se pueda ejecutar la aplicacion correctamente
                    fetch(URL)
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(myJson) {
                        // obtengo la informacion de la pelicula clickeada
                        infoPelicula = myJson;
                        infoPelicula.idMovieDB = movieId;
                        infoPelicula.posicion_toprated = (indice + 1);
                        // compruebo si la pelicula clickeada ya es una pelicula favorita
                        if (pelisIds.includes(movieId)) {
                            // si es una pelicula favorita, doy la opcion de Quitar de los favoritos
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
                            // si no es una pelicula favorita, doy la opcion de Agregar a favoritos
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

                    if (document.querySelector('.btnFav')) {
                        let btnFav = document.querySelector('.btnFav');
                        btnFav.addEventListener('click', () => {
                            const url = '/guardarPeliFavorita';

                            fetch(url, {
                                method: 'POST',
                                body: JSON.stringify(infoPelicula),
                                credentials: "same-origin",
                                headers:{
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            }).then(res => res.text())
                            .catch(error => console.error('Error:', error))
                            .then(response => {
                                console.log('Success:', response)
                                setTimeout(() => {
                                    // recargo la pagina
                                    window.location.reload()
                                }, 500);
                            });
                        })
                    }
                    

                    if (document.querySelector('.chauFav')) {
                        let chauFav = document.querySelector('.chauFav');
                        chauFav.addEventListener('click', () => {

                            // envio el id de la pelicula por parametro
                            var deleteUrl = '/deletePeliFavorita/'+tablePeliId;

                            fetch(deleteUrl, {
                                method: 'DELETE',
                                headers:{
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                }
                            }).then(res => res.text())
                            .catch(error => console.error('Error:', error))
                            .then(response => {
                                console.log(response);

                                setTimeout(() => {
                                    // recargo la pagina
                                    window.location.reload()
                                }, 500);
                            });
                            
                        })
                    }
                }, 1000);
            @else
            // si el usuario no esta autenticado
            fetch(URL)
                .then(function(response) {
                    return response.json();
                })
                .then(function(myJson) {
                    // le doy la opcion de agregar a favoritos
                    // pero cuando clickea lo envio al loguearse
                    // y si no, registrarse
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