<?php 

class DebugLogHelper {
  public $current_user = null;
  public $user_login = null;

  public function __construct() { 
    $__current_user = wp_get_current_user();
    $this->current_user = $__current_user;
    $this->user_login = $__current_user->user_login ? $__current_user->user_login : 'anonymous'; 
  }

  public function log($content) { 
    $upload_dir = wp_upload_dir();
    if ( isset( $this->user_login ) && ! empty( $upload_dir['basedir'] ) ) {
      $path_log = $upload_dir['basedir'].'/__log/';
      if ( ! file_exists( $path_log ) ) {
        wp_mkdir_p( $path_log );
      } 

      $__header = PHP_EOL . "User: " . $this->user_login . PHP_EOL . date("F j, Y, g:i a") . PHP_EOL . "-------------------------" . PHP_EOL;
      $__footer = PHP_EOL . "-------------------------" . PHP_EOL . PHP_EOL;

      $log_content = $__header . $content . $__footer; 
      file_put_contents($path_log . $this->user_login . '.log', $log_content, FILE_APPEND);
    }
  }
}