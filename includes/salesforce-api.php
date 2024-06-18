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
 * @return Boolean True/False
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

