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
    add_meta_box('ranking_assessment_linked', 'Assessment', array($this, 'ranking_assessment_linked_render'), 'ranking', 'side', 'default');
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

}

new AndAssessmentRanking();