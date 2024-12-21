<?php
global $post;
$report_id = $post->ID;
$org_data = get_post_meta($report_id, 'org_data', true);
$id_org = !empty($org_data) ? sanitize_text_field($org_data['Id']) : '';

// $users = get_users(array(
//     'meta_key' => '__salesforce_account_id',
//     'meta_value' => $id_org,
// ));

$users = get_users();

?>
<div id="report-share">
    <select name="share_users[]" class="select-users-report" multiple="multiple">
    <?php foreach ($users as $user): ?>
        <option value="<?php echo esc_attr($user->ID) ?>"><?php echo esc_html($user->display_name) ?></option>
    <?php endforeach; ?>
    </select>
    <div class="report-message"></div>
    <input type="hidden" name="post_share" value="<?php echo $report_id; ?>">
    <a href="javascript:;" class="button button-primary button-large btn-share-report">Send</a>
</div>
