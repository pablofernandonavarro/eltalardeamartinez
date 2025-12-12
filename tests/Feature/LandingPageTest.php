<?php

test('example', function () {
    $response = $this->get('/');

    $response->assertSuccessful();
    $response->assertSee('Ingresar');
    $response->assertSee('Registrarse');
});
