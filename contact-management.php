<?php
/**
 * Plugin Name: Contact Management
 * Description: A simple WordPress plugin for contact management.
 * Version: 1.0
 * Author: Sohanraj Samadder
 * Author URI: https://yourwebsite.com
 */

class ContactManagementPlugin {
    private $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'wp_contacts';

        register_activation_hook(__FILE__, array($this, 'create_table'));
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('register', array($this, 'display_contacts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_save_contact', array($this, 'save_contact'));

    }

    public function create_table() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE {$this->table_name} (
            id INT(16) NOT NULL AUTO_INCREMENT,
            email VARCHAR(255) NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            phone_number VARCHAR(50) NOT NULL,
            address VARCHAR(500),
            PRIMARY KEY (id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function add_admin_menu() {
        add_menu_page(
            'Contact Management',
            'Contact Management',
            'manage_options',
            'contact-management',
            array($this, 'display_contacts'),
            'dashicons-businessman',
            20
        );
    }

    public function register() {
        if (!empty($_POST['email']) && !empty($_POST['first_name']) && !empty($_POST['last_name']) && !empty($_POST['phone_number']) && empty($_POST['address'])) {
            global $wpdb;

            $data = array(
                'email' => sanitize_email($_POST['email']),
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name' => sanitize_text_field($_POST['last_name']),
                'phone_number' => sanitize_text_field($_POST['phone_number']),
                'address' => sanitize_textarea_field($_POST['address'])
            );

            $wpdb->insert($this->table_name, $data);
            wp_send_json_success('Contact saved successfully!');
        } else {
            wp_send_json_error('All fields are mandatory except address!');
        }
    }

    public function enqueue_scripts() {
       wp_enqueue_script('jquery');
        wp_enqueue_script('contact-form', plugin_dir_url(__FILE__) . 'contact-form.js', array('jquery'));
    }
  
    public function display_contacts() {
        global $wpdb;
        
        ?>
    <!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
body {
  font-family: Arial, Helvetica, sans-serif;
  background-color: black;
}

* {
  box-sizing: border-box;
}

/* Add padding to containers */
.container {
  padding: 16px;
  background-color: white;
}

/* Full-width input fields */
input[type=text] {
  width: 100%;
  padding: 15px;
  margin: 5px 0 22px 0;
  display: inline-block;
  border: none;
  background: #f1f1f1;
}

input[type=text]:focus {
  background-color: #ddd;
  outline: none;
}

/* Overwrite default styles of hr */
hr {
  border: 1px solid #f1f1f1;
  margin-bottom: 25px;
}

/* Set a style for the submit button */
.registerbtn {
  background-color: #04AA6D;
  color: white;
  padding: 16px 20px;
  margin: 8px 0;
  border: none;
  cursor: pointer;
  width: 100%;
  opacity: 0.9;
}

.registerbtn:hover {
  opacity: 1;
}

/* Add a blue text color to links */
a {
  color: dodgerblue;
}

</style>
</head>
<body>
    <?php
if ( isset( $_POST['submit'] ) ){

    global $wpdb;
    
    $tablename = $wpdb->prefix.'contacts';
    
    $wpdb->insert( $tablename, array(
    
    'email' =>$_POST['email'],
    
    'first_name' =>$_POST['first_name'],
    
    'last_name' =>$_POST['last_name'],
    
    'phone_number' => $_POST['phone_number'],
    
    'address' => $_POST['address']
    
     ),
    
    array('%s', '%s', '%s', '%s', '%s' )
    
    );
    
    }
?>
<form action="" method="POST">
  <div class="container">
    <h1>Register</h1>
    <p>Please fill in this form to create an account.</p>
    <hr>

    <label for="email"><b>Email</b></label>
    <input type="text" placeholder="Enter Email" name="email" id="email" required>

    <label for="first_name"><b>First Name</b></label>
    <input type="text" placeholder="Enter First Name" name="first_name" id="first_name" required>

    <label for="last_name"><b>Last Name</b></label>
    <input type="text" placeholder=" Enter Last Name" name="last_name" id="last_name" required>

    <label for="phone_number"><b>Phone Number</b></label>
    <input type="text" placeholder="Enter Phone Number" name="phone_number" id="phone_number" required>

    <label for="address"><b>Address</b></label>
    <input type="text" placeholder="Enter Address" name="address" id="address">
    <hr>
    <button type="submit" name = "submit" class="registerbtn">Register</button>
  </div>
</form>
</body>
</html>
<?php

    }
}

new ContactManagementPlugin();
