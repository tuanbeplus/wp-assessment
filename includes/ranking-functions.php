<?php

/** Const for Ranking */
const AD_RANKING_DETAIL_VIEW = WP_ASSESSMENT_DIR . '/views/admin/ranking/ranking-detail-view.php';
const AD_RANKING_ASSESSMENT_VIEW = WP_ASSESSMENT_DIR . '/views/admin/ranking/ranking-assessment-view.php';

/**
 * All functions for Ranking
 */
class AndAssessmentRanking {

  /**
   * Construct function
   */
  public function __construct() {
    add_action('init', array($this, 'register_ranking_custom_post_type'));
    add_action('admin_init', array($this, 'add_ranking_meta_boxes'));
    add_action('admin_enqueue_scripts', array($this, 'ranking_enqueue_scripts'));

    add_action('save_post', array($this, 'save_post_for_ranking'));

    // Custom ranking admin column
    add_filter('manage_ranking_posts_columns', array($this, 'customize_ranking_admin_column'));
    add_action('manage_ranking_posts_custom_column', array($this, 'customize_ranking_admin_column_value'), 10, 2);
  }

  /**
   * Function to create post type Ranking
   */
  function register_ranking_custom_post_type(): void {
    $labels = array(
      'name' => _x('Ranking', 'and'),
      'singular_name' => _x('Ranking', 'and'),
      'add_new' => _x('Add New', 'and'),
      'add_new_item' => _x('Add New Ranking', 'and'),
      'edit_item' => _x('Edit Ranking', 'and'),
      'new_item' => _x('New Ranking', 'and'),
      'view_item' => _x('View Ranking', 'and'),
      'search_items' => _x('Search Ranking', 'and'),
      'not_found' => _x('No Ranking found', 'and'),
      'not_found_in_trash' => _x('No ranking found in Trash', 'and'),
      'parent_item_colon' => _x('Parent Report:', 'and'),
      'menu_name' => _x('Ranking', 'and'),
    );
    $args = array(
      'labels' => $labels,
      'hierarchical' => false,
      'supports' => array('title', 'thumbnail', 'author'),
      'show_ui' => true,
      'show_in_menu' => true,
      'show_in_nav_menus' => true,
      'publicly_queryable' => true,
      'exclude_from_search' => true,
      'has_archive' => true,
      'query_var' => true,
      'can_export' => true,
      'rewrite' => true,
      'public' => true,
      'map_meta_cap' => true,
      'menu_icon' => 'dashicons-editor-ol',
    );
    register_post_type('ranking', $args);
  }

  /**
   * Function to create meta boxes
   */
  function add_ranking_meta_boxes(): void {
    add_meta_box('ranking_detail_sections', 'Ranking detail', array($this, 'ranking_detail_sections_render'), 'ranking', 'normal', 'default');
    // add_meta_box('ranking_assessment_linked', 'Assessment', array($this, 'ranking_assessment_linked_render'), 'ranking', 'side', 'default');
  }

  function ranking_enqueue_scripts(): void {
    global $post_type;
    if( $post_type == 'ranking' ) {
        wp_enqueue_style('bootstrap-min', WP_ASSESSMENT_ASSETS . '/css/bootstrap.min.css');
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css');
        
        wp_enqueue_script('admin-ranking-js', WP_ASSESSMENT_ASSETS . '/js/admin/ranking.js', true, WP_ASSESSMENT_VER);
        wp_enqueue_style('admin-ranking-css', WP_ASSESSMENT_ASSETS . '/css/admin/ranking.css', false, WP_ASSESSMENT_VER);
    }
  }

  /**
   * Function to render Ranking detail box
   */
  function ranking_detail_sections_render() {
    return include_once AD_RANKING_DETAIL_VIEW;
  }

  /**
   * Function to render Ranking Assessment box
   */
  function ranking_assessment_linked_render() {
    return include_once AD_RANKING_ASSESSMENT_VIEW;
  }

  /**
   * Function to save ranking info
   */
  function save_post_for_ranking($post_id): void {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        if (!current_user_can('edit_post', $post_id) || get_post_type($post_id) != 'ranking')
            return;

        $assessment_id = get_field('assessment', $post_id);
        $submissions_info = array();
        $org_list = array();

        // Get all submission for this assessment
        $args = array(
          'numberposts' => -1, 
          'post_status' => 'publish', 
          'post_type' => 'submissions',
          'meta_key' => 'assessment_id',
		      'meta_value' => $assessment_id,
        );
        $submissions = get_posts( $args );
        foreach ( $submissions as $sub ) {     
          $user_id = get_post_meta($sub->ID, 'user_id', true);
          $org_id = get_post_meta($sub->ID, 'organisation_id', true);
          $org_metadata = get_post_meta($sub->ID, 'org_data', true);
          // $sub_all_scores = get_post_meta($sub->ID, 'org_score', true);
          // $group_all_scores = get_post_meta($sub->ID, 'org_section_score', true);

          // Calculator Agreed Scores
          $agreed_score = get_post_meta($sub->ID, 'agreed_score', true);
          $sub_all_scores = cal_scores_with_weighting($assessment_id, $agreed_score, 'sub');
          $group_all_scores = cal_scores_with_weighting($assessment_id, $agreed_score, 'group');

          if ( ! $org_metadata ) {
            $org_metadata = get_sf_organisation_data($user_id, $org_id);
            update_post_meta($sub->ID, 'org_data', $org_metadata);
          }
          $total_score = get_post_meta($sub->ID, 'total_agreed_score', true);
          $org_name = (isset($org_metadata['Name'])) ? $org_metadata['Name'] : '';
          $industry_name = (isset($org_metadata['Industry'])) ? $org_metadata['Industry'] : '';

          if ( ! in_array($org_name, $org_list) && $org_name ) {
            $total_score_sum = ( isset($total_score['sum']) ) ? $total_score['sum'] : 0;
            $total_percent = ( isset($total_score['percent']) ) ? $total_score['percent'] : 0;
            $submissions_info[] = array(
              'sub_id' => $sub->ID,
              'org_id' => $org_id,
              'org_name' => $org_name,
              'industry_name' => $industry_name,
              'total_score' => $total_score_sum,
              'total_percent' => $total_percent,
              'group_score' => $group_all_scores,
              'all_score' => $sub_all_scores
            );
            $org_list[] = $org_name;
          }
        }

        // Start - Position by Total Score
        $ranking_by_total_score = $submissions_info;
        usort($ranking_by_total_score, fn($a, $b) => $b['total_score'] <=> $a['total_score']);
        $ranking_by_tt_sc = array();
        foreach ($ranking_by_total_score as $key=>$rk_item) {
          $temp_item = $rk_item;
          $temp_item['org_rank'] = $key+1;
          $ranking_by_tt_sc[$rk_item['org_id']] = $temp_item;
        }
        update_field('position_by_total_score', base64_encode(serialize($ranking_by_tt_sc)), $post_id );
        // End - Position by Total Score

        // Start - Position by Industry
        $ranking_by_industry = array();
        foreach ($submissions_info as $sub_i) {
          $ranking_by_industry[$sub_i['industry_name']][] = $sub_i;
        }
        $ranking_by_indus = array();
        $indus_data = array();
        foreach ($ranking_by_industry as $id_key => $industry) {
          $indus = $industry;
          usort($indus, fn($a, $b) => $b['total_score'] <=> $a['total_score']);
          foreach ($indus as $key => $item) {
            $temp_item = $item;
            $temp_item['org_rank'] = $key+1;
            $indus_data[$item['org_id']] = $temp_item;
          }
          $ranking_by_indus[$id_key] = $indus;
        }
        $ranking_by_indus_data = array(
          'by_indus_data' => $ranking_by_indus,
          'rank_data' => $indus_data
        );
        update_field('position_by_industry', base64_encode(serialize($ranking_by_indus_data)), $post_id );
        // End - Position by Industry

        // Start - Position by Framework
        $ranking_by_framework = array();
        $wp_ass = new WP_Assessment();
        $key_areas = get_assessment_key_areas($assessment_id);
        $questions = get_post_meta($assessment_id, 'question_group_repeater', true);
        $questions = $wp_ass->wpa_unserialize_metadata($questions);

        foreach ($questions as $parent_id => $parent_question) {
          $child_questions = array();
          $parent_title = htmlentities(stripslashes(utf8_decode( $parent_question['title'] )));
          $parent_title = $parent_title;
          $child_questions_lst = $parent_question['list'];

          // Get Parents Ranking info
          $parent_lst = array();
          foreach ($submissions_info as $sub_i) {
            $group_score = $sub_i['group_score'];
            $parent_lst[] = array(
              'org_id' => $sub_i['org_id'],
              'org_name' => $sub_i['org_name'],
              'group_q_score' => $group_score[$parent_id]
            );
          }
          usort($parent_lst, fn($a, $b) => $b['group_q_score'] <=> $a['group_q_score']);
          $ranking_by_parent_q = array();
          $level1 = $level2 = $level3 = $level4 = 0;
          $pr_total_score = $pr_items_cnt = 0;
          foreach ($parent_lst as $key=>$pr_item) {
            $pr_items_cnt++;
            $pr_total_score += (float) $pr_item['group_q_score'];
            $level = get_maturity_level_org($pr_item['group_q_score']);
            $temp_item = $pr_item;
            $temp_item['org_rank'] = $key+1;
            $temp_item['level'] = $level;
            $ranking_by_parent_q[$pr_item['org_id']] = $temp_item;

            if ( $level >= 4 ) {
              $level4++;
            } elseif ($level >= 3) {
              $level3++;
            } elseif ($level >= 2) {
              $level2++;
            } else {
              $level1++;
            }
          }
          $org_at_levels = array(
            'level1' => $level1,
            'level2' => $level2,
            'level3' => $level3,
            'level4' => $level4
          );
          $maturity_pr_level = ( $pr_items_cnt > 0 ) ? get_maturity_level_org($pr_total_score/$pr_items_cnt) : 0;

          // Get Child Question Ranking info
          foreach ($child_questions_lst as $child_id => $child_question) {
            $subs_lst = array();
            foreach ($submissions_info as $sub_i) {
              $all_score = $sub_i['all_score'];
              $subs_lst[] = array(
                'org_name' => $sub_i['org_name'],
                'q_score' => $all_score[$parent_id][$child_id]
              );
            }
            $child_questions[$child_id] = array(
              'title' => $child_question['sub_title'] ,
              'subs' => $subs_lst
            );
          }
          $ranking_by_framework[$parent_id] = array(
            'title' => $parent_title,
            'parent_questions' => $ranking_by_parent_q,
            'org_at_levels' => $org_at_levels,
            'average_maturity_level' => $maturity_pr_level,
            'child_questions' => $child_questions
          );
        }
        update_field('position_by_framework', base64_encode(serialize($ranking_by_framework)), $post_id );
        // End - Position by Framework

    }

    /**
     * Add new column to Ranking table
     */
    function customize_ranking_admin_column($columns)
    {
      $columns['assessment'] = 'Assessment';
      return $columns;
    }

    /**
     * Custom column value of Ranking table
     */
    function customize_ranking_admin_column_value($column_key, $post_id): void
    {
      if ($column_key == 'assessment') {
        $assessment_id = get_field('assessment', $post_id);
        if (isset($assessment_id)) {
          if (isset($assessment_id)) {
            echo '<a href="/wp-admin/post.php?post='.$assessment_id.'&action=edit" target="_blank">'
                    .get_the_title($assessment_id).
                '</a>';
          }
        }
      }
    }

    /**
     * Ranking orgs by group questions with key area
     * 
     * @param $assessment_id    Assessment ID
     * 
     * @return Array Ranking with key area
     */
    function ranking_orgs_group_question($assessment_id) {
      $wp_assessment = new WP_Assessment();
      $key_areas = get_assessment_key_areas($assessment_id);
      $questions = get_post_meta($assessment_id, 'question_group_repeater', true);
      $questions = $wp_assessment->wpa_unserialize_metadata($questions);
      $index_submissions = $this->get_all_index_submission_finalised($assessment_id);
      $ranking_by_key_areas = array();
      $ranking_orgs_data = array();

      foreach ($key_areas as $key) {
          // Loop group question
          foreach ($questions as $group_id => $gr_field) {
            $sub_list = $gr_field['list'] ?? array();
            $gr_title = $gr_field['title'] ?? '';

            // Add group title
            $ranking_by_key_areas[$key][$group_id]['title'] = $gr_title;

            if (!empty($sub_list)) {
                // Loop Sub question
                foreach ($sub_list as $sub_id => $sub_field) {
                  // Weighting
                  $weighting = $sub_field['point'] ?? '';

                  if (!empty($sub_field['key_area']) && $sub_field['key_area'] == $key) {
                    if (!empty($index_submissions)) {
                      $agreed_score = array();
                      // Loop all Index submissions
                      foreach ($index_submissions as $submission) {
                        $agreed_score = get_post_meta($submission->ID, 'agreed_score', true);

                        if (!empty($weighting)) {
                          $sub_score = (float)$agreed_score[$group_id][$sub_id] * (float)$weighting;
                        }
                        else {
                          $sub_score = (float)$agreed_score[$group_id][$sub_id];
                        }
                        // Add average group scores
                        $ranking_by_key_areas[$key][$group_id]['gr_ranking'][$submission->ID]['score_average'] = $this->cal_group_scores_key_area($key, $group_id, $questions, $submission->ID);
                        // Add empty froup rank
                        $ranking_by_key_areas[$key][$group_id]['gr_ranking'][$submission->ID]['rank'] = '';
                        // Add submission title
                        $ranking_by_key_areas[$key][$group_id]['sub_data'][$sub_id]['sub_ranking'][$submission->ID]['submission_title'] = $submission->post_title;
                        // Add submission Id
                        $ranking_by_key_areas[$key][$group_id]['sub_data'][$sub_id]['sub_ranking'][$submission->ID]['submission_id'] = $submission->ID;
                        // Add sub score
                        if (isset($agreed_score[$group_id][$sub_id])) {
                          $ranking_by_key_areas[$key][$group_id]['sub_data'][$sub_id]['sub_ranking'][$submission->ID]['sub_score'] = number_format($sub_score, 1);
                        }   
                        // Add empty rank Id
                        $ranking_by_key_areas[$key][$group_id]['sub_data'][$sub_id]['sub_ranking'][$submission->ID]['rank'] = '';
                      }
                    }
                    // Add sub question title
                    $ranking_by_key_areas[$key][$group_id]['sub_data'][$sub_id]['sub_title'] = $sub_field['sub_title'] ?? '';
                  }
                }

                // Sort the Sub score in DESC order
                if(isset($ranking_by_key_areas[$key][$group_id]['sub_data'])) {
                  $sub_data = $ranking_by_key_areas[$key][$group_id]['sub_data'];

                  foreach ($sub_data as $sub_id => $sub_field) {
                    $sub_ranking = $sub_field['sub_ranking'] ?? array();

                    if (!empty($sub_ranking)) {
                      uasort($sub_ranking, function ($a, $b) {
                        if (!isset($b['sub_score']) && !isset($a['sub_score'])) return;
                        return $b['sub_score'] <=> $a['sub_score'];
                      });
                      // Add index numbers to 'rank'
                      $rankIndex = 1;
                      foreach ($sub_ranking as &$item) {
                        $item['rank'] = $rankIndex++;
                      }
                      $ranking_by_key_areas[$key][$group_id]['sub_data'][$sub_id]['sub_ranking'] = $sub_ranking;
                    }
                  }
                }
            }                

          // Sort the Group score array by 'score_average' key in DESC order
          if (isset($ranking_by_key_areas[$key][$group_id]['gr_ranking'])) {
            $gr_score_sort = $ranking_by_key_areas[$key][$group_id]['gr_ranking'];

            uasort($gr_score_sort, function ($a, $b) {
              if (!isset($b['score_average']) && !isset($a['score_average'])) return;
              return $b['score_average'] <=> $a['score_average'];
            });
            // Add index numbers to 'rank'
            $rankIndex = 1;
            foreach ($gr_score_sort as &$item) {
              $item['rank'] = $rankIndex++;
            }
            $ranking_by_key_areas[$key][$group_id]['gr_ranking'] = $gr_score_sort;
          }
          else {
            $ranking_by_key_areas[$key][$group_id]['gr_ranking'] = array();
          }
        }
      }
      return $ranking_by_key_areas;
    }

    /**
     * Ranking orgs by group questions with key area
     * 
     * @param $assessment_id    Assessment ID
     * @param $group_id         Group ID
     * @param $questions        Assessment Quiz data
     * @param $submission_id    Submission ID
     * 
     * @return Array Group scores average with key area
     */
    function cal_group_scores_key_area($key_area, $group_id, $questions, $submission_id) {
      $gr_ranking = array();
      $gr_scores_arr = array();
      $agreed_score = get_post_meta($submission_id, 'agreed_score', true);
      $gr_field = $questions[$group_id] ?? array();

      if (isset($gr_field) && !empty($gr_field)) {
        $sub_questions_list = $gr_field['list'] ?? array();
        if (isset($sub_questions_list) && !empty($sub_questions_list)) {
          // Loop sub questions
          foreach ($sub_questions_list as $sub_id => $sub_field) {
            $weighting = 1;
            if (!empty($sub_field['key_area']) && $sub_field['key_area'] == $key_area) {
              $weighting = $sub_field['point'] ?? '';
          
              if (!empty($weighting)) {
                $sub_score = (float)$agreed_score[$group_id][$sub_id] * (float)$weighting;
              }
              else {
                $sub_score = (float)$agreed_score[$group_id][$sub_id];
              }
              $gr_scores_arr[$sub_id] = $sub_score;
            }
          }
        }
      }
      // Average group scores
      if (!empty($gr_scores_arr)) {
        $gr_scores_average = number_format(array_sum($gr_scores_arr)/count($gr_scores_arr), 1);
      }
      else {
        $gr_scores_average = 0;
      }

      return $gr_scores_average;
    }

    /**
     * Get all Index submission finalised (published)
     * 
     * @param $assessment_id    Assessment ID
     * 
     * @return Array All Index Submissions finalised
     */
    function get_all_index_submission_finalised($assessment_id) {
      $args = array(
        'post_type' => 'submissions',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'order_by' => 'date',
        'order' => 'ASC',
        'meta_query' => array(
        array(
            'key' => 'assessment_id',
            'value' => $assessment_id,
        )
        ),
      );
      $index_submissions = get_posts($args);
      wp_reset_postdata();
      return $index_submissions;
    }

}

new AndAssessmentRanking();