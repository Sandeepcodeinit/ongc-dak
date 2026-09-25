<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('user_name')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('temp_password')->nullable();
            $table->integer('user_type')->nullable();
            $table->integer('active_yn')->nullable();
            $table->timestamps();
        });
    }

    public function test_user_can_login_and_store_session(): void
    {
        $email = 'dbuser' . uniqid() . '@example.com';

        DB::table('users')->insert([
            'user_name' => 'DB User',
            'email' => $email,
            'password' => Hash::make('Admin@240'),
            'user_type' => 1,
            'active_yn' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/login', [
            'emailid' => $email,
            'password' => 'Admin@240',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertEquals('DB User', session('user.name'));
        $this->assertEquals($email, session('user.email'));
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $email = 'dbuser2' . uniqid() . '@example.com';

        DB::table('users')->insert([
            'user_name' => 'DB User 2',
            'email' => $email,
            'password' => Hash::make('Admin@240'),
            'user_type' => 1,
            'active_yn' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/login', [
            'emailid' => $email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_user_with_inactive_or_unauthorized_status_cannot_login(): void
    {
        $email = 'dbuser3' . uniqid() . '@example.com';

        DB::table('users')->insert([
            'user_name' => 'DB User 3',
            'email' => $email,
            'password' => Hash::make('Admin@240'),
            'user_type' => 99,
            'active_yn' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post('/login', [
            'emailid' => $email,
            'password' => 'Admin@240',
        ]);

        $response->assertSessionHasErrors(['email']);
    }
}
