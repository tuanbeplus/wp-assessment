<?php

class Question_Form
{
    public int $user_id;

    public function __construct()
    {
        add_action('wp_ajax_save_question', array($this, 'save_question'));
        add_action('wp_ajax_nopriv_save_question', array($this, 'save_question'));

        add_action('wp_ajax_create_assessment_submission', array($this, 'create_assessment_submission'));
        add_action('wp_ajax_nopriv_create_assessment_submission', array($this, 'create_assessment_submission'));

        add_action('wp_ajax_submit_assessment_progress', array($this, 'submit_assessment_progress'));
        add_action('wp_ajax_nopriv_submit_assessment_progress', array($this, 'submit_assessment_progress'));

        add_action('wp_ajax_update_quiz_status_submission', array($this, 'update_quiz_status_submission'));
        add_action('wp_ajax_nopriv_update_quiz_status_submission', array($this, 'update_quiz_status_submission'));

        add_action('wp_ajax_save_quiz_feedback_submission', array($this, 'save_quiz_feedback_submission'));
        add_action('wp_ajax_nopriv_save_quiz_feedback_submission', array($this, 'save_quiz_feedback_submission'));

        add_action('wp_ajax_final_accept_reject_assessment', array($this, 'final_accept_reject_assessment'));
        add_action('wp_ajax_nopriv_final_accept_reject_assessment', array($this, 'final_accept_reject_assessment'));

        add_action('wp_ajax_and_insert_attachment', array($this, 'and_insert_attachment'));
        add_action('wp_ajax_nopriv_and_insert_attachment', array($this, 'and_insert_attachment'));

        add_action('wp_ajax_upload_assessment_attachment', array($this, 'upload_assessment_attachment'));
        add_action('wp_ajax_nopriv_upload_assessment_attachment', array($this, 'upload_assessment_attachment'));

        add_action('wp_ajax_delete_additional_file_assessment', array($this, 'delete_additional_file_assessment'));
        add_action('wp_ajax_nopriv_delete_additional_file_assessment', array($this, 'delete_additional_file_assessment'));

        add_action('wp_ajax_send_invite_to_collaborator', array($this, 'send_invite_to_collaborator'));
        add_action('wp_ajax_nopriv_send_invite_to_collaborator', array($this, 'send_invite_to_collaborator'));

        add_action('wp_ajax_send_invite_to_colleagues', array($this, 'send_invite_to_colleagues'));
        add_action('wp_ajax_nopriv_send_invite_to_colleagues', array($this, 'send_invite_to_colleagues'));

        // Share report to users
        add_action('wp_ajax_send_report_to_users', array($this, 'share_report_to_users'));

        // Save Charts image URL to meta
        add_action('wp_ajax_save_dashboard_charts_image_url', array($this, 'save_dashboard_charts_image_url'));
    }

    /**
     * Save answers question by ajax
     * 
     */
    function save_question()
    {
        try {
            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            if (isset($_COOKIE['userId'])) {
                $user_id = $_COOKIE['userId'];
            } else {
                $user_id = get_current_user_id();
            }

            $organisation_id = $_POST['organisation_id'];
            if (empty($organisation_id))
                throw new Exception('Organisation not found.');

            $arr_attachment_ids = $_POST['attachment_ids'];

            $data_quiz = $_POST['data_quiz'];
            $type_quiz = $_POST['type_quiz'];
            $quiz_id = intval($_POST['quiz_id']);
            if (empty($quiz_id) || !$quiz_id)
                throw new Exception('Question not found.');

            $status_submisstion = '';
            $submission_id = $this->is_submission_progress_exist($organisation_id, $assessment_id);
            $assessment_terms = get_assessment_terms($assessment_id);

            if ($type_quiz == 'Comprehensive Assessment') {

                $list_quiz = array();
                foreach ($data_quiz as $id => $f) {
                    $exp   = explode('_',$f['name']);
                    $value = $f['value'];
                    if($exp[0] == 'questions'){
                        $id_question = $exp[1];
                        $id_quiz     = $exp[3];
                        $name        = $exp[4];

                        if($name == 'choice'){
                            $choice_value = isset($list_quiz[$id_question][$id_quiz][$name]) ? $list_quiz[$id_question][$id_quiz][$name] : array();
                            $count_choice = count($choice_value);
                            $item = [];
                            $item['id'] = $count_choice > 0 ? $count_choice++ : 0;
                            $item['title'] = $value;

                            $choice_value[] = $item;
                            $list_quiz[$id_question][$id_quiz][$name] = $choice_value;
                        }
                        elseif($name == 'attachmentIDs'){
                            $attachment_value = isset($list_quiz[$id_question][$id_quiz][$name]) ? $list_quiz[$id_question][$id_quiz][$name] : array();
                            $count_attachment = count($attachment_value);
                            $att_item = [];
                            $att_item['id'] = $count_attachment > 0 ? $count_attachment++ : 0;
                            $att_item['value'] = $value;

                            $attachment_value[] = $att_item;
                            $list_quiz[$id_question][$id_quiz][$name] = $attachment_value;
                        }
                        else {
                            $list_quiz[$id_question][$id_quiz][$name] = $value;
                        }
                    }
                }

                //Save quiz
                $main = new WP_Assessment();

                foreach ($list_quiz as $parent_id => $quiz_post) {

                    if($quiz_id != $parent_id) continue;

                    foreach ($quiz_post as $quiz_id => $p) {

                        $answers = $p['choice'] ?? null;
                        $description = $p['description'] ?? null;
                        $attachment_id = $p['attachment'] ?? null;
                        $attachmentIDs = $p['attachmentIDs'] ?? null;
                        $quiz_point = $p['point'] ?? null;

                        if ($submission_id) {
                            $quiz_data = $main->get_quiz_by_assessment_id_and_submission_parent(
                                $assessment_id, 
                                $submission_id, 
                                $quiz_id, 
                                $organisation_id, 
                                $parent_id,
                            );
                        } 
                        else {
                            $quiz_data = $main->get_quiz_by_assessment_id_and_parent(
                                $assessment_id, 
                                $quiz_id, 
                                $organisation_id , 
                                $parent_id,
                            );
                        }

                        $input = [];

                        if (!empty($user_id))
                            $input['user_id'] = $user_id;

                        if (!empty($organisation_id))
                            $input['organisation_id'] = $organisation_id;

                        if (!empty($submission_id))
                            $input['submission_id'] = $submission_id;

                        if (!empty($answers)) {
                            $input['answers'] = json_encode($answers);
                        }
                        else {
                            $input['answers'] = null;
                        }

                        if (!empty($description)) {
                            $input['description'] = $description;
                        }
                        else {
                            $input['description'] = null;
                        }

                        if (!empty($attachment_id))
                            $input['attachment_id'] = $attachment_id;

                        if (!empty($attachmentIDs))
                            $input['attachment_ids'] = json_encode($attachmentIDs);

                        if ($quiz_point != null)
                            $input['quiz_point'] = $quiz_point;

                        if($submission_id){
                            $conditions = array(
                                // 'user_id' => $user_id,
                                'organisation_id' => $organisation_id,
                                'assessment_id' => $assessment_id,
                                'quiz_id' => $quiz_id,
                                'parent_id' => $parent_id,
                                'submission_id' => $submission_id
                            );
                        }else{
                            $conditions = array(
                                // 'user_id' => $user_id,
                                'organisation_id' => $organisation_id,
                                'assessment_id' => $assessment_id,
                                'quiz_id' => $quiz_id,
                                'parent_id' => $parent_id,
                            );
                        }

                        $quiz_action = '';
                        if (!$quiz_data) {
                            // Insert Quiz record if quiz_data not exist
                            $main->insert_quiz_by_assessment_id(array_merge($input, $conditions));
                            $quiz_action = 'Inserted';
                        } else {
                            // Update Quiz record if quiz_data exist
                            $main->update_quiz_assessment($input, $conditions);
                            $quiz_action = 'Updated';
                        }
                    }
                }
            }
            else {

                $answers = $_POST['answers'] ?? null;
                $description = $_POST['description'] ?? null;
                $attachment_id = $_POST['attachment_id'] ?? null;

                $main = new WP_Assessment();

                if ($submission_id) {
                    $quiz_data = $main->get_quiz_by_assessment_id_and_submission(
                        $assessment_id, 
                        $submission_id, 
                        $quiz_id, 
                        $organisation_id,
                    );
                }
                else {
                    $quiz_data = $main->get_quiz_by_assessment_id(
                        $assessment_id, 
                        $quiz_id, 
                        $organisation_id,
                    );
                }

                $input = [];

                if (!empty($user_id))
                    $input['user_id'] = $user_id;

                if (!empty($organisation_id))
                    $input['organisation_id'] = $organisation_id;

                if (!empty($submission_id))
                    $input['submission_id'] = $submission_id;

                $input['answers'] = json_encode($answers);

                if (!empty($description))
                    $input['description'] = $description;

                if (!empty($attachment_id))
                    $input['attachment_id'] = $attachment_id;

                if($submission_id){
                    $conditions = array(
                        // 'user_id' => $user_id,
                        'organisation_id' => $organisation_id,
                        'assessment_id' => $assessment_id,
                        'quiz_id' => $quiz_id,
                        'submission_id' => $submission_id
                    );
                }else{
                    $conditions = array(
                        // 'user_id' => $user_id,
                        'organisation_id' => $organisation_id,
                        'assessment_id' => $assessment_id,
                        'quiz_id' => $quiz_id
                    );
                }

                $quiz_action = '';
                if (!$quiz_data) {
                    // Insert Quiz record if quiz_data not exist
                    $main->insert_quiz_by_assessment_id(array_merge($input, $conditions));
                    $quiz_action = 'Inserted';
                } else {
                    // Update Quiz record if quiz_data exist
                    $main->update_quiz_assessment($input, $conditions);
                    $quiz_action = 'Updated';
                }
            }

            return wp_send_json(array(
                    'message' => 'Answers has been saved', 
                    'status' => true, 
                    'data' => array_merge($input, $conditions),
                    'quiz_action' => $quiz_action,
                )
            );
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    /**
     * Create a publish Submission by ajax
     * 
     */
    function create_assessment_submission()
    {
        try {
            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id)) throw new Exception('Assessment not found.');

            $assessment = get_post($assessment_id);

            if (isset($_COOKIE['userId'])) {
                $user_id = $_COOKIE['userId'];
            } else {
                $user_id = get_current_user_id();
            }

            $organisation_id = $_POST['organisation_id'];
            if (empty($organisation_id)) throw new Exception('Organisation not found.');

            $is_submission_exist = $this->is_submission_exist($organisation_id, $assessment_id);
            $is_submission_progress_exist = $this->is_submission_progress_exist($organisation_id, $assessment_id);
            $post_id = $is_submission_exist;

            // Get assessment terms
            $assessment_terms = get_assessment_terms($assessment_id);
            if (empty($assessment_terms)) throw new Exception('Assessment category not found.');

            if (isset($assessment_terms[0])) {
                if ($assessment_terms[0] == 'dcr') {
                    $submission_type = 'dcr_submissions';
                }
                else {
                    $submission_type = 'submissions';
                }
            }
            
            if ($is_submission_exist) {
                $org_name = ' - '.get_post_meta($is_submission_exist, 'org_data', true)['Name'] ?? '';
            }
            else if ($is_submission_progress_exist) {
                $org_name = ' - '.get_post_meta($is_submission_progress_exist, 'org_data', true)['Name'] ?? '';
            }
            else {
                $org_name = '';
            }

            $submission_title = 'Submission on ' .$assessment->post_title . $org_name;

            // If not exist any submissions
            if (!$is_submission_exist && !$is_submission_progress_exist) {
                $submission = wp_insert_post(array(
                    'post_type' => $submission_type,
                    'post_title' => $submission_title,
                    'post_status' => 'publish'
                ));
                if (!$submission) throw new Exception('Failed to created a new submission!');

                $post_id = $submission;
            }
            // If existing Progress on Submission
            elseif ($is_submission_progress_exist) {
                $submission = wp_update_post(array(
                    'ID'        => $is_submission_progress_exist,
                    'post_type' => $submission_type,
                    'post_title' => $submission_title,
                    'post_status' => 'publish'
                ));
                if (!$submission) throw new Exception('Failed to updated progress on submission!');
                $post_id = $submission;
            }
            // If existing Submit on Submission
            elseif ($is_submission_exist) {
                // Is DCR submission
                if ($assessment_terms[0] == 'dcr') {
                    $new_submission = wp_insert_post(array(
                        'post_type' => $submission_type,
                        'post_title' => $submission_title,
                        'post_status' => 'publish'
                    ));
                    if (!$new_submission) throw new Exception('Failed to resubmit submission!');
                    $post_id = $new_submission;
                }
                // Is Index & other submission
                else {
                    $submission = wp_update_post(array(
                        'ID'        => $is_submission_exist,
                        'post_type' => $submission_type,
                        'post_title' => $submission_title,
                        'post_status' => 'publish'
                    ));
                    if (!$submission) throw new Exception('Failed to updated submission!');
                    $post_id = $is_submission_exist;
                }
            }

            // Update post meta
            $this->update_submission_meta_data($user_id, $organisation_id, $assessment_id, $post_id, 'pending');  

            // Get submission url
            $submission_url = get_permalink( $post_id );

            return wp_send_json(array('message' => 'This Submission has been saved.', 'submission_url' => $submission_url, 'status' => true, 'submission_id' => $post_id));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    /**
     * Create a draft Submission by ajax
     * 
     */
    function submit_assessment_progress()
    {
        try {
            $main = new WP_Assessment();
            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $assessment = get_post($assessment_id);            

            if (isset($_COOKIE['userId'])) {
                $user_id = $_COOKIE['userId'];
            } else {
                $user_id = get_current_user_id();
            }

            $organisation_id = $_POST['organisation_id'];
            if (empty($organisation_id))
                throw new Exception('Organisation not found.');

            $is_submission_exist = $this->is_submission_exist($organisation_id, $assessment_id);
            $is_submission_progress_exist = $this->is_submission_progress_exist($organisation_id, $assessment_id);
            $post_id = $is_submission_exist;

            // Get assessment term
            $assessment_terms = get_assessment_terms($assessment_id);
            if (empty($assessment_terms)) throw new Exception('Assessment category not found.');

            if (isset($assessment_terms[0])) {
                if ($assessment_terms[0] == 'dcr') {
                    $submission_type = 'dcr_submissions';
                }
                else {
                    $submission_type = 'submissions';
                }
            }
            
            if ($is_submission_exist) {
                $org_name = ' - '.get_post_meta($is_submission_exist, 'org_data', true)['Name'] ?? '';
            }
            else if ($is_submission_progress_exist) {
                $org_name = ' - '.get_post_meta($is_submission_progress_exist, 'org_data', true)['Name'] ?? '';
            }
            else {
                $org_name = '';
            }
            
            $submission_title = 'Progress on ' .$assessment->post_title . $org_name;

            // Not exist any submissions
            if (!$is_submission_exist && !$is_submission_progress_exist) {
                $submission = wp_insert_post(array(
                    'post_type' => $submission_type,
                    'post_title' => $submission_title,
                    'post_status' => 'draft'
                ));

                if (!$submission) throw new Exception('Cannot submit progress to this assessment!');

                $post_id = $submission;
            }
            // Exist a progress submission
            elseif ($is_submission_progress_exist) {
                $submission = wp_update_post(array(
                    'ID'        => $is_submission_progress_exist,
                    'post_type' => $submission_type,
                    'post_title' => $submission_title,
                    'post_status' => 'draft'
                ));
                $post_id = $is_submission_progress_exist;
            }
            // Exist a submission
            elseif($is_submission_exist) {
                // Is DCR submission
                if ($assessment_terms[0] == 'dcr') {
                    $new_submission = wp_insert_post(array(
                        'post_type' => $submission_type,
                        'post_title' => $submission_title,
                        'post_status' => 'draft'
                    ));
                    if (!$new_submission) throw new Exception('Cannot resubmit progress to this assessment!');
                    $post_id = $new_submission;
                }
                // Is Index & other submission
                else {
                    $submission = wp_update_post(array(
                        'ID'        => $is_submission_exist,
                        'post_type' => $submission_type,
                        'post_title' => $submission_title,
                        'post_status' => 'draft'
                    ));
                    $post_id = $is_submission_exist;
                }
            }

            // Update post meta
            $this->update_submission_meta_data($user_id, $organisation_id, $assessment_id, $post_id, 'draft'); 

            //Update submission
            if ($post_id) {
                // update submission_id
                global $wpdb;
                $table_name = $main->get_quiz_submission_table_name($assessment_id);

                // Update Submission ID to table
                $wpdb->query($wpdb->prepare(
                        "UPDATE $table_name
                        SET submission_id='$post_id'
                        WHERE organisation_id='$organisation_id'
                        AND assessment_id='$assessment_id'
                        AND submission_id=0"
                    )
                );
            }

            return wp_send_json(array('message' => 'Submission progress has been saved', 'status' => true, 'submission_id' => $post_id));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    /**
     * Update metadata to Submission
     *
     * @param int $user_id   	    SF User ID
     * @param int $org_id   	    Organisation ID
     * @param int $assessment_id   	Assessment ID
     * @param int $post_id   	    Submission ID
     * @param int $status   	    draft, pending
     * 
     */
    function update_submission_meta_data($user_id, $org_id, $assessment_id, $post_id, $status) 
    {
        // Get existing meta data in Submission
        $existing_user_id = get_post_meta($post_id, 'user_id', true);
        $existing_org_id = get_post_meta($post_id, 'organisation_id', true);
        $existing_assessment_id = get_post_meta($post_id, 'assessment_id', true);
        $existing_submission_id = get_post_meta($post_id, 'submission_id', true);
        $existing_org_metadata = get_post_meta($post_id, 'org_data', true);

        // Update Salsesforce User ID meta
        if ($existing_user_id == null) {
            update_post_meta($post_id, 'user_id', $user_id);
        }

        // Update Salsesforce Org ID meta
        if ($existing_org_id == null) {
            update_post_meta($post_id, 'organisation_id', $org_id);
        }

        // Update Assessment ID meta
        if ($existing_assessment_id == null) {
            update_post_meta($post_id, 'assessment_id', $assessment_id);
        }

        // Update Submission ID meta
        if ($existing_submission_id == null) {
            update_post_meta($post_id, 'submission_id', $post_id);
        }

        // Update Submission status
        if (isset($status)) {
            update_post_meta($post_id, 'assessment_status', $status);
        }

        // Update Salsforce Org data meta
        if (empty($existing_org_metadata)) {
            $org_metadata = get_sf_organisation_data($user_id, $org_id);
            update_post_meta($post_id, 'org_data', $org_metadata);
        }

        // Update user info
        if(isset($_COOKIE['userId'])) {
            update_field('sf_user_id' , $_COOKIE['userId'], $post_id);
        }
        if(isset($_COOKIE['sf_name'])) {
            update_field('sf_user_name' , $_COOKIE['sf_name'], $post_id);
        }
        if(isset($_COOKIE['sf_user_email'])) {
            update_post_meta($post_id, 'sf_user_email' , $_COOKIE['sf_user_email']);
        }
    }

    function check_multiple_choice_exist_in_assessment($assessment_id, $quiz_id): ?bool
    {
        try {
            $questions = unserialize(get_post_meta($assessment_id, 'question_group_repeater', true));

            if (!is_array($questions) || !array_key_exists($quiz_id, $questions))
                throw new Exception('Invalid quiz or assessment');

            $quiz = $questions[$quiz_id];
            return
                array_key_exists('choice', $quiz)
                && is_array($quiz['choice'])
                && count($quiz['choice']) > 0;

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function is_submission_exist($organisation_id, $assessment_id)
    {
        $submission_id = null;
        $assessment_terms = get_assessment_terms($assessment_id);

        if (is_array($assessment_terms) && isset($assessment_terms[0])) {
            if ($assessment_terms[0] == 'dcr') {
                $post_type = 'dcr_submissions';
            }
            else {
                $post_type = 'submissions';
            }
        }
        else {
            return null;
        }

        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => 'organisation_id',
                    'value' => $organisation_id,
                ),
                array(
                    'key' => 'assessment_id',
                    'value' => $assessment_id,
                ),
            ),
        );
        $submission = get_posts($args);
        // Reset Post Data
        wp_reset_postdata();

        if (is_array($submission) && count($submission) > 0) {
            $submission_id = $submission[0]->ID;
            $status = get_post_meta($submission_id, 'assessment_status', true);

            // If it's DCR assessment
            if ($assessment_terms[0] == 'dcr') {
                if($status != 'draft') {
                    return $submission_id;
                }
            }
            // If it's Index assessment
            else{
                return $submission_id;
            }
        }
    }

    function is_submission_progress_exist($organisation_id, $assessment_id)
    {
        $post_id = null;
        $assessment_terms = get_assessment_terms($assessment_id);

        if (is_array($assessment_terms) && isset($assessment_terms[0])) {
            if ($assessment_terms[0] == 'dcr') {
                $post_type = 'dcr_submissions';
            }
            else {
                $post_type = 'submissions';
            }
        }
        else {
            return null;
        }

        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => 1,
            'post_status' => 'draft',
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => 'organisation_id',
                    'value' => $organisation_id,
                ),
                array(
                    'key' => 'assessment_id',
                    'value' => $assessment_id,
                ),
            ),
        );

        $query = new WP_Query($args);
        $post = $query->get_posts();

        if (is_array($post) && count($post) > 0) {
            $post_id = $post[0]->ID;
            return $post_id;
        }
    }

    /**
     * upload file form Admin to WP media
     *
     */
    function upload_assessment_attachment()
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
            // check_ajax_referer('assessment_attachment_upload', 'security');
            $attachment = wp_upload_bits($fileName, null, file_get_contents($file["tmp_name"]));

            if (!empty($attachment['error'])) {
                throw new Exception($attachment['error']);
            }
            $main = new WP_Assessment();
            $attachment_id = $main->wp_insert_attachment_from_url($attachment);

            return wp_send_json(array('message' => 'Attachment has uploaded', 'status' => true, 'attachment_id' => $attachment_id));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function update_quiz_status_submission()
    {
        try {
            global $post;
            $post_id = $post->ID;
            $main = new WP_Assessment();
            $submission_id = intval($_POST['submission_id']);

            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $organisation_id = $_POST['organisation_id'];
            if (empty($organisation_id))
                throw new Exception('Organisation not found.');

            $quiz_id = intval($_POST['quiz_id']);
            if (empty($quiz_id))
                throw new Exception('Quiz not found.');

            $type = $_POST['type'];
            if (empty($type))
                throw new Exception('Invalid type');

            $parent_quiz_id = intval($_POST['parent_quiz_id']);
            if (empty($parent_quiz_id))
                throw new Exception('Invalid Group ID');

            $input = [];            
            $input['status'] = $type;

            $conditions = array(
                'organisation_id' => $organisation_id,
                'quiz_id' => $quiz_id,
                'parent_id' => $parent_quiz_id,
                'assessment_id' => $assessment_id,
                'submission_id' => $submission_id,
            );

            $main->update_quiz_assessment($input, $conditions);

            return wp_send_json(array(
                'quiz_id' => $quiz_id, 
                'parent_id' => $parent_quiz_id, 
                'message' => 'Quiz status '.$parent_quiz_id.'.'.$quiz_id.' has been updated', 
                'quiz_status' => $type,
                'status' => true,
            ));
        
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function save_quiz_feedback_submission()
    {
        try {
            global $post;
            $post_id = $post->ID;
            $main = new WP_Assessment();
            $submission_id = intval($_POST['submission_id']);

            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $organisation_id = $_POST['organisation_id'];
            if (empty($organisation_id))
                throw new Exception('Organisation not found.');

            $quiz_id = intval($_POST['quiz_id']);
            if (empty($quiz_id))
                throw new Exception('Quiz not found.');

            $post_type = get_post($submission_id)->post_type;

            $feedback = $_POST['feedback'] ?? null;

            $parent_quiz_id = intval($_POST['parent_quiz_id']);
            if (empty($parent_quiz_id))
                throw new Exception('Invalid Group ID');

            $input = [];
            
            if ($post_type == 'submissions') {
                $input['feedback'] = $feedback;

                $conditions = array(
                    // 'user_id' => $user_id,
                    'organisation_id' => $organisation_id,
                    'quiz_id' => $quiz_id,
                    'parent_id' => $parent_quiz_id,
                    'assessment_id' => $assessment_id,
                    'submission_id' => $submission_id,
                );
    
                $main->update_quiz_assessment($input, $conditions);
    
                return wp_send_json(array(
                    'quiz_id' => $quiz_id, 
                    'parent_id' => $parent_quiz_id, 
                    'message' => 'Feedback has been saved', 
                    'status' => true
                ));
            }
                    
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function save_all_submission_feedback()
    {
        $main = new WP_Assessment();
        $input = [];
        $submission_id = $_POST['submission_id'] ?? null;
        $assessment_id = get_post_meta($submission_id, 'assessment_id', true) ?? null;
        $organisation_id = $_POST['organisation_id'] ?? null;
        $quiz_feedback_arr = $_POST['quiz_feedback'] ?? array();
        $assessment_terms = get_assessment_terms($assessment_id);

        if (!empty($quiz_feedback_arr) && isset($submission_id) && isset($assessment_id) && isset($organisation_id)) {
            foreach ($quiz_feedback_arr as $i => $section) {
                foreach ($section as $j => $feedback) {
                    $input['feedback'] = $feedback ?? null;
                    $conditions = array(
                        // 'user_id' => $user_id,
                        'organisation_id' => $organisation_id,
                        'quiz_id' => $j,
                        'parent_id' => $i,
                        'assessment_id' => $assessment_id,
                        'submission_id' => $submission_id,
                    );
                    $main->update_quiz_assessment($input, $conditions);
                }
            }
        }
    }

    function final_accept_reject_assessment()
    {
        try {
            global $post;
            $post_id = $post->ID;

            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $submission_id = intval($_POST['submission_id']);
            if (empty($submission_id))
                throw new Exception('Submission not found.');

            $organisation_id = $_POST['organisation_id'];
            if (empty($organisation_id))
                throw new Exception('Organisation not found.');

            $type = $_POST['type'];
            if (empty($type))
                throw new Exception('Type not found.');

            $assessment_link = get_permalink($assessment_id);
            $sf_user_id = get_post_meta($submission_id, 'sf_user_id', true);
            $sf_user_email = get_post_meta($submission_id, 'sf_user_email', true);
            $sf_user_name = get_post_meta($submission_id, 'sf_user_name', true);

            if (empty($sf_user_email))
                throw new Exception('User email not found.');

            $content  = '<p>Hi '. $sf_user_name .'</p>';
            $content .= '<p>You have a new comment to view for your '. get_the_title($assessment_id) .'</p>';
            $content .= '<p><a href=' . $assessment_link . '>Click here to view the comment.</a> or <a href='.home_url('/login').'>login to your account</a></p>';
            $content .= '<p>Kind regards,</p>';
            $content .= '<p>Australian Network on Disability</p>';

            $sent = wp_mail($sf_user_email , 'New feedback about your Submission on '. get_the_title($assessment_id), $content);

            update_post_meta($submission_id, 'assessment_status', $type);

            if (!$sent) throw new Exception($sent, 1);

            return wp_send_json(array(
                'message' => ucfirst($type).', feedback has been updated and send to: '.$sf_user_email, 
                'submission_status' => $type,
                'status' => true,
            ));

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function send_invite_to_collaborator()
    {
        try {
            $post_id = intval($_POST['post_id']);
            if (empty($post_id))
                throw new Exception('Post ID not found.');

            $user_id_arr = $_POST['user_id_arr'];
            if (empty($user_id_arr))
                throw new Exception('User not found.');

            $post_edit_link = home_url() .'/wp-admin/post.php?post='. $post_id .'&action=edit';
            $post_title = get_the_title($post_id);
            $content = '<p>Click here to view the <a href=' . $post_edit_link . '>'. $post_title  .'</a></p>';

            foreach ($user_id_arr as $user_id) {

                $user = get_user_by('id', $user_id['id']);
                $email = $user->user_email;
                $sent = wp_mail($email , 'You have an invitation to work on the '.$post_title, $content);

                if (!$sent) throw new Exception($sent, 1);
            }

            return wp_send_json(array('message' => 'Invite has been send to the Collaborator', 'status' => true));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function save_dashboard_charts_image_url()
    {
        try {
            $data_imgs_arr = $_POST['data_imgs'];
            if (empty($data_imgs_arr)) throw new Exception('Chart images not found, please add scores to Dashboard first.');

            $report_id = $_POST['report_id'];
            if (empty($report_id)) throw new Exception('Report ID not found.');
            $org_data = get_post_meta($report_id, 'org_data', true);
            $org_name = $org_data['Name'] ?? null;
            $attachments_arr = array();
            $dashboard_chart_imgs = get_post_meta($report_id, 'dashboard_chart_imgs', true);

            // Delete all Chart images existing in Media
            if (!empty($dashboard_chart_imgs)) {
                foreach ($dashboard_chart_imgs as $img_id) {
                    wp_delete_attachment( $img_id, true );
                }
            }

            // Upload all new Chart images to Media
            foreach ($data_imgs_arr as $record) {

                $img_data = $record['data'];
                $img_name = $record['name'];

                // Convert base64-encoded data to image file
                $img_data = str_replace('data:image/png;base64,', '', $img_data);
                $img_data = str_replace(' ', '+', $img_data);
                $img_data = base64_decode($img_data);

                // Save the image to the media library
                $upload_dir = wp_upload_dir();
                $upload_path = $upload_dir['path'];
                $upload_file = $upload_path .'/'.$img_name .'-dashboard-chart-'.$org_name.'-'.rand(1,999).'.png';

                file_put_contents($upload_file, $img_data);

                // Insert the image into the media library
                $file_array = [
                    'name' => basename($upload_file),
                    'type' => 'image/png',
                    'tmp_name' => $upload_file,
                    'error' => 0,
                    'size' => filesize($upload_file),
                ];

                $attachment_id = media_handle_sideload($file_array, 0);

                if (is_wp_error($attachment_id)) {
                    throw new Exception($attachment_id->get_error_message());
                } 
                $attachments_arr[$img_name] = $attachment_id;
            }

            if (!empty($attachments_arr)) {
                // Update Chart images ID to post meta
                $update_meta = update_post_meta($report_id, 'dashboard_chart_imgs', $attachments_arr);

                if ($update_meta == true) {
                    // Return Responsive
                    return wp_send_json(array(
                        'message'       => 'Add chart images successfully!', 
                        'attachment_id' => $attachments_arr,
                        'update'        => $update,
                        'status'        => true,
                    ));
                }
                else {
                    throw new Exception('Update images to meta failed!');
                }
            }
            else {
                throw new Exception('Failed, chart images not exist!');
            }

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function share_report_to_users()
    {
        try {
            $users = $_POST['users'];
            $post_id = intval($_POST['post_id']);

            if(empty($users)){
              return wp_send_json(array('message' => 'You don\'t not to choose any users yet!', 'status' => false));
            }

            $link_post = get_permalink($post_id);
            $post_title = get_the_title($post_id);
            $content = '<p>Click here to view the <a href=' . $link_post . '>'. $post_title  .'</a></p>';
            $headers = array('Content-Type: text/html; charset=UTF-8');

            foreach ($users as $user_id) {

                $user = get_user_by('id', $user_id);

                $email = $user->user_email;
                $sent = wp_mail($email , 'You got an share of the report on the '.$post_title, $content , $headers);

                if (!$sent) throw new Exception($sent, 1);

            }

            return wp_send_json(array('message' => 'Report has been send to the users!', 'status' => true, 'user' => $user));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function delete_additional_file_assessment()
    {
        $file_id = $_POST['file_id'];
        wp_delete_attachment( $file_id, true );
    }

    function get_invite_colleagues_form()
    {
        ?>
        <div class="invite-colleagues-wrapper">
            <form id="form-invite-colleagues" method="post" onsubmit="return false">
                <textarea id="emails-area" rows="3" placeholder="Add the emails (from your company) of all your colleagues who will assist you with the submission, please seperate each email by a comma."></textarea>
                <div class="form-action">
                    <button id="btn-send-invite-colleagues" type="submit">
                        Send invite
                        <img class="send-rolling" src="<?php echo WP_ASSESSMENT_FRONT_IMAGES; ?>/Rolling-0.6s-104px.svg" alt="Rolling">
                    </button>
                    <p class="send-message"></p>
                </div>
            </form>
            <button id="btn-close-invite" aria-label="Close invite colleagues area">
                Close
                <span class="icon-close"><i class="fa-solid fa-xmark"></i></span>
            </button>
        </div>
        <?php
    }

    function send_invite_to_colleagues()
    {
        try {
            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $emails = $_POST['emails'];
            if (empty($emails))
                throw new Exception('Emails not found.');

            $main = new WP_Assessment();
            $emails_sent = array();
            $invited_members_arr = array();
            $updated_meta = false;
            $assessment_link = get_permalink($assessment_id);
            $assessment_title = get_the_title($assessment_id);
            $assessment_title = str_replace('&#8211;', '-', $assessment_title); //Remove special character code to dash(-)

            // Filter mail from
            add_filter( 'wp_mail_from', 'sf_user_mail_from' );
            add_filter( 'wp_mail_from_name', 'sf_user_mail_from_name' );

            foreach ($emails as $email) {
                $email = trim($email);
                $email = preg_replace('/\s+/', '', $email); //Remove all white space from email
                $email_name = strstr($email, '@', true);

                $content  = '<p style="font-size:15px;">Hello <strong>'. $email_name .'</strong></p>';
                $content .= '<p style="font-size:15px;">You have an invitation to work on the '. $assessment_title .'<br>';
                $content .= '<a href=' . $assessment_link . ' target="_blank">Click here to view the assessment.</a></p>';

                // Send mail to users
                $sent = wp_mail($email , 'Invitation to work on the '.$assessment_title, $content);
                $emails_sent[] = $email;
                if (!$sent) throw new Exception($sent, 1);

                // Add users ID to Array
                $user = getUserFromEmail($email);
                if (!empty($user)) {
                    $invite_members_arr[] = $user[0]->Id;
                }
            }

            if (!empty($invite_members_arr)) {
                $invited_members = get_post_meta($assessment_id, 'invited_members', true);
                if (!empty($invited_members)) {
                    // Merge old & new invite members array and update to post meta
                    $new_invited_members = array_unique(array_merge($invited_members, $invite_members_arr));
                    $updated_meta = update_post_meta($assessment_id, 'invited_members', $new_invited_members);
                }
                else {
                    // Update new invite members to post meta
                    $updated_meta = update_post_meta($assessment_id, 'invited_members', $invite_members_arr);
                }
            }

            // Remove filter mail from
            remove_filter( 'wp_mail_from', 'sf_user_mail_from' );
            remove_filter( 'wp_mail_from_name', 'sf_user_mail_from_name' );

            return wp_send_json(array(
                    'message' => 'Invitations has been send',
                    'emails' => $emails_sent,
                    'updated_meta' => $updated_meta,
                    'status' => true
                ));

        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

}

new Question_Form();
