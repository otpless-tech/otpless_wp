<?php

/**
 * Plugin Name:       Otpless Wp
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            OTPless<www.otpless.com> and Solai Raj M<msraj085@gmail.com>
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
	register_block_type(__DIR__ . '/build');
	if (isset($_POST['submit_otpless'])) {
		$_SESSION['client_id'] = $_POST['client_id'];
		$_SESSION['client_secret'] = $_POST['client_secret'];
		if (get_option('client_id')) {
			update_option('client_id', $_POST['client_id']);
			update_option('client_secret', $_POST['client_secret']);
		}
		add_option('client_id', $_POST['client_id']);
		add_option('client_secret', $_POST['client_secret']);
	}
	if (isset($_POST['whatsapp'])) {
		$this_args = array(
			'headers' => array(
				'clientid' => get_option('client_id'),
				'client_secret' => get_option('client_secret')
			)
		);
		$response = wp_remote_get($GET_INTENT_REQUEST_URI, $this_args);
		$body     = wp_remote_retrieve_body($response);
		echo '<script>console.log' + $body + '</script>';
?>
		<script>
			var clientId = "<?php echo get_option('client_id') ?>";
			console.log("HELLO HELLO HELLO", clientId);
		</script>
	<?php
	}
}


# Injecting javascript code for getting intent url and opening whatsapp
function wpb_hook_javascript()
{
	$shit = "sdf";
	?>
	<script>
		var clientId = "<?php echo get_option('client_id') ?>";
		var clientSecret = '<?php echo get_option('client_secret') ?>';
		const loginWithWhatsapp = async () => {
			const config = {
				headers: {
					"clientid": clientId,
					"clientsecret": clientSecret
				}
			};
			console.log("MY CLIENT ID GIVEN IS", clientId);
			console.log("MY CLIENT SECRET IS", clientSecret);
			console.log("config", config);
			const res = await axios.get("https://api.otpless.com/api/v1/user/getSignupUrl", config);
			console.log("response", res);
			window.open(res.url, '_blank');
		};
	</script>
<?php
}

# Show admin panel and get client ID and client secret from admin panel
# to store in session
function otpless_admin_form()
{
?>
	<div>
		<h2>OTPless Settings</h2>
		<p>Enter your ClientID and Client Secret to save</p>
		<div class="wrap">
			<p>Please enter the Client ID & Client Secret</p>
			<form method="post" id="mapform" name="mapform">
				<input type="text" name="client_id" placeholder="Client ID">
				<input type="text" name="client_secret" placeholder="Client Secret">
				<input type="submit" name="submit_otpless" value="save_client">
			</form>
		</div>
	</div>
<?php
}

# Read the token from the redirection and make API calls to get user details
# get user name and user id to save it as current user or
# have a simple session created to save the user's authenticated value
if (isset($_GET['token'])) {
	$response = wp_remote_post('https://api.otpless.com/api/v1/user/getUserDetails', array(
		'headers'     => array(
			'Content-Type' => 'application/json; charset=utf-8',
			'state' => '', //TODO: add state
			'clientsecret' => '', //TODO: add client secret (figure a way to get client secret)
			'clientid' => '' //TODO: set the client id (figure a way to get client id)
		),
		'body'        => json_encode(array(
			'token' => $_GET['token']
		)),
		'method'      => 'POST',
		'data_format' => 'body'
	));
	$body     = wp_remote_retrieve_body($response);
	echo $body;
}

function otpless_plugin_section_text()
{
	echo '<p>Set Client ID and SECRET for Otpless</p>';
}

session_start();

function otpless_admin_page()
{
	add_menu_page("OTPless", 'OTPless', 'edit_posts', 'otpless_wp', 'otpless_admin_form', '', 24);
}

add_action('admin_menu', 'otpless_admin_page');

add_action('wp_head', 'wpb_hook_javascript');

wp_enqueue_script('axios', 'https://unpkg.com/axios/dist/axios.min.js');

add_action('init', 'create_block_otpless_wp_block_init');
