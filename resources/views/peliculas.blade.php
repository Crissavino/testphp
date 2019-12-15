@extends('layouts.app')
@section('content')
    
    <div class="container">
        <h1 class="text-center">Las 100 películas mas votadas</h1>
        @if (Cache::has('pelisEnRedis'))
            <a href="{{ route('filtradas') }}" class="btnTopPelis btn btn-info mb-3">Top Películas</a>
        @endif
        <div class="m-auto">
            {{ $peliculas->links() }}
        </div>
        <table class="table">
            <thead class="thead-dark">
              <tr>
                <th scope="col">Posición</th>
                <th scope="col">Título</th>
                <th scope="col"></th>
              </tr>
            </thead>
            <tbody>
                @foreach ($peliculas as $indice => $peli)
                    <tr>
                        <th scope="row">{{$indice + 1}}</th>
                        <td>{{$peli['title']}}</td>
                        <td>
                            <button type="button" class="btn btn-primary" onclick="mostrarMas({{$peli['idMovieDB']}}, {{$indice + 1}})">
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

        async function obtenerActoresIdsYguardarlos(){
            let apiKey ='77a05aec0e235c4962c40565514582e8';
            let todasPelis = {!! json_encode(Cache::store('redis')->get('peliculas')) !!};
            
            todasPelis.forEach(async peli => {
                // await setTimeout(() => {
                    await fetch("https://api.themoviedb.org/3/movie/"+ peli.idMovieDB +"?language=en-US&api_key="+ apiKey + "&append_to_response=credits")
                        .then(function(response) {
                            return response.json();
                        })
                        .then(async function(myJson) {
                            let actoresIds = [];
                            myJson.credits.cast.forEach(async actor => {
                                await actoresIds.push({id: actor.id})
                            });
                            let url = '/guardarActoresIds/'+ (todasPelis.indexOf(peli)+1);
                            // await console.log(actoresIds);
                            fetch(url, {
                                    method: 'PUT', // or 'PUT'
                                    body: JSON.stringify({actoresIds}), // data can be `string` or {object}!
                                    credentials: "same-origin",
                                    headers:{
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                    }
                                }).then(res => res.text())
                                .catch(error => console.error('Error:', error))
                                .then(response => {
                                    console.log('Success:', response)
                                });
                        });
                // }, 2000);

                esperar = async () => {
                    if (await (todasPelis.indexOf(peli)+1) === 100) {
                        setTimeout(() => {
                            let btnTopPelis = document.querySelector('.btnTopPelis');
                            btnTopPelis.setAttribute('style', 'display:block')
                        }, 500);
                    }
                }
                esperar();
            });
        }

        // obtenerActoresIdsYguardarlos();

        
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
                        infoPelicula.idMovieDB = movieId;
                        infoPelicula.posicion_toprated = (indice + 1);
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

                    if (document.querySelector('.btnFav')) {
                        let btnFav = document.querySelector('.btnFav');
                        btnFav.addEventListener('click', () => {
                            const url = '/guardarPeliFavorita';

                            fetch(url, {
                                method: 'POST', // or 'PUT'
                                body: JSON.stringify(infoPelicula), // data can be `string` or {object}!
                                // body: JSON.stringify({id: indice}), // data can be `string` or {object}!
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
                                    window.location.reload()
                                }, 500);
                            });
                        })
                    }
                    

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

        // let btnFiltro = document.querySelector('.btnFiltro');
        // btnFiltro.addEventListener('click', () => {
            
        // })

        function filtrarPelis(){
            let tabla = document.querySelector('.table');
            // tabla.innerHTML = " "
            let apiKey ='77a05aec0e235c4962c40565514582e8';
            let idpPeliculasConKR = []
            fetch("https://api.themoviedb.org/3/person/6384/combined_credits?api_key="+ apiKey)
                .then(function(response) {
                    return response.json();
                })
                .then(function(myJson) {
                    myJson.cast.forEach(element => {
                        idpPeliculasConKR.push(element.id)
                    });

                    const urlFiltro = '/filtroPeliculas'
                    fetch(urlFiltro, {
                        method: 'POST', // or 'PUT'
                        body: JSON.stringify(idpPeliculasConKR), // data can be `string` or {object}!
                        credentials: "same-origin",
                        headers:{
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    }).then(res => res.text())
                    .catch(error => console.error('Error:', error))
                    .then(response => {
                        console.log('Success:', response)
                    });
                });
        }
    </script>
@endsection