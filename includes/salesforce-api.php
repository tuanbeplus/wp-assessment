<?php

function getSalesforceProduct2()
{
  	$sql = "SELECT Id, Name, ProductCode, Description, CreatedDate, Inclusion_Product_Type__c 
            FROM Product2 
            WHERE Inclusion_Product_Type__c='Index' 
            OR Inclusion_Product_Type__c='DCR' 
            ORDER BY CreatedDate DESC";

	$response = sf_query_object_metadata($sql);

	return $response;
}

function getOpportunityLineItem($opportunity_id)
{
  	$sql = "SELECT FIELDS(ALL) 
			FROM OpportunityLineItem 
			WHERE OpportunityId='".$opportunity_id."' 
			ORDER BY CreatedDate DESC 
			LIMIT 200";

	$response = sf_query_object_metadata($sql);

	return $response;
}

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

function get_submissions_completed($user_id, $assessment_id)
{
    $args = array(
		'post_type' => 'submissions',
		'posts_per_page' => -1,
		'post_status' => 'publish',
		'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => 'user_id',
                'value' => $user_id,
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
    
    return $submissions->posts;
}

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