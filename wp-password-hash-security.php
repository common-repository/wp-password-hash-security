<?php
/*
Plugin Name: Wordpress Password Hash Security
Description: Simple plugin to change the hashing rounds used for Wordpress password hashes
Version: 1.0.2
Author: DesignSmoke Web Developers
Author URI: https://www.designsmoke.com
License: GPL2

This code is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
  
This code is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
  
You should have received a copy of the GNU General Public License
along with this code. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/


// Load admin options for this plugin into $password_hash_options
$password_hash_options = get_option('wp_password_hash_option');

// This block will perform all validation and sanitation
function wp_sanitize_password_hash_rounds($rounds_value) {
    $rounds_int = intval($rounds_value);
    if(($rounds_int < 8) || ($rounds_int > 20)) {
        return 8;
    }
    else {
        return $rounds_int;
    }
}

// Get the rounds setting and then sanitize
function wp_get_password_hash_rounds() {
    global $password_hash_options; //make sure we use the global variable set

    if(isset($password_hash_options['wp_password_hash_rounds'])) {
        return wp_sanitize_password_hash_rounds($password_hash_options['wp_password_hash_rounds']);
    }
    else {
        return 8;
    }
}

// This block will override the wp_hash_password function with our custom method
if ( ! function_exists( 'wp_hash_password' ) ) {

    function wp_hash_password($password) {
        global $wp_hasher;
     
        if ( empty($wp_hasher) ) {
            require_once( ABSPATH . WPINC . '/class-phpass.php');

            // Wordpress usually sets this to 8 (256 rounds) by default, override with user's setting
            // $password_hash_rounds is log2(rounds) which is equivalent to rounds=2^$password_hash_rounds
            $password_hash_rounds = wp_get_password_hash_rounds(); 

            $wp_hasher = new PasswordHash($password_hash_rounds, true);
        }
     
        return $wp_hasher->HashPassword( trim( $password ) );
    }

}

// Adds a link to the footer, if the user has opted-in
if(isset($password_hash_options['wp_password_hash_add_link']) && (intval($password_hash_options['wp_password_hash_add_link']) == 1)) {
        function wp_password_hash_credit() {
            $wp_pass_hash_credits = '<div style="width:100%;text-align:center; font-size:11px; clear:both">Proudly using a plugin developed by: <a target="_blank" title="DesignSmoke WordPress Developers" href="https://www.designsmoke.com/" >DesignSmoke.com</a></div>';
            echo $wp_pass_hash_credits;
        }

        add_action('wp_footer', 'wp_password_hash_credit');
}


// This file includes all admin menu code
// (class WpPasswordHashSettingsPage)
include_once('settings-page.php');

?>