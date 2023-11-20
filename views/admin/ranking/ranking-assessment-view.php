<?php
$ass_options = array();

// Get all Assessments
$args = array(
  'numberposts' => -1, 
  'post_status' => 'publish', 
  'post_type' => 'assessments',
);
$assessments = get_posts( $args );
foreach ( $assessments as $ass ) {
    $ass_options[] = array( 'id' => $ass->ID, 'text' => $ass->post_title );
}
?>
<select id="and-ranking-assessment">
  <option value=""></option>
  <?php
  foreach ( $ass_options as $ass ) {
    echo '<option value="'.$ass->ID.'">'.$ass->post_title.'</option>';
  }
  ?>
</select>
