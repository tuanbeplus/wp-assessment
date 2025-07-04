<?php
/*
Plugin Name: WP Assessment
Plugin URI: #
Description: Custom plugin for Project Saturn
Version: 2.9.9
Author: tom@ysnstudios.com
Author URI: tom@ysnstudios.com
Text Domain: wp-assessment
*/

// Make sure we don't expose any info if called directly
if (!function_exists('add_action')) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

define('WP_ASSESSMENT_VER', '2.9.9');
define('WP_ASSESSMENT_DIR', plugin_dir_path(__FILE__));
define('WP_ASSESSMENT_ASSETS', plugins_url('/assets', __FILE__));
define('WP_ASSESSMENT_FRONT_IMAGES', plugins_url('/assets/images/front', __FILE__));

require_once(WP_ASSESSMENT_DIR . '/includes/function.php');
require_once(WP_ASSESSMENT_DIR . '/includes/statics.php');
require_once(WP_ASSESSMENT_DIR . '/includes/helper.php');
require_once(WP_ASSESSMENT_DIR . '/includes/hooks.php');
require_once(WP_ASSESSMENT_DIR . '/includes/custom-post-types.php');
require_once(WP_ASSESSMENT_DIR . '/includes/custom-fields.php');
require_once(WP_ASSESSMENT_DIR . '/includes/question-form.php');
require_once(WP_ASSESSMENT_DIR . '/includes/salesforce-api.php');
require_once(WP_ASSESSMENT_DIR . '/includes/salesforce-webhook.php');
require_once(WP_ASSESSMENT_DIR . '/includes/azure-storage.php');
require_once(WP_ASSESSMENT_DIR . '/includes/feedbacks-functions.php');
require_once(WP_ASSESSMENT_DIR . '/includes/ranking-functions.php');
// require_once(WP_ASSESSMENT_DIR . '/includes/saturn-archive-functions.php');
require_once(WP_ASSESSMENT_DIR . '/vendor/autoload.php');
