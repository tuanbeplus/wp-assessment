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

function updateSobjectRecord($obj_name, $record_id, $content = []) 
{
	$sf_access_token = get_field('salesforce_api_access_token', 'option');
	$sf_endpoint_url = get_field('salesforce_endpoint_url', 'option');
	$sf_api_ver = get_field('salesforce_api_version', 'option');

	$url = "$sf_endpoint_url/services/data/$sf_api_ver/sobjects/$obj_name/$record_id";

   	$content_json = json_encode($content);

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_HEADER, false);
	curl_setopt($curl, CURLOPT_HTTPHEADER,
		array("Authorization: OAuth $sf_access_token",
			"Content-type: application/json"));
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PATCH");
	curl_setopt($curl, CURLOPT_POSTFIELDS, $content_json);

	$response = curl_exec($curl);
	$status = curl_getinfo($curl, CURLINFO_HTTP_CODE);

	if ($status != 204) {
		echo "Error: call to URL $url failed with status $status, curl_error " . curl_error($curl) . ", curl_errno " . curl_errno($curl);
	}
	if ($status == 204) {
		echo "Update record successful.";
	}

	curl_close($curl);
}







