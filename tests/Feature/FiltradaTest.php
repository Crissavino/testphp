<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FiltradaTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_la_pagina_carga()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
