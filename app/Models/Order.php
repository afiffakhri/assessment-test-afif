<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model {
	use HasFactory;
	public $timestamps = false;

	protected $fillable = [
		'user_id',
		'created_at'
	];

	protected $casts = [
		'created_at'	=> 'datetime'
	];

	public function user() {
		return $this->belongsTo(User::class);
	}
}
