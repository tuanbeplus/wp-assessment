<?php
/**
 * All function for Ranking
 */

class AndAssessmentRanking {

  public function __construct() {
    add_action('init', array($this, 'register_ranking_custom_post_type'));
  }

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
}

new AndAssessmentRanking();