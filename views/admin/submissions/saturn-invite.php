<?php 
/**
 * Template Saleforce Saturn Invite meta box
 *
 * Show Saturn Invite informations
 *
 * @author Tuan
 */

global $post;
$post_id = $post->ID;
$saturn_invites = array();

if (get_post_type($post_id) == 'assessments') {
    // Show Saturn Invite field
    if (isset($_GET['saturn_invite']) && $_GET['saturn_invite'] === 'show') {
        $assessment_id = $post->ID;
        $saturn_invites_meta = get_post_meta($assessment_id, 'sf_saturn_invites', true);
        if (!empty($saturn_invites_meta)) {
            foreach ($saturn_invites_meta as $record) {
                $record_arr = array($record);
                $organisation_id = $record['Organisation__c'] ?? '';
                $saturn_invites_org = custom_and_filter_saturn_invites_meta($record_arr, $organisation_id);
                foreach ($saturn_invites_org as $row) {
                    $saturn_invites[] = $row;
                }
            }
            // Sort the unique array by 'Org Name'
            usort($saturn_invites, function($a, $b) {
                return strcmp($a['Organisation_name'], $b['Organisation_name']);
            });
        }
    }
    // Hide Saturn Invite field
    else {
        echo '<style>#saturn-invite.postbox {display:none;}</style>';
        return;
    }
}
else {
    $assessment_id = get_post_meta($post_id, 'assessment_id', true);
    $organisation_id = get_post_meta($post_id, 'organisation_id', true);
    $saturn_invites_meta = get_post_meta($assessment_id, 'sf_saturn_invites', true);
    $saturn_invites = custom_and_filter_saturn_invites_meta($saturn_invites_meta, $organisation_id);
}
?>

<div id="salesforce-saturn-invite">
    <!-- Saturn Invite -->
    <h3>All Saturn Invites in this <?php echo (get_post_type($post_id) == 'assessments') ? 'Assessment' : 'Submission'; ?></h3>
    <?php if (!empty($saturn_invites)): ?>
        <table class="saturn-invites-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Opportunity</th>
                    <th>Saturn Product</th>
                    <th>Organisation Name</th>
                    <th>Contact</th>
                    <th>Contact Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
            <?php 
            foreach ($saturn_invites as $index => $record): ?>
                <tr>
                    <td class="index"><?php echo $index + 1; ?></td>
                    <td>
                        <a href="<?php echo $record['Invite_url'] ?>" target="_blank">
                            <?php echo $record['Name'] ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $record['Opportunity_url'] ?>" target="_blank">
                            <?php echo $record['Opportunity_name'] ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $record['Saturn_Product_url'] ?>" target="_blank">
                            <?php echo $record['Saturn_Product_name'] ?>
                        </a>
                    </td>
                    <td class="org-name">
                        <a href="<?php echo $record['Organisation_url'] ?>" target="_blank">
                            <?php echo $record['Organisation_name'] ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo $record['Contact_url'] ?>" target="_blank">
                            <?php echo $record['Contact_name'] ?>
                        </a>
                    </td>
                    <td>
                        <?php echo $record['Contact_Type__c'] ?>
                    </td>
                    <td><strong class="<?php echo $record['Status__c'] ?>">
                        <?php echo $record['Status__c'] ?></strong>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>
            No Saturn Invites found. <br>
            Please select the Saturn Products in the Assessment to update the Saturn Invites.
        </p>
    <?php endif; ?>
    <!-- /Saturn Invite -->
</div>
