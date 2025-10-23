<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\AdminUserRegistered;
use App\Mail\UserRegistered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Exception;

class UserController extends Controller{
	public function store(Request $request){
		$data = $request->validate([
			'email'    => 'required|email|unique:users,email',
			'password' => 'required|string|min:8',
			'name'     => 'required|string|min:3|max:50',
			'role'     => ['sometimes', 'string', Rule::in(['administrator', 'manager', 'user'])],
			'active'   => ['sometimes', 'boolean'],
		]);
		
		try {

			$user = User::create([
				'email'			=>	$data['email'],
				'password'		=>	Hash::make($data['password']),
				'name'			=>	$data['name'],
				'role'			=>	$data['role'] ?? 'user',
				'active'		=>	$data['active'] ?? true,
				'created_at'	=>	now()
			]);

			/* SEND EMAIL TO USER */
			try {
				Mail::to($user->email)->send(new UserRegistered($user));
				Log::info('User registration email sent', ['email' => $user->email, 'user_id' => $user->id]);
			} catch (Exception $e) {
				Log::error('Failed to send user registration email', [
					'email' => $user->email,
					'user_id' => $user->id,
					'error' => $e->getMessage(),
				]);
			}
			
			/* SEND EMAIL TO ADMIN */
			try {
				$adminEmail = config('mail.admin_email');
				Mail::to($adminEmail)->send(new AdminUserRegistered($user));
				Log::info('Admin notification email sent', ['admin_email' => $adminEmail, 'user_id' => $user->id]);
			} catch (Exception $e) {
				Log::error('Failed to send admin notification email', [
					'admin_email' => $adminEmail,
					'user_id' => $user->id,
					'error' => $e->getMessage(),
				]);
			}

			return response()->json([
				'id'         => $user->id,
				'email'      => $user->email,
				'name'       => $user->name,
				'created_at' => $user->created_at->toIso8601String(),
			], 201);

		} catch (QueryException $e) {
			return response()->json([
				'message' => 'Database error: ' . $e->getMessage(),
			], 500);
		} catch (Exception $e) {
			return response()->json([
				'message' => 'Server error: ' . $e->getMessage(),
			], 500);
		}
	}

	public function getUsers(Request $request){
		$request->validate([
			'search'	=>	'sometimes|string',
			'page'		=>	'sometimes|integer|min:1',
			'sortBy'	=>	'sometimes|string|in:name,email,created_at'
		]);

		$search = $request->input('search');
		$page = $request->input('page', 1);
		$per_page = 5;
		$sortBy = $request->input('sortBy', 'created_at');

		$loggedInId = ($request->header('X-User')) ? (int) base64_decode($request->header('X-User')) : null;
		$loggedInRole = ($request->header('X-User-Role')) ? base64_decode($request->header('X-User-Role')) : null;

		$query = User::where('active', true);

		if ($search) {
			$query->where(function ($q) use ($search){
				$q->where('name', 'like', "%{$search}%")
				->orWhere('email', 'like', "%{$search}%");
			});
		}

		$query->withCount('orders');
		$query->orderBy($sortBy, 'ASC');

		$users = $query->paginate($per_page, ['*'], 'page', $page);

		$users->getCollection()->transform(function($user) use($loggedInId, $loggedInRole){
			$canEdit = false;

			switch ($loggedInRole) {
				case 'administrator':
					$canEdit = true;
					break;

				case 'manager':
					$canEdit = ($user->role === 'user');
					break;

				case 'user':
					$canEdit = ($user->id === $loggedInId);
					break;
			}

			return [
				'id'			=> $user->id,
				'email'			=> $user->email,
				'name'			=> $user->name,
				'role'			=> ucfirst($user->role),
				'created_at'	=> $user->created_at->toIso8601String(),
				'orders_count'	=> $user->orders_count,
				'can_edit'		=> $canEdit
			];
		});

		return response()->json([
			'page'	=>	$users->currentPage(),
			'users'	=>	$users->items()
		]);
	}
}
