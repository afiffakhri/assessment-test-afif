<!DOCTYPE html>
<html>
	<head>
		<title>New User Registered</title>
		<style>
			li,p {
				font-weight: normal;
			}
		</style>
	</head>
	<body>
		<section style="border-bottom: 2px solid gray; padding: 1.5rem;">
			<p>
				A new user has just registered: 
			</p>
		</section>
		<section style="border-bottom: 2px solid gray; padding: 1.5rem;">
			<h3 style="font-weight: bold; font-size: 1.25rem;">Registration Details</h3>
			<ul>
				<li><span style="font-weight: normal;">Name:</span> {{ $user->name }}</li>
				<li><span style="font-weight: normal;">Email:</span> {{ $user->email }}</li>
				<li><span style="font-weight: normal;">Role:</span> {{ ucfirst($user->role) }}</li>
				<li><span style="font-weight: normal;">Registered at:</span> {{ $user->created_at }}</li>
			</ul>
		</section>
		<section style="padding: 1.5rem;">
			<p>Thanks,</p>
			<p style="font-weight: normal;">{{ config('app.name') }}</p>
		</section>
	</body>
</html>
