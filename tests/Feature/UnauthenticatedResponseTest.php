<?php

namespace Tests\Feature;

use Tests\TestCase;

class UnauthenticatedResponseTest extends TestCase
{
    public function test_api_sem_token_retorna_401_json(): void
    {
        $response = $this->getJson('/api/user');
        $response->assertStatus(401);
        $response->assertJsonStructure(['message']);
    }
}
