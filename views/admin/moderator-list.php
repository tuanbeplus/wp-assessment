<?php
global $post;

$args = array(
    'role'    => 'moderator',
    'order'   => 'ASC'
);
$moderators = get_users($args);
$selected_moderator = get_post_meta($post->ID, 'assigned_moderator', true);

$collaborator_roles = array('moderator', 'administrator');

$args_collab = array(
    'role__in'    => $collaborator_roles,
    'order'   => 'ASC',
    'exclude' => $selected_moderator,
);
$collaborators = get_users($args_collab);
$selected_collaborators = get_post_meta($post->ID, 'assigned_collaborator', true);
$is_assessment_completed = get_post_meta($post->ID, 'is_assessment_completed', true);
?>

<div class="assigned-moderator-wrapper">
    <div class="moderator-box">
        <p class="_label">Moderator</p>
        <select name="assigned_moderator" id="assigned_moderator" class="assigned-moderator-select-list">
            <option></option>
            <?php foreach ($moderators as $moderator) :
                $id = $moderator->ID;
            ?>
                <option value="<?php echo esc_attr($id); ?>" <?php selected($selected_moderator, esc_attr($id)); ?>>
                    <?php echo esc_html($moderator->display_name); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="collaborator-box">
        <p class="_label">Collaborator</p>
        <div id="collaborator-selected-list" data-text="Select Collaborator">
            <?php if( empty($selected_collaborators) ): ?>
                <label class="_placeholder">+ Add Collaborator</label>
            <?php  else: ?>
                <?php foreach ($selected_collaborators as $selected_collab_id) : 
                    $collab_obj = get_user_by('id', $selected_collab_id);
                    ?>
                    <li class="selected-collab-item" data-id="<?php echo esc_attr($selected_collab_id); ?>">
                        <label for="input-hiden"><?php echo esc_html($collab_obj->display_name); ?></label>
                        <input id="input-hiden" type="hidden" name="assigned_collaborator[]" value="<?php echo esc_attr($selected_collab_id); ?>">
                        <span class="remove-collab"><i class="fa-solid fa-xmark"></i></span>
                    </li>
                <?php endforeach; ?>
            <?php  endif; ?>
        </div>
        <div class="send-invite-wrapper">
            <span id="btn-send-invite" class="button button-primary">Send invite</span>
        </div>
        <ul id="assigned_collaborator" class="collaborator-select-list">
            <?php foreach ($collaborators as $collaborator) :
                $collab_id = $collaborator->ID;
            ?>
                <li id="collaborator_<?php echo esc_attr($collab_id); ?>" class="collaborator-item" data-id="<?php echo esc_attr($collab_id); ?>">
                    <?php echo esc_html($collaborator->display_name); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="assessment-completed-box">
        <p class="_label">All questions of assessment are completed, <br>mark this assessment as completed and lock all questions</p>
        <label for="is_assessment_completed">
            <input <?php if ($is_assessment_completed == true) echo 'checked'; ?>
                    type="checkbox" id="is_assessment_completed" 
                    name="is_assessment_completed" value="1">
            Tick as Completed
        </label>
    </div>

    <input id="assessment_id" type="hidden" name="assessment_id" value="<?php echo $post->ID; ?>" />
    
</div>

