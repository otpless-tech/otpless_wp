<?php
function handleUserLogin()
{
    session_start();
    if (isset($_SESSION['otpless_user_name'])) { //TODO: this means that the phone number is verified
        $user_name = $_SESSION['otpless_user_name'];
        $user_email = $_SESSION['otpless_user_mobile'] + "@otpless.com";
        $user_id = username_exists($user_name);
        if (!$user_id && false == email_exists($user_email)) {
            $random_password = "password"; //TODO: Change to User's password here
            $result = wp_create_user($user_name, $random_password, $user_email);
            if (is_wp_error($result)) {
                $error = $result->get_error_message();
            }
        } else {
            $creds = array(
                'user_login'    => $user_name,
                'user_password' => 'password', //TODO: Change to User's password here
                'remember'      => true
            );

            $user = wp_signon($creds, false);
        }
    } else {
        //** Handle phone number not verified here */
    }
}

function clear_sessions()
{
    session_start();
    $_SESSION['otpless_user_name'] = "";
    exit();
}

add_action('wp_logout', 'clear_sessions');
add_action('init', 'handleUserLogin');
