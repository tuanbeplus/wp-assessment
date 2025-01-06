<?php 
/**
 * Module submission details of simple assessment
 *
 * @author Tuan
 */

$questions = get_query_var('questions') ?? [];
$quizzes = get_query_var('quizzes') ?? [];
$reorganize_quizzes = [];
foreach ($quizzes as $row) {
    $reorganize_quizzes[$row->quiz_id] = $row;
}
?>
<!-- Begin Simple Submission -->
<?php if (!empty($quizzes)): 
    foreach ($questions as $field_id => $field):
        $question_title = $field['title'] ?? '';
        $question_des = $field['description'] ?? '';
        $quiz_row = $reorganize_quizzes[$field_id] ?? null;
        if (!empty($quiz_row)) {
            $answers = json_decode($quiz_row->answers) ?? null;
            $description = $quiz_row->description ?? '';
            $attachment_id = $quiz_row->attachment_id ?? null;
            $attachment_url = wp_get_attachment_url($attachment_id);
            $attachment_type = get_post_mime_type($attachment_id);
        }
        ?>
        <div class="submission-view-item-row simple" id="main-container-<?php echo $field_id ?>">
            <div class="card">
                <div class="card-body">
                    <h4 class="quiz-title"><?php echo esc_html($question_title); ?></h4>
                    <div class="question-des"><?php echo $question_des; ?></div>
                    <?php if ( !empty($answers) ): ?>
                        <div class="submission-answers-list">
                            <strong>Selected Answer:</strong>
                            <ul>
                            <?php foreach ($answers as $answer): ?>
                                <li><?php echo $answer->title ?? ''; ?></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php else: ?>
                        <ul>
                            <li>No answer.</li>
                        </ul>
                    <?php endif; ?>
                    <?php if (!empty($description)): ?>
                        <div class="user-comment-area">
                            <p class="description-label"><strong>User Comment: </strong></p>
                            <div class="description-thin"><?php echo esc_html($description); ?></div>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($attachment_id)) : ?>
                        <a href="<?php echo esc_attr($attachment_url) ?>" target="_blank"><p>View Supporting Documentation</p></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php endif; ?>
<style>
    .submission-view-item-row:last-of-type {
        margin-bottom: 10px;
    }
    .submission-view-item-row .card {
        width: 100% !important;
    }
</style>
<!-- End Simple Submission -->