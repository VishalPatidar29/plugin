function user_data_export_button() {
    return '<form method="post"><button type="submit" name="export_data">Export User Data</button></form>';
}
add_shortcode('user_data_export', 'user_data_export_button');

// Handle the export data functionality
function export_user_data() {
    if (isset($_POST['export_data'])) {
        global $wpdb;
        
        $users = get_users(); // Retrieve all users
        
        $csv_output = "User ID,Username,Email\n"; // CSV header
        
        foreach ($users as $user) {
            $csv_output .= "{$user->ID},{$user->user_login},{$user->user_email}\n";
        }
        
        // Generate CSV file
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="user_data.csv"');
        echo $csv_output;
        exit();
    }
}
add_action('init', 'export_user_data');