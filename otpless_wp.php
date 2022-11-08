<?php

/**
 * Plugin Name:       Otpless - Login with Whatsapp
 * Description:       Wordpress plugin for logging with Whatsapp using OTPless
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            OTPless<www.otpless.com>
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       otpless_wp
 *
 * @package           create-block
 */

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */

$GET_INTENT_REQUEST_URI = "https://api.otpless.com/api/v1/user/getSignupUrl";

function create_block_otpless_wp_block_init()
{
	session_start();
	register_block_type(__DIR__ . '/build');
	if (isset($_POST['submit_otpless'])) {
		$_SESSION['client_id'] = $_POST['client_id'];
		$_SESSION['client_secret'] = $_POST['client_secret'];
		$_SESSION['redirect_url'] = $_POST['redirect_url'];
		$_SESSION['use_check'] = $_POST['use_check'];
		if (get_option('client_id')) {
			update_option('client_id', $_POST['client_id']);
			update_option('client_secret', $_POST['client_secret']);
			update_option('redirect_url', $_POST['redirect_url']);
			update_option('use_check', $_POST['use_check']);
		}
		add_option('client_id', $_POST['client_id']);
		add_option('client_secret', $_POST['client_secret']);
		update_option('redirect_url', $_POST['redirect_url']);
	}
}

# state id generator function
function getRandomString($n)
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randomString = '';

	for ($i = 0; $i < $n; $i++) {
		$index = rand(0, strlen($characters) - 1);
		$randomString .= $characters[$index];
	}

	return $randomString;
}

# Injecting javascript code for getting intent url and opening whatsapp
function wpb_hook_javascript()
{
	session_start();
	$state = getRandomString(10);
	if (!($_SESSION['c_state'])) {
		$_SESSION['c_state'] = $state;
	}
?>
	<script>
		const loginWithWhatsapp = async () => {
			var clientId = "<?php echo get_option('client_id') ?>";
			var clientSecret = '<?php echo get_option('client_secret') ?>';
			var redirectUrl = '<?php echo get_option('redirect_url') ?>';
			var useCurrentRedirectionUrl = '<?php echo get_option('use_check') ?>';
			var cState = '<?php echo $_SESSION['c_state'] ?>';
			var newWindow = window.open('', '_blank');
			const config = {
				headers: {
					"appId": clientId,
				}
			};
			const body = {
				"loginMethod": "WHATSAPP",
				"redirectionURL": redirectUrl == "" ? window.location.href : redirectUrl,
				"state": cState
			}
			const res = await axios.post("https://api.otpless.app/v1/client/user/session/initiate", body, config);
			console.log("response", res.data.data.intent);
			newWindow.location = res.data.data.intent;
		};
	</script>
<?php
}

# Show admin panel and get client ID and client secret from admin panel
# to store in session
function otpless_admin_form()
{
?>
	<div class="form-wrap" style="width: 40vw;">
		<h2>OTPless Settings</h2>
		<p>Enter your App ID and App Secret to save</p>
		<div class="wrap">
			<p>Please enter the App ID & App Secret</p>
			<form method="post" id="mapform" name="mapform" style="display: flex;flex-direction:column;gap:1rem;">
				<input type="text" name="client_id" placeholder="App ID">
				<input type="text" name="client_secret" placeholder="App Secret">
				<input type="text" name="redirect_url" placeholder="Redirection Url">
				<span><em>Leave Redirection Url field Empty to use dynamic URL (Page where whatsapp login occurs)</em></span>
				<input type="submit" class="button button-primary" name="submit_otpless" value="Save Configuration">
			</form>
		</div>
	</div>
<?php
}

# Read the token from the redirection and make API calls to get user details
# get user name and user id to save it as current user or
# have a simple session created to save the user's authenticated value
if (isset($_GET['token'])) {
	session_start();
	$response = wp_remote_post('https://api.otpless.app/v1/client/user/session/userdata', array(
		'headers'     => array(
			'Content-Type' => 'application/json; charset=utf-8',
			'appId' => get_option('client_id'),
			'appSecret' => get_option('client_secret')
		),
		'body'        => json_encode(array(
			'token' => $_GET['token'],
			'state' => $_SESSION['c_state']
		)),
		'method'      => 'POST',
		'data_format' => 'body'
	));
	$body     = json_decode(wp_remote_retrieve_body($response));
	$_SESSION['otpless_user_name'] = $body->data->name;
	$_SESSION['otpless_user_mobile'] = $body->data->mobile;
}

function otpless_plugin_section_text()
{
	echo '<p>Set Client ID and SECRET for Otpless</p>';
}

function otpless_admin_page()
{
	add_menu_page("OTPless", 'OTPless', 'edit_posts', 'otpless_wp', 'otpless_admin_form', '', 24);
}

add_action('admin_menu', 'otpless_admin_page');

add_action('wp_head', 'wpb_hook_javascript');

wp_enqueue_script('axios', 'https://unpkg.com/axios/dist/axios.min.js');

add_action('init', 'create_block_otpless_wp_block_init');
