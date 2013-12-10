<?php
session_start();
require_once(dirname(dirname(__FILE__)).'/config.php');
if (!isset($options->admin_credentials) || $options->admin_credentials['username'] == '' || $options->admin_credentials['password'] == '') {
	die('<h2>Admin privileges required</h2><p>This page requires admin privileges but Full-Text RSS has not been configured with admin credentials.</p><p>If you are the administrator, please edit your <tt>custom_config.php</tt> file and enter the credentials in the appropriate section. When you\'ve done that, this page will prompt you for your admin credentials.</p>');
}

$name = @$_POST['username'];
$pass = @$_POST['pass'];
$invalid_login = false;

if ($name || $pass) {
	if ($name == $options->admin_credentials['username'] && $pass == $options->admin_credentials['password']) {
		// Authentication successful - set session
		$_SESSION['auth'] = 1;
		if (isset($_POST['redirect']) && preg_match('/^[0-9a-z]+$/', $_POST['redirect'])) {
			header('Location: '.$_POST['redirect'].'.php');
		} else {
			header('Location: index.php');
		}
		exit;
	}
	$invalid_login = true;
}
?>
<!DOCTYPE html>
<html>
<head><title>Login</title></head>
<body>
<?php if ($invalid_login) echo '<p><strong>Invalid login, please try again.</strong> If you can\'t remember your admin credentials, open your <tt>custom_config.php</tt> and you\'ll find them in there.</p>'; ?>
<form method="post" action="login.php">
<?php if (isset($_GET['redirect'])) echo '<input type="hidden" name="redirect" value="'.htmlspecialchars($_GET['redirect']).'" />'; ?>
<label>Username: <input type="text" name="username" value="<?php echo @$_POST['username']; ?>" /></label>
<label>Password: <input type="password" name="pass" /></label>
<input type="submit" name="submit" value="Log In" />
</form>
</body>
</html>