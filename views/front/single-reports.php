<?php
/**
 * The template for displaying single posts of reports post type
 *
 */
 
get_header(); 
?>
<?php 
global $post;

$post_id = $post->ID;
$post_meta = get_post_meta($post_id);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$assessment_title = get_the_title($assessment_id);
?>
 
<section id="primary" class="content-area">
    <div class="container">
        <div class="report-content-wrapper">
            <?php
            // Start the loop.
            while ( have_posts() ) : the_post();
            
                the_content();

            // End the loop.
            endwhile;
            ?>
        </div>
    </div>
</section><!-- .content-area -->
 
<?php get_footer(); ?>
