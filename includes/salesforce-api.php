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
