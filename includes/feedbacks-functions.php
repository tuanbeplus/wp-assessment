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

    // Create a feedback
    add_action('wp_ajax_and_add_a_submission_feedback', array($this, 'and_add_a_submission_feedback_func'));
    add_action('wp_ajax_nopriv_and_add_a_submission_feedback', array($this, 'and_add_a_submission_feedback_func'));

    // Remove a feedback
    add_action('wp_ajax_and_remove_a_submission_feedback', array($this, 'and_remove_a_submission_feedback_func'));
    add_action('wp_ajax_nopriv_and_remove_a_submission_feedback', array($this, 'and_remove_a_submission_feedback_func'));

    $this->set_submission_feedbacks_table();
    $this->create_submissions_feedbacks_table();
  }

  /**
   * Enqueue script for feedbacks
   */
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

  /**
   * Add a Submission Feedback
   */
  function and_add_a_submission_feedback_func() {
    try {
      global $wpdb;

      $submission_id = intval($_POST['submission_id']);
      if (empty($submission_id))
        throw new Exception('Submission not found.');

      $assessment_id = intval($_POST['assessment_id']);
      if (empty($assessment_id))
        throw new Exception('Assessment not found.');

      $organisation_id = $_POST['organisation_id'];
      if (empty($organisation_id))
        throw new Exception('Organisation not found.');

      $quiz_id = intval($_POST['quiz_id']);
      if (empty($quiz_id))
        throw new Exception('Quiz not found.');

      $feedback = $_POST['feedback'] ?? null;
      if (empty($feedback))
        throw new Exception('Please add your feedback');

      $parent_quiz_id = intval($_POST['parent_quiz_id']);
      if (empty($parent_quiz_id))
        throw new Exception('Invalid Group ID');

      $current_time = current_time( 'mysql' );
      $current_user = wp_get_current_user();
      $input_data = array(
        'time' => $current_time,
        'user_id' => $current_user->ID,
        'user_name' => $current_user->display_name,
        'quiz_id' => $quiz_id,
        'parent_id' => $parent_quiz_id,
        'assessment_id' => $assessment_id,
        'submission_id' => $submission_id,
        'organisation_id' => $organisation_id,
        'feedback' => $feedback
      );

      $table_name = $this->get_submission_feedbacks_table();
      $wpdb->insert( $table_name, $input_data, array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' , '%s' , '%s' ) );

      if ($wpdb->last_error) throw new Exception($wpdb->last_error);

      return wp_send_json(array(
        'time' => date("M d Y H:i a", strtotime($current_time)),
        'feedback_id' => $wpdb->insert_id, 
        'user_name' => $current_user->display_name,
        'message' => 'Feedback for this quiz has been added', 
        'status' => true )
      );
    } catch (Exception $exception) {
      return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
    }
  }

  /**
   * Remove a Submission Feedback
   */
  function and_remove_a_submission_feedback_func() {
    try {
      global $wpdb;

      $feedback_id = intval($_POST['feedback_id']);
      if (empty($feedback_id))
        throw new Exception('Feedback not found.');

      $table_name = $this->get_submission_feedbacks_table();
      $wpdb->delete( $table_name, [ 'id'=> $feedback_id ], [ '%d' ] );

      if ($wpdb->last_error) throw new Exception($wpdb->last_error);

      return wp_send_json( array(
        'message' => 'Feedback for this quiz has been removed', 
        'status' => true )
      );
    } catch (Exception $exception) {
      return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
    }
  }

  /**
   * Get all Submission Feedbacks for Assessment
   */
  function get_all_feedbacks_by_assessment_and_organisation($assessment_id, $organisation_id) {
    try {
      global $wpdb;
      $table_name = $this->get_submission_feedbacks_table();
      $sql = "SELECT * FROM $table_name WHERE assessment_id = $assessment_id AND organisation_id = '$organisation_id'"; 
      $result = $wpdb->get_results($sql, ARRAY_A);

      if ($wpdb->last_error) {
        throw new Exception($wpdb->last_error);
      }

      return !empty($result) ? $result : null;

    } catch (Exception $exception) {
      return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
    }
  }

  /**
   * Format Feedbacks for Assessment to show
   * 
   * @author Tuan
   * 
   * @return array Feedbacks 
   * 
   */
  function format_feedbacks_by_question($assessment_id, $organisation_id) {
    $question_feedbacks = array();
    $all_feedbacks = $this->get_all_feedbacks_by_assessment_and_organisation($assessment_id, $organisation_id);
    if ($all_feedbacks && is_array($all_feedbacks)) {
        foreach ($all_feedbacks as $key => $fb) {
            $question_feedbacks[$fb['parent_id']][$fb['quiz_id']][] = array(
                'fb_id' => $fb['id'],
                'time' => $fb['time'],
                'user_id' => $fb['user_id'],
                'user_name' => $fb['user_name'],
                'feedback' => htmlentities(stripslashes($fb['feedback']))
            );
        }
        return $question_feedbacks;
    }
  }

}

new AndSubmissionFeedbacks();