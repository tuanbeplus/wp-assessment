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
    $args = array(
		'post_type' => 'submissions',
		'posts_per_page' => 1,
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
	$submissions = new WP_Query($args);
	wp_reset_postdata();

	if (!empty($submissions->posts)) {
		$submission_id = $submissions->posts[0]->ID;
		$submission_status = get_post_meta($submission_id, 'assessment_status', true);

		if ($submission_status != 'rejected') {
			return $submissions->posts;
		}
	}
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
	$terms_arr = $main->get_assessment_terms($assessment_id);
	$is_all_users_can_access = get_post_meta($assessment_id, 'is_all_users_can_access', true);
	$related_sf_products = get_post_meta($assessment_id, 'related_sf_products', true);
	
	$assigned_members = get_post_meta( $assessment_id, 'assigned_members', true);
	$invited_members = get_post_meta( $assessment_id, 'invited_members', true);
	$assigned_member_ids = array();
	foreach ($assigned_members as $member) {
		$assigned_member_ids[] = $member['id'];
	}

	// $sf_product_id_opp = getProductIdByOpportunity();
	// $drc_product_id = isset($sf_product_id_opp['dcr_product_id']) ? $sf_product_id_opp['dcr_product_id'] : null;
	// $index_product_id = isset($sf_product_id_opp['index_product_id']) ? $sf_product_id_opp['index_product_id'] : null;
	

	// check user access to asessment
	// if ($drc_product_id && !empty($related_sf_products)) {
	//     if (in_array('dcr', $terms_arr) && in_array($drc_product_id, $related_sf_products)) {
	//         $is_user_can_access = true;
	//     }
	// }
	// if ($index_product_id && !empty($related_sf_products)) {
	//     if (in_array('index', $terms_arr) && in_array($index_product_id, $related_sf_products)) {
	//         $is_user_can_access = true;
	//     }
	// }

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
function array_sum_submission_score($score_array=[]) 
{
	$total_score = array();
	if (is_array($score_array) && !empty($score_array)) {
		foreach ($score_array as $i => $group) {
			foreach ($group as $j => $quiz) {
				$total_score[] = $quiz;
			}
		}
	}
	$result = array_sum($total_score) ?? 0;
	return $result;
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
function is_report_of_submission_exist($submission_id) {
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
	return $reports[0]->ID;
}