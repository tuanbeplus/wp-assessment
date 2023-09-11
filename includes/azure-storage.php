<?php 
/**
 * Class Azure Storage for Saturn
 *
 */
class WP_Azure_Storage {

    public function __construct() 
    {
        add_action('wp_ajax_save_attachments_azure_storage_ajax', array($this, 'save_attachments_azure_storage_ajax'));
        add_action('wp_ajax_nopriv_save_attachments_azure_storage_ajax', array($this, 'save_attachments_azure_storage_ajax'));

        add_action('wp_ajax_delete_azure_attachments_ajax', array($this, 'delete_azure_attachments_ajax'));
        add_action('wp_ajax_nopriv_delete_azure_attachments_ajax', array($this, 'delete_azure_attachments_ajax'));

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
            attachment_name varchar(100) NOT NULL,
            attachment_path varchar(200) NOT NULL,
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
	 * Insert attachment to WP Media
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

        // remove filter
        remove_filter( 'wp_generate_attachment_metadata', 'windows_azure_storage_wp_generate_attachment_metadata', 9);
        remove_filter( 'content_save_pre', 'windows_azure_storage_content_save_pre');
        remove_filter( 'wp_handle_upload_prefilter', 'windows_azure_storage_wp_handle_upload_prefilter');
        remove_filter( 'wp_handle_upload', 'windows_azure_storage_wp_handle_upload');
        remove_filter( 'xmlrpc_methods', 'windows_azure_storage_xmlrpc_methods');

        return $attach_id;
    }

    /**
	 * Get Azure attachments uploaded in table
	 *
	 */
    function get_azure_attachments_uploaded($parent_id, $quiz_id, $assessment_id, $organisation_id) {
        try {
            global $wpdb;

            $table = $this->get_azure_storage_table();

            $sql = "SELECT * FROM $table 
                    WHERE parent_id = $parent_id 
                    AND quiz_id = $quiz_id 
                    AND assessment_id = $assessment_id 
                    AND organisation_id = '$organisation_id'";

            $result = $wpdb->get_results($sql);

            return !empty($result) ? $result : null;

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
     * Upload file form User's front to MS Azure & insert to table
     * 
     */
    function save_attachments_azure_storage_ajax()
    {
        try {
            if (!isset($_FILES["file"]))
                throw new Exception('File not found.');

            $file = $_FILES["file"];
            $path = $file["tmp_name"];
            $max_file_size = wp_max_upload_size();

            if (filesize($path) >  $max_file_size) {
                throw new Exception('Maximum file size is ' . size_format($max_file_size) . '');
            }

            $fileName = preg_replace('/\s+/', '-', $file["name"]);
            $attachment_name = preg_replace('/[^A-Za-z0-9.\-]/', '', $fileName);
            // check_ajax_referer('assessment_attachment_upload', 'security');
            
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

            $attachment = wp_upload_bits($fileName, null, file_get_contents($file["tmp_name"]));

            if (!empty($attachment['error'])) {
                throw new Exception($attachment['error']);
            }
            $attachment_id = $this->insert_attachments_wp_media($attachment);

            // When upload file to WP media successful
            if (isset($attachment_id)) {
                $attachment_path = wp_get_attachment_url($attachment_id);

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

                $insert_table = $this->insert_attachments_azure_storage(array_merge($inputs, $conditions));

                // Delete attachment in WP media
                $wp_media_delete = wp_delete_attachment( $attachment_id, true );

                return wp_send_json(array(
                    'attachment_id' => $attachment_id, 
                    'insert_row_id' => $insert_table, 
                    'message' => 'Attachments has been uploaded', 
                    'status' => true,                     
                    'wp_media_delete' => $wp_media_delete,
                ));
            }
            else {
                return wp_send_json(array('message' => 'Attachments not exist', 'status' => false));
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

            if(isset($attachment_id)) {
                $delete_row_id = $this->delete_azure_attachment_uploaded($attachment_id, $assessment_id, $organisation_id);
            }

            return wp_send_json(array('delete_row_id' => $delete_row_id));

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }
}
new WP_Azure_Storage();
