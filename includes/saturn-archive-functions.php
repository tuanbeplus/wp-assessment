<?php
class WPA_Saturn_Archive {

public $archive_orgs_table;
public $archive_posts_table;
public $archive_postmeta_table;
public $archive_users_table;
public $archive_usermeta_table;

public function __construct() {
    
    if (is_admin()) {
        add_action('admin_menu', array($this, 'register_saturn_archive_page'));
    }
    add_action('wp_ajax_get_client_org_data_ajax', array($this, 'get_client_org_data_ajax'));
    add_action('wp_ajax_archive_client_org_ajax', array($this, 'archive_client_org_ajax'));
    add_action('wp_ajax_restore_archived_org_data_ajax', array($this, 'restore_archived_org_data_ajax'));
    add_action('wp_ajax_delete_archived_org_data_ajax', array($this, 'delete_archived_org_data_ajax'));
    add_action('wp_ajax_delete_current_org_ajax', array($this, 'delete_current_org_ajax'));

    $this->set_archive_tables();
    $this->create_archive_orgs_table();
    $this->create_archive_tables();
}

/**
 * Set archive tables name.
 */
function set_archive_tables() {
    global $wpdb;
    $this->archive_orgs_table = $wpdb->prefix . "archive_client_orgs";
    $this->archive_posts_table = $wpdb->prefix . "archive_posts";
    $this->archive_postmeta_table = $wpdb->prefix . "archive_postmeta";
    $this->archive_users_table = $wpdb->prefix . "archive_users";
    $this->archive_usermeta_table = $wpdb->prefix . "archive_usermeta";
}

/**
 * Create the archive_client_orgs table if it does not exist
 */
private function create_archive_orgs_table() {
    try {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $this->archive_orgs_table;

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            org_id VARCHAR(100) NOT NULL,
            org_name VARCHAR(255) NOT NULL,
            archived_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            archived_by int(11) NOT NULL,
            posts JSON,
            users JSON,
            attachments JSON
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $result = dbDelta($sql);
        
        if ($wpdb->last_error) {
            error_log('Archive table creation error: ' . $wpdb->last_error);
            return false;
        }
        return true;
    } catch (Exception $e) {
        error_log('Archive table creation exception: ' . $e->getMessage());
        return false;
    }
}

/**
 * Create archive tables for posts and users if they don't exist
 */
private function create_archive_tables() {
    try {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Generate SQL for archive tables
        $sql_posts = $this->get_archive_create_sql($wpdb->posts, $this->archive_posts_table);
        $sql_postmeta = $this->get_archive_create_sql($wpdb->postmeta, $this->archive_postmeta_table);
        $sql_users = $this->get_archive_create_sql($wpdb->users, $this->archive_users_table);
        $sql_usermeta = $this->get_archive_create_sql($wpdb->usermeta, $this->archive_usermeta_table);

        // Run dbDelta for table creation
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_posts);
        dbDelta($sql_postmeta);
        dbDelta($sql_users);
        dbDelta($sql_usermeta);

        return true;

    } catch (Exception $e) {
        error_log('Create archive tables error: ' . $e->getMessage());
        return false;
    }
}

// Helper function to generate CREATE TABLE for archive tables
private function get_archive_create_sql($table_name, $archive_name) {
    global $wpdb;
    $structure = $wpdb->get_row("SHOW CREATE TABLE $table_name", ARRAY_N);
    if (!$structure) {
        throw new Exception("Failed to retrieve structure for $table_name");
    }
    $create_sql = $structure[1];

    // Ensure proper table replacement (remove backticks)
    $create_sql = preg_replace("/CREATE TABLE `?$table_name`?/i", "CREATE TABLE IF NOT EXISTS `$archive_name`", $create_sql);
    
    return $create_sql;
}

/**
 * Validate JSON string
 *
 * @param string $json JSON string to validate
 * @return bool
 */
private function validate_json($json) {
    if (empty($json)) return false;
    json_decode($json);
    return (json_last_error() === JSON_ERROR_NONE);
}

/**
 * Check if organisation is already archived
 *
 * @param string $org_id Organisation ID
 * @return bool
 */
private function is_org_archived($org_id) {
    global $wpdb;
    $table_name = $this->archive_orgs_table;
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table_name} WHERE org_id = %s",
        $org_id
    ));
    return (int)$result > 0;
}

/**
 * Get all archived client organisations
 * 
 * @return array Array of archived organisation records
 */
function get_all_archived_client_orgs() {
    try {
        global $wpdb;
        $table_name = $this->archive_orgs_table;
        
        // Get all records ordered by archived date descending
        $results = $wpdb->get_results(
            "SELECT aco.*, u.display_name as archived_by_name 
            FROM {$table_name} aco
            LEFT JOIN {$wpdb->users} u ON aco.archived_by = u.ID
            ORDER BY aco.archived_date ASC",
            ARRAY_A
        );

        if ($results === null) {
            throw new Exception($wpdb->last_error);
        }

        return $results;

    } catch (Exception $e) {
        error_log('Get archived client organisations error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Register saturn archive page
 */
function register_saturn_archive_page() {
    add_options_page(
        'Saturn Archive', // Page title
        'Saturn Archive', // Menu title
        'manage_options', // Capability required
        'saturn-archive', // Menu slug
        array($this, 'render_saturn_archive_page') // Callback function
    );
}

/**
 * Render the settings page content
 */
function render_saturn_archive_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    return wpa_get_template_admin_view('saturn-archive', 'saturn-archive-view');
}

/**
 * Ajax get all org data in Saturn
 */
function get_client_org_data_ajax() {
    try {
        // Fix parameter validation
        $org_id = sanitize_text_field($_POST['org_id'] ?? '');
        $org_name = sanitize_text_field($_POST['org_name'] ?? ''); // Fixed typo from 'org_anme'
        
        if (empty($org_id)) {
            throw new Exception('Organisation not found.');
        }

        // Properly structure the data collection
        $data = [
            'users' => [],
            'submissions' => [],
            'dcr_submissions' => [],
            'reports' => [],
            'dcr_reports' => [],
            'attachments' => [],
        ];

        $post_ids = [];
        $user_ids = [];

        // Get users
        $users = get_users(array(
            'meta_key' => '__salesforce_account_id',
            'meta_value' => $org_id,
        ));

        if (!empty($users)) {
            // Format user data
            foreach ($users as $user) {
                $data['users'][] = $user->user_login;
                $user_ids[] = $user->ID;
            }
        }
        $user_names = array_unique($data['users']) ?? [];

        // Get posts data with proper error handling
        $post_types = ['submissions', 'dcr_submissions', 'reports', 'dcr_reports'];
        foreach ($post_types as $post_type) {
            $posts = get_posts([
                'post_type' => $post_type,
                'post_status' => 'any',
                'numberposts' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'organisation_id',
                        'value' => $org_id,
                        'compare' => '='
                    ),
                ),
            ]);

            $data[$post_type] = array_map(function($post) {
                return $post->post_title;
            }, $posts);
            array_unique($data[$post_type]);

            $post_ids[] = array_map(function($post) {
                if (!empty($post->ID)) {
                    return $post->ID;
                }
            }, $posts);
            array_unique($post_ids);
        }

        // Get files uploaded
        $azure = new WP_Azure_Storage();
        $azure_files_uploaded = $azure->get_azure_files_uploaded_by_org($org_id) ?? [];
        $attachment_ids = [];
        foreach ($azure_files_uploaded as $row) {
            if (isset($row->attachment_id) && !empty($row->attachment_id)) {
                $attachment_ids[] = intval($row->attachment_id);
            }
        }

        // Generate HTML with the collected data
        ob_start();
        ?>
        <h3>Organisation: <?php echo esc_html($org_name); ?></h3>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Contacts (<?php echo count($user_names); ?>)</th>
                    <th>Index Submissions (<?php echo count($data['submissions']); ?>)</th>
                    <th>DCR Submissions (<?php echo count($data['dcr_submissions']); ?>)</th>
                    <th>Index Reports (<?php echo count($data['reports']); ?>)</th>
                    <th>DCR Reports (<?php echo count($data['dcr_reports']); ?>)</th>
                    <th>Documents uploaded (<?php echo count($azure_files_uploaded); ?>)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <ul>
                        <?php foreach($user_names as $user_name): ?>
                            <li><?php echo esc_html($user_name); ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </td>
                    <td>
                        <ul>
                        <?php foreach($data['submissions'] as $submission): ?>
                            <li><?php echo esc_html($submission); ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </td>
                    <td>
                        <ul>
                        <?php foreach($data['dcr_submissions'] as $submission): ?>
                            <li><?php echo esc_html($submission); ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </td>
                    <td>
                        <ul>
                        <?php foreach($data['reports'] as $report): ?>
                            <li><?php echo esc_html($report); ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </td>
                    <td>
                        <ul>
                        <?php foreach($data['dcr_reports'] as $report): ?>
                            <li><?php echo esc_html($report); ?></li>
                        <?php endforeach; ?>
                        </ul>
                    </td>
                    <td><?php if (count($azure_files_uploaded) > 0) echo count($azure_files_uploaded) . ' files'; ?></td>
                </tr>
            </tbody>
        </table>
        <input type="hidden" name="archive_posts" value="<?php echo esc_attr(json_encode(array_merge(...array_filter(array_unique($post_ids))))); ?>">
        <input type="hidden" name="archive_users" value="<?php echo esc_attr(json_encode(array_unique($user_ids))); ?>">
        <input type="hidden" name="archive_atts" value="<?php echo esc_attr(json_encode(array_unique($attachment_ids))); ?>">
        <?php
        $html = ob_get_clean();

        wp_send_json_success([
            'html' => $html,
            'data' => $data
        ]);

    } catch (Exception $exception) {
        wp_send_json_error([
            'message' => $exception->getMessage()
        ]);
    }
}

/**
 * Insert archive client organisation data
 *
 * @param string $org_id        The organisation ID from Salesforce
 * @param string $org_name      The organisation name
 * @param string|null $posts The JSON posts data (can be null)
 * @param string|null $users The JSON users data (can be null)
 * @param string|null $att_ids The JSON attachments data (can be null)
 * @return array{success: bool, message: string, id?: int} Array containing operation result
 */
private function insert_archive_client_org($org_id, $org_name, $posts = null, $users = null, $att_ids = null) {
    try {
        // Input validation
        if (empty($org_id) || empty($org_name)) {
            throw new Exception('Organisation ID and name are required.');
        }
        global $wpdb;
        $table_name = $this->archive_orgs_table;
        $current_user_id = get_current_user_id();

        // Check if organisation already archived
        if ($this->is_org_archived($org_id)) {
            throw new Exception('Organisation is already archived.');
        }

        // Convert arrays to JSON for storage
        $posts = !empty($posts) ? json_encode($posts) : null;
        $users = !empty($users) ? json_encode($users) : null;
        $att_ids = !empty($att_ids) ? json_encode($att_ids) : null;
            
        // Prepare data for insertion
        $data = [
            'org_id' => $org_id,
            'org_name' => $org_name,
            'archived_date' => current_time('mysql'),
            'archived_by' => $current_user_id,
            'posts' => $posts,
            'users' => $users,
            'attachments' => $att_ids,
        ];

        // Prepare format specifiers
        $format = [
            '%s', // org_id
            '%s', // org_name
            '%s', // archived_date
            '%d', // archived_by
            '%s', // posts (JSON string)
            '%s', // users (JSON string)
            '%s', // attachments (JSON string)
        ];

        // Insert data into the archive table
        $result = $wpdb->insert($table_name, $data, $format);

        if ($result === false) {
            throw new Exception($wpdb->last_error ?: 'Failed to insert archive record.');
        }

        return [
            'status' => true,
            'message' => sprintf('Successfully archived organisation: %s', $org_name),
            'id' => $wpdb->insert_id
        ];

    } catch (Exception $e) {
        return [
            'status' => false,
            'message' => $e->getMessage()
        ];
    }
}

/**
 * Ajax add org to archive table
 */
function archive_client_org_ajax() {
    try {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            throw new Exception('You do not have permission to perform this action.');
        }
        
        // Validate and sanitize input parameters
        $org_id = isset($_POST['org_id']) ? sanitize_text_field($_POST['org_id']) : '';
        $org_name = isset($_POST['org_name']) ? sanitize_text_field($_POST['org_name']) : '';
        $post_ids = isset($_POST['archive_posts']) ? json_decode(stripslashes($_POST['archive_posts']), true) : [];
        $user_ids = isset($_POST['archive_users']) ? json_decode(stripslashes($_POST['archive_users']), true) : [];
        $attachment_ids = isset($_POST['archive_atts']) ? json_decode(stripslashes($_POST['archive_atts']), true) : [];
        
        if (empty($org_id) || empty($org_name)) {
            throw new Exception('Organisation ID and name are required.');
        }
        
        // Insert the organisation into the archive table
        $insert_result = $this->insert_archive_client_org($org_id, $org_name, $post_ids, $user_ids, $attachment_ids);
        if ($insert_result['status'] !== true) { 
            throw new Exception($insert_result['message']);
        }

        if (!empty($post_ids)) {
            $move_posts_result = $this->move_posts_data_to_archive($post_ids);
            if ($move_posts_result === false) {
                throw new Exception('Failed to move posts data to archive table.');
            }
        }

        if (!empty($user_ids)) {
            $move_users_result = $this->move_users_data_to_archive($user_ids);
            if ($move_users_result === false) {
                throw new Exception('Failed to move users data to archive table.');
            }
        }

        if (!empty($attachment_ids)) {
            $update_result = $this->update_azure_attachments_archived($attachment_ids);
            if ($update_result === false) {
                throw new Exception('Failed to update Azure attachments archive status.');
            }
        }

        $current_user = wp_get_current_user();

        $this->log_saturn_activity('ARCHIVE', $org_name, $org_id);
        
        // Return success response
        return wp_send_json([
            'status' => true,
            'message' => $insert_result['message'],
            'org_id' => $org_id,
            'archive_id' => $insert_result['id'],
            'author' => esc_html($current_user->display_name),
            'time' => date('M d Y, h:i a'),
        ]);
        
    } catch (Exception $e) {
        return wp_send_json(['message' => $e->getMessage(), 'status' => false]);
    }
}

/**
 * Get all organisations are existing in Saturn
 */
function get_all_existing_client_orgs() {
    // Get all WP users
    $users = get_users();

    $client_org_data = [];
    if (!empty($users)) {
        foreach ($users as $index => $user) {
            $org_data = get_user_meta($user->ID, '__salesforce_account_json', true);
            $org_data = json_decode($org_data, true) ?? [];
            if (!empty($org_data['Id']) && !empty($org_data['Name'])) {
                $client_org_data[$index]['Id'] = $org_data['Id'];
                $client_org_data[$index]['Name'] = $org_data['Name'];
            }
        }
    }
    if (!empty($client_org_data)) {
        // Merge items with same Id and sort by Name
        $merged_data = [];
        foreach ($client_org_data as $item) {
            $id = $item['Id'];
            if (!isset($merged_data[$id])) {
                $merged_data[$id] = $item;
            }
        }
        $client_org_data = array_values($merged_data);
        // Sort by Name
        usort($client_org_data, function($a, $b) {
            return strcmp($a['Name'], $b['Name']);
        });
    }
    return $client_org_data ?? [];
}

/**
 * Move post data to archive tables
 *
 * @param array $post_ids Array of Post IDs to archive
 * @return bool Success status
 */
private function move_posts_data_to_archive($post_ids) {
    try {
        global $wpdb;

        if (empty($post_ids) || !is_array($post_ids)) {
            return false;
        }

        // Convert array to comma-separated string for IN clause
        $post_ids_string = implode(',', array_map('intval', $post_ids));

        // Insert posts into archive table using batch insert
        $wpdb->query("
            INSERT INTO {$this->archive_posts_table}
            SELECT * FROM {$wpdb->posts}
            WHERE ID IN ($post_ids_string)
        ");

        // Insert post meta into archive table using batch insert
        $wpdb->query("
            INSERT INTO {$this->archive_postmeta_table} (post_id, meta_key, meta_value)
            SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta}
            WHERE post_id IN ($post_ids_string)
        ");

        // Optional: Delete original data after archiving
        $wpdb->query("DELETE FROM {$wpdb->posts} WHERE ID IN ($post_ids_string)");
        $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id IN ($post_ids_string)");

        return true;

    } catch (Exception $e) {
        error_log('Archive post data error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Move user data to archive tables
 *
 * @param array $user_ids Array of User IDs to archive
 * @return bool Success status
 */
private function move_users_data_to_archive($user_ids) {
    try {
        global $wpdb;

        if (empty($user_ids) || !is_array($user_ids)) {
            return false;
        }

        // Convert array to comma-separated string for IN clause
        $user_ids_string = implode(',', array_map('intval', $user_ids));

        // Insert users into archive table using batch insert
        $wpdb->query("
            INSERT INTO {$this->archive_users_table}
            SELECT * FROM {$wpdb->users}
            WHERE ID IN ($user_ids_string)
        ");

        // Insert user meta into archive table using batch insert
        $wpdb->query("
            INSERT INTO {$this->archive_usermeta_table} (user_id, meta_key, meta_value)
            SELECT user_id, meta_key, meta_value FROM {$wpdb->usermeta}
            WHERE user_id IN ($user_ids_string)
        ");

        // Optional: Delete original data after archiving
        $wpdb->query("DELETE FROM {$wpdb->users} WHERE ID IN ($user_ids_string)");
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE user_id IN ($user_ids_string)");

        return true;

    } catch (Exception $e) {
        error_log('Archive user data error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Update Azure storage attachments to mark as archived
 *
 * @param array $attachment_ids Array of attachment IDs to mark as archived
 * @return bool Success status
 */
private function update_azure_attachments_archived($attachment_ids) {
    try {
        global $wpdb;
        $azure = new WP_Azure_Storage();
        $table_name = $azure->get_azure_storage_table();

        if (empty($attachment_ids) || !is_array($attachment_ids)) {
            return false;
        }

        // Convert array to comma-separated string for IN clause
        $attachment_ids_string = implode(',', array_map('intval', $attachment_ids));

        // Get attachments that need to be archived
        $attachments = $wpdb->get_results(
            "SELECT * FROM $table_name 
            WHERE attachment_id IN ($attachment_ids_string)"
        );

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        foreach ($attachments as $attachment) {
            // Move blob to archive storage
            $move_result = $azure->move_blob_between_storage_accounts($attachment->attachment_path, 'archive');
            
            if ($move_result['status']) {
                // Update record with new path and archived flag
                $wpdb->update(
                    $table_name,
                    array(
                        'is_archived' => 1,
                        'attachment_path' => $move_result['url'],
                    ),
                    array('id' => $attachment->id)
                );

                if ($wpdb->last_error) {
                    throw new Exception($wpdb->last_error);
                }
            } else {
                error_log('Failed to move blob to archive: ' . $move_result['message']);
                return false;
            }
        }

        return true;

    } catch (Exception $e) {
        error_log('Update Azure attachments archived error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Restore archived users and user meta
 *
 * @param array $user_ids Array of user IDs to restore
 * @return bool Success status
 */
private function restore_archived_users($user_ids) {
    try {
        global $wpdb;

        if (empty($user_ids) || !is_array($user_ids)) {
            return false;
        }

        // Convert array to comma-separated string for IN clause
        $user_ids_string = implode(',', array_map('intval', $user_ids));

        // Restore users
        $wpdb->query(
            "INSERT INTO {$wpdb->users} 
            SELECT * FROM {$this->archive_users_table} 
            WHERE ID IN ($user_ids_string)"
        );

        // Restore user meta
        $wpdb->query(
            "INSERT INTO {$wpdb->usermeta} 
            SELECT * FROM {$this->archive_usermeta_table} 
            WHERE user_id IN ($user_ids_string)"
        );

        // Delete from archive tables
        $wpdb->query("DELETE FROM {$this->archive_users_table} WHERE ID IN ($user_ids_string)");
        $wpdb->query("DELETE FROM {$this->archive_usermeta_table} WHERE user_id IN ($user_ids_string)");

        return true;

    } catch (Exception $e) {
        error_log('Restore users error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Restore archived posts and post meta
 *
 * @param array $post_ids Array of post IDs to restore
 * @return bool Success status
 */
private function restore_archived_posts($post_ids) {
    try {
        global $wpdb;

        if (empty($post_ids) || !is_array($post_ids)) {
            return false;
        }

        // Convert array to comma-separated string for IN clause
        $post_ids_string = implode(',', array_map('intval', $post_ids));

        // Restore posts
        $wpdb->query(
            "INSERT INTO {$wpdb->posts} 
            SELECT * FROM {$this->archive_posts_table} 
            WHERE ID IN ($post_ids_string)"
        );

        // Restore post meta
        $wpdb->query(
            "INSERT INTO {$wpdb->postmeta} 
            SELECT * FROM {$this->archive_postmeta_table} 
            WHERE post_id IN ($post_ids_string)"
        );

        // Delete from archive tables
        $wpdb->query("DELETE FROM {$this->archive_posts_table} WHERE ID IN ($post_ids_string)");
        $wpdb->query("DELETE FROM {$this->archive_postmeta_table} WHERE post_id IN ($post_ids_string)");

        return true;

    } catch (Exception $e) {
        error_log('Restore posts error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Restore archived attachments by moving blobs back from archive storage
 *
 * @param array $attachment_ids Array of attachment IDs to restore
 * @return bool Success status
 */
private function restore_archived_attachments($attachment_ids) {
    try {
        global $wpdb;
        $azure = new WP_Azure_Storage();
        $table_name = $azure->get_azure_storage_table();

        if (empty($attachment_ids) || !is_array($attachment_ids)) {
            return false;
        }

        // Convert array to comma-separated string for IN clause
        $attachment_ids_string = implode(',', array_map('intval', $attachment_ids));

        // Get attachments that need to be restored
        $attachments = $wpdb->get_results(
            "SELECT * FROM $table_name 
            WHERE attachment_id IN ($attachment_ids_string)"
        );

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        foreach ($attachments as $attachment) {
            // Move blob back from archive storage
            $move_result = $azure->move_blob_between_storage_accounts($attachment->attachment_path, 'restore');
            
            if ($move_result['status']) {
                // Update record with new path and archived flag
                $wpdb->update(
                    $table_name,
                    [
                        'attachment_path' => $move_result['url'],
                        'is_archived' => 0
                    ],
                    ['attachment_id' => $attachment->attachment_id]
                );
            } else {
                throw new Exception('Failed to restore file from archive: ' . $move_result['message']);
            }
        }

        return true;

    } catch (Exception $e) {
        error_log('Restore attachments error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Restore all archived data for an organisation via AJAX
 *
 * @return void Outputs JSON response
 */
public function restore_archived_org_data_ajax() {
    try {
        // Get archive ID from POST
        if (!isset($_POST['archive_id']) || empty($_POST['archive_id'])) {
            throw new Exception('Archive ID is required');
        }

        $archive_id = intval($_POST['archive_id']);
        global $wpdb;

        // Get archive record
        $archive_data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->archive_orgs_table} WHERE id = %d", $archive_id)
        );

        if (empty($archive_data)) {
            throw new Exception('Archive record not found');
        }

        // Decode JSON data
        $org_id = $archive_data->org_id ?? '';
        $org_name = $archive_data->org_name ?? '';
        $post_ids = !empty($archive_data->posts) ? json_decode($archive_data->posts, true) : [];
        $user_ids = !empty($archive_data->users) ? json_decode($archive_data->users, true) : [];
        $att_ids = !empty($archive_data->attachments) ? json_decode($archive_data->attachments, true) : [];

        // Restore data
        if (!empty($post_ids) && !$this->restore_archived_posts($post_ids)) {
            throw new Exception('Failed to restore posts');
        }

        if (!empty($user_ids) && !$this->restore_archived_users($user_ids)) {
            throw new Exception('Failed to restore users');
        }

        if (!empty($att_ids) && !$this->restore_archived_attachments($att_ids)) {
            throw new Exception('Failed to restore attachments');
        }

        // Delete archive record
        $wpdb->delete($this->archive_orgs_table, ['id' => $archive_id]);

        $this->log_saturn_activity('RESTORE', $org_name, $org_id);

        wp_send_json([
            'status' => true,
            'message' => 'Organisation data restored successfully.',
            'archive_id' => $archive_id,
            'org_id' => $org_id,
            'org_name' => $org_name,
        ]);

    } catch (Exception $e) {
        wp_send_json([
            'status' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Delete archived posts and their meta from archive tables
 *
 * @param array $post_ids Array of post IDs to delete
 * @return bool True on success, false on failure
 */
private function delete_archived_posts($post_ids) {
    try {
        if (empty($post_ids) || !is_array($post_ids)) {
            return false;
        }

        global $wpdb;
        $posts_table = $this->archive_posts_table;
        $postmeta_table = $this->archive_postmeta_table;

        // Convert array to comma-separated string for IN clause
        $post_ids_string = implode(',', array_map('intval', $post_ids));

        // Delete posts from archive table
        $wpdb->query(
            "DELETE FROM $posts_table 
            WHERE ID IN ($post_ids_string)"
        );

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        // Delete post meta from archive table
        $wpdb->query(
            "DELETE FROM $postmeta_table 
            WHERE post_id IN ($post_ids_string)"
        );

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        return true;

    } catch (Exception $e) {
        error_log('Delete archived posts error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Delete archived users and their meta from archive tables
 *
 * @param array $user_ids Array of user IDs to delete
 * @return bool True on success, false on failure
 */
private function delete_archived_users($user_ids) {
    try {
        if (empty($user_ids) || !is_array($user_ids)) {
            return false;
        }

        global $wpdb;
        $users_table = $this->archive_users_table;
        $usermeta_table = $this->archive_usermeta_table;

        // Convert array to comma-separated string for IN clause
        $user_ids_string = implode(',', array_map('intval', $user_ids));

        // Delete users from archive table
        $wpdb->query(
            "DELETE FROM $users_table 
            WHERE ID IN ($user_ids_string)"
        );

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        // Delete user meta from archive table
        $wpdb->query(
            "DELETE FROM $usermeta_table 
            WHERE user_id IN ($user_ids_string)"
        );

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        return true;

    } catch (Exception $e) {
        error_log('Delete archived users error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Delete attachments from the database and Azure storage
 *
 * @param array $attachment_ids Array of attachment IDs to delete
 * @return bool True on success, false on failure
 */
private function delete_attachments($attachment_ids) {
    try {
        if (empty($attachment_ids) || !is_array($attachment_ids)) {
            return false;
        }

        global $wpdb;
        $azure = new WP_Azure_Storage();
        $attachments_table = $azure->get_azure_storage_table();

        // Convert array to comma-separated string for IN clause
        $attachment_ids_string = implode(',', array_map('intval', $attachment_ids));

        // Get attachment URLs before deletion
        $attachment_urls = $wpdb->get_col(
            "SELECT attachment_path FROM $attachments_table WHERE attachment_id IN ($attachment_ids_string)"
        );

        // Delete attachments from the database
        $wpdb->query(
            "DELETE FROM $attachments_table 
            WHERE attachment_id IN ($attachment_ids_string)"
        );

        if ($wpdb->last_error) {
            throw new Exception($wpdb->last_error);
        }

        if (!empty($attachment_urls) && is_array($attachment_urls)) {
            // Delete blobs from Azure storage
            foreach ($attachment_urls as $url) {
                $response = $azure->delete_blob_by_url($url);
                if (!$response['status']) {
                    throw new Exception('Failed to delete blob: ' . $response['message']);
                }
            }
        }
        
        return true;

    } catch (Exception $e) {
        error_log('Delete attachments error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Delete all quiz data and feedbacks for a specific organisation
 * 
 * @param string $org_id The organisation ID to delete data for
 * @return bool True on success, false on failure
 */
private function delete_org_quiz_data($org_id) {
    try {
        // Validate input
        if (empty($org_id) || !is_string($org_id)) {
            throw new InvalidArgumentException('Invalid organisation ID');
        }

        global $wpdb;
        
        // Initialize required classes
        $main = new WP_Assessment();
        $feedbacks = new AndSubmissionFeedbacks();
        
        // Get table names
        $index_quiz_table = $main->get_quiz_table();
        $dcr_quiz_table = $main->get_dcr_quiz_table();
        $feedbacks_table = $feedbacks->get_submission_feedbacks_table();

        // Start transaction for atomic operations
        $wpdb->query('START TRANSACTION');

        // Delete from index quiz table
        $index_result = $wpdb->delete(
            $index_quiz_table,
            array('organisation_id' => $org_id),
            array('%s')
        );

        // Delete from DCR quiz table
        $dcr_result = $wpdb->delete(
            $dcr_quiz_table,
            array('organisation_id' => $org_id),
            array('%s')
        );

        // Delete from feedbacks table
        $feedback_result = $wpdb->delete(
            $feedbacks_table,
            array('organisation_id' => $org_id),
            array('%s')
        );

        // Check for errors
        if ($wpdb->last_error) {
            throw new Exception('Database error: ' . $wpdb->last_error);
        }

        // Commit transaction
        $wpdb->query('COMMIT');

        return true;

    } catch (InvalidArgumentException $e) {
        error_log('Validation error in delete_org_quiz_data: ' . $e->getMessage());
        return false;
    } catch (Exception $e) {
        // Rollback transaction on error
        $wpdb->query('ROLLBACK');
        error_log('Error in delete_org_quiz_data: ' . $e->getMessage());
        return false;
    }
}


/**
 * Delete archived organisation data via AJAX
 */
public function delete_archived_org_data_ajax() {
    try {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            throw new Exception('You do not have permission to perform this action.');
        }

        // Get and validate archive ID
        if (!isset($_POST['archive_id']) || empty($_POST['archive_id'])) {
            throw new Exception('Archive ID is required');
        }
        $archive_id = intval($_POST['archive_id']);
        
        global $wpdb;

        // Get archive record
        $archive_data = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->archive_orgs_table} WHERE id = %d", $archive_id)
        );

        if (empty($archive_data)) {
            throw new Exception('Archive record not found');
        }

        // Decode JSON data
        $org_id   = $archive_data->org_id ?? '';
        $org_name = $archive_data->org_name ?? '';
        $post_ids = !empty($archive_data->posts) ? json_decode($archive_data->posts, true) : [];
        $user_ids = !empty($archive_data->users) ? json_decode($archive_data->users, true) : [];
        $att_ids  = !empty($archive_data->attachments) ? json_decode($archive_data->attachments, true) : [];

        // Delete posts permanently
        if (!empty($post_ids)) {
            // First delete from archive tables
            $delete_archived_posts = $this->delete_archived_posts($post_ids);
            if (!$delete_archived_posts) {
                throw new Exception('Failed to delete archived posts.');
            }
            // Then delete from main tables
            foreach ($post_ids as $post_id) {
                wp_delete_post($post_id, true);
            }
        }

        // Delete users
        if (!empty($user_ids)) {
            // First delete from archive tables
            $delete_archived_users = $this->delete_archived_users($user_ids);
            if (!$delete_archived_users) {
                throw new Exception('Failed to delete archived users.');
            }
            // Then delete from main tables
            foreach ($user_ids as $user_id) {
                if (get_userdata($user_id)) {
                    wp_delete_user($user_id);
                }
            }
        }

        // Delete attachments
        if (!empty($att_ids)) {
            $delete_atts = $this->delete_attachments($att_ids);
            if (!$delete_atts) {
                throw new Exception('Failed to delete attachments.');
            }
        }

        // Delete quiz data for the organisation
        $org_id = $archive_data->org_id ?? '';
        if (!empty($org_id)) {
            $this->delete_org_quiz_data($org_id);
        }

        // Delete archive record
        $delete_result = $wpdb->delete($this->archive_orgs_table, ['id' => $archive_id]);
        if (!$delete_result) {
            throw new Exception('Failed to delete archive record.');
        }

        $this->log_saturn_activity('DELETE', $org_name, $org_id);

        wp_send_json([
            'status' => true,
            'message' => 'Organisation data deleted successfully.',
            'archive_id' => $archive_id
        ]);

    } catch (Exception $e) {
        error_log('Delete archived org data error: ' . $e->getMessage());
        wp_send_json([
            'status' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Ajax delete organization data from main system
 */
public function delete_current_org_ajax() {
    try {
        // Check user capabilities
        if (!current_user_can('manage_options')) {
            throw new Exception('You do not have permission to perform this action.');
        }

        // Validate required inputs
        $org_id = isset($_POST['org_id']) ? sanitize_text_field($_POST['org_id']) : '';
        $org_name = isset($_POST['org_name']) ? sanitize_text_field($_POST['org_name']) : '';
        $post_ids = isset($_POST['archive_posts']) ? json_decode(stripslashes($_POST['archive_posts']), true) : [];
        $user_ids = isset($_POST['archive_users']) ? json_decode(stripslashes($_POST['archive_users']), true) : [];
        $att_ids = isset($_POST['archive_atts']) ? json_decode(stripslashes($_POST['archive_atts']), true) : [];

        // Delete posts and their meta
        if (!empty($post_ids) && is_array($post_ids)) {
            foreach ($post_ids as $post_id) {
                wp_delete_post($post_id, true); // Force delete
            }
        }

        // Delete users and their meta
        if (!empty($user_ids) && is_array($user_ids)) {
            foreach ($user_ids as $user_id) {
                if (get_userdata($user_id)) {
                    wp_delete_user($user_id);
                }
            }
        }

        // Delete attachments
        if (!empty($att_ids) && is_array($att_ids)) {
            $delete_atts = $this->delete_attachments($att_ids);
            if (!$delete_atts) {
                throw new Exception('Failed to delete attachments');
            }
        }

        // Delete quiz data for the organisation
        if (!empty($org_id)) {
            $this->delete_org_quiz_data($org_id);
        }

        $this->log_saturn_activity('DELETE', $org_name, $org_id);

        wp_send_json([
            'status' => true,
            'message' => 'Organization data deleted successfully',
            'deleted_posts' => $post_ids,
            'deleted_users' => $user_ids,
            'deleted_atts' => $att_ids
        ]);

    } catch (Exception $e) {
        wp_send_json([
            'status' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Update organisation_id meta for reports and dcr_reports post types
 * 
 * This function retrieves all posts from reports and dcr_reports post types,
 * extracts the organisation ID from the org_data meta field, and updates
 * the organisation_id meta field with this value.
 */
public function update_organisation_id_meta() {
    // Check if this is an admin request with the specific action
    if (isset($_GET['action']) && $_GET['action'] === 'update_organisation_id_meta' && current_user_can('manage_options')) {
        // Post types to update
        $post_types = ['reports', 'dcr_reports'];
        $updated_count = 0;
        $failed_count = 0;
        
        foreach ($post_types as $post_type) {
            // Get all posts of the current post type
            $posts = get_posts([
                'post_type' => $post_type,
                'post_status' => 'any',
                'numberposts' => -1,
                'fields' => 'ids',
            ]);
            
            foreach ($posts as $post_id) {
                // Get the org_data meta
                $org_data = get_post_meta($post_id, 'org_data', true);
                
                // Check if org_data exists and contains the Id key
                if (!empty($org_data) && isset($org_data['Id'])) {
                    $org_id = $org_data['Id'];
                    
                    // Update the organisation_id meta
                    $result = update_post_meta($post_id, 'organisation_id', $org_id);
                    
                    if ($result) {
                        $updated_count++;
                        echo "Updated organisation_id for {$post_type} ID: {$post_id} with value: {$org_id}<br>";
                    } else {
                        $failed_count++;
                        echo "Failed to update organisation_id for {$post_type} ID: {$post_id}<br>";
                    }
                } else {
                    $failed_count++;
                    echo "Missing org_data or Id for {$post_type} ID: {$post_id}<br>";
                }
            }
        }
        
        echo "<br>Update complete. Updated: {$updated_count}, Failed: {$failed_count}";
        exit;
    }
}

/**
 * Log Saturn archive activities to a history file
 * 
 * @param string $action_type The type of action (ARCHIVE, DELETE, RESTORE)
 * @param string $org_name The name of the organization
 * @param string $org_id The ID of the organization
 */
private function log_saturn_activity($action_type, $org_name, $org_id) {
    try {
        // Define the log directory and file path
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/saturn-archive';
        $log_file = $log_dir . '/saturn-archive-history.txt';

        // Create directory if it doesn't exist
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        // Create file if it doesn't exist
        if (!file_exists($log_file)) {
            file_put_contents($log_file, "");
        }

        // Get current user's display name
        $current_user = wp_get_current_user();
        $action_by = $current_user->display_name ?? '';

        // Prepare the log entry
        $timestamp = date('M d Y, H:i a');
        $log_entry = sprintf(
            "[%s] [%s] - Organisation: %s (ID: %s) by %s\n",
            $timestamp,
            strtoupper($action_type),
            $org_name,
            $org_id,
            $action_by
        );

        // Prepend the log entry to the file
        $current_content = file_get_contents($log_file);
        file_put_contents($log_file, $log_entry . $current_content);

        return true;

    } catch (Exception $e) {
        error_log('Saturn activity log error: ' . $e->getMessage());
        return false;
    }
}

}

add_action('init', function() {
    new WPA_Saturn_Archive();
});
