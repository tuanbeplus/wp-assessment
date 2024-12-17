<?php
/**
 * Get Member's Opportunities has perchased
 *
 * @return array Opportunities perchased
 * 
 */
function getOpportunitiesPerchased()
{
	$opportunities = getOpportunity();
    $opps_purchased_arr = array();

    foreach ($opportunities->records as $opportunity) {
        if (isset($opportunity->DCR_Purchase__c) && $opportunity->DCR_Purchase__c == true) {
            $opps_purchased_arr['drc_purchase'][] = $opportunity;
        }
        if (isset($opportunity->Index_Purchase__c) && $opportunity->Index_Purchase__c == true) {
            $opps_purchased_arr['index_purchase'][] = $opportunity;
        }
    }

    return $opps_purchased_arr;
}

/**
 * Get Salesforce Products ID by member's Opportunity
 *
 * @return array Salesforce Products ID 
 * 
 */
function getProductIdByOpportunity() 
{
	$sf_products_id = array(
		'dcr_product_id' => array(),
		'index_product_id' => array(),
	);
	$opps_purchased_arr = getOpportunitiesPerchased();
	$drc_purchase = $opps_purchased_arr['drc_purchase'] ?? array();
	$index_purchase = $opps_purchased_arr['index_purchase'] ?? array();

	// Add DCR Product2 ID to array
	if (isset($drc_purchase) && !empty($drc_purchase)) {
		foreach ($drc_purchase as $item) {
			$opp_has_drc_purchase_id = $item->Id ?? '';
			$opp_line_items = getOpportunityLineItem($opp_has_drc_purchase_id);

			if (isset($opp_line_items->records) && !empty($opp_line_items->records)) {
				foreach ($opp_line_items->records as $opp_line_item) {
					if ($opp_line_item->Inclusion_Product_Type__c == 'DCR') {
						$sf_products_id['dcr_product_id'][] = $opp_line_item->Product2Id ?? '';
					}
				}
			}
		}
	}
	// Add Index Product2 ID to array
	if (isset($index_purchase) && !empty($index_purchase)) {
		foreach ($index_purchase as $item) {
			$opp_has_index_purchase_id = $item->Id ?? '';
			$opp_line_items = getOpportunityLineItem($opp_has_index_purchase_id);

			if (isset($opp_line_items->records) && !empty($opp_line_items->records)) {
				foreach ($opp_line_items->records as $opp_line_item) {
					if ($opp_line_item->Inclusion_Product_Type__c == 'Index') {
						$sf_products_id['index_product_id'][] = $opp_line_item->Product2Id ?? '';
					}
				}
			}
		}
	}

    return $sf_products_id;
}

/**
 * Get all assessments from a specified category that have the 'related_sf_products' meta field
 *
 * @param array $term_slug    Category slug
 *
 * @return array Assessments ID List
 */
function get_assessments_related_saturn_products()
{
    $args = array(
		'post_type' => 'assessments',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'meta_query' => array(
            array(
                'key' => 'related_sf_products',
                'value' => '',
                'compare' => '!='
            )
		),
	);

	$assessments = get_posts($args);
	$assessments_arr = array();

	if (!empty($assessments)) {
		foreach ($assessments as $assessment) {
			$assessments_arr[] = $assessment->ID;
		}
	}
	wp_reset_postdata();
    
    return $assessments_arr;
}

/**
 * Get Submissions (pending, accepted) was submitted by user
 *
 * @param string $organisation_id   User's Salesforce Organisation ID
 * @param string $assessment_id     Assessment ID
 *
 * @return array Submission data
 * 
 */
function get_submissions_completed($organisation_id, $assessment_id)
{
	$assessment_terms = get_assessment_terms($assessment_id);

	if (is_array($assessment_terms) && isset($assessment_terms[0])) {
		if ($assessment_terms[0] == 'dcr') {
			return null;
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
		'posts_per_page' => -1,
		'nopaging' => true, 
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'DESC',
		'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'organisation_id',
                'value' => $organisation_id,
                'compare' => '=',
            ),
            array(
                'key' => 'assessment_id',
                'value' => $assessment_id,
                'compare' => '=',
            ),
        ),
	);
	// get all submissions completed
	$submissions = get_posts($args);
	$submissions_arr = array();

	if (!empty($submissions)) {
		foreach ($submissions as $submission) {
			$status = get_post_meta($submission->ID, 'assessment_status', true);
			if ($status == 'pending' || $status == 'completed' || $status == 'accepted') {
				$submissions_arr[] = $submission->ID;
			}
		}
	}
	return $submissions_arr;
}

/**
 * Get all terms of an Assessment
 *
 * @param int $assessment_id   	Assessment ID
 *
 * @return array Terms Array
 * 
 */
function get_assessment_terms($assessment_id)
{
	$terms = get_the_terms( $assessment_id , 'category' );
	$terms_arr = array();

	if ($terms) {
		foreach ($terms as $term) {
			$terms_arr[] = $term->slug;
		}
	}

	return $terms_arr;
}

/**
 * Get current WP user's ID by Salesforce user's ID
 *
 * @param int $sf_user_id   Salesforce user's ID
 *
 * @return int WP User's ID
 * 
 */
function get_current_user_by_salesforce_id($sf_user_id) 
{
    $user = get_users(array(
        'meta_key' => '__salesforce_user_id',
        'meta_value' => $sf_user_id,
    ));

    if (!empty($user)) {
        return $user[0]->ID;
    }
}

/**
 * Get current WP user's Email by Salesforce user's ID
 *
 * @param int $sf_user_id   Salesforce user's ID
 *
 * @return string WP User's Email
 * 
 */
function get_current_user_email_by_salesforce_id($sf_user_id)
{
	$user = get_users(array(
        'meta_key' => '__salesforce_user_id',
        'meta_value' => $sf_user_id,
    ));

    if (isset($user[0]) && !empty($user[0])) {
        $user_email = $user[0]->user_email;
		return $user_email;
    }
	else {
		$sf_user_data = getUser($sf_user_id)->records[0];
		if (isset($sf_user_data) && !empty($sf_user_data)) {
			$user_email = getUser($sf_user_id)->records[0]->Email;
			return $user_email;
		}
	}
}

/**
 * Check accessible for Salesforce Members (User)
 *
 * @param string $user_id   		Salesforce User ID
 * @param int 	 $assessment_id   	Assessment ID
 *
 * @return boolean true/false
 * 
 */
function check_access_salesforce_members($user_id, $assessment_id)
{
	$is_user_can_access = false;
	$is_all_users_can_access = get_post_meta($assessment_id, 'is_all_users_can_access', true);

	// Assessment is accessible for all loged in users
	if ($is_all_users_can_access == true) {
		$is_user_can_access = true;
	}

	return $is_user_can_access;
}

/**
 * Get Assessments accessible for all logged in users
 *
 * @return array Array of Assessments ID
 * 
 */
function get_assessments_accessible_all_users()
{
    $args = array(
		'post_type' => 'assessments',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'meta_query' => array(
            array(
                'key' => 'is_all_users_can_access',
                'value' => true,
                'compare' => '=',
            ),
        ),
	);
	$assessments = get_posts($args);
	$assessments_arr = array();

	foreach ($assessments as $assessment) {
		$assessments_arr[] = $assessment->ID;
	}
	wp_reset_postdata();
    
    return $assessments_arr;
}

/**
 * Get the Status of Saturn Invites by Contact ID
 *
 * @param string $sf_user_id   		Salesforce User ID
 * @param int 	 $assessment_id   	Assessment ID
 *
 * @return Status Active/Expired/Null
 * 
 */
function get_saturn_invite_status($sf_user_id, $assessment_id) {
	// Get WordPress User ID
	$wp_user_id = get_current_user_by_salesforce_id($sf_user_id);

	// Salesforce Contact ID
	$contact_id = get_user_meta($wp_user_id, 'salesforce_contact_id', true);
	if (!empty($contact_id)) {
		$sf_user_data = getUser($sf_user_id);
		if (!empty($sf_user_data) && !empty($sf_user_data->records)) {
			$contact_id = $sf_user_data->records[0]->ContactId ?? '';
		}
		else {
			return null;
		}
	}
	// Get the Saturn Invites data
	$saturn_invites = get_post_meta($assessment_id, 'sf_saturn_invites', true);

	$filtered = '';
	if (is_array($saturn_invites) && !empty($saturn_invites)) {
		$filtered = array_filter($saturn_invites, function($item) use ($contact_id) {
			return isset($item['Contact__c']) && $item['Contact__c'] === $contact_id;
		});
	}

	// Return the Status__c of the first matching item, or null if no matches found
    return !empty($filtered) ? reset($filtered)['Status__c'] : null;
}

/**
 * Get Assessments accessible on Dashboard
 *
 * @param string $sf_user_id   		Salesforce User ID
 * @param string $organisation_id   Salesforce Account ID
 * @param array  $assessments_list  Assessments ID array
 *
 * @return array Assessments accessible final array
 * 
 */
function get_assessments_on_dashboard($sf_user_id, $organisation_id, $assessments_list) {
	$assessment_final_list = array();
	if (!empty($assessments_list)) {
		foreach ($assessments_list as $assessment_id) {
			// Get Submission completed of the Assessment
			$submission_completed = get_submissions_completed($organisation_id, $assessment_id);
			// Check user access to asessment
			$is_all_users_can_access = get_post_meta($assessment_id, 'is_all_users_can_access', true);
			// Get Status of the Saturn Invite
			$saturn_invite_status = get_saturn_invite_status($sf_user_id, $assessment_id);
		
			if (empty($submission_completed)){
				if ($is_all_users_can_access == true || $saturn_invite_status == 'Active') {
					$assessment_final_list[] = $assessment_id;
				}
			}
		}
	}
	return $assessment_final_list;
}

/**
 * Get List members (User) from Organisation
 *
 * @param string $organisation_id   Salesforce Organisation ID
 * 
 * @param string $post_id   Assessment ID
 *
 * @return html List Members
 * 
 */
add_action('wp_ajax_get_members_from_org_ajax', 'get_members_from_org_ajax');
add_action('wp_ajax_nopriv_get_members_from_org_ajax', 'get_members_from_org_ajax');
function get_members_from_org_ajax() 
{
	try {
		$organisation_id = $_POST['organisation_id'];
		if (empty($organisation_id))
            throw new Exception('Organisation not found.');

		$post_id = $_POST['post_id'];
		if (empty($post_id))
			throw new Exception('Assessment ID not found.');

		$users = get_option('salesforce_members_data');
		$assigned_members = get_post_meta($post_id, 'assigned_members', true);
		$assigned_member_ids = array();
		$list_members_dropdown = null;
		$selected = null;
		foreach ($assigned_members as $member) {
			$assigned_member_ids[] = $member['id'];
		}

		if (!empty($users)) {
			foreach ($users as $user) {
				if ($user['AccountId'] == $organisation_id) {
					if (in_array($user['Id'], $assigned_member_ids)) 
						$selected = 'selected';
					else 
						$selected = null;

					$list_members_dropdown.= '<li class="item member '.$selected.'" data-id="'.$user['Id'];
					$list_members_dropdown.= 	'" data-org-name="'.$user['OrgName'].'">';
					$list_members_dropdown.= 	$user['Name'];
					$list_members_dropdown.= '</li>';
				}
			}
			if (empty($list_members_dropdown)) {
				$list_members_dropdown = '<p>No members found</p>';
			}

			return wp_send_json(array('list' => $list_members_dropdown, 'status' => true));
			die;
		}
		else {
			throw new Exception('The organisation did not have any members.');
		}

	} catch (Exception $exception) {
		return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
	}
}

/**
 * Save option salesforce_members_data to WP Options
 *
 * @param array $members_data   Salesforce Member(User) Data get by API
 * 
 * @return string Message Successful/Failed
 * 
 */
add_action('wp_ajax_save_option_members_data_ajax', 'save_option_members_data_ajax');
add_action('wp_ajax_nopriv_save_option_members_data_ajax', 'save_option_members_data_ajax');
function save_option_members_data_ajax()
{
	try {
		if ($_POST['clicked'] == true) {
			$members_data = getAllUsersFromOrgMember();

			if (!empty($members_data)) {
				
				if (get_option('salesforce_members_data')) {
					update_option('salesforce_members_data', $members_data);
					return wp_send_json(array('message' => 'Refresh Successful', 'status' => true));
				}
				else {
					$add_option = add_option('salesforce_members_data', $members_data);

					if ($add_option) {
						return wp_send_json(array('message' => 'Add Successful', 'status' => true));
					}
					else {
						throw new Exception('Refresh Failed');
					}
				}
			}
			else {
				throw new Exception('Empty Members Data');
			}
		}
		
	} catch (Exception $exception) {
		return wp_send_json(array('message' => $exception->getMessage(), 'status' => false));
	}
}

/**
 * Sum Submission score array
 *
 * @param array $score_array   Submission Score Array
 * 
 * @return string Total Score 
 * 
 */
function array_sum_submission_score($assessment_id, $score_array=[]) 
{
	$main = new WP_Assessment();
	$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
	$questions = $main->wpa_unserialize_metadata($questions);
	$total_score = array();

	if (is_array($score_array) && !empty($score_array)) {

		foreach ($score_array as $i => $group) {
			foreach ($group as $j => $quiz) {
				$weighting = $questions[$i]['list'][$j]['point'];
				if (!empty($quiz)) {
					$total_score[] = $quiz;
				}
			}
		}
		if (!empty($total_score)) {
			return array_sum($total_score) ?? 0;
		}
		else {
			return 0;
		}
	}
	else {
		return 0;
	}
}

function array_sum_submission_score_with_weighting($assessment_id, $score_array=[]) 
{
	$main = new WP_Assessment();
	$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
	$questions = $main->wpa_unserialize_metadata($questions);
	$total_score = array();

	if (is_array($score_array) && !empty($score_array)) {

		foreach ($score_array as $i => $group) {
			foreach ($group as $j => $quiz) {
				$weighting = $questions[$i]['list'][$j]['point'];
				if (!empty($quiz)) {
					$total_score[] = $quiz * $weighting;
				}
			}
		}
		if (!empty($total_score)) {
			return array_sum($total_score) ?? 0;
		}
		else {
			return 0;
		}
	}
	else {
		return 0;
	}
}

/**
 * Calculator Submission score array
 *
 * @param array $scores_arr   Submission Score Array
 * 
 * @return array Array Score after cal with weighting
 * 
 */
function cal_scores_with_weighting($assessment_id, $scores_arr, $arr_type = 'sub'){
	$main = new WP_Assessment();
	$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
	$questions = $main->wpa_unserialize_metadata($questions);
	$cal_scores_array = array();

	if (is_array($scores_arr) && !empty($scores_arr)) {
		// Get Scoring formula type
		$scoring_formula = get_post_meta($assessment_id, 'scoring_formula', true);

		foreach ($scores_arr as $i => $group) {
			foreach ($group as $j => $quiz) {
				$weighting = $questions[$i]['list'][$j]['point'];
				if (!empty($quiz)) {
					// Using Index formula 2024
					if (!empty($scoring_formula) && $scoring_formula == 'index_formula_2024') {
						$cal_scores_array[$i][$j] = (float)$quiz;
					}
					// Using Index formula 2023
					else {
						$cal_scores_array[$i][$j] = (float)$quiz * (float)$weighting;
					}
				}
				else {
					$cal_scores_array[$i][$j] = 0;
				}
			}
		}

		if ($arr_type != 'group') return $cal_scores_array;

		$average_group_scores = array();
		if (!empty($cal_scores_array)) {
			foreach ($cal_scores_array as $i => $group) {
				if (is_array($group) && !empty($group)) {
					$average_group_scores[$i] = number_format(round(array_sum($group) / count($group), 1), 1);
				}
			}

			return $average_group_scores;
		}
	}
}

function cal_scores_with_weighting_for_percentages($assessment_id, $scores_arr, $arr_type = 'sub'){
	$main = new WP_Assessment();
	$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
	$questions = $main->wpa_unserialize_metadata($questions);
	$cal_scores_array = array();

	if (is_array($scores_arr) && !empty($scores_arr)) {

		foreach ($scores_arr as $i => $group) {
			foreach ($group as $j => $quiz) {
				$weighting = $questions[$i]['list'][$j]['point'];
				if (!empty($quiz)) {
					$cal_scores_array[$i][$j] = (float)$quiz * (float)$weighting;
				}
				else {
					$cal_scores_array[$i][$j] = 0;
				}
			}
		}

		if ($arr_type != 'group') return $cal_scores_array;

		$average_group_scores = array();
		if (!empty($cal_scores_array)) {
			foreach ($cal_scores_array as $i => $group) {
				if (is_array($group) && !empty($group)) {
					$average_group_scores[$i] = number_format(round(array_sum($group) / count($group), 1), 1);
				}
			}

			return $average_group_scores;
		}
	}
}

/**
 * Get Organisation data by Salesforce User ID & Org ID
 *
 * @return array Organisation data 
 * 
 */
function get_sf_organisation_data($sf_user_id, $org_id)
{
	$args = array(
		'meta_query' => array(
			'relation' => 'AND', 
			array(
				'key' => '__salesforce_user_id',
				'value' => $sf_user_id,
				'compare' => '='
			),
			array(
				'key' => '__salesforce_account_id',
				'value' => $org_id,
				'compare' => '='
			)
		)
	);
	$user = get_users($args);
	$org_meta = get_user_meta($user[0]->ID, '__salesforce_account_json', true);

	if (!empty($org_meta)) {
		return json_decode($org_meta, true);
	}
	else {
		$org_data = sf_get_object_metadata('Account', $org_id);
		$org_data = json_decode(json_encode($org_data), true);
		return $org_data;
	}
}

/**
 * Check if a report for a submission exists.
 * 
 * @param int|string $submission_id The submission ID to check.
 * @param string $report_type The post type to search within.
 * @return int|null Report ID if found, null otherwise.
 */
function is_report_of_submission_exist($submission_id = null, $report_type = '') {
    // Validate input parameters
    if (empty($submission_id) || empty($report_type)) {
        return null;
    }
    // Query to find a matching post
    $args = array(
        'post_type'      => $report_type,
        'posts_per_page' => 1,
        'post_status'    => 'any',
        'fields'         => 'ids', 
        'meta_query'     => array(
            array(
                'key'   => 'submission_id',
                'value' => $submission_id,
            ),
        ),
    );
    $query = new WP_Query($args);
    // Return the first report ID or null
    return !empty($query->posts) ? $query->posts[0] : null;
}

/**
 * Get Maturity Level step 1 from Organisation Score
 * 
 * @param $score 
 *
 * @return int Maturity Level step 1
 * 
 */
function get_maturity_level_org($score) 
{
	if ($score != null) {
		if ($score >= 0 && $score < 0.5) {
			return 1;
		}
		else if ($score >= 0.5 && $score < 1) {
			return 1.5;
		}
		else if ($score >= 1 && $score < 1.5) {
			return 2;
		}
		else if ($score >= 1.5 && $score < 2) {
			return 2.5;
		}
		else if ($score >= 2 && $score < 3) {
			return 3;
		}
		else if ($score >= 3 && $score < 4) {
			return 3.5;
		}
		else if ($score >= 4) {
			return 4;
		}
	}
	else {
		return 1;
	}
}

/**
 * Get Maturity Level step 2 from step 1
 * 
 * @param $level 
 *
 * @return int Maturity Level step 2
 * 
 */
function get_maturity_level_org_step_2($level) 
{
	if ($level != null) {
		if ($level >= 1 && $level < 2) {
			return 1;
		}
		else if ($level >= 2 && $level < 3) {
			return 2;
		}
		else if ($level >= 3 && $level < 4) {
			return 3;
		}
		else if ($level >= 4) {
			return 4;
		}
	}
	else {
		return null;
	}
}

/**
 * Get all submissions of an assessment
 * 
 * @param $assessment_id 
 *
 * @return array Submission objects
 * 
 */
function get_all_submissions_of_assessment($assessment_id)
{
	$args = array(
		'post_type' => 'submissions',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'meta_query' => array(
			array(
				'key' => 'assessment_id',
				'value' => $assessment_id,
			)
		),
	);
	$submissions = get_posts($args);
	return $submissions;
}

/**
 * Get all submissions that have been finalised(published)
 * 
 * @param $assessment_id 
 *
 * @return string Number of Submissions
 * 
 */
function count_all_index_submissions_finalised($assessment_id)
{
	$args = array(
		'post_type' => 'submissions',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'meta_query' => array(
			array(
				'key' => 'assessment_id',
				'value' => $assessment_id,
			)
		),
	);
	$index_query = new WP_Query($args);
	
	$index_count = $index_query->post_count;

	return $index_count;
}

/**
 * Get ranking of an assessment
 * 
 * @param $assessment_id 
 *
 * @return int Ranking ID
 * 
 */
function get_ranking_of_assessment($assessment_id) 
{
	$args = array(
		'post_type' => 'ranking',
		'posts_per_page' => 1,
		'post_status' => 'publish',
		'orderby' => 'date',
		'order' => 'DESC',
		'meta_query' => array(
			array(
				'key' => 'assessment',
				'value' => $assessment_id,
			)
		),
	);
	$ranking = get_posts($args);
	return $ranking[0]->ID;
}

/**
 * Calculator Overall of all Submissions Score
 * 
 * @param $assessment_id 
 * @param $post_meta 
 *
 * @return array average score of all Submissions, Percent average score of all Submissions
 * 
 */
function cal_overall_total_score($assessment_id, $post_meta)
{
	$overall_scores = array();
	$result = array();
	$submissions = get_all_submissions_of_assessment($assessment_id);
	$assessment_max_score = get_assessment_max_score($assessment_id);

	if (!empty($submissions)) {
		foreach ($submissions as $submission){
			$total_score = get_post_meta($submission->ID, $post_meta, true) ?? 0;
			if (isset($total_score['sum'])) {
				$overall_scores[] = $total_score['sum'];
			}
		}
		if (!empty($overall_scores) && is_array($overall_scores)) {
			$result['sum_average'] = number_format(array_sum($overall_scores)/count($overall_scores), 1);
			$result['percent_average'] = round(array_sum($overall_scores)/count($overall_scores) / $assessment_max_score * 100);
			return $result;
		}
		else {
			return 0;
		}
	}
	else {
		return 0;
	}
}

/**
 * Calculator average of all Industry type Score
 * 
 * @param array $industry_score_data	Industry total score data from Ranking 
 *
 * @return int Average Percent 
 * 
 */
function cal_average_industry_score($assessment_id, $industry_score_data=[])
{
	$total_industry_score = array();
	$assessment_max_score = get_assessment_max_score($assessment_id);

	if (!empty($industry_score_data)) {
		foreach ($industry_score_data as $record) {
			$total_industry_score[] = $record['total_score'];
		}

		if (is_array($total_industry_score)) {
			$average = array_sum($total_industry_score) / count($total_industry_score);
			$average_percent = round($average / $assessment_max_score * 100);
			return $average_percent;
		}
		else {
			return false;
		}
	}
}


function set_org_data_to_all_submissions() {
	$all_orgs = array();
    $args = array(
		'post_type' => 'submissions',
		'posts_per_page' => -1,
		'post_status' => 'any',
	);
	$all_submissions = get_posts($args);

	$index = 1;
	foreach ($all_submissions as $submission) {

		$organisation_id = get_post_meta($submission->ID, 'organisation_id', true);
		$org_metadata = get_post_meta($submission->ID, 'org_data', true);

		if (isset($organisation_id) && empty($org_metadata['Industry'])) {
			
			$new_org_data_obj = sf_get_object_metadata('Account', $organisation_id);
			$new_org_data_arr = json_decode(json_encode($new_org_data_obj), true);

			if (isset($new_org_data_arr['Industry'])) {
				$update_status = update_post_meta($submission->ID, 'org_data', $new_org_data_arr);
				if ($update_status) {
					echo 'Update '.$submission->ID.' Successful!';
				}
				else {
					echo 'Update '.$submission->ID.' Failed.';
				}
				echo '<br>';
			}
			$index++;
		}
	}
} 

// Function to merge arrays by a common value
function merge_array_score_by_value($arrays, $key)
{
    // Use array_reduce to merge arrays based on the specified key
    $result = array_reduce($arrays, function ($carry, $item) use ($key) {
        $index = $item[$key];
        if (!isset($carry[$index])) {
            $carry[$index] = $item;
        } else {
            // Sum the numeric values for each "key_area"
            foreach ($item as $subKey => $value) {
                if (is_numeric($value) && isset($carry[$index][$subKey])) {
                    $carry[$index][$subKey] += $value;
                } else {
                    // Set the value for the new year
                    $carry[$index][$subKey] = $value;
                }
            }
        }
        return $carry;
    }, []);

    // Use array_values to reset the array keys
    return array_values($result);
}

/**
 * Get history dashboard score fields 
 * 
 * @param array $data_score		Scores each year from report ACF dashboard fields 
 *
 * @return array Data score by years
 * 
 */
function get_history_dashboard_scores($data_score) {
	$data_score_by_year = array();
	$index = 0;
	if (is_array($data_score) && count($data_score) > 0) {
		foreach ($data_score as $record) {
			if (isset($record['data_values'])) {
				foreach ($record['data_values'] as $key_area) {
					$data_score_by_year[$index]['key_area'] = $key_area['key_area'];
					$data_score_by_year[$index][$record['year']] = $key_area['value'];
					$index++;
				}
			}
		}
	}
	if (!empty($data_score_by_year)) {
		$result = merge_array_score_by_value($data_score_by_year, 'key_area');
		return $result;
	}
}

/**
 * Get the Key areas of Assessment
 * 
 * @param int $assessment_id	Assessment ID
 *
 * @return array Key Areas
 * 
 */
function get_assessment_key_areas($assessment_id) {
	// get key areas from post meta
	$key_areas = get_post_meta($assessment_id, 'report_key_areas', true);
	
	if (empty($key_areas)) {
		// set default key areas if it not exist
		$key_areas = array('Framework', 'Implementation', 'Review', 'Innovation');
	}

	return $key_areas;
}

/**
 * Remove attributes from HTML tags
 * 
 * @param string $html_string	HTML string
 *
 * @return string Clean HTML string
 * 
 */
function clean_html_report_pdf($html_string) {
	$clean_html = preg_replace_callback(
		'/<(?!img|a\s)([a-zA-Z0-9]+)([^>]*)>/',
		function ($matches) {
			// If it's an anchor tag, preserve href and target attributes
			if ($matches[1] === 'a') {
				return "<{$matches[1]} href{$matches[2]}>";
			} else {
				return "<{$matches[1]}>";
			}
		},
		$html_string
	);
	return $clean_html;
}

/**
 * Get the Exception Organisations ID
 *
 * @return array Orgs ID
 * 
 */
function get_exception_orgs_id() {
	$exception_orgs_id = array();
	$exception_repeater = get_field('exception_orgs_id', 'option');

	if (!empty($exception_repeater)) {
		foreach($exception_repeater as $row) {
			$exception_orgs_id[] = $row['organisation_id'];
		}
	}
	return $exception_orgs_id;
}

/**
 * Get the Max Score of the Assessment.
 *
 * @param int|string $assessment_id The ID of the assessment post.
 * @return float Max score of the assessment.
 */
function get_assessment_max_score($assessment_id = '') {
    // Define an associative array of max scores by year.
    $max_scores = array(
		'index_formula_2023' => 272.0,
        'index_formula_2024' => 271.2,
	);
    // Default to 2023 if no assessment ID is provided or if the formula is missing/invalid.
    $default_formula = 'index_formula_2023';

    // Get the selected scoring formula in the assessment.
    if (!empty($assessment_id)) {
        $scoring_formula = get_post_meta($assessment_id, 'scoring_formula', true);
        if (!empty($scoring_formula) && array_key_exists($scoring_formula, $max_scores)) {
            return (float) $max_scores[$scoring_formula];
        }
    }
    // Return the max score for the default year.
    return (float) $max_scores[$default_formula];
}

/**
 * Find a matching quiz record based on group ID and sub-question ID.
 *
 * @param array  $user_quizzes List of user quizzes.
 * @param string $group_id     The group ID to match.
 * @param string $sub_id       The sub-question ID to match.
 *
 * @return object|null The matching quiz record, or null if none found.
 */
function wpa_find_matching_quiz($user_quizzes, $group_id, $sub_id) {
    foreach ($user_quizzes as $quiz) {
        if ($quiz->parent_id == $group_id && $quiz->quiz_id == $sub_id) {
            return $quiz;
        }
    }
    return null;
}

/**
 * Get all index answer scores for a given assessment, submission, and organisation.
 *
 * @param string $assessment_id    The ID of the assessment.
 * @param string $submission_id    The ID of the submission.
 * @param string $organisation_id  The ID of the organisation.
 *
 * @return array|null An associative array of answer scores, or null if invalid parameters are provided.
 */
function get_all_index_answer_scores($assessment_id = '', $submission_id = '', $organisation_id = '') {
    // Validate input parameters
    if (empty($assessment_id) || empty($submission_id) || empty($organisation_id)) {
        return null; // Return null if any parameter is missing
    }
    // Initialize an empty array to store the scores
    $answer_scores = array();
    // Instantiate WP_Assessment class
    $assessment = new WP_Assessment();
    // Retrieve and unserialize the questions associated with the assessment
    $question_groups = $assessment->wpa_unserialize_metadata(
        get_post_meta($assessment_id, 'question_group_repeater', true)
    );
    // Retrieve user quiz data based on assessment, submission, and organisation IDs
    $user_quizzes = $assessment->get_quizzes_by_assessment_and_submissions(
        $assessment_id, 
        $submission_id, 
        $organisation_id
    );
    // Process each group of questions
    foreach ($question_groups as $group_id => $group_questions) {
        if (isset($group_questions['list']) && !empty($group_questions['list'])) {
            foreach ($group_questions['list'] as $sub_id => $sub_question) {
                // Find matching quiz record and store the quiz point
                $matching_quiz = wpa_find_matching_quiz($user_quizzes, $group_id, $sub_id);
                if ($matching_quiz) {
                    $answer_scores[$group_id][$sub_id] = $matching_quiz->quiz_point ?? 0;
                }
            }
        }
    }
    // Return the answer scores array
    return $answer_scores;
}

/**
 * Get name of the submission version by Id.
 *
 * @param int|string $submission_id    Submission's ID
 * @return string Submission Name
 */
function get_submission_version_name($submission_id) {
	if (empty($submission_id)) {
		return 'Susmission ID not found.';
	}
	$submission_name = '';

	$this_sub_ver = get_post_meta($submission_id, 'submission_version', true);
	$this_sub_ver = !empty($this_sub_ver) ? '#'.$this_sub_ver : 'N/A';

	$created_date = get_post_meta($submission_id, 'created_date', true);
	$created_date = !empty($created_date) ? $created_date : get_the_date('Y-m-d H:i:s', $submission_id);

	$submission_name = 'Submission '.$this_sub_ver.' on '. date('M d, Y \a\t H:i a', strtotime($created_date));
	return $submission_name;
}