<?php 
$user_id = get_user_meta($_GET['user_id'], '__salesforce_user_id', true);
$_COOKIE['userId'] = $user_id;

$assessments_accessible_all_users = get_assessments_accessible_all_users(); 
$sf_product_id_opp = getProductIdByOpportunity();
$drc_product_id = isset($sf_product_id_opp['dcr_product_id']) ? $sf_product_id_opp['dcr_product_id'] : null;
$index_product_id = isset($sf_product_id_opp['index_product_id']) ? $sf_product_id_opp['index_product_id'] : null;

$dcr_assessments_list = get_assessments_related_sf_products($drc_product_id, 'dcr') ?? null;
$index_assessments_list = get_assessments_related_sf_products($index_product_id, 'index') ?? null;
?>

<h2>Assessments</h2>
<div class="assessments-purchased form-table">
    <div class="assessments-list">
        <?php if ($assessments_accessible_all_users || $index_assessments_list): ?>
            <div class="index-list _list">
                <h3 class="list-heading">Index</h3>
                <span class="hepler-text">Assessments in Index section on Dashboard.</span>
                <ul>
                    <?php foreach ($assessments_accessible_all_users as $assessment_id): ?>
                        <li>
                            <a href="<?php echo get_the_permalink($assessment_id); ?>" target="_blank">
                                <?php echo get_the_title($assessment_id); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>

                    <?php foreach ($index_assessments_list as $index): ?>
                        <li>
                            <a href="<?php echo get_the_permalink($index); ?>" target="_blank">
                                <?php echo get_the_title($index); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($dcr_assessments_list): ?>
            <div class="dcr-list _list">
                <h3 class="list-heading">DCR</h3>
                <span class="hepler-text">Assessments in DCR section on Dashboard.</span>
                <ul>
                    <?php foreach ($dcr_assessments_list as $dcr): ?>
                        <li>
                            <a href="<?php echo get_the_permalink($dcr); ?>" target="_blank">
                                <?php echo get_the_title($dcr); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>