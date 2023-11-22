<?php
global $post;

$assessment_id = get_field('assessment', $post->ID);

$by_total_score = get_field('position_by_total_score', $post->ID);
$ranking_by_total_score = json_decode($by_total_score, true);

$by_industry = get_field('position_by_industry', $post->ID);
$ranking_by_industry = json_decode($by_industry, true);

$by_framework = get_field('position_by_framework', $post->ID);
$ranking_by_framework = json_decode($by_framework, true);

// print_r($ranking_by_framework);
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
              <div class="label">Position by Total Score</div>
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
                </tr>
                <?php
                usort($ranking_by_total_score, fn($a, $b) => $b['total_score'] <=> $a['total_score']);
                foreach ($ranking_by_total_score as $key => $item) {
                  ?>
                  <tr>
                    <td class="no-col"><?php echo $key+1; ?></td>
                    <td><?php echo $item['org_name']; ?></td>
                    <td><?php echo $item['total_score']; ?></td>
                  </tr>
                  <?php
                }
                ?>
              </table>
          </div>
        </div>
    </div>

    <div class="ranking-detail-ss">
      <div class="ss-row">
          <div class="left-col">
              <label class="label">Section</label>
          </div>
          <div class="right-col">
              <div class="label">Position by Industry</div>
          </div>
      </div>
      <div class="ss-row">
          <div class="left-col">
              <label class="label">Industry</label>
          </div>
          <div class="right-col">
            <?php 
            foreach ($ranking_by_industry as $key => $industry) {
              $indus = $industry;
              usort($indus, fn($a, $b) => $b['total_score'] <=> $a['total_score']);
            ?>
            <div class="cl-row">
              <table>
                  <tr>
                    <th class="no-col">#</th>
                    <th><?php echo $key; ?></th>
                    <th>Total score</th>
                  </tr>
                  <?php
                  foreach ($indus as $key => $item) {
                    ?>
                    <tr>
                      <td class="no-col"><?php echo $key+1; ?></td>
                      <td><?php echo $item['org_name']; ?></td>
                      <td><?php echo $item['total_score']; ?></td>
                    </tr>
                    <?php
                  }
                  ?>
                </table>
            </div>
            <?php } ?>
          </div>
        </div>
    </div>

    <div class="ranking-detail-ss">
      <div class="ss-row">
          <div class="left-col">
              <label class="label">Section</label>
          </div>
          <div class="right-col">
              <div class="label">Position by Framework</div>
          </div>
      </div>
      <div class="ss-row">
          <div class="left-col">
              <label class="label">Framework</label>
          </div>
          <div class="right-col">
            <?php 
            foreach ($ranking_by_framework as $parent_id => $framework) {
              ?>
              <div class="fr-row">
                <div class="pr-info">
                  <h3><?php echo $parent_id . ' - ' . $framework['title'];?></h3>
                </div>
                <div class="cr-info">
                  <?php
                  $child_questions = $framework['child_questions'];
                  foreach ($child_questions as $child_id => $child_question) {
                    $q_subs = $child_question['subs'];
                    usort($q_subs, fn($a, $b) => $b['q_score'] <=> $a['q_score']);
                  ?>
                    <div class="cl-row">
                      <table>
                          <tr>
                            <th class="no-col">#</th>
                            <th><?php echo $parent_id.'.'.$child_id.' - '. $child_question['title']; ?></th>
                            <th>Score</th>
                          </tr>
                          <?php
                          foreach ($q_subs as $key => $item) {
                            ?>
                            <tr>
                              <td class="no-col"><?php echo $key+1; ?></td>
                              <td><?php echo $item['org_name']; ?></td>
                              <td><?php echo $item['q_score']; ?></td>
                            </tr>
                            <?php
                          }
                          ?>
                        </table>
                    </div>
            <?php } ?>
                </div>
              </div>
            <?php
            } ?>
          </div>
        </div>
    </div>
    
    
  <?php } ?>

</div>

<?php
