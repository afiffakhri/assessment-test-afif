<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminUserRegistered extends Mailable {
    use Queueable, SerializesModels;

	public $user;

	public function __construct(User $user) {
		$this->user = $user;
	}

	public function build() {
		return $this->subject('New User Registration')
					->view('emails.admin.new_user')
					->with(['user' => $this->user]);
	}
}
