<?php 
$wp_user_id = $_GET['user_id'] ?? get_current_user_id();
$user_id = get_user_meta($wp_user_id, '__salesforce_user_id', true);
$organisation_id = get_user_meta($wp_user_id, '__salesforce_account_id', true);
$_COOKIE['userId'] = $user_id;
$index_terms = array('self-assessed','index');
$dcr_terms = array('dcr');

$index_accessible_list = get_assessments_accessible_members($user_id, $organisation_id, $index_terms);
$dcr_accessible_list = get_assessments_accessible_members($user_id, $organisation_id, $dcr_terms); 
?>

<h2>Assessments</h2>
<div class="assessments-purchased form-table">
    <div class="assessments-list">
        <?php if ($index_accessible_list): ?>
            <div class="index-list _list">
                <h3 class="list-heading">Index</h3>
                <span class="hepler-text">Assessments in Index section on Dashboard.</span>
                <ul>
                    <?php foreach ($index_accessible_list as $index_id): ?>
                        <li>
                            <a href="<?php echo get_the_permalink($index_id); ?>" target="_blank">
                                <?php echo get_the_title($index_id); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($dcr_accessible_list): ?>
            <div class="dcr-list _list">
                <h3 class="list-heading">DCR</h3>
                <span class="hepler-text">Assessments in DCR section on Dashboard.</span>
                <ul>
                    <?php foreach ($dcr_accessible_list as $dcr_id): ?>
                        <li>
                            <a href="<?php echo get_the_permalink($dcr_id); ?>" target="_blank">
                                <?php echo get_the_title($dcr_id); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>