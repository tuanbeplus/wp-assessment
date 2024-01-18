<?php
global $post;
$report_id = $post->ID;
$org_data = get_post_meta($report_id, 'org_data', true);
$id_org = !empty($org_data) ? $org_data['Id'] : '';

$users = get_users(array(
    'meta_key' => '__salesforce_account_id',
    'meta_value' => $id_org
));

?>
<style media="screen">
  .report-message{
    margin-bottom: 10px;
  }
  .report-message .success{
    color: green;
    font-weight: 600;
  }
  .report-message .error{
    color: red;
    font-weight: 600;
  }
  .select2-container .select2-search--inline .select2-search__field {
    padding: 0 6px;
  }
</style>
<div id="report-dashboard-share">
   <p>
     <select name="share_users[]" class="select-users-report" multiple="multiple">
        <?php foreach ($users as $user) {
            ?><option value="<?php echo $user->ID ?>"><?php echo $user->display_name ?></option><?php
        } ?>
     </select>
   </p>
   <p>
    <div class="report-message"></div>
     <input type="hidden" name="post_share" value="<?php echo $report_id; ?>">
     <a href="javascript:;" class="button button-primary button-large btn-share-report">Send</a>
   </p>
</div>
