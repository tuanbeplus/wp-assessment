<?php 
/**
 * Class Azure Storage for Saturn
 * 
 * @author Tuan
 *
 */

use MicrosoftAzure\Storage\Common\Internal\Resources;
use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\ListBlobsOptions;
use MicrosoftAzure\Storage\Common\Exceptions\ServiceException;

class WP_Azure_Storage {

    public $azure_table_name;

    public function __construct() 
    {
        add_action('wp_ajax_save_attachments_azure_storage_ajax', array($this, 'save_attachments_azure_storage_ajax'));
        add_action('wp_ajax_nopriv_save_attachments_azure_storage_ajax', array($this, 'save_attachments_azure_storage_ajax'));

        add_action('wp_ajax_delete_azure_attachments_ajax', array($this, 'delete_azure_attachments_ajax'));
        add_action('wp_ajax_nopriv_delete_azure_attachments_ajax', array($this, 'delete_azure_attachments_ajax'));

        add_action('wp_ajax_create_sas_blob_url_azure_ajax', array($this, 'create_sas_blob_url_azure_ajax'));
        add_action('wp_ajax_nopriv_create_sas_blob_url_azure_ajax', array($this, 'create_sas_blob_url_azure_ajax'));

        add_action('init', array($this, 'report_create_azure_sas_blob_url'));
    
        $this->set_azure_storage_table();
        $this->init_azure_storage_attachments();
    }

    /**
	 * Set storage table name.
	 *
	 * @param string $storage table name.
	 *
	 */
    function set_azure_storage_table(): void
    {
        global $wpdb;
        $this->azure_table_name = $wpdb->prefix . "azure_storage_attachments";
    }

    /**
	 * Get storage table name.
	 *
	 */
    function get_azure_storage_table()
    {
        return $this->azure_table_name;
    }

    /**
     * Create Azure Storage Attachment table and add is_archived column if it doesn't exist
     *
     */
    function init_azure_storage_attachments()
    {
        global $wpdb;
        $table_name = $this->get_azure_storage_table();
        $charset_collate = $wpdb->get_charset_collate();

        // Create table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
            attachment_id int(11) NOT NULL,
            attachment_name varchar(300) NOT NULL,
            attachment_path varchar(500) NOT NULL,
            assessment_id int(11) NOT NULL,
            parent_id int(11) NOT NULL,
            quiz_id int(11) NOT NULL,
            user_id varchar(100) NOT NULL,
            user_name varchar(100) NOT NULL,
            organisation_id varchar(100) NOT NULL,
            PRIMARY KEY  (id)
            ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Check if is_archived column exists, if not add it
        $column_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_NAME = %s AND COLUMN_NAME = 'is_archived'",
                $table_name
            )
        );

        if (!$column_exists) {
            $wpdb->query(
                "ALTER TABLE $table_name 
                ADD COLUMN is_archived BOOLEAN DEFAULT FALSE"
            );
        }
    }

    /**
	 * Insert attachment to Azure table
	 *
	 */
    function insert_attachments_azure_storage($data)
    {
        try {
            global $wpdb;
            $table = $this->get_azure_storage_table();
            $data['time'] = current_time( 'mysql' );
            $wpdb->insert( $table, $data, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' , '%s' ) );

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }

            return $wpdb->insert_id;

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    /**
	 * Insert attachment to WP Media & Azure blob
	 *
	 */
    function insert_attachments_wp_media($upload, $parent_post_id = null)
    {
        // Hooks for handling default file uploads.
        add_filter( 'wp_generate_attachment_metadata', 'windows_azure_storage_wp_generate_attachment_metadata', 9, 2 );
        // Hook for handling blog posts via xmlrpc. This is not full proof check.
        add_filter( 'content_save_pre', 'windows_azure_storage_content_save_pre' );
        add_filter( 'wp_handle_upload_prefilter', 'windows_azure_storage_wp_handle_upload_prefilter' );
        // Hook for handling media uploads.
        add_filter( 'wp_handle_upload', 'windows_azure_storage_wp_handle_upload' );
        // Filter to modify file name when XML-RPC is used.
        add_filter( 'xmlrpc_methods', 'windows_azure_storage_xmlrpc_methods' );

        $file_path = $upload['file'];
        $file_name = basename($file_path);
        $file_type = wp_check_filetype($file_name, null);
        $wp_upload_dir = wp_upload_dir();

        $post_info = array(
            'guid' => $wp_upload_dir['url'] . '/' . $file_name,
            'post_mime_type' => $file_type['type'],
            'post_title' => $file_name,
            'post_content' => '',
            'post_status' => 'inherit',
        );

        $attach_id = wp_insert_attachment($post_info, $file_path, $parent_post_id);

        require_once ABSPATH . 'wp-admin/includes/image.php';

        $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);

        wp_update_attachment_metadata($attach_id, $attach_data);

        // remove MS Azure storage filter
        remove_filter( 'wp_generate_attachment_metadata', 'windows_azure_storage_wp_generate_attachment_metadata', 9);
        remove_filter( 'content_save_pre', 'windows_azure_storage_content_save_pre');
        remove_filter( 'wp_handle_upload_prefilter', 'windows_azure_storage_wp_handle_upload_prefilter');
        remove_filter( 'wp_handle_upload', 'windows_azure_storage_wp_handle_upload');
        remove_filter( 'xmlrpc_methods', 'windows_azure_storage_xmlrpc_methods');

        return $attach_id;
    }

    /**
     * Get Azure attachments uploaded in table.
     *
     * @param int $assessment_id The ID of the assessment.
     * @param string $organisation_id The ID of the organisation.
     * @return array|null|WP_Error The list of attachments, null if none found, or WP_Error on failure.
     */
    function get_azure_attachments_uploaded($assessment_id, $organisation_id) {
        global $wpdb;
        try {
            // Get the Azure storage table name.
            $table = $this->get_azure_storage_table();
            // Prepare the query to prevent SQL injection.
            $query = $wpdb->prepare(
                "SELECT * FROM $table WHERE assessment_id = %d AND organisation_id = %s AND is_archived != 1",
                $assessment_id,
                $organisation_id
            );
            // Execute the query.
            $result = $wpdb->get_results($query);
            // Check for database errors.
            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
            $azure_attachments = [];
            // Loop through the original array and reorganize the data
            if (!empty($result)) {
                foreach ($result as $row) {
                    // Create the structure based on parent_id and quiz_id
                    $azure_attachments[$row->parent_id][$row->quiz_id][] = $row;
                }
            }
            // Return the result or null if empty.
            return !empty($azure_attachments) ? $azure_attachments : null;
        } 
        catch (Exception $exception) {
            // Return a WP_Error object for better error handling.
            return new WP_Error('database_error', $exception->getMessage());
        }
    }

    /**
     * Get Azure files uploaded by org.
     *
     * @param string $organisation_id The ID of the organisation.
     * @return array|null|WP_Error The list of attachments, null if none found, or WP_Error on failure.
     */
    function get_azure_files_uploaded_by_org($organisation_id) {
        global $wpdb;
        try {
            // Get the Azure storage table name.
            $table = $this->get_azure_storage_table();
            // Prepare the query to prevent SQL injection.
            $query = $wpdb->prepare(
                "SELECT * FROM $table WHERE organisation_id = %s",
                $organisation_id
            );
            // Execute the query.
            $result = $wpdb->get_results($query);
            // Check for database errors.
            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
            // Return the result or null if empty.
            return !empty($result) ? $result : null;
        } 
        catch (Exception $exception) {
            // Return a WP_Error object for better error handling.
            return new WP_Error('database_error', $exception->getMessage());
        }
    }

    /**
	 * Rename Azure attachment Name uploaded in table
	 *
	 * @return string New attachment Name
	 */
    function rename_azure_attachment_upload($file_name, $assessment_id, $user_id) {
        try {
            global $wpdb;
            $table = $this->get_azure_storage_table();

            $sql = "SELECT attachment_name FROM $table  
                    WHERE assessment_id = $assessment_id 
                    AND user_id = '$user_id'";

            $result = $wpdb->get_results($sql);

            if (!empty($result)) {
                $attachment_name_arr = array();
                $new_file_name = '';
                $current_time = date('-H-i-s');
                foreach ($result as $row) {
                    if ($file_name == $row->attachment_name) {
                        $name = substr($file_name, 0, strrpos($file_name, "."));
                        $dot_and_ex = str_replace($name, '', $file_name);
                        $new_file_name = $name.$current_time.$dot_and_ex; // New file name
                    }
                    else {
                        $new_file_name = $file_name;
                    }
                }
                return $new_file_name;
            }
            else {
                return $file_name;
            }

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    /**
	 * Delete Azure attachment uploaded in table & blob
	 *
	 */
    function delete_azure_attachment_uploaded($attachment_id, $assessment_id, $organisation_id) {
        try {
            global $wpdb;
            $table = $this->get_azure_storage_table();

            $sql = "SELECT * FROM $table 
                    WHERE attachment_id = $attachment_id 
                    AND assessment_id = $assessment_id 
                    AND organisation_id = '$organisation_id'";

            $result = $wpdb->get_results($sql);

            if (!empty($result)) {
                $row_id = $result[0]->id;
                $container_name = get_option('default_azure_storage_account_container_name');
                $attachment_path = $result[0]->attachment_path;
                $blob_name = str_replace($container_name.'/', '', strstr($attachment_path, $container_name));

                if (class_exists('Windows_Azure_Helper')) {
                    // Delete attachment from Azure blob storage.
                    \Windows_Azure_Helper::delete_blob( $container_name, $blob_name);
                }
                else {
                    return wp_send_json(array(
                        'message' => 'Windows_Azure_Helper class does not exist!', 
                        'status' => false
                    ));
                }
                // Delete attachment row from WP azure table.
                $wpdb->delete( $table, array('id' => $row_id ));

                return $row_id;
            }

            if ($wpdb->last_error) {
                throw new Exception($wpdb->last_error);
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    /**
     * Generate Shared Access Signature token Azure
     *
     * @param string  $account_name    Account name for Microsoft Azure.
     * @param string  $account_key     Account key for Microsoft Azure.
     * @param string  $resource_path   Container/Blob.
     *
     * @return string URL
     */    
    function generate_sas_token_azure($resource_path) {
        $account_name = get_option('azure_storage_account_name');
        $account_key = get_option('azure_storage_account_primary_access_key');

        $sas_helper = new MicrosoftAzure\Storage\Blob\BlobSharedAccessSignatureHelper($account_name, $account_key);
        $sas = $sas_helper->generateBlobServiceSharedAccessSignatureToken(
            Resources::RESOURCE_TYPE_BLOB,              # Resource name to generate the canonicalized resource. It can be Resources::RESOURCE_TYPE_BLOB or Resources::RESOURCE_TYPE_CONTAINER
            "{$resource_path}",                         # The name of the resource, including the path of the resource. It should be {container}/{blob}: for blobs.
            "r",                                        # Signed permissions.
            (new \DateTime())->modify('+55 minute'),    # Signed expiry
            (new \DateTime())->modify('-5 minute'),     # Signed start
            '',                                         # Signed IP, the range of IP addresses from which a request will be accepted, eg. "168.1.5.60-168.1.5.70"
            'https',                                    # Signed protocol, should always be https
        );
        return "https://{$account_name}.blob.core.windows.net/{$resource_path}?{$sas}";
    }

    /**
     * Upload attachments from User's front to MS Azure & insert to table
     * 
     */
    function save_attachments_azure_storage_ajax() {
        try {
            if (!isset($_FILES["file"]))
                throw new Exception('File not found.');

            $file = $_FILES["file"];
            $path = $file["tmp_name"];
            $max_file_size = wp_max_upload_size();

            if (filesize($path) >  $max_file_size) {
                throw new Exception('Maximum file size is ' . size_format($max_file_size) . '');
            }
            
            if (isset($_COOKIE['userId'])) {
                $user_id = $_COOKIE['userId'];
            } else {
                $user_id = get_current_user_id();
            }
            if (empty($user_id))
                throw new Exception('User not found.');

            $user_name = $_POST['user_name'];
            if (empty($user_name))
                throw new Exception('User name not found.');

            $parent_id = $_POST['parent_id'];
            if (empty($parent_id))
                throw new Exception('Group ID not found.');

            $quiz_id = $_POST['quiz_id'];
            if (empty($quiz_id))
                throw new Exception('Question ID not found.');

            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment ID not found.');

            $organisation_id = $_POST['organisation_id'];
            if (empty($organisation_id))
                throw new Exception('Organisation ID not found.');
            
            $fileName = preg_replace('/\s+/', '-', $file["name"]);
            $fileName = $this->rename_azure_attachment_upload($fileName, $assessment_id, $user_id);
            $attachment = wp_upload_bits($fileName, null, file_get_contents($file["tmp_name"]));

            if (!empty($attachment['error'])) {
                throw new Exception($attachment['error']);
            }
            $attachment_id = $this->insert_attachments_wp_media($attachment);

            // When upload file to WP media successful
            if (isset($attachment_id)) {
                $attachment_path = wp_get_attachment_url($attachment_id);
                $attachment_name = get_the_title($attachment_id);

                $inputs = array(
                    'attachment_name' => $attachment_name,
                    'attachment_path' => $attachment_path,
                    'user_id' => $user_id,
                    'user_name' => $user_name,
                    'parent_id' => $parent_id,
                    'quiz_id' => $quiz_id,
                );
                $conditions = array(
                    'attachment_id' => $attachment_id,
                    'assessment_id' => $assessment_id,
                    'organisation_id' => $organisation_id,
                );

                // Insert attachment data row to Azure table
                $insert_table = $this->insert_attachments_azure_storage(array_merge($inputs, $conditions));

                // Delete attachment in WP media
                $wp_media_deleted = wp_delete_attachment( $attachment_id, true );

                return wp_send_json(array(
                    'attachment_id' => $attachment_id, 
                    'insert_row_id' => $insert_table, 
                    'message' => 'Attachment has been uploaded', 
                    'status' => true,                     
                    'wp_media_deleted' => $wp_media_deleted,
                ));
            }
            else {
                return wp_send_json(array('message' => 'Attachment not exist', 'status' => false));
            }
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    /**
	 * Delete Azure attachment uploaded in table & blob ajax
	 *
	 */
    function delete_azure_attachments_ajax() {
        try {
            $attachment_id = $_POST['attachment_id'];
            if (empty($attachment_id))
                throw new Exception('Attachment ID not found.');

            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $organisation_id = $_POST['organisation_id'];
            if (empty($organisation_id))
                throw new Exception('Organisation not found.');

            if (!empty($attachment_id)) {
                $deleted_row_id = $this->delete_azure_attachment_uploaded($attachment_id, $assessment_id, $organisation_id);
            }

            return wp_send_json(array(
                'deleted_row_id' => $deleted_row_id,
                'status' => true,
            ));

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    /**
     * Create Shared Access Signature Blob URL Ajax
     * 
     */
    function create_sas_blob_url_azure_ajax() {
        try {
            $blob_url = $_POST['blob_url'];
            if (empty($blob_url))
                throw new Exception('Blob URL not found.');

            $resource_path = str_replace('blob.core.windows.net/', '', strstr($blob_url, 'blob.core.windows.net/'));
            $sas_blob_url = $this->generate_sas_token_azure($resource_path);

            return wp_send_json(array('sas_blob_url' => $sas_blob_url, 'status' => true));

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    /**
     * Create Shared Access Signature Blob URL at the URL
     * 
     * @return void
     */
    function report_create_azure_sas_blob_url() {
        if (isset($_GET['action']) && $_GET['action'] === 'create_sas_blob_url') {
            $blob_url = $_GET['blob_url'] ?? '';
            
            if (empty($blob_url)) {
                echo 'Error: Create file URL failed. Blob URL not found.';
                exit;
            }
            // Extract resource path from the blob URL
            $resource_path = str_replace('blob.core.windows.net/', '', strstr($blob_url, 'blob.core.windows.net/'));
            
            // Generate the SAS token
            $sas_blob_url = $this->generate_sas_token_azure($resource_path);
    
            if (!empty($sas_blob_url)) {
                // Redirect the user to the generated SAS Blob URL
                wp_redirect($sas_blob_url);
                exit;
            } 
            else {
                echo 'Error: Generate Azure Shared Access Signature failed.';
                exit;
            }
        }
    }

    /**
     * Sanitize blob name for Azure storage
     * 
     * @param string $blob_name The blob name to sanitize
     * @return string Sanitized blob name
     */
    private function sanitize_blob_name($blob_name) {
        // Remove any characters that aren't alphanumeric, dash, underscore, forward slash, or period
        $blob_name = preg_replace('/[^a-zA-Z0-9\-._\/]/', '-', $blob_name);
        
        // Replace multiple forward slashes with a single one
        $blob_name = preg_replace('/\/+/', '/', $blob_name);
        
        // Remove leading and trailing slashes
        $blob_name = trim($blob_name, '/');
        
        // Replace spaces with dashes (just in case)
        $blob_name = str_replace(' ', '-', $blob_name);
        
        return $blob_name;
    }

    /**
     * Move blob between two Azure storage accounts
     * 
     * @param string $source_url Source blob URL
     * @param string $move_type Type of move operation (must be 'archive' or 'restore')
     * @return array Status and result message
     */
    public function move_blob_between_storage_accounts($source_url, $move_type) {
        try {
            // Input validation
            if (empty($source_url)) {
                throw new Exception('Source URL is required');
            }
            if ($move_type !== 'archive' && $move_type !== 'restore') {
                throw new Exception('Move type must be either "archive" or "restore"');
            }

            // Get account configurations
            $main_account = [
                'name'      => get_option('azure_storage_account_name'),
                'key'       => get_option('azure_storage_account_primary_access_key'),
                'container' => get_option('default_azure_storage_account_container_name'),
            ];
            $archive_account = [
                'name'      => get_field('archive_storage_account_name', 'option'),
                'key'       => get_field('archive_storage_account_key', 'option'),
                'container' => get_field('archive_storage_container', 'option')
            ];

            // Set source and destination based on move type
            $source_account = $move_type === 'archive' ? $main_account : $archive_account;
            $dest_account = $move_type === 'archive' ? $archive_account : $main_account;

            // Validate account configurations
            if (empty($source_account['name']) || empty($source_account['key'])) {
                throw new Exception('Source account details are required');
            }
            if (empty($dest_account['name']) || empty($dest_account['key']) || empty($dest_account['container'])) {
                throw new Exception('Destination account details are required'); 
            }

            // Parse and validate URL
            $url_parts = parse_url($source_url);
            if (!$url_parts || empty($url_parts['path'])) {
                throw new Exception('Invalid source URL format');
            }

            // Extract container and blob path
            $path_parts = explode('/', trim($url_parts['path'], '/'));
            if (count($path_parts) < 2) {
                throw new Exception('Invalid blob path format');
            }

            $source_container = $this->sanitize_blob_name($path_parts[0]);
            $blob_name = $this->sanitize_blob_name(implode('/', array_slice($path_parts, 1))); // Preserve path structure

            // Initialize blob clients
            $source_conn_string = "DefaultEndpointsProtocol=https;AccountName={$source_account['name']};AccountKey={$source_account['key']}";
            $dest_conn_string = "DefaultEndpointsProtocol=https;AccountName={$dest_account['name']};AccountKey={$dest_account['key']}";
            
            $source_blob_client = BlobRestProxy::createBlobService($source_conn_string);
            $dest_blob_client = BlobRestProxy::createBlobService($dest_conn_string);

            try {
                // Copy blob from source to destination
                $blob_content = $source_blob_client->getBlob($source_container, $blob_name);
                $content = stream_get_contents($blob_content->getContentStream());
                
                $dest_blob_client->createBlockBlob($dest_account['container'], $blob_name, $content);

                // Only delete source after successful copy
                $source_blob_client->deleteBlob($source_container, $blob_name);

                return [
                    'status' => true,
                    'url' => "https://{$dest_account['name']}.blob.core.windows.net/{$dest_account['container']}/$blob_name",
                    'message' => "Blob {$move_type}d successfully"
                ];

            } catch (ServiceException $e) {
                $error_details = $e->getResponse() ? print_r($e->getResponse(), true) : '';
                error_log("Azure service error details: " . $error_details);
                throw new Exception('Azure service error: ' . $e->getMessage());
            }

        } catch (Exception $e) {
            error_log("Error {$move_type}ing blob: " . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete a blob by its source URL
     *
     * @param string $url The source URL of the blob to delete
     * @return array Response status and message
     */
    public function delete_blob_by_url($url) {
        try {
            // Validate URL format
            $url_parts = parse_url($url);
            if (!isset($url_parts['host']) || !isset($url_parts['path'])) {
                throw new Exception('Invalid source URL format');
            }

            // Extract container and blob path
            $path_parts = explode('/', trim($url_parts['path'], '/'));
            if (count($path_parts) < 2) {
                throw new Exception('Invalid blob path format');
            }

            $container = $this->sanitize_blob_name($path_parts[0]);
            $blob_name = $this->sanitize_blob_name(implode('/', array_slice($path_parts, 1))); // Preserve path structure

            // Determine account based on account name from URL
            $account_name = $url_parts['host'] === 'saturn007.blob.core.windows.net' ? 'saturn007' : 'saturnarchive';
            if ($account_name === 'saturn007') {
                $account = [
                    'name'      => get_option('azure_storage_account_name'),
                    'key'       => get_option('azure_storage_account_primary_access_key'),
                    'container' => get_option('default_azure_storage_account_container_name'),
                ];
            } elseif ($account_name === 'saturnarchive') {
                $account = [
                    'name'      => get_field('archive_storage_account_name', 'option'),
                    'key'       => get_field('archive_storage_account_key', 'option'),
                    'container' => get_field('archive_storage_container', 'option')
                ];
            } else {
                throw new Exception('Invalid account name');
            }

            // Initialize blob client
            $conn_string = "DefaultEndpointsProtocol=https;AccountName={$account['name']};AccountKey={$account['key']}";
            $blob_client = BlobRestProxy::createBlobService($conn_string);

            // Delete the blob
            $blob_client->deleteBlob($container, $blob_name);

            return [
                'status' => true,
                'message' => "Blob deleted from {$account_name} successfully",
            ];

        } catch (Exception $e) {
            error_log("Error deleting blob: " . $e->getMessage());
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
}
new WP_Azure_Storage();

