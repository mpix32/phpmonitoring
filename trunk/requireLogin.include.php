<?php

// Makes sure we're logged in.
// If not logged in, presents a login page and exits.
// Optionally, the login page can be suppressed by setting $allowLoginForm to false
// before including this file.  In this case, if we're not logged in, this include
// file just exits.

class_exists('Settings', false) or include('./classes/Settings.class.php');

$__cp = array_merge($_COOKIE, $_POST);
$user = isset($__cp['u']) ? $__cp['u'] : '';
$passwd = isset($__cp['p']) ? $__cp['p'] : '';
unset($__cp);

$settings = Settings::getSettings();
if ( ($user == $settings['username']) && ($passwd == $settings['passwd']) ) {
	// We're logged in.
	// Set or update the cookies, so they don't expire.
	setcookie('u', $user, time()+3600*24*365);
	setcookie('p', $passwd, time()+3600*24*365);
} else {
	if ( (!isset($allowLoginForm)) || ($allowLoginForm) ) {
		include('./header.include.php');
		include('./loginForm.include.php');
		include('./footer.include.php');
	}
	exit();
}
?>
