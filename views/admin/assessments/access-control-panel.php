<?php 
/**
 * Template Access control panel meta box
 *
 * Control the members access to Assessments Saturn
 *
 * @author Tuan
 */
global $post;
$all_org_members = getAllOrgMembers();
$all_users = get_option('salesforce_members_data');
$assigned_members = get_post_meta( $post->ID, 'assigned_members', true);
$assigned_member_ids = array();

foreach ($assigned_members as $member) {
    $assigned_member_ids[] = $member['id'];
}
?>

<div class="access-control-panel-wrapper">
    <!-- Member Option -->
    <div class="member-options">
        <div class="field-refresh">
            <label>Refresh Members Data</label>
            <span id="btn-refresh-members" class="button button-medium">
                <span class="icon-refresh"><i class="fa-solid fa-rotate-right"></i></span>
                Refresh
            </span>
        </div>
        <div class="field-select">
            <label for="select-org">Select Organisation</label>
            <select id="select-org" name="selected_org">
            <?php if (!empty($all_org_members)): ?>
                <?php foreach ($all_org_members as $org_member):?>
                    <option value="<?php echo $org_member->Id; ?>"><?php echo $org_member->Name; ?></option>
                <?php endforeach; ?>
            <?php endif; ?>
            </select>
        </div>
        <div class="field-select2">
            <label for="products-selected-area">Select Members</label>
            <!-- List items selected -->
            <ul id="members-selected-area" class="list-items-selected-area">
                <input type="search" id="search-members" class="search-item" placeholder="Enter member name" autocomplete="off">              
            </ul>
            <!-- /List items selected -->

            <!-- List items dropdown -->
            <ul id="list-members-dropdown" class="list-items-dropdown" style="display: none;">
            <?php if (!empty($all_users)): ?>
                <?php foreach ($all_users as $user): ?>
                    <li class="item member <?php if(in_array($user['Id'], $assigned_member_ids)) echo 'selected'; ?>" 
                        data-id="<?php echo $user['Id']; ?>"
                        data-org-name="<?php echo $user['OrgName']; ?>">
                        <?php echo $user['Name']; ?>                 
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
            </ul>
            <!-- /List items dropdown -->
        </div>
    </div>
    <!-- /Member Option -->

    <!-- Assigned Members -->
    <div class="assigned-members">
        <h3>Assigned Members</h3>
        <ul id="assigned-members-list" class="assigned-members-list">
        <?php if (!empty($assigned_members)): ?>
            <?php foreach ($assigned_members as $key => $member): ?>
                <li class="member-item" data-id="<?php echo $member['id']; ?>">
                    <span>
                        <i class="fa-solid fa-user"></i>
                        <span class="member-name">
                            <?php echo $member['name']; ?> - <?php echo $member['org']; ?>
                        </span>
                    </span>
                    <span class="icon-delete-member"><i class="fa-regular fa-circle-xmark"></i></span>
                    <input type="hidden" name="assigned_members[<?php echo $key; ?>][id]" value="<?php echo $member['id']; ?>">
                    <input type="hidden" name="assigned_members[<?php echo $key; ?>][name]" value="<?php echo trim($member['name']); ?>">
                    <input type="hidden" name="assigned_members[<?php echo $key; ?>][org]" value="<?php echo trim($member['org']); ?>">
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
        </ul>
    </div>
    <!-- /Assigned Members -->
</div>