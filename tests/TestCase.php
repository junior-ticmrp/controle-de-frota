<?php

namespace Tests;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Os testes de feature simulam formulários diretamente; a proteção CSRF
        // continua ativa no ciclo HTTP real da aplicação.
        $this->withoutMiddleware(PreventRequestForgery::class);
    }
}
