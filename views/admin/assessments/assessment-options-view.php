<?php 
global $post;
$post_id = $post->ID;
$is_required_answer_all = get_post_meta($post_id, 'is_required_answer_all', true);
$is_required_document_all = get_post_meta($post_id, 'is_required_document_all', true);
$is_invite_colleagues = get_post_meta($post_id, 'is_invite_colleagues', true);
$is_all_users_can_access = get_post_meta($post_id, 'is_all_users_can_access', true);
$sf_products_assessment = getSalesforceProduct2();
$related_sf_products = get_post_meta($post_id, 'related_sf_products', true);
?>

<div class="assessment-options-container">
    <div class="assessment-required field-checkbox">
        <label for="assessment-required">
            <input <?php if ($is_required_answer_all == true) echo 'checked'; ?>
                    type="checkbox" name="is_required_answer_all" 
                    id="assessment-required" value="1">
            Required to answer and comment on all questions.
        </label>
    </div>
    <div class="documents-required field-checkbox">
        <label for="document-required">
            <input <?php if ($is_required_document_all == true) echo 'checked'; ?>
                    type="checkbox" name="is_required_document_all" 
                    id="document-required" value="1">
            Required to provide document on all questions.
        </label>
    </div>
    <!-- <div class="invite-colleagues field-checkbox">
        <label for="invite-colleagues">
            <input  if ($is_invite_colleagues == true) echo 'checked'; 
                    type="checkbox" name="is_invite_colleagues" 
                    id="invite-colleagues" value="1">
            Show invite colleagues area.
        </label>
    </div> -->
    <div class="accessible-option field-checkbox">
        <label for="accessible">
            <input <?php if ($is_all_users_can_access == true) echo 'checked'; ?>
                    type="checkbox" name="is_all_users_can_access" 
                    id="accessible" value="1">
            All logged in users have access to this assessment.
        </label>
    </div>
    
    <!-- Related Salesforce Products -->
    <div class="related-sf-products field-select2">
        <label for="products-selected-area">Related to Salesforce products</label>
        <!-- List items selected -->
        <ul id="products-selected-area" class="list-items-selected-area">
            <?php if (! empty($related_sf_products)): ?>
                <?php foreach ($sf_products_assessment->records as $product): ?>
                    <?php if (in_array($product->Id, $related_sf_products)): ?>
                        <li class="item-selected products-selected" data-id="<?php echo $product->Id; ?>">
                            <?php echo $product->Name; ?>
                            <input type="hidden" name="related_sf_products[]" value="<?php echo $product->Id; ?>">
                            <span class="remove-item"><i class="fa-solid fa-xmark"></i></span>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>
        <!-- /List items selected -->
        <?php if (isset($sf_products_assessment->records)): ?>
            <!-- List items dropdown -->
            <ul class="list-items-dropdown sf-products-list">
                <?php foreach ($sf_products_assessment->records as $product): ?>
                    <?php 
                        if (is_array($related_sf_products)) {
                            $selected_class = '';
                            if (in_array($product->Id, $related_sf_products)) {
                                $selected_class = 'selected';
                            }
                        }
                    ?>
                    <li class="item product <?php echo $selected_class; ?>" data-id="<?php echo $product->Id; ?>">
                        <?php echo $product->Name; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <!-- /List items dropdown -->
        <?php else: ?>
            <ul class="list-items-dropdown sf-products-list">
                No products found
            </ul>
        <?php endif; ?>
    </div>
    <!-- /Related Salesforce Products -->
</div>
