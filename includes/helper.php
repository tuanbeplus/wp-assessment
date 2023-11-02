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
function get_assessments_accessible_all_users($arr_terms)
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

		if (empty($submission_completed)) {
			$assessments_arr[] = $assessment->ID;
		}
	}
	wp_reset_postdata();
    
    return $assessments_arr;
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