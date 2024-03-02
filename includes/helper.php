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
            $opps_purchased_arr['drc_purchase'] = $opportunity;
        }
        if (isset($opportunity->Index_Purchase__c) && $opportunity->Index_Purchase__c == true) {
            $opps_purchased_arr['index_purchase'] = $opportunity;
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
	$sf_products_id = array();
	$opps_purchased_arr = getOpportunitiesPerchased();

	if (isset($opps_purchased_arr['drc_purchase'])) {
		$opp_has_drc_purchase_id = $opps_purchased_arr['drc_purchase']->Id;
		$opp_line_items = getOpportunityLineItem($opp_has_drc_purchase_id);

		foreach ($opp_line_items->records as $opp_line_item) {
			if ($opp_line_item->Inclusion_Product_Type__c == 'DCR') {
				$sf_products_id['dcr_product_id'] = $opp_line_item->Product2Id;
			}
		}
	}

	if (isset($opps_purchased_arr['index_purchase'])) {
		$opp_has_index_purchase_id = $opps_purchased_arr['index_purchase']->Id;
		$opp_line_items = getOpportunityLineItem($opp_has_index_purchase_id);

		foreach ($opp_line_items->records as $opp_line_item) {
			if ($opp_line_item->Inclusion_Product_Type__c == 'Index') {
				$sf_products_id['index_product_id'] = $opp_line_item->Product2Id;
			}
		}
	}

    return $sf_products_id;
}

/**
 * Get Assessments that related to Salesforce products
 *
 * @param string $product_id    Salesforce porducts ID
 * @param string $term          assessment Term's slug
 *
 * @return array Assessments 
 * 
 */
function get_assessments_related_sf_products($product_id, $term)
{
    $args = array(
		'post_type' => 'assessments',
		'posts_per_page' => -1,
		'post_status' => 'publish',
	);
	if (isset($term)) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'category',
				'field'    => 'slug',
				'terms'    => $term,
			)
		);
	}

	$assessments = new WP_Query($args);
	$assessments_arr = array();

    foreach ($assessments->posts as $assessment) {
        $related_sf_products = get_post_meta($assessment->ID, 'related_sf_products', true);

        if (! empty($related_sf_products)) {
			if (in_array($product_id, $related_sf_products)) {
				$assessments_arr[] = $assessment->ID;
			}
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
 * Get Assessments accessible for all logged in users
 *
 * @param array $arr_terms   Terms array include in Assessment
 *
 * @return array Assessments array
 * 
 */
function get_assessments_accessible_all_users($organisation_id, $arr_terms)
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
		'tax_query' => array(  
			array(
				'taxonomy' => 'category',
				'field' => 'slug',
				'terms' => $arr_terms,
				'include_children' => true,
				'operator' => 'IN'
			)
		),
	);
	$assessments = new WP_Query($args);
	$assessments_arr = array();

	foreach ($assessments->posts as $assessment) {
		$submission_completed = get_submissions_completed($organisation_id, $assessment->ID);
		if (empty($submission_completed)) {
			$assessments_arr[] = $assessment->ID;
		}
	}
	wp_reset_postdata();
    
    return $assessments_arr;
}

/**
 * Get all terms of an Assessment
 *
 * @param int 	 $assessment_id   	Assessment ID
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
 * Check accessible for Salesforce Members(User)
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
	$main = new WP_Assessment();
	$terms_arr = get_assessment_terms($assessment_id);
	$is_all_users_can_access = get_post_meta($assessment_id, 'is_all_users_can_access', true);
	$related_sf_products = get_post_meta($assessment_id, 'related_sf_products', true);
	
	$assigned_members = get_post_meta( $assessment_id, 'assigned_members', true);
	$invited_members = get_post_meta( $assessment_id, 'invited_members', true);
	$assigned_member_ids = array();
	foreach ($assigned_members as $member) {
		$assigned_member_ids[] = $member['id'];
	}

	$sf_product_id_opp = getProductIdByOpportunity();
	$drc_product_id = isset($sf_product_id_opp['dcr_product_id']) ? $sf_product_id_opp['dcr_product_id'] : null;
	$index_product_id = isset($sf_product_id_opp['index_product_id']) ? $sf_product_id_opp['index_product_id'] : null;
	
	// check user access to asessment
	if ($drc_product_id && !empty($related_sf_products)) {
	    if (in_array('dcr', $terms_arr) && in_array($drc_product_id, $related_sf_products)) {
	        $is_user_can_access = true;
	    }
	}
	if ($index_product_id && !empty($related_sf_products)) {
	    if (in_array('index', $terms_arr) && in_array($index_product_id, $related_sf_products)) {
	        $is_user_can_access = true;
	    }
	}

	// Accessible for all assigned members
	if (is_array($assigned_member_ids)) {
		if (in_array($user_id, $assigned_member_ids)) {
			$is_user_can_access = true;
		}
	}
	// Accessible for all invited members
	if (is_array($invited_members)) {
		if (in_array($user_id, $invited_members)) {
			$is_user_can_access = true;
		}
	}
	// Assessment is accessible for all loged in users
	if ($is_all_users_can_access == true) {
		$is_user_can_access = true;
	}

	return $is_user_can_access;
}

/**
 * Get all Assessments accessible for Member
 *
 * @param string $user_id   		Salesforce User ID
 * @param string $organisation_id   Salesforce Account ID
 * @param array  $arr_terms   		Terms array of Assessment
 *
 * @return array Assessments accessible array
 * 
 */
function get_assessments_accessible_members($user_id, $organisation_id, $arr_terms)
{
	$args = array(
		'post_type' => 'assessments',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'tax_query' => array(  
			array(
				'taxonomy' => 'category',
				'field' => 'slug',
				'terms' => $arr_terms,
				'include_children' => true,
				'operator' => 'IN'
			)
		),
	);
	$assessments = get_posts($args);
	$accessible_assessments = array();

	foreach ($assessments as $assessment) {
		$is_accessible = check_access_salesforce_members($user_id, $assessment->ID);
		$submission_completed = get_submissions_completed($organisation_id, $assessment->ID);

		if ($is_accessible == true && empty($submission_completed)) {
			$accessible_assessments[] = $assessment->ID;
		}
	}
	wp_reset_postdata();
	
	return $accessible_assessments;
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
		foreach ($scores_arr as $i => $group) {
			foreach ($group as $j => $quiz) {
				$weighting = $questions[$i]['list'][$j]['point'];
				if (!empty($quiz)) {
					$cal_scores_array[$i][$j] = (float)$quiz * (float)$weighting;
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
 * Check Report of Submission exist 
 * 
 * @param $submission_id
 *
 * @return int Report ID  
 * 
 */
function is_report_of_submission_exist($submission_id) 
{
	$args = array(
		'post_type' => 'reports',
		'posts_per_page' => 1,
		'post_status' => 'any',
		'meta_query' => array(
			array(
				'key' => 'submission_id',
				'value' => $submission_id,
			)
		),
	);
	$reports = get_posts($args);
	if (!empty($reports)) {
		return $reports[0]->ID;
	}
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
		return null;
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
		'post_status' => 'any',
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

	if (!empty($submissions)) {
		foreach ($submissions as $submission){
			$total_score = get_post_meta($submission->ID, $post_meta, true) ?? 0;
			if (isset($total_score['sum'])) {
				$overall_scores[] = $total_score['sum'];
			}
		}
		if (!empty($overall_scores) && is_array($overall_scores)) {
			$result['sum_average'] = number_format(array_sum($overall_scores)/count($overall_scores), 1);
			$result['percent_average'] = round($result['sum_average']/272*100);
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
function cal_average_industry_score($industry_score_data=[])
{
	$total_industry_score = array();
	if (!empty($industry_score_data)) {
		foreach ($industry_score_data as $record) {
			$total_industry_score[] = $record['total_score'];
		}

		if (is_array($total_industry_score)) {
			$average = array_sum($total_industry_score) / count($total_industry_score);
			$average_percent = round($average/272*100);
			return $average_percent;
		}
		else {
			return false;
		}
	}
}


function set_org_data_to_all_submissions() {
    $args = array(
		'post_type' => 'submissions',
		'posts_per_page' => -1,
		'post_status' => 'any',
	);
	$all_submissions = get_posts($args);

	foreach ($all_submissions as $submission) {
		$user_id = get_post_meta($submission->ID, 'user_id', true);
		$organisation_id = get_post_meta($submission->ID, 'organisation_id', true);

		if (isset($user_id) && isset($organisation_id)) {
			$org_metadata = get_post_meta($submission->ID, 'org_data', true);

			if (empty($org_metadata)) {
				$sf_org_data = get_sf_organisation_data($user_id, $organisation_id);
    			update_post_meta($submission->ID, 'org_data', $sf_org_data);
			}
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



