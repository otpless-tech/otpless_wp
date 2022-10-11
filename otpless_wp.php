<?php

/**
 * Plugin Name:       Otpless Wp
 * Description:       Example block scaffolded with Create Block tool.
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            The WordPress Contributors
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
function create_block_otpless_wp_block_init()
{
	register_block_type(__DIR__ . '/build');
}

function wpb_hook_javascript()
{
?>
	<script>
		const loginWithWhatsapp = async () => {
			const out = await axios.get("http://www.example.com");
			console.log(out);
		};
	</script>
<?php
}
add_action('wp_head', 'wpb_hook_javascript');

wp_enqueue_script('axios', 'https://unpkg.com/axios/dist/axios.min.js');

add_action('init', 'create_block_otpless_wp_block_init');
