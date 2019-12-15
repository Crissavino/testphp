<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function test_si_el_usuario_esta_logueado_ve_home(){
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
                         ->get('/home');
        $response->assertStatus(200);
    }

    public function test_si_el_usuario_no_esta_logueado_vaya_login(){
        $response = $this->get('/home')
                         ->assertRedirect('/login');
    }

    public function test_si_ya_estoy_logueado_voy_al_home(){
        $user = factory(User::class)->make();

        $response = $this->actingAs($user);
        $response = $this->get('/login')
                         ->assertRedirect('/home');
    }

    public function test_usuario_no_ve_login_cuando_esta_logueado()
    {
        $user = factory(User::class)->make();

        $response = $this->actingAs($user)
                    ->get('/login');
        $response->assertRedirect('/home');
    }

}
