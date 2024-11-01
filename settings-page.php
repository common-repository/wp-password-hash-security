<?php
/*
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


class WpPasswordHashSettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'wp_password_hash_add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'wp_password_hash_page_init' ) );
    }

    /**
     * Add options page
     */
    public function wp_password_hash_add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'WP Password Hash Security', 
            'manage_options', 
            'wp-password-hash-setting-admin', 
            array( $this, 'wp_password_hash_create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function wp_password_hash_create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'wp_password_hash_option' );
        ?>
        <div class="wrap">
            <h1>WP Password Hash Security Options</h1>
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'wp_password_hash_option_group' );
                do_settings_sections( 'wp-password-hash-setting-admin' );
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function wp_password_hash_page_init()
    {        
        register_setting(
            'wp_password_hash_option_group', // Option group
            'wp_password_hash_option', // Option name
            array( $this, 'wp_password_hash_sanitize' ) // Sanitize
        );

        add_settings_section(
            'wp_password_hash_section_id', // ID
            'WordPress Password Hash Security', // Title
            array( $this, 'wp_password_hash_print_section_info' ), // Callback
            'wp-password-hash-setting-admin' // Page
        );  

        add_settings_field(
            'wp_password_hash_rounds', // ID
            'Password Hash Rounds Exponent', // Title 
            array( $this, 'wp_password_hash_rounds_callback' ), // Callback
            'wp-password-hash-setting-admin', // Page
            'wp_password_hash_section_id' // Section           
        );      

        add_settings_field(
            'wp_password_hash_add_link', 
            '&#9924;&#65039; Enable credits? (creates a link in the footer)', 
            array( $this, 'wp_password_hash_add_link_callback' ), 
            'wp-password-hash-setting-admin', 
            'wp_password_hash_section_id'
        );      
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function wp_password_hash_sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['wp_password_hash_rounds'] ) ) {
            $new_rounds_value = wp_sanitize_password_hash_rounds($input['wp_password_hash_rounds']);
            $new_input['wp_password_hash_rounds'] = $new_rounds_value;
        }

        if( isset( $input['wp_password_hash_add_link'] ) ) {
            $new_input['wp_password_hash_add_link'] = ((intval($input['wp_password_hash_add_link']) === 1) ? '1' : '0');
        }
        else {
            $new_input['wp_password_hash_add_link'] = '0';
        }

        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function wp_password_hash_print_section_info()
    {
        print 'WordPress uses an exponent of 8 by default. Please enter a number between 8 and 20. Keep in mind that <i>rounds = 2 <sup>exponent</sup></i><br/>For this to take effect you must change your password after modifying the exponent.';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function wp_password_hash_rounds_callback()
    {
        printf(
            '<input type="text" id="wp_password_hash_rounds" name="wp_password_hash_option[wp_password_hash_rounds]" value="%s" />',
            wp_sanitize_password_hash_rounds($this->options['wp_password_hash_rounds']) //defined in main plugin file
        );
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function wp_password_hash_add_link_callback()
    {
        echo '<input type="checkbox" name="wp_password_hash_option[wp_password_hash_add_link]" value="1" ' . ( (isset($this->options['wp_password_hash_add_link']) && (intval($this->options['wp_password_hash_add_link']) === 1) ) ? 'checked ' : '') . '/>';
    }
}
// end of class WpPasswordHashSettingsPage


// If admin, create a new instance of the settings class
if( is_admin() ) {
    $wp_password_hash_settings_page = new WpPasswordHashSettingsPage();
}

?>