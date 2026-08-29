<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

include '../includes/header.php';
include '../includes/navbar.php';
?>
<main class="py-5">
	<div class="container">
		<div class="row justify-content-center">
			<div class="col-lg-5 col-md-7">
				<div class="card-ui p-4 p-lg-5 bg-white">
					<div class="text-center mb-4">
						<h1 class="h3 fw-bold mb-2">Forgot Password</h1>
						<p class="text-muted mb-0">Enter the email address associated with your GradConnect SL account and we'll send you a link to reset your password.</p>
					</div>
					<?php displayFlashMessages(); ?>
					<form method="post" action="forgot_password_process.php">
						<div class="mb-3">
							<label for="email" class="form-label fw-semibold">Email Address</label>
							<input type="email" class="form-control" id="email" name="email" required autocomplete="email">
						</div>
						<button type="submit" class="btn btn-primary-custom w-100">Send Reset Link</button>
					</form>
					<p class="text-center mt-3 mb-0"><a href="login.php">Back to Login</a></p>
				</div>
			</div>
		</div>
	</div>
</main>
<?php include '../includes/footer.php'; ?>
