<?php 
/**
 * Template Scoring formula options meta box
 *
 * Option select Formula for Index scoring
 *
 * @author Tuan
 */

global $post;
$selected_formula = get_post_meta($post->ID, 'scoring_formula', true);
?>
<div class="formula-options-wrapper">
    <label for="scoring-formula-select">
        Select the formula to apply to Index scoring.
    </label>
    <select name="scoring_formula" id="scoring-formula-select">
        <option value="">Select the formula</option>
        <option value="index_formula_2023" <?php selected($selected_formula, 'index_formula_2023'); ?>>
            Index Formula 2023
        </option>
        <option value="index_formula_2024" <?php selected($selected_formula, 'index_formula_2024'); ?>>
            Index Formula 2024
        </option>
    </select>
</div>