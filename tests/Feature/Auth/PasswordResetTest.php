<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['nome' => $user->nome]);

        $response->assertSessionHas('status');
        $this->assertDatabaseHas('password_reset_tokens', ['email' => $user->nome]);
    }

    public function test_reset_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['nome' => $user->nome]);

        $token = DB::table('password_reset_tokens')
            ->where('email', $user->nome)
            ->value('token');

        $response = $this->get('/reset-password/'.$token.'?nome='.$user->nome);

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['nome' => $user->nome]);

        $token = DB::table('password_reset_tokens')
            ->where('email', $user->nome)
            ->value('token');

        $response = $this->post('/reset-password', [
            'token' => $token,
            'nome' => $user->nome,
            'senha' => 'password',
            'senha_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));
    }
}
