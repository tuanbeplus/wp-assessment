<?php 
$wp_user_id = $_GET['user_id'] ?? get_current_user_id();
$user_id = get_user_meta($wp_user_id, '__salesforce_user_id', true);
$organisation_id = get_user_meta($wp_user_id, '__salesforce_account_id', true);
$_COOKIE['userId'] = $user_id;

$assessments_for_all_users = get_assessments_accessible_all_users();
$saturn_assessments_list = get_assessments_related_saturn_products();
$merged_assessments = array_unique(array_merge($assessments_for_all_users, $saturn_assessments_list));
$assessments_accessible_list = get_assessments_on_dashboard($user_id, $account_id, $merged_assessments);
?>

<h2>Saturn Assessments</h2>
<div class="assessments-purchased form-table">
    <div class="assessments-list">
        <?php if (!empty($assessments_accessible_list)): ?>
            <div class="index-list _list">
                <h3 class="list-heading">Index</h3>
                <span class="hepler-text">Assessments in Index section on Dashboard.</span>
                <ul>
                <?php foreach ($assessments_accessible_list as $assessment_id): 
                    $terms_arr = get_assessment_terms($assessment_id);
                    if (in_array('self-assessed', $terms_arr) || in_array('index', $terms_arr)):
                    ?>
                        <li>
                            <a href="<?php echo get_the_permalink($assessment_id); ?>" target="_blank">
                                <?php echo get_the_title($assessment_id); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if (!empty($assessments_accessible_list)): ?>
            <div class="dcr-list _list">
                <h3 class="list-heading">DCR</h3>
                <span class="hepler-text">Assessments in DCR section on Dashboard.</span>
                <ul>
                <?php foreach ($assessments_accessible_list as $assessment_id): 
                    $terms_arr = get_assessment_terms($assessment_id);
                    if (in_array('dcr', $terms_arr)):
                    ?>
                        <li>
                            <a href="<?php echo get_the_permalink($assessment_id); ?>" target="_blank">
                                <?php echo get_the_title($assessment_id); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>