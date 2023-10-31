<?php 
global $post;
$attachment_id = $post->ID;
$sf_user_id = get_post_meta( $attachment_id, 'sf_user_id', true );
$sf_user_name = get_post_meta( $attachment_id, 'sf_user_name', true );
$assessment_id = get_post_meta( $attachment_id, 'assessment_id', true );
$organisation_id = get_post_meta( $attachment_id, 'organisation_id', true );
?>
<div class="attachment-uploader-info">
    <div class="sf-user-id field">
        <label for="sf-user-id">User ID</label>
        <input type="text" name="sf_user_id" id="sf-user-id" value="<?php echo $sf_user_id; ?>">
    </div>
    <div class="sf-user-name field">
        <label for="sf-user-name">User Name</label>
        <input type="text" name="sf_user_name" id="sf-user-name" value="<?php echo $sf_user_name; ?>">
    </div>
    <div class="assessment-id field">
        <label for="assessment-id">Assessment ID</label>
        <input type="text" name="assessment_id" id="assessment-id" value="<?php echo $assessment_id; ?>">
    </div>
    <div class="organisation-id field">
        <label for="organisation-id">Organisation ID</label>
        <input type="text" name="organisation_id" id="organisation-id" value="<?php echo $organisation_id; ?>">
    </div>
</div>