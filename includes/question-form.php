<?php

class Question_Form
{
    public int $user_id;

    public function __construct()
    {
        add_action('wp_ajax_save_question', array($this, 'save_question'));
        add_action('wp_ajax_nopriv_save_question', array($this, 'save_question'));

        add_action('wp_ajax_save_question_progress', array($this, 'save_question_progress'));
        add_action('wp_ajax_nopriv_save_question_progress', array($this, 'save_question_progress'));

        add_action('wp_ajax_get_quiz_detail', array($this, 'get_quiz_detail'));
        add_action('wp_ajax_nopriv_get_quiz_detail', array($this, 'get_quiz_detail'));

        add_action('wp_ajax_create_assessment_submission', array($this, 'create_assessment_submission'));
        add_action('wp_ajax_nopriv_create_assessment_submission', array($this, 'create_assessment_submission'));

        add_action('wp_ajax_submit_assessment_progress', array($this, 'submit_assessment_progress'));
        add_action('wp_ajax_nopriv_submit_assessment_progress', array($this, 'submit_assessment_progress'));

        add_action('wp_ajax_reject_submission_feedback', array($this, 'reject_submission_feedback'));
        add_action('wp_ajax_nopriv_reject_submission_feedback', array($this, 'reject_submission_feedback'));

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

        $this->user_id = get_current_user_id(); 

    }

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
                throw new Exception('Assessment not found.');

            $status_submisstion = '';
            // $submission_id = $this->is_submission_progress_exist($user_id, $assessment_id);
            $submission_id = $this->is_submission_progress_exist($organisation_id, $assessment_id);

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

                        if($submission_id){
                            // $quiz_data = $main->get_quiz_by_assessment_id_and_submisstion_parent($assessment_id,$submission_id, $quiz_id, $user_id , $parent_id);
                            $quiz_data = $main->get_quiz_by_assessment_id_and_submisstion_parent($assessment_id,$submission_id, $quiz_id, $organisation_id , $parent_id);
                        }else{
                            // $quiz_data = $main->get_quiz_by_assessment_id_and_parent($assessment_id, $quiz_id, $user_id , $parent_id);
                            $quiz_data = $main->get_quiz_by_assessment_id_and_parent($assessment_id, $quiz_id, $organisation_id , $parent_id);
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
                                'parent_id' => $parent_id
                            );
                        }

                        if (!$quiz_data) { 
                            // Insert Quiz record if quiz_data not exist
                            $main->insert_quiz_by_assessment_id(array_merge($input, $conditions));
                        } else {
                            // Update Quiz record if quiz_data exist
                            $main->update_quiz_assessment($input, $conditions);
                        }
                    }
                }
            }
            else {

                $answers = $_POST['answers'] ?? null;
                $description = $_POST['description'] ?? null;
                $attachment_id = $_POST['attachment_id'] ?? null;

                //   $is_options_exist = $this->check_multiple_choice_exist_in_assessment($assessment_id, $quiz_id);

                //   if ($is_options_exist && !is_array($answers))
                //       throw new Exception('Invalid answers');

                $main = new WP_Assessment();

                if($submission_id){
                    // $quiz_data = $main->get_quiz_by_assessment_id_and_submisstion($assessment_id,$submission_id, $quiz_id, $user_id);
                    $quiz_data = $main->get_quiz_by_assessment_id_and_submisstion($assessment_id,$submission_id, $quiz_id, $organisation_id);
                }else{
                    // $quiz_data = $main->get_quiz_by_assessment_id($assessment_id, $quiz_id, $user_id);
                    $quiz_data = $main->get_quiz_by_assessment_id($assessment_id, $quiz_id, $organisation_id);
                }

                $input = [];

                if (!empty($user_id))
                    $input['user_id'] = $user_id;

                if (!empty($organisation_id))
                    $input['organisation_id'] = $organisation_id;

                if (!empty($submission_id))
                    $input['submission_id'] = $submission_id;

                //   if ($is_options_exist)
                    $input['answers'] = json_encode($answers);

                if (!empty($description))
                    $input['description'] = $description;

                if (!empty($attachment_id))
                    $input['attachment_id'] = $attachment_id;

                //   if (count($input) === 0)
                //       throw new Exception('Please complete the answer');

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

                if (!$quiz_data) { 
                    // Insert Quiz record if quiz_data not exist
                    $main->insert_quiz_by_assessment_id(array_merge($input, $conditions));
                } else {
                    // Update Quiz record if quiz_data exist
                    $main->update_quiz_assessment($input, $conditions);
                }
            }

            return wp_send_json(array('message' => 'Progress has been updated', 'status' => true, 'data' => array_merge($input, $conditions)));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function save_question_progress()
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

            $data_quiz = $_POST['data_quiz'];
            $type_quiz = $_POST['type_quiz'];
            $quiz_id = intval($_POST['quiz_id']);
            $arr_attachment_ids = $_POST['attachment_ids'];

            if (empty($quiz_id) || !$quiz_id)
                throw new Exception('Assessment not found.');

            $status_submisstion = '';
            // $submission_id = $this->is_submission_progress_exist($user_id, $assessment_id);
            $submission_id = $this->is_submission_progress_exist($organisation_id, $assessment_id);

            if($type_quiz == 'Comprehensive Assessment'){ 

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

                        $answers = $p['choice'];
                        $description = $p['description'] ?? null;
                        $attachment_id = $p['attachment'] ?? null;
                        $attachmentIDs = $p['attachmentIDs'] ?? null;
                        $quiz_point = $p['point'] ?? null;

                        if($submission_id){
                            // $quiz_data = $main->get_quiz_by_assessment_id_and_submisstion_parent($assessment_id,$submission_id, $quiz_id, $user_id , $parent_id);
                            $quiz_data = $main->get_quiz_by_assessment_id_and_submisstion_parent($assessment_id,$submission_id, $quiz_id, $organisation_id , $parent_id);
                        }
                        else{
                            // $quiz_data = $main->get_quiz_by_assessment_id_and_parent($assessment_id, $quiz_id, $user_id , $parent_id);
                            $quiz_data = $main->get_quiz_by_assessment_id_and_parent($assessment_id, $quiz_id, $organisation_id , $parent_id);
                        }

                        $input = [];

                        if (!empty($user_id))
                            $input['user_id'] = $user_id;

                        if (!empty($organisation_id))
                            $input['organisation_id'] = $organisation_id;

                        if (!empty($submission_id))
                            $input['submission_id'] = $submission_id;

                        if (!empty($answers))
                            $input['answers'] = json_encode($answers);

                        if (!empty($description))
                            $input['description'] = $description;

                        if (!empty($attachment_id))
                            $input['attachment_id'] = $attachment_id;

                        if (!empty($attachmentIDs))
                            $input['attachment_ids'] = json_encode($attachmentIDs);

                        if ($quiz_point != null) 
                            $input['quiz_point'] = $quiz_point;

                        // if (count($input) === 0)
                        //     throw new Exception('Please complete the answer');

                        if($submission_id){
                            $conditions = array(
                                // 'user_id' => $user_id,
                                'organisation_id' => $organisation_id,
                                'assessment_id' => $assessment_id,
                                'quiz_id' => $quiz_id,
                                'parent_id' => $parent_id,
                                'submission_id' => $submission_id
                            );
                        } else {
                            $conditions = array(
                                // 'user_id' => $user_id,
                                'organisation_id' => $organisation_id,
                                'assessment_id' => $assessment_id,
                                'quiz_id' => $quiz_id,
                                'parent_id' => $parent_id
                            );
                        }

                        if (!$quiz_data) { 
                            // Insert Quiz record if quiz_data not exist
                            $main->insert_quiz_by_assessment_id(array_merge($input, $conditions));
                        } else {
                            // Update Quiz record if quiz_data exist
                            $main->update_quiz_assessment($input, $conditions);
                        }
                    }
                }

            }else{

                $answers = $_POST['answers'];
                $description = $_POST['description'] ?? null;
                $attachment_id = $_POST['attachment_id'] ?? null;

                //$is_options_exist = $this->check_multiple_choice_exist_in_assessment($assessment_id, $quiz_id);

                // if ($is_options_exist && !is_array($answers))
                //     throw new Exception('Invalid answers');

                $main = new WP_Assessment();

                if($submission_id){
                    // $quiz_data = $main->get_quiz_by_assessment_id_and_submisstion($assessment_id,$submission_id, $quiz_id, $user_id);
                    $quiz_data = $main->get_quiz_by_assessment_id_and_submisstion($assessment_id,$submission_id, $quiz_id, $organisation_id);
                }else{
                    // $quiz_data = $main->get_quiz_by_assessment_id($assessment_id, $quiz_id, $user_id);
                    $quiz_data = $main->get_quiz_by_assessment_id($assessment_id, $quiz_id, $organisation_id);
                }

                $input = [];

                if (!empty($user_id))
                    $input['user_id'] = $user_id;

                if (!empty($organisation_id))
                    $input['organisation_id'] = $organisation_id;

                if (!empty($submission_id))
                    $input['submission_id'] = $submission_id;

                if ($is_options_exist)
                    $input['answers'] = json_encode($answers);

                if (!empty($description))
                    $input['description'] = $description;

                if (!empty($attachment_id))
                    $input['attachment_id'] = $attachment_id;

                // if (count($input) === 0)
                //     throw new Exception('Please complete the answer');

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

                if (!$quiz_data) { 
                    // Insert Quiz record if quiz_data not exist
                    $main->insert_quiz_by_assessment_id(array_merge($input, $conditions));
                } else {
                    // Update Quiz record if quiz_data exist
                    $main->update_quiz_assessment($input, $conditions);
                }
            }

            return wp_send_json(array('message' => 'Progress has been updated', 'attachment_id' => $attachment_id , 'status' => true, 'data' => array_merge($input, $conditions)));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function get_quiz_detail()
    {
        try {
            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $quiz_id = intval($_POST['quiz_id']);

            $main = new WP_Assessment();
            $assessment = $main->get_quiz_by_assessment_id($assessment_id, $quiz_id);
            if (!$assessment)
                throw new Exception('Quiz not found.');

            $assessment->answers = json_decode($assessment->answers);

            return wp_send_json(array('message' => 'Progress has been updated', 'status' => true, 'data' => $assessment->answers));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function create_assessment_submission()
    {
        try {
            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $assessment = get_post($assessment_id);
            $assessment_title = $assessment->post_title;

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

            $post_id = $is_submission_progress_exist;
            if (!$is_submission_exist && !$is_submission_progress_exist) {
                $submission = wp_insert_post(array(
                    'post_type' => 'submissions',
                    'post_title' => 'Submission on ' . $assessment_title,
                    'post_status' => 'publish'
                ));

                if (!$submission) throw new Exception('Cannot submit assessment - Error 1!');

                $post_id = $submission;
            }
            elseif ($is_submission_progress_exist) {
                $submission = wp_update_post(array(
                    'ID'        => $is_submission_progress_exist,
                    'post_type' => 'submissions',
                    'post_title' => 'Submission on ' . $assessment_title,
                    'post_status' => 'publish'
                ));

                if (!$submission) throw new Exception('Cannot submit assessment - Error 2!');

                $post_id = $submission;
            }
            elseif ($is_submission_exist) {
                $post_id = $is_submission_exist;
                update_post_meta($post_id, 'assessment_status', 'pending');
            }

            update_post_meta($post_id, 'user_id', $user_id);
            update_post_meta($post_id, 'organisation_id', $organisation_id);
            update_post_meta($post_id, 'assessment_id', $assessment_id);
            update_post_meta($post_id, 'submission_id', $post_id);
            update_post_meta($post_id, 'assessment_status', 'pending');

            $org_metadata = get_post_meta($post_id, 'org_data', true);
            if (empty($org_metadata)) {
                $sf_org_data = get_sf_organisation_data($user_id, $organisation_id);
                update_post_meta($post_id, 'org_data', $sf_org_data);
            }

            $submission_url = get_permalink( $post_id );

            if(isset($_COOKIE['userId'])) {
                update_field('sf_user_id' , $_COOKIE['userId'], $post_id);
            }
            if(isset($_COOKIE['sf_name'])) {
                update_field('sf_user_name' , $_COOKIE['sf_name'], $post_id);
            }
            if(isset($_COOKIE['sf_user_email'])) {
                update_post_meta($post_id, 'sf_user_email', $_COOKIE['sf_user_email']);
            }

            return wp_send_json(array('message' => 'This Submission has been saved.', 'submission_url' => $submission_url, 'status' => true, 'submission_id' => $post_id));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
        }
    }

    function submit_assessment_progress()
    {
        try {
            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $assessment = get_post($assessment_id);
            $assessment_title = $assessment->post_title;

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

            if (!$is_submission_exist && !$is_submission_progress_exist) {
                $submission = wp_insert_post(array(
                    'post_type' => 'submissions',
                    'post_title' => 'Progress on ' . $assessment_title,
                    'post_status' => 'draft'
                ));

                if (!$submission) throw new Exception('Cannot submit progress this assessment!');

                $post_id = $submission;
                update_post_meta($post_id, 'assessment_status', 'draft');
            }
            elseif ($is_submission_progress_exist) {
                update_post_meta($post_id, 'assessment_status', 'draft');
            }
            elseif($is_submission_exist) {
                $submission = wp_update_post(array(
                    'ID'        => $is_submission_exist,
                    'post_type' => 'submissions',
                    'post_title' => 'Progress on ' . $assessment_title,
                    'post_status' => 'draft'
                ));
            }

            update_post_meta($post_id, 'user_id', $user_id);
            update_post_meta($post_id, 'organisation_id', $organisation_id);
            update_post_meta($post_id, 'assessment_id', $assessment_id);
            update_post_meta($post_id, 'submission_id', $post_id);
            $org_metadata = get_post_meta($post_id, 'org_data', true);
            if (empty($org_metadata)) {
                $sf_org_data = get_sf_organisation_data($user_id, $organisation_id);
                update_post_meta($post_id, 'org_data', $sf_org_data);
            }

            if(isset($_COOKIE['userId'])) {
                update_field('sf_user_id' , $_COOKIE['userId'], $post_id);
            }
            if(isset($_COOKIE['sf_name'])) {
                update_field('sf_user_name' , $_COOKIE['sf_name'], $post_id);
            }
            if(isset($_COOKIE['sf_user_email'])) {
                update_post_meta($post_id, 'sf_user_email' , $_COOKIE['sf_user_email']);
            }

            //Update submission
            if ($post_id) {
                // update submission_id
                global $wpdb;
                $table_name = $wpdb->prefix . 'user_quiz_submissions';

                // Update Submission ID to table
                // $wpdb->query($wpdb->prepare(
                //         "UPDATE $table_name
                //         SET submission_id='$post_id'
                //         WHERE user_id='$user_id'
                //         AND assessment_id='$assessment_id'
                //         AND submission_id=0"
                //     )
                // );

                $wpdb->query($wpdb->prepare(
                    "UPDATE $table_name
                    SET submission_id='$post_id'
                    WHERE organisation_id='$organisation_id'
                    AND assessment_id='$assessment_id'
                    AND submission_id=0"
                    )
                );
            }

            return wp_send_json(array('message' => 'Assessment progress has been saved', 'status' => true, 'submission_id' => $post_id));
        } catch (Exception $exception) {
            return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
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
        $post_id = null;

        $args = array(
            'post_type' => 'submissions',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'meta_query' => array(
                // array(
                //     'key' => 'user_id',
                //     'value' => $user_id,
                // ),
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
        }

        return $post_id;
    }

    function is_submission_progress_exist($organisation_id, $assessment_id)
    {
        $post_id = null;

        $args = array(
            'post_type' => 'submissions',
            'posts_per_page' => 1,
            'post_status' => 'any',
            'meta_query' => array(
                // array(
                //     'key' => 'user_id',
                //     'value' => $user_id,
                // ),
                array(
                    'key' => 'organisation_id',
                    'value' => $organisation_id,
                ),
                array(
                    'key' => 'assessment_id',
                    'value' => $assessment_id,
                )
            ),
        );

        $query = new WP_Query($args);
        $post = $query->get_posts();

        if (is_array($post) && count($post) > 0) {
            $post_id = $post[0]->ID;
            $status = get_post_meta($post_id, 'assessment_status', true);
            if($status == 'pending' || $status == 'accepted') return '';
        }

        return $post_id;
    }

    function is_submission_save_progress_exist($organisation_id, $assessment_id)
    {
        $post_id = null;

        $args = array(
            'post_type' => 'submissions',
            'posts_per_page' => 1,
            'post_status' => 'draft',
            'meta_query' => array(
                // array(
                //     'key' => 'user_id',
                //     'value' => $user_id,
                // ),
                array(
                    'key' => 'organisation_id',
                    'value' => $organisation_id,
                ),
                array(
                    'key' => 'assessment_id',
                    'value' => $assessment_id,
                )
            ),
        );

        $query = new WP_Query($args);
        $post = $query->get_posts();

        if (is_array($post) && count($post) > 0) {
            $post_id = $post[0]->ID;
        }

        return $post_id;
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

    function reject_submission_feedback()
    {
        try {
            global $post;
            $post_id = $post->ID;
            $main = new WP_Assessment();
            $submission_id = intval($_POST['submission_id']);
            // $sf_user_id = get_post_meta($submission_id, 'sf_user_id', true);

            $assessment_id = intval($_POST['assessment_id']);
            if (empty($assessment_id))
                throw new Exception('Assessment not found.');

            $organisation_id = $_POST['organisation_id'];
            if (empty($organisation_id))
                throw new Exception('Organisation not found.');

            $quiz_id = intval($_POST['quiz_id']);
            if (empty($quiz_id))
                throw new Exception('Quiz not found.');

            // if (!empty($sf_user_id)) {
            //     $user_id = $sf_user_id;
            // }
            // else {
            //     $user_id = intval($_POST['user_id']);
            // }

            // if (empty($user_id))
            //     throw new Exception('User not found.');

            $feedback = $_POST['feedback'] ?? null;
            // if (empty($feedback))
            //     throw new Exception('Please add your feedback');

            $type = $_POST['type'];
            if (empty($type))
                throw new Exception('Invalid type');

            $parent_quiz_id = intval($_POST['parent_quiz_id']);
            if (empty($parent_quiz_id))
                throw new Exception('Invalid Group ID');

            $input = [];
            $input['feedback'] = $feedback;
            $input['status'] = $type;

            $conditions = array(
                // 'user_id' => $user_id,
                'organisation_id' => $organisation_id,
                'quiz_id' => $quiz_id,
                'parent_id' => $parent_quiz_id,
                'assessment_id' => $assessment_id,
                'submission_id' => $submission_id,
            );

            $main->update_quiz_assessment($input, $conditions);

            return wp_send_json(array('quiz_id' => $quiz_id, 'parent_id' => $parent_quiz_id, 'message' => 'Feedback for this quiz has been updated', 'status' => true));
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

            return wp_send_json(array('message' => 'Feedback has been updated and send to: '.$sf_user_email, 'status' => true));
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
