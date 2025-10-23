<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserRegistered extends Mailable {
	use Queueable, SerializesModels;

	public $user;

	public function __construct(User $user) {
		$this->user = $user;
	}

	public function build() {
		return $this->subject('Your Account has been Created')
					->view('emails.user.created_user')
					->with(['user' => $this->user]);
	}
}
