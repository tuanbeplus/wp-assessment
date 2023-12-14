<?php

/**
 * All functions for Submission Feedbacks
 */
class AndSubmissionFeedbacks {

  /**
   * Construct function
   */
  public function __construct() {
    add_action('admin_enqueue_scripts', array($this, 'submission_feedbacks_enqueue_scripts'));

    add_action('wp_ajax_and_add_a_submission_feedback', array($this, 'and_add_a_submission_feedback_func'));
    add_action('wp_ajax_nopriv_and_add_a_submission_feedback', array($this, 'and_add_a_submission_feedback_func'));

    $this->set_submission_feedbacks_table();
    $this->create_submissions_feedbacks_table();
  }

  function submission_feedbacks_enqueue_scripts(): void {
    global $post_type;
    if( $post_type == 'dcr_submissions' || $post_type == 'submissions' ) {
      wp_enqueue_script('admin-feedbacks-js', WP_ASSESSMENT_ASSETS . '/js/admin/feedbacks.js', true, WP_ASSESSMENT_VER);
      wp_localize_script(
        'admin-feedbacks-js',
        'fb_object',
        array( 'ajax_url' => admin_url('admin-ajax.php') )
    );
    }
  }

  /**
   * Set feedback table name
   */
  function set_submission_feedbacks_table(): void {
      global $wpdb;
      $this->submission_feedbacks_table_name = $wpdb->prefix . "submission_feedbacks";
  }

  /**
   * Get feedback table name
   */
  function get_submission_feedbacks_table() {
      return $this->submission_feedbacks_table_name;
  }

  /**
   * Create table for Submission Feedbacks
   * 
   */
  function create_submissions_feedbacks_table() {
      global $wpdb;

      $charset_collate = $wpdb->get_charset_collate();
      $table_name = $this->get_submission_feedbacks_table();

      $sql = "CREATE TABLE IF NOT EXISTS $table_name (
          id mediumint(9) NOT NULL AUTO_INCREMENT,
          time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
          user_id varchar(100) NOT NULL,
          user_name varchar(100) NOT NULL,
          quiz_id int(11) NOT NULL,
          parent_id int(11) DEFAULT 0,
          assessment_id int(11) NOT NULL,
          submission_id int(11) NOT NULL,
          organisation_id varchar(100) NOT NULL,
          feedback LONGTEXT,
          PRIMARY KEY  (id)
      ) $charset_collate;";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
  }
  
}

new AndSubmissionFeedbacks();