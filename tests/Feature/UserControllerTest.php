<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class UserControllerTest extends TestCase {
	use RefreshDatabase;

	public function test_create_a_user_successfully() {
		$payload = [
			'email'    => 'testuser@example.com',
			'password' => 'password123',
			'name'     => 'Test User'
		];

		$response = $this->postJson('/api/users', $payload);

		$response->assertStatus(201)
				->assertJsonStructure([
					'id', 'email', 'name', 'created_at'
				]);

		$this->assertDatabaseHas('users', [
			'email' => 'testuser@example.com'
		]);
    }

    public function test_validates_required_fields_on_create() {
    	$response = $this->postJson('/api/users', []);

    	$response->assertStatus(422)
    			->assertJsonValidationErrors(['email', 'password', 'name']);
    }

    public function test_paginated_users_list() {
    	User::factory()->count(5)->create();

    	$response = $this->getJson('/api/users', [
    		'X-User-Role' => base64_encode('administrator')
    	]);

    	$response->assertStatus(200)
    			->assertJsonStructure([
    				'page',
    				'users' => [
    					'*' => ['id', 'email', 'name', 'role', 'orders_count', 'can_edit']
    				]
    			]);
    }

    public function test_check_can_edit_feature_correctly_based_on_user_role() {
    	$admin = User::factory()->create(['role' => 'administrator']);
    	$manager = User::factory()->create(['role' => 'manager']);
    	$user = User::factory()->create(['role' => 'user']);

    	$otherUsers = User::factory()->count(3)->create();

    	/* ADMINISTRATOR */
    	$response = $this->getJson('/api/users', [
    		'X-User-Role' => base64_encode('administrator'),
    		'X-User'   => base64_encode($admin->id)
    	]);

    	$response->assertStatus(200);
    	$data = $response->json('users');

    	foreach ($data as $u) {
    		$this->assertTrue($u['can_edit'], "Administrator can edit user ID {$u['id']}");
    	}

    	/* MANAGER */
    	$response = $this->getJson('/api/users', [
    		'X-User-Role' => base64_encode('manager'),
    		'X-User'   => base64_encode($manager->id)
    	]);

    	$response->assertStatus(200);
    	$data = $response->json('users');

    	foreach ($data as $u) {
    		if ($u['role'] === 'User') {
    			$this->assertTrue($u['can_edit'], "Manager can edit user only with 'user' role.");
    		} else {
    			$this->assertFalse($u['can_edit'], "Manager can't edit user with admin/manager role.");
    		}
    	}

    	/* USER */
    	$response = $this->getJson('/api/users', [
    		'X-User-Role' => base64_encode('user'),
    		'X-User'   => base64_encode($user->id)
    	]);

    	$response->assertStatus(200);
    	$data = $response->json('users');

    	foreach ($data as $u) {
    		if ($u['id'] === $user->id) {
    			$this->assertTrue($u['can_edit'], "User can edit their own account.");
    		} else {
    			$this->assertFalse($u['can_edit'], "User can't edit other users.");
    		}
    	}
    }

    public function test_filter_users_using_search() {
		$john = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
		$jane = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
		$alex = User::factory()->create(['name' => 'Alex Johnson', 'email' => 'alex@example.com']);

		$response = $this->getJson('/api/users?search=jane', [
			'X-User-Role' => base64_encode('administrator'),
			'X-User'   => base64_encode($john->id),
		]);

		$response->assertStatus(200);
		$users = $response->json('users');

		$this->assertCount(1, $users);
		$this->assertEquals('Jane Smith', $users[0]['name']);
	}

	public function test_sort_users_by_name_email_or_created_at() {
		User::query()->delete();

		$john = User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com', 'created_at' => now()->subDays(1)]);
		$jane = User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com', 'created_at' => now()]);
		$alex = User::factory()->create(['name' => 'Alex Johnson', 'email' => 'alex@example.com', 'created_at' => now()->subDays(3)]);

		/* SORT BY NAME */
		$response = $this->getJson('/api/users?sortBy=name', [
			'X-User-Role' => base64_encode('administrator'),
			'X-User'   => base64_encode($john->id),
		]);

		$response->assertStatus(200);
		$users = $response->json('users');

		$names = array_column($users, 'name');
		$this->assertEquals(['Alex Johnson', 'Jane Smith', 'John Doe'], $names, 'Users should be sorted alphabetically by name.');

		/* SORT BY created_at */
		$response = $this->getJson('/api/users?sortBy=created_at', [
			'X-User-Role' => base64_encode('administrator'),
			'X-User'   => base64_encode($john->id),
		]);

		$response->assertStatus(200);
		$users = $response->json('users');

		$names = array_column($users, 'name');
		$this->assertEquals(['Alex Johnson', 'John Doe', 'Jane Smith'], $names, 'Users should be sorted by created_at ascending.');
	}
}
