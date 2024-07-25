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

function getOrgFromId($org_id)
{
	$response = sf_get_object_metadata('Account', $org_id);
	return $response;
}

/**
 * Get Salesforce Object data
 * 
 * @param $object_name		SObject Name
 * @param $record_id		Record ID
 * 
 * @return array SObject data
 * 
 */
function getSalesforceObjectData($object_name, $record_id)
{
	$response = sf_get_object_metadata($object_name, $record_id);

	if (isset($response)) {
		$records = json_encode($response);
		$records = json_decode($records, true);
		return $records;
	}
	else {
		return false;
	}
}

function getAllOrgMembers()
{
  	$sql = "SELECT Id, Name, Type 
			FROM Account 
			WHERE Type='Member'
			ORDER BY Name ASC";

	$response = sf_query_object_metadata($sql);

	if (isset($response->records)) {
		return $response->records;
	}
	else {
		return null;
	}
}

function getUsersFromOrgId($organisation_id)
{
	$sql = "SELECT Id, Name, Email, ContactId, AccountId
			FROM User
			WHERE AccountId='{$organisation_id}'
			ORDER BY Name ASC";

	$response = sf_query_object_metadata($sql);

	if (isset($response->records)) {
		return $response->records;
	}
	else {
		return null;
	}
}

function getAllUsersFromOrgMember()
{
	$all_org_members = getAllOrgMembers();
	$user_members = array();
	$count = 0;

	foreach ($all_org_members as $org) {

		$users = getUsersFromOrgId($org->Id);

		if (!empty($users)) {
			foreach ($users as $user) {
				$user_arr = json_decode(json_encode($user), true);
				$user_arr['OrgName'] = $org->Name;
				$user_members[] = $user_arr;
			}
		}
	}
	usort($user_members, function ($a, $b) {
		return strcmp($a["Name"], $b["Name"]);
	});

	return $user_members;
}

function getUserFromEmail($email) 
{
	$sql = "SELECT Id, Name, Email, ContactId, AccountId
			FROM User
			WHERE Email='{$email}'
			ORDER BY Name ASC";

	$response = sf_query_object_metadata($sql);

	if (isset($response->records)) {
		return $response->records;
	}
	else {
		return null;
	}
}

function getUserById($user_id)
{
	$response = sf_get_object_metadata('User', $user_id);
	return $response;
}


/**
 * Update the Record SObject Salesforce
 * 
 * @param $sobject_name		SObject Name
 * @param $record_id		Record ID
 * @param $data				Array data
 * 
 * @return boolean True/False
 * 
 */
function update_record_sobject_salesforce($sobject_name, $record_id, $data = array()) {

    if (empty($sobject_name) || empty($record_id)) return;
    if (!is_array($data) || empty($data)) return;

    $update_data = json_encode($data);
    $sf_access_token = get_field('salesforce_api_access_token', 'option');
	$sf_endpoint_url = get_field('salesforce_endpoint_url', 'option');
    $curl = curl_init();

    curl_setopt_array($curl, array(
		CURLOPT_URL => $sf_endpoint_url .'/services/data/v56.0/sobjects/'. $sobject_name .'/'. $record_id,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'PATCH',
		CURLOPT_POSTFIELDS => $update_data,
		CURLOPT_HTTPHEADER => array(
			'Authorization: Bearer ' . $sf_access_token,
			'Content-Type: application/json',
		),
    ));
    $response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

	if ($status == 204) {
		return true;
	}
	else {
		return false;
	}
}

/**
 * Get Salesforce Saturn Invite records 
 * 
 * @param string $sf_product2_id	Salesforce Product2 ID
 * 
 * @return array Saturn Invite records
 * 
 */
function get_saturn_invite_from_product($sf_product2_id) {

	if (empty($sf_product2_id)) return;

	$sql = "SELECT Id, Name, Status__c, Opportunity__c, Organisation__c, Contact__c, 
				Contact_Type__c, Saturn_Product__c, Saturn_Product_Id__c
			FROM Saturn_Invite__c
			WHERE Saturn_Product_Id__c='{$sf_product2_id}'
			";

	$response = sf_query_object_metadata($sql);

	if (isset($response) && isset($response->records)) {
		$records = json_encode($response->records);
		$records = json_decode($records, true);
		return $records;
	}
	else {
		return false;
	}
}

/**
 * Get Salesforce Saturn Invite Contacts list 
 * 
 * @param string $sf_product2_id	Salesforce Product2 ID
 * 
 * @return array Salesforce Contacts ID list
 * 
 */
function get_saturn_invited_contacts_list($sf_product2_id) {

	$saturn_invites = get_saturn_invite_from_product($sf_product2_id);
	$contacts_list = array();

	if (!empty($saturn_invites)) {
		foreach ($saturn_invites as $record) {
			$contact_id = $record['Contact__c'] ?? '';
			$status = $record['Status__c'] ?? '';
			if (isset($contact_id) && !empty($contact_id) && $status == 'Active') {
				$contacts_list[] = $contact_id;
			}
		}
	}
	return $contacts_list;
}

/**
 * Save Salesforce Saturn Invite metadata to Assessment
 * 
 * @param int|string $post_id		Assessment ID
 * @param array $sf_products_list	Salesforce Product2 ID
 * 
 */
function save_saturn_invite_meta_assessment($post_id, $sf_products_list) {

	$all_saturn_invites = array();
	$all_saturn_invited_contacts = array();

	if (is_array($sf_products_list) && !empty($sf_products_list)) {
		foreach ($sf_products_list as $sf_product_id) {
		
			$saturn_invites = get_saturn_invite_from_product($sf_product_id);
			if (!empty($saturn_invites)) {
				// Push Saturn Invites data
				foreach ($saturn_invites as $record) {
					array_push($all_saturn_invites, $record);
				}
			}
			$saturn_invited_contacts = get_saturn_invited_contacts_list($sf_product_id);
			if (!empty($saturn_invited_contacts)) {
				// Push Saturn Invites Contacts list
				foreach ($saturn_invited_contacts as $contact_id) {
					array_push($all_saturn_invited_contacts, $contact_id);
				}
			}
		}
	}
	// Update Saturn Invites data
	update_post_meta($post_id, 'sf_saturn_invites', $all_saturn_invites);
}

/**
 * Customize the Saturn Invites Metadata and Sort by Org name
 * 
 * @param array  $saturn_invites_meta Saturn Invites assessment meta
 * @param string $submission_org_id   Organisation ID of the Submission
 * 
 * @return array New array of Saturn Invites
 */
function custom_and_filter_saturn_invites_meta($saturn_invites_meta, $submission_org_id) {
    // Return empty array if input is empty
    if (empty($saturn_invites_meta) || empty($submission_org_id)) {
        return array();
    }
    $sf_endpoint_url = get_field('salesforce_endpoint_url', 'option');
    $final_saturn_invites = array();

    foreach ($saturn_invites_meta as $record) {
        // Get Organisation ID of the record
        $org_id = $record['Organisation__c'] ?? '';

        // Filter the Invites from Organisation of the Submission
        if ($org_id === $submission_org_id) {
            $invite_id 		= $record['Id'] ?? '';
            $opportunity_id = $record['Opportunity__c'] ?? '';
            $product_id 	= $record['Saturn_Product_Id__c'] ?? '';
            $contact_id 	= $record['Contact__c'] ?? '';

            // Retrieve additional data
			$org 		 = getSalesforceObjectData('Account', $org_id) ?? array();
            $product 	 = getSalesforceObjectData('Product2', $product_id) ?? array();
            $contact 	 = getSalesforceObjectData('Contact', $contact_id) ?? array();
			$opportunity = getSalesforceObjectData('Opportunity', $opportunity_id) ?? array();
            
            // Add data to new array
            $final_saturn_invites[] = array(
                'Id' 					=> $invite_id,
                'Name' 				 	=> $record['Name'] ?? '',
                'Status__c' 		  	=> $record['Status__c'] ?? '',
                'Invite_url' 		  	=> "$sf_endpoint_url/lightning/r/Saturn_Invite__c/$invite_id/view",
                'Opportunity__c' 	  	=> $opportunity_id,
                'Opportunity_name' 		=> $opportunity['Name'] ?? '',
                'Opportunity_url' 		=> "$sf_endpoint_url/lightning/r/Opportunity/$opportunity_id/view",
                'Saturn_Product_Id__c' 	=> $product_id,
                'Saturn_Product_name' 	=> $product['Name'] ?? '',
                'Saturn_Product_url' 	=> "$sf_endpoint_url/lightning/r/Product2/$product_id/view",
                'Organisation__c' 		=> $org_id,
                'Organisation_name' 	=> $org['Name'] ?? '',
                'Organisation_url'		=> "$sf_endpoint_url/lightning/r/Account/$org_id/view",
                'Contact__c' 			=> $contact_id,
                'Contact_name' 			=> $contact['Name'] ?? '',
                'Contact_Type__c' 		=> $record['Contact_Type__c'] ?? '',
                'Contact_url' 			=> "$sf_endpoint_url/lightning/r/Contact/$contact_id/view"
            );
        }
    }
    return $final_saturn_invites;
}