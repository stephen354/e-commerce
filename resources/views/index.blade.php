<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Animated Login Form</title>
	@vite(['resources/css/style.css', 'resources/js/app.js'])
</head>
<body>
	 {{ session('message') }}
	<div class="box">
		<form method="POST" action="{{ route('actionlogin') }}">
			@csrf
			<h2>Sign in</h2>
			<div class="inputBox">
				<input type="email" required="required" name="email">
				<span>Email</span>
				<i></i>
			</div>
			<div class="inputBox">
				<input type="password" required="required" name="password">
				<span>Password</span>
				<i></i>
			</div>
			<div class="links">
				<a href="#">Forgot Password ?</a>
				<a href="#">Signup</a>
			</div>
			<input type="submit" value="Login">
		</form>
	</div>
</body>
</html>