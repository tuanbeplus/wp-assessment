<?php 
/**
 * Module submission details of simple assessment
 *
 * @author Tuan
 */

$questions = get_query_var('questions') ?? [];
$quizzes = get_query_var('quizzes') ?? [];
?>
<!-- Begin Simple Submission -->
<?php if (!empty($quizzes)): 
    foreach ($quizzes as $field):
        $quiz_id = $field->quiz_id;
        $attachment_id = null;
        $attachment_type = null;
        $url = null;

        $answers = [];
        if (!empty($field->answers)) {
            $answers = json_decode($field->answers);
        }
        $description = null;
        if (!empty($field->description)) {
            $description = $field->description;
        }
        if (!empty($field->attachment_id)) {
            $attachment_id = $field->attachment_id;
            $url = wp_get_attachment_url($attachment_id);
            $attachment_type = get_post_mime_type($attachment_id);
        }
        $question_title = $questions[$quiz_id]['title'] ?? null;
        $question_des = $questions[$quiz_id]['description'] ?? null;
        ?>
        <div class="submission-view-item-row simple" id="<?php echo $quiz_id ?>-main-container">
            <div class="card">
                <div class="card-body">
                    <input class="quiz_id" type="hidden" name="quiz_id[]" value="<?php echo $quiz_id ?>" class="quiz-input"/>
                    <h4 class="quiz-title"><?php echo esc_html($question_title); ?></h4>
                    <div class="question-des"><?php echo $question_des; ?></div>
                    <?php if (is_array($answers) && count($answers) > 0) : ?>
                        <div class="submission-answers-list">
                            <strong>Selected Answer:</strong>
                            <ul>
                            <?php foreach ($answers as $answer): ?>
                                <li><?php echo $answer->title; ?></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <ul>
                            <li>No answer</li>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($description)): ?>
                        <div class="user-comment-area">
                            <p class="description-label"><strong>User Comment: </strong></p>
                            <div class="description-thin"><?php echo esc_html($description); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($attachment_id) : ?>
                        <a href="<?php echo esc_attr($url) ?>" target="_blank"><p>View Supporting Documentation</p></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<!-- End Simple Submission -->