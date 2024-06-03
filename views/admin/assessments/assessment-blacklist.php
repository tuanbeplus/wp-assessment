<?php 
/**
 * Template Assessment Blacklist meta box
 *
 * Function restrict users access to Index & DCR
 *
 * @author Tuan
 */

global $post;
$blacklist_emails = get_post_meta($post->ID, 'blacklist_emails', true);
?>
<div id="assessment-blacklist">
    <div class="assessment-blacklist-wrapper">
        <form class="form-blacklist" action="false">
            <label for="blacklist-emails-area">Add emails to Blacklist to stop users access the assessment</label>
            <textarea id="blacklist-emails-area"  
                    rows="2" placeholder="Seperate each email by a comma."></textarea>
            <a id="btn-add-emails-to-blacklist" role="button" 
                tabindex="0"
                class="button button-primary button-large">
                Add emails
            </a>
        </form>
        <p class="bl-message-error"></p>
        <p class="bl-message-success"></p>
        <h3>Blacklist:</h3>
        <ul id="blacklist">
        <?php if (isset($blacklist_emails) && !empty($blacklist_emails)): ?>
            <?php foreach ($blacklist_emails as $email): ?>
                <li class="blacklist-item" tabindex="0">
                    <span class="text"><?php echo $email ?></span>
                    <span class="btn-remove-blacklist-item" tabindex="0" role="button">
                        <i class="fa-solid fa-xmark"></i>
                    </span>
                    <input type="hidden" name="blacklist_emails[]" value="<?php echo $email ?>">
                    <div class="confirm-remove">
                        Do you want to remove this email? 
                        <a class="btn-confirmed-remove" tabindex="0" role="button">Remove</a>
                        <a class="btn-not-remove" tabindex="0" role="button">No</a>
                    </div>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
        </ul>
    </div>
</div>