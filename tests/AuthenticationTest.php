<?php

namespace Wink\Tests;

use Illuminate\Support\Facades\Mail;
use Wink\Mail\ResetPasswordEmail;

class AuthenticationTest extends TestCase
{
    public function test_guests_are_sent_to_the_login_screen(): void
    {
        $this->get('/wink')->assertRedirect(route('wink.auth.login'));
        $this->get('/wink/login')->assertOk()->assertSee('Log In');
        $this->getJson('/wink/api/posts')->assertUnauthorized();
    }

    public function test_authors_can_log_in_and_out(): void
    {
        $this->author();

        $this->post('/wink/login', [
            'email' => 'admin@mail.com',
            'password' => 'secret',
        ])->assertRedirect('/wink');

        $this->get('/wink')->assertOk()->assertSee('Regina Phalange', false);

        $this->get('/wink/logout')->assertRedirect(route('wink.auth.login'));
        $this->get('/wink/api/posts')->assertRedirect(route('wink.auth.login'));
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->author();

        $this->from('/wink/login')->post('/wink/login', [
            'email' => 'admin@mail.com',
            'password' => 'wrong',
        ])->assertRedirect('/wink/login')->assertSessionHasErrors('email');
    }

    public function test_password_reset_issues_a_new_password(): void
    {
        $author = $this->author();

        Mail::fake();

        $this->post('/wink/password/forgot', [
            'email' => $author->email,
        ])->assertRedirect(route('wink.password.forgot'));

        $token = null;

        Mail::assertSent(ResetPasswordEmail::class, function (ResetPasswordEmail $mail) use (&$token) {
            $token = $mail->token;

            return $mail->hasTo('admin@mail.com');
        });

        $this->get('/wink/password/reset/'.$token)
            ->assertOk()
            ->assertSee('Copy your new password');

        $author->refresh();

        $this->assertFalse(\Illuminate\Support\Facades\Hash::check('secret', $author->password));

        $this->get('/wink/password/reset/'.$token)
            ->assertRedirect(route('wink.password.forgot'));
    }
}
