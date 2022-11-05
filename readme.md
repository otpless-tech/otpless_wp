# OTPless Wordpress plugin

## Make a OTPless project

- log on to otpless.com
- create a web project
- enter the details
- copy the appID and appSecret

## Install Wordpress plugin

### Using zip file

- download the plugin zip file
- upload the file in the installation page
- activate the plugin

### Using plugin manager

- search for 'otpless' in plugins online downloader (builtin to wordpress)
- activate the plugin

## Enter the credentials

- In admin panel you will see a seperate section for otpless

- on clicking the section a simple form appears for configuration

- Enter the appSecret and appId also check toggle the checkbox if you want to redirect to same page where the login starts

- Save the details (this can be updated later if needed)

## Place the otpless login button

- open / create any page
- just like adding any elements to page, you can search for "otpless" and add the element
- after adding the element you might see something like this
- now you can publish this page / preview for testing

## Handling user

User handling such as creation and delete is fully upto your choice and can be customized.

```php
$_SESSION['otpless_user_name'] # use this to get username from whatsapp login
```

```php
$_SESSION['otpless_user_mobile'] # use this to get mobile number from whatsapp login
```

Below is a sample code for handling user login and user creation based on whatsapp login via otplesss

### Example code to create user / login based on otpless user

```php
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
    $_SESSION['otpless_user_name'] = "";
    wp_destroy_current_session();
    wp_clear_auth_cookie();
    exit();
}

add_action('wp_logout', 'clear_sessions');
add_action('init', 'handleUserLogin');


```
