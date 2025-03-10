<?php 
/**
 * Class Azure Storage for Saturn
 * 
 * @author Tuan
 *
 */

use MicrosoftAzure\Storage\Common\Internal\Resources;

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
	 * Create Azure Storage Attachment table
	 *
	 */
    function init_azure_storage_attachments()
    {
        global $wpdb;
        $table_name = $this->get_azure_storage_table();
        $charset_collate = $wpdb->get_charset_collate();

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
                "SELECT * FROM $table WHERE assessment_id = %d AND organisation_id = %s",
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
    
}
new WP_Azure_Storage();

