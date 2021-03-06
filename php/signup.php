<?php
require_once('inc/connect.php');

if (ALLOW_SIGNUP == false)
{
	$title = 'Sign Up';
	require_once('inc/header.php');
	print '<div class="post-list-outline no-content center">
		<form>
			<p style="position:relative;top:105px;">Signup is Disabled.</p>
		</form>
	</div>';
	require_once('inc/footer.php');
	exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (!isset($_POST['token']) || $_SESSION['token'] !== $_POST['token'])
	{
        $error = 'The CSRF check failed.';
        goto showForm;
    }
	
    if (empty($_POST['username']) || empty($_POST['nickname']) || empty($_POST['password']) || empty($_POST['confirm_password']))
	{
        $error = 'You must fill out all fields.';
        goto showForm;
    }
	
    if (!preg_match("/^[A-Za-z0-9-._]{1,32}$/", $_POST['username']))
	{
        $error = 'Your username is invalid.';
        goto showForm;
    }
	
    $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
	
    if (!$stmt)
	{
        $error = 'There was an error while preparing to search for users with your username.';
        goto showForm;
    }
	
    $stmt->bind_param('s', $_POST['username']);
	
    $stmt->execute();
	
    if ($stmt->error)
	{
        $error = 'There was an error while searching for users with your username.';
        goto showForm;
    }
	
    $result = $stmt->get_result();
	
    $row = $result->fetch_array();
	
    if ($row['COUNT(*)'] > 0)
	{
        $error = 'A user already exists with that username.';
        goto showForm;
    }
	
    if (mb_strlen($_POST['nickname']) > 64)
	{
        $error = 'Your nickname is too long.';
        goto showForm;
    }
	
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
	{
        $error = 'Your email address is invalid.';
        goto showForm;
    }
	
    if (!empty($_POST['email']))
	{
        $stmt = $db->prepare('SELECT COUNT(*) FROM users WHERE email = ? AND status = 0');
		
        $stmt->bind_param('s', $_POST['email']);
		
        $stmt->execute();
		
        if ($stmt->error)
		{
            $error = 'An error occurred while checking for users with that email.';
            goto showForm;
        }
		
        $result = $stmt->get_result();
		
        $row = $result->fetch_assoc();
		
        if ($row['COUNT(*)'] > 0)
		{
            $error = 'An account already exists with that email.';
            goto showForm;
        }
    }
	
    if (mb_strlen($_POST['password']) > 72)
	{
        $error = 'Your password is too long.';
        goto showForm;
    }
	
    if ($_POST['password'] !== $_POST['confirm_password'])
	{
        $error = 'Your password and confirm password do not match.';
        goto showForm;
    }
	
    if (!empty($_POST['nnid']) && !preg_match('/^[A-Za-z0-9-._]{6,16}$/', $_POST['nnid']))
	{
        $error = 'Your Nintendo Network ID is invalid.';
        goto showForm;
    }
	
    if (!empty($_POST['nnid']))
	{
        $ch = curl_init('https://pf2m.com/hash/' . urlencode($_POST['nnid']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $miiHash = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($responseCode < 200 || $responseCode > 299)
		{
            $error = 'The Nintendo Network ID could not be found.';
            goto showForm;
        }
    }
	
    if (!empty(RECAPTCHA_SECRET))
	{
        $ch = curl_init('https://www.google.com/recaptcha/api/siteverify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['secret' => RECAPTCHA_SECRET, 'response' => $_POST['g-recaptcha-response'], 'remoteip' => $ip]));
        $response = curl_exec($ch);
        curl_close($ch);
        $responseJSON = json_decode($response);
        if($responseJSON->success === 0) {
            $error = 'The reCAPTCHA was not solved correctly.';
            goto showForm;
        }
    }
	
	$proxy_check = file_get_contents(
		'https://check.getipintel.net/check.php?contact='.
		(!empty(CONTACT_EMAIL) ? urlencode(CONTACT_EMAIL) : 'miiverseworld@reconmail.com').
		'&flags=m&ip=' . urlencode($ip)
	);
	
    // Get IP Address
$IP_ADDRESS = $_SERVER['REMOTE_ADDR']; # Automatically get IP Address

// Input VPNAPI.IO API Key
// Create an account to get a free API Key
// Free API keys has a limit of 1000/requests per a day
$API_KEY = "6a9a0ac5ef404c3c889d683e303035c6"; // Don't worry about changing this, it'll use my account but i dont give a fuck

// API URL
$API_URL = 'https://vpnapi.io/api/' . $IP_ADDRESS . '?key=' . $API_KEY;

// Fetch VPNAPI.IO API 
$proxy_check = file_get_contents($API_URL);

// Decode JSON response
$proxy_check = json_decode($proxy_check);
    
    if($proxy_check->security->vpn == true)
    {
        $error = 'You cannot sign up using a VPN.';
        goto showForm;
    }
        if($proxy_check->security->proxy == true)
    {
        $error = 'You cannot sign up using a Proxy.';
        goto showForm;
    }
        if($proxy_check->security->tor == true)
    {
        $error = 'You cannot sign up using a tor exit node.';
        goto showForm;
    }
        if($proxy_check->security->relay == true)
    {
        $error = 'You cannot sign up using a Relay.';
        goto showForm;
    }

    $api_email = 'lordfrootloops@gmail.com'; //CHANGE THIS OR YOU'LL BAN YOURSELF FROM THE CHECKER NETWORK, it wont work lol
    $Api_urlII = 'http://check.getipintel.net/check.php?ip=' . $IP_ADDRESS . '&contact=' . $api_email . '&flags=';

    $proxy_checkII = file_get_contents($Api_urlII);
    $proxy_checkII = json_decode($proxy_checkII);

    if($proxy_checkII > 0.9) // should be good enough for adams shit vpn
    {
     $error = 'Suspicious IP Detected.';
        goto showForm;
    }
    //if($proxy_checkII = -5)
    //{
    //$error = 'Something went horribly wrong! Contact Braden for assistance.';
    //    goto showForm;
    //}

    $email = empty($_POST['email']) ? null : $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $avatar = empty($miiHash) ? (!empty($_POST['email']) ? GRAVATAR_URL . md5($_POST['email']) . '?s=' . GRAVATAR_IMAGE_SIZE : null) : $miiHash;
    $has_mh = empty($miiHash) ? 0 : 1;
    $nnid = empty($_POST['nnid']) ? null : $_POST['nnid'];
    $mh = empty($miiHash) ? null : $miiHash;
	
    $stmt = $db->prepare('INSERT INTO users (username, nickname, email, password, avatar, has_mh, nnid, mh, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
	
    if (!$stmt)
	{
        $db->prepare('INSERT INTO users (username, nickname, email, password, avatar, has_mh, nnid, mh, ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $error = 'There was an error while preparing to insert your account into the database. ' . $db->error;
        goto showForm;
    }
	
    $stmt->bind_param('sssssisss', $_POST['username'], $_POST['nickname'], $email, $password, $avatar, $has_mh, $nnid, $mh, $ip);
	
    $stmt->execute();
	
    if ($stmt->error)
	{
        $error = 'There was an error while inserting your account into the database. ' . $stmt->error;
        goto showForm;
    }

    $stmt = $db->prepare('SELECT id FROM users WHERE username = ? ORDER BY id DESC LIMIT 1');
	
    if (!$stmt)
	{
        $error = 'There was an error while preparing to fetch your account\'s ID.';
        goto showForm;
    }
	
    $stmt->bind_param('s', $_POST['username']);
	
    $stmt->execute();
	
    if ($stmt->error)
	{
        $error = 'There was an error while fetching your account\'s ID.';
        goto showForm;
    }
	
    $result = $stmt->get_result();
	
    $row = $result->fetch_array();
	
    $_SESSION['id'] = $row['id'];
    $_SESSION['username'] = $_POST['username'];
    $_SESSION['avatar'] = $avatar;
    $_SESSION['has_mh'] = $has_mh;
    $_SESSION['level'] = 0;
	
    $token = md5(uniqid());
	
    $stmt = $db->prepare('INSERT INTO tokens (source, value) VALUES (?, ?)');
	
    if (!$stmt)
	{
        $error = 'There was an error while preparing to insert your login token into the database.';
        goto showForm;
    }
	
    $stmt->bind_param('is', $row['id'], $token);
	
    $stmt->execute();
	
    if ($stmt->error)
	{
        $error = 'There was an error while inserting your login token into the database.';
        goto showForm;
    }
	
    setcookie('gp_auth', $token, time() + 2592000, '/');

    http_response_code(301);
	
    if (!empty($_GET['callback']) && substr($_GET['callback'], 0, 1) === '/')
	{
        header('Location: ' . $_GET['callback']);
    }
	else
	{
        header('Location: /');
    }
	
    exit();
}

showForm:
$title = 'Sign Up';
$header_is_login = true;
require_once('inc/header.php');
?>
<div class="post-list-outline no-content center">
    <form method="post">
        <input type="hidden" name="token" value="<?=$_SESSION['token']?>"><br>
        <img src="/assets/img/menu-logo.png">
        <p>Sign up for a Galaxy Plaza account to make posts and interact with other users.</p>
        <?php if(!empty($error)) { ?><p class="post-tag post-topic-category symbol"><?=htmlspecialchars($error)?></p><?php } ?><br>
        <div class="row">
			<input type="text" class="form-control form-box" name="username" placeholder="Username" required maxlength="32">
		</div>
		<div class="row">
			<input type="text" class="form-control form-box" name="nickname" placeholder="Nickname" required maxlength="64">
		</div>
		<div class="row">
			<input type="email" class="form-control form-box" name="email" placeholder="Email Address / Gravatar Email Address" maxlength="254">
		</div>
		<div class="row">
			<input type="password" class="form-control form-box" name="password" placeholder="Password" required maxlength="72">
		</div>
		<div class="row">
			<input type="password" class="form-control form-box" name="confirm_password" placeholder="Confirm Password" required maxlength="72">
		</div>
		<div class="row">
			<input type="text" class="form-control form-box" name="nnid" placeholder="Nintendo Network ID" maxlength="16" minlength="6">
		</div>
        <?php if(!empty(RECAPTCHA_PUBLIC)) { ?><br>
        <script src="https://www.google.com/recaptcha/api.js"></script>
        <div class="g-recaptcha" style="display:inline-block" data-sitekey="<?=htmlspecialchars(RECAPTCHA_PUBLIC)?>"></div>
        <?php } ?><div class="form-buttons">
            <button class="black-button" type="submit">Sign Up</button>
        </div><br>
        <p>Email address and Nintendo Network ID are optional and can be changed later, but without one you can't add an Gravatar or reset your password if you get locked out of your account.</p><br>
        <p>If you already have an account, you can <a href="/login">sign in here.</a></p><br>
    </form>
</div><?php
require_once('inc/footer.php');
?>