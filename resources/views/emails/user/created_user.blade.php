<!DOCTYPE html>
<html>
	<head>
		<title>Your Account has been Created</title>
		<style>
			li,p {
				font-weight: normal;
			}
		</style>
	</head>
	<body>
		<section style="border-bottom: 2px solid gray; padding: 1.5rem;">
			<h3 style="font-weight: normal;">Dear {{ $user->name }},</h3><br/>
			<p>
				Thank you for registering, 
			</p>
			<p>
				Your account have been successfully created.
			</p>
		</section>
		<section style="padding: 1.5rem;">
			<p>Thanks,</p>
			<p style="font-weight: normal;">{{ config('app.name') }}</p>
		</section>
	</body>
</html>
