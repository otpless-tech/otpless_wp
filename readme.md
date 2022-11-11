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
<img width="953" alt="Screenshot 2022-11-11 at 9 20 40 AM" src="https://user-images.githubusercontent.com/54436424/201300930-e896afcf-b5c0-4a53-a51b-d2f3f9d4e684.png">
<img width="1235" alt="Screenshot 2022-11-11 at 9 21 20 AM" src="https://user-images.githubusercontent.com/54436424/201300956-9f0d7999-d9f6-423d-8610-c865f99a60aa.png">
<img width="1231" alt="Screenshot 2022-11-11 at 9 21 53 AM" src="https://user-images.githubusercontent.com/54436424/201300971-529fc7a5-52bc-44f5-9df3-3e71437cb3e3.png">
<img width="1241" alt="Screenshot 2022-11-11 at 9 22 33 AM" src="https://user-images.githubusercontent.com/54436424/201300998-bb21023b-1e6f-425d-bd80-f272260f4d67.png">

- activate the plugin
<img width="720" alt="Screenshot 2022-11-11 at 9 24 54 AM" src="https://user-images.githubusercontent.com/54436424/201301027-fb2de163-4e7e-4bf7-9be5-831286adbf13.png">
- Now you should see the Installed Plugin
<img width="1252" alt="Screenshot 2022-11-11 at 9 25 51 AM" src="https://user-images.githubusercontent.com/54436424/201301120-765c7131-faa2-42dc-b72b-a414a8b50235.png">

<!-- ### Using plugin manager

- search for 'otpless' in plugins online downloader (builtin to wordpress)
- activate the plugin -->

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
