<?php
global $post;

$wp_ass = new WP_Assessment();

$assessment_id = get_field('assessment', $post->ID);

$by_total_score = get_field('position_by_total_score', $post->ID);
$ranking_by_total_score = $wp_ass->wpa_unserialize_metadata($by_total_score);

$by_industry = get_field('position_by_industry', $post->ID);
$ranking_by_industry = $wp_ass->wpa_unserialize_metadata($by_industry);

$by_framework = get_field('position_by_framework', $post->ID);
$ranking_by_framework = $wp_ass->wpa_unserialize_metadata($by_framework);

$and_ranking = new AndAssessmentRanking();
$ranking_by_key_areas = $and_ranking->ranking_orgs_group_question($assessment_id);
?>

<div class="container">
  <?php 
  if ( ! $assessment_id  ) {
    ?>
    <div class="no-info-box">
      <h3>To show detail info, please select assessment on the right side and publish this post.</h3>
    </div>
  <?php
  } else { ?>
    <div class="ranking-detail-ss">
      <div class="ss-row">
          <div class="left-col">
              <label class="label">Section</label>
          </div>
          <div class="right-col">
              <div class="ss-title">Position by Total Score</div>
          </div>
      </div>
      <div class="ss-row">
          <div class="left-col">
              <label class="label">Total score</label>
          </div>
          <div class="right-col">
            <table>
              <tr>
                <th class="no-col">#</th>
                <th>Organization name</th>
                <th>Total score</th>
                <th>Total percent</th>
              </tr>
              <?php
              foreach ($ranking_by_total_score as $key => $item) {
                ?>
                <tr>
                  <td class="no-col"><?php echo $item['org_rank']; ?></td>
                  <td><?php echo $item['org_name']; ?></td>
                  <td><?php echo $item['total_score']; ?></td>
                  <td><?php echo $item['total_percent']; ?>%</td>
                </tr>
                <?php
              }
              ?>
            </table>
          </div>
        </div>
    </div>
    
    <!-- Ranking by Industry -->
    <div class="ranking-detail-ss">
      <div class="ss-row">
        <div class="left-col">
          <label class="label">Section</label>
        </div>
        <div class="right-col">
          <div class="ss-title">Position by Industry</div>
        </div>
      </div>
      <div class="ss-row">
        <div class="left-col">
          <label class="label">Industry</label>
        </div>
        <div class="right-col">
          <?php foreach ($ranking_by_industry['by_indus_data'] as $key => $industry): ?>
          <div class="cl-row">
            <table class="table-industry">
              <tr>
                <th class="no-col">#</th>
                <th class="industry"><?php echo $key; ?></th>
                <th class="total-score">Total score</th>
                <th class="total-percent">Total percent</th>
              </tr>
              <?php foreach ($industry as $key => $item): ?>
              <tr>
                <td class="no-col"><?php echo $key+1; ?></td>
                <td class="industry"><?php echo $item['org_name']; ?></td>
                <td class="total-score"><?php echo $item['total_score']; ?></td>
                <td class="total-percent"><?php echo $item['total_percent']; ?>%</td>
              </tr>
              <?php endforeach; ?>
            </table>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <!-- /Ranking by Industry -->
    
    <?php foreach ($ranking_by_key_areas as $key => $field): ?>
    <!-- Ranking by key Ares -->
    <div class="ranking-detail-ss">
      <div class="ss-row">
        <div class="left-col">
            <label class="label">Section</label>
        </div>
        <div class="right-col">
            <div class="ss-title">Position by <?php echo $key; ?></div>
        </div>
      </div>
        <div class="ss-row">
            <div class="left-col">
                <label class="label"><?php echo $key; ?></label>
            </div>
            <div class="right-col">
              <?php if (!empty($field)): ?>
                <?php foreach ($field as $gr_id => $gr_field): ?>
                <?php 
                    $gr_title = $gr_field['title'] ?? '';
                ?>
                <div class="fr-row">
                  <div class="pr-info">
                    <h3><?php echo $gr_id.' - '.$gr_title; ?></h3>
                    <a class="btn-expland-fr active" role="button">
                        <span class="text">Collapse</span>
                        <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>
                    </a>
                  </div>
                    <div class="pr-ranking-lst">
                        <!-- Group Ranking -->
                        <?php if (isset($gr_field['gr_ranking']) && !empty($gr_field['gr_ranking'])): ?>
                        <div class="pr-ranking">
                          <table>
                            <tbody>
                              <tr>
                                  <th class="no-col">#</th>
                                  <th><?php echo $gr_title; ?></th>
                                  <th>Score</th>
                                  <th>Maturity level</th>
                              </tr>
                              <?php foreach ($gr_field['gr_ranking'] as $submission_id => $info): ?>
                              <?php 
                                  $org_rank       = $info['rank'] ?? '';
                                  $gr_score       = $info['score_average'] ?? '';
                                  $maturity_level = isset($gr_score) ? get_maturity_level_org($gr_score) : 0;
                                  $org_data = get_post_meta($submission_id, 'org_data', true);
                              ?>
                              <tr>
                                  <td class="no-col"><?php echo $org_rank; ?></td>
                                  <td><?php echo $org_data['Name'] ?? ''; ?></td>
                                  <td><?php echo $gr_score; ?></td>
                                  <td><?php echo 'Level '.$maturity_level; ?></td>
                              </tr>
                              <?php endforeach; ?>
                            </tbody>
                          </table>
                        </div>
                        <?php endif; ?>
                        <!-- /Group Ranking -->

                        <?php if (isset($gr_field['sub_data']) && !empty($gr_field['sub_data'])): ?>
                        <a class="btn-expand-wrapper" role="button">
                            <span class="text">Expand Group</span>
                            <span class="icon-chevron-down"><i class="fa-solid fa-chevron-down"></i></span>
                        </a>
                        <!-- Question Ranking -->
                        <div class="cr-info hide">
                          <div class="cl-row">
                            <table>
                              <tbody>
                              <?php foreach ($gr_field['sub_data'] as $sub_id => $sub_field): ?>
                              <?php 
                                $sub_title   = $sub_field['sub_title'] ?? '';
                                $sub_ranking = $sub_field['sub_ranking'] ?? array(); ?>
                                <tr>
                                    <th class="no-col">#</th>
                                    <th><?php echo $gr_id.'.'.$sub_id.' - '.$sub_title; ?></th>
                                    <th>Score</th>
                                </tr>
                                <?php if (!empty($sub_ranking)): ?>
                                  <?php foreach ($sub_ranking as $submission_id => $info): ?>
                                  <?php 
                                      $org_rank  = $info['rank'] ?? '';
                                      $sub_score = $info['sub_score'] ?? '';
                                      $org_data  = get_post_meta($submission_id, 'org_data', true);
                                  ?>
                                  <tr>
                                      <td class="no-col"><?php echo $org_rank; ?></td>
                                      <td><?php echo $org_data['Name'] ?? ''; ?></td>
                                      <td><?php echo $sub_score; ?></td>
                                  </tr>
                                  <?php endforeach; ?>
                                <?php endif; ?>
                              <?php endforeach; ?>
                              </tbody>
                            </table>
                          </div>
                        </div>
                        <!-- /Question Ranking -->
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
        </div>
    </div>
    <!-- /Ranking by key Ares -->
    <?php endforeach; ?>
    
  <?php } ?>

</div>

<?php
