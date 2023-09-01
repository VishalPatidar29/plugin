<?php

/*
* Plugin Name: Task Plugin

* Description: The Task Plugin is for Demo Purpose.

* Version: 1.0.0

* Author: Zehntech Technologies Pvt. Ltd.

* Author URI: https://www.zehntech.com/

* License: GPL2

* License URI: https://www.gnu.org/licenses/gpl-2.0.html


*/

// defined('ABSPATH') || exit;

// this cose add the jQuery in over plugin
wp_enqueue_script('jquery');



// this code add the setting page in admin panel
    add_action('admin_menu', 'test_plugin_setup_menu');
    

function test_plugin_setup_menu(){
    add_menu_page( 'Test_Plugin_Page', 'Setting Page', 'manage_options', 'test-plugin', 'test_init' );
}
 
function test_init(){
?>
    <form method="post">
    <input type="checkbox" id="approve" name="approve" value="approve">
    <label for="approve"> Aprrove Users</label><br>
    <input type="checkbox" id="alluser" name="alluser" value="alluser">
    <label for="notapprove">All Users</label><br>
    <button type="submit" name="export_data">Download</button>
  </form>


<?php

}
 



// this code add the contact and address section in user meta field
function new_contact_methods( $contactmethods ) {
    $contactmethods['phone'] = 'Phone Number';
    $contactmethods['address'] = 'Address';
    return $contactmethods;
}
add_filter( 'user_contactmethods', 'new_contact_methods', 10, 1 );




// this  code show the  value in user panel
function new_modify_user_table( $column ) {
    $column['phone'] = 'Phone';
    $column['address'] = 'Address';
    return $column;
}
add_filter( 'manage_users_columns', 'new_modify_user_table',50,3 );

function new_modify_user_table_row( $val, $column_name, $user_id ) {
    switch ($column_name) {
        case 'phone' :
            return get_the_author_meta( 'phone', $user_id );
        case 'address' :
            return get_the_author_meta('address',$user_id); 
    
        default:
    }
    return $val;
}
add_filter( 'manage_users_custom_column', 'new_modify_user_table_row', 10, 3 );




// custom approve button add and show the butoon in user panel

function add_display_column( $columns){
    $columns['display_as'] = 'Approve';
    return $columns;
}

add_filter( 'manage_users_columns', 'add_display_column',10,1);


add_filter('manage_users_custom_column',  'add_display_value', 50, 3);	// we use a lower priority (higher number, 50) here so as to fire after the custom_metadata plugin, which interferes. Damn plugin
function add_display_value( $value, $column_name, $user_id ){
	$user = get_userdata( $user_id );
	switch ($column_name) {
		case 'display_as' :
			$buttons =  '<label><input type="radio" class="save_display" name="display_as-'.$user_id.'" value="approve" '.checked( 'approve' == $user->display_as, true, false ).'>Approve</label><br />'.
						'<label><input type="radio" class="save_display" name="display_as-'.$user_id.'" value="notapprove" '.checked( 'notapprove' == $user->display_as, true, false ).'>Not Approve</label>';
			return $buttons;
			break;
		default:
	}

	return $value;
}






// ajax for save the value

add_action('wp_ajax_save_display_value', 'save_display_value');
function save_display_value(){
	$value = $_POST['value'];
	$user_id = $_POST['userid'];
	update_usermeta($user_id, 'display_as', $value );
	echo 'success';
	exit();
}


add_action('admin_footer', 'save_display_value_javascript'); 
function save_display_value_javascript() {
	global $current_screen;
	if ($current_screen->id != 'users') return; ?>
	<script type="text/javascript">
		jQuery('.save_display').click(function() {
			var value = jQuery(this).val();
			var userid = jQuery(this).attr('name').split('-')[1];
			jQuery.post('<?php echo admin_url("admin-ajax.php") ?>', { action: 'save_display_value', userid: userid, value: value }, function( response ) {  console.log('successfull add');/* do nothing */ } );
		});
	</script>
    
	<?php
}





// export data code for approve user

// Handle the export data functionality
function export_user_data() {
    if (isset($_POST['export_data']) && isset($_POST['alluser'])) {
        global $wpdb;
        
        $users = get_users();
        // echo "<pre>";
        // print_r($user);
        // die("");
        $csv_output = "User ID     ||     Username      ||     Email                ||     Number          ||     Address\n"; // CSV header
        
        foreach ($users as $user) {
          
            $csv_output .= "{$user->ID}           ||     {$user->user_login}        ||     {$user->user_email}     ||";
            $usermeta = get_user_meta($user->ID);
            $csv_output .= "     {$usermeta['phone'][0]}     ||     {$usermeta['address'][0]}\n";
           
        }
        // Generate CSV file
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="user_data.csv"');
        echo $csv_output;
        exit();
    }
else if(isset($_POST['approve'])){

    global $wpdb;
        
    $users = get_users();
    $csv_output = "User ID     ||     Username      ||     Email                ||     Number          ||     Address\n"; // CSV header

    foreach ($users as $user) {
        $usermeta = get_user_meta($user->ID);
        $data = $usermeta['display_as'][0];
        if( $data == 'approve'){

            $csv_output .= "{$user->ID}           ||     {$user->user_login}        ||     {$user->user_email}     ||";
            $csv_output .= "     {$usermeta['phone'][0]}     ||     {$usermeta['address'][0]}\n";
        }

     }
       // Generate CSV file
       header('Content-Type: text/csv');
       header('Content-Disposition: attachment; filename="user_data.csv"');
       echo $csv_output;
       exit();


}
}

add_action('init', 'export_user_data');


?>