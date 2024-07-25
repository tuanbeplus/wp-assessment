<?php
/**
 * Sync Saleforce SObject data by Webhook Outbound Message
 * 
 */
function wpa_get_salesforce_object_data_by_webhook() {
    // Handle event the Saturn Invite change
    if (isset($_GET['action']) && $_GET['action'] == 'saturn-invite-change') {

        // wp_remote_post('https://e2fab3f405955b6c7a8d9e2ec045efdf.m.pipedream.net', [
        //     'body' => file_get_contents('php://input'),
        // ]);

        $xml = file_get_contents('php://input');
        if(empty($xml)) return;
    
        $object_data = array();
        $result = new DOMDocument();
        $result->loadXML($xml);
    
        foreach(array(
            "Id",
            "Name",
            "Opportunity__c",
            "Saturn_Product_Id__c",
            "Saturn_Product__c",
            "Organisation__c",
            "Contact__c",
            "Contact_Type__c",
            "Status__c",
            ) as $key) {
            foreach($result->getElementsByTagNameNS("urn:sobject.enterprise.soap.sforce.com", $key) as $element) {
                if($element instanceof DOMElement) {
                    $object_data[$key] = $element->textContent;
                }
            }
        }

        // Update Saturn Invite data to Assessments
        wpa_update_saturn_invite_meta_assessment($object_data);
        
        // Return XML code
        wpa_sf_return_webhook();
    }
}
add_action( 'init', 'wpa_get_salesforce_object_data_by_webhook', 10 );

/**
 * Return Success message True to Saleforce with content XML
 * 
 * @return XML
 * 
 */
function wpa_sf_return_webhook() {
    echo '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/">
        <soapenv:Body>
        <notificationsResponse xmlns="http://soap.sforce.com/2005/09/outbound">
        <Ack>true</Ack>
        </notificationsResponse>
        </soapenv:Body>
        </soapenv:Envelope>';
    die;
}

/**
 * Update Saturn Invite meta to Assessment from Sobject data
 * 
 * @param array $sf_object_data     Saturn Invite Sobject
 * 
 */
function wpa_update_saturn_invite_meta_assessment($sf_object_data=array()) {
    ob_start();
    if (empty($sf_object_data)) return;
    $sf_product_id = $sf_object_data['Saturn_Product_Id__c'] ?? '';
    if (!isset($sf_product_id) || empty($sf_product_id)) return;
    
    // Get all assessment related to the Saturn Product 
    $assessments_list = wpa_get_assessments_from_product_meta($sf_product_id);
    if (!empty($assessments_list)) {
        foreach ($assessments_list as $assessment) {
            $assessment_id = $assessment->ID ?? '';
            $saturn_invites = get_post_meta($assessment_id, 'sf_saturn_invites', true);
            $new_saturn_invites = array();

            // Update existing post meta
            if (!empty($saturn_invites)) {
                // Update the specific record
                $new_saturn_invites = modify_record_saturn_invite_meta($saturn_invites, $sf_object_data);
            }
            // Add new post meta
            else {
                $new_saturn_invites[] = $sf_object_data;
            }

            // Update Saturn Invites data
	        $update_success = update_post_meta($assessment_id, 'sf_saturn_invites', $new_saturn_invites);

            // if ($update_success) {
            //     $saturn_invites = get_post_meta($assessment_id, 'sf_saturn_invites', true);
            //     $to = 'tom@ysnstudios.com';
            //     $subject = 'The meta Saturn Invite has been updated to assessment '. $assessment_id;
            //     $body = json_encode($saturn_invites);
            //     // Send Notification mail
            //     wp_mail($to, $subject, $body);
            // }
        }
    }
    ob_get_clean(); 
}

/**
 * Modify (update or add new) record of current Saturn Invite Assessment metadata
 * 
 * @param array $saturn_invites_meta     Saturn Invite Metadata
 * @param array $sf_invite_record        Record of Saturn Invite SObject from Webhook
 * 
 * @return Array New Saturn Invite data
 * 
 */
function modify_record_saturn_invite_meta($saturn_invites_meta, $sf_invite_record) {
    if (!empty($saturn_invites_meta) && !empty($sf_invite_record)) {
        // Update the existing record
        foreach ($saturn_invites_meta as &$record) {  // Use reference to update the actual array element
            if ($record['Id'] == $sf_invite_record['Id']) {
                $record['Name']                 = $sf_invite_record['Name'] ?? '';
                $record['Opportunity__c']       = $sf_invite_record['Opportunity__c'] ?? '';
                $record['Saturn_Product_Id__c'] = $sf_invite_record['Saturn_Product_Id__c'] ?? '';
                $record['Saturn_Product__c']    = $sf_invite_record['Saturn_Product__c'] ?? '';
                $record['Organisation__c']      = $sf_invite_record['Organisation__c'] ?? '';
                $record['Contact__c']           = $sf_invite_record['Contact__c'] ?? '';
                $record['Contact_Type__c']      = $sf_invite_record['Contact_Type__c'] ?? '';
                $record['Status__c']            = $sf_invite_record['Status__c'] ?? '';

                return $saturn_invites_meta;
            }
        }
        // If Id is not found, add the new record
        $saturn_invites_meta[] = $sf_invite_record;

        return $saturn_invites_meta;
    }
    // If the original array is empty, simply add the new record
    if (empty($saturn_invites_meta) && !empty($sf_invite_record)) {
        $saturn_invites_meta[] = $sf_invite_record;
        return $saturn_invites_meta;
    }
    // Return the original array if no modifications are made
    return $saturn_invites_meta;
}

/**
 * Get all assessments related to the Saturn Product
 * 
 * @param string $sf_product_id     Saturn Product ID
 * 
 * @return Object Assessments List
 * 
 */
function wpa_get_assessments_from_product_meta($sf_product_id) {
    $args = array(
        'post_type'   => 'assessments',
        'numberposts' => -1,
        'post_status' => 'any',
        'meta_query'  => array(
            array(
                'key'     => 'related_sf_products',
                'value'   => '"' . $sf_product_id . '"',
                'compare' => 'LIKE'
            )
        ),
    );
    // Retrieve the posts
    $assessments = get_posts($args);
    // Reset Post data
    wp_reset_postdata();

    return $assessments;
}



