<?php
/**
 * The template for displaying Draft Preliminary Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

if (empty($questions) || empty($quizzes)) {
    return;
}
foreach ($questions as $group_id => $field_group) {
    $group_title = wpa_stripslashes_string($field_group['title']) ?? '';
    $draft_table = 
    '<div class="page">
        <h2>Section '. $group_id .': '. $group_title .'</h2>
        <table class="recom-table dcr-table" width="100%">
            <tr>
                <th width="10%">Section <br> Name</th>
                <th width="7%">Section <br> Number</th>
                <th width="8%">Submission #</th>
                <th width="11%">User Name <br> & Date of Entry</th>
                <th width="15%">User Comments</th>
                <th width="15%">Documents Upload</th>
                <th width="12%">Assessor Name <br> & Date of Entry</th>
                <th width="11%">Assessor <br> Comments</th>
                <th width="11%">Criteria Status <br> & Date of Entry</th>
            </tr>';
        $count_quiz = 0;
        foreach ($quizzes as $quiz) { 
            if ($quiz->parent_id == $group_id && $quiz->submission_id == $submission_id) {
                $wp_user_id = get_current_user_by_salesforce_id($quiz->user_id) ?? '';
                $first_name = get_user_meta($wp_user_id, 'first_name', true);
                $last_name = get_user_meta($wp_user_id, 'last_name', true);
                $documents_list = '';
                $documents_uploaded = $azure->get_azure_attachments_uploaded($quiz->parent_id, $quiz->quiz_id, $assessment_id, $organisation_id) ?? array();
                if (!empty($documents_uploaded)) {
                    foreach ($documents_uploaded as $document) {
                        $documents_list .= '<div>
                            <a href="?action=create_sas_blob_url&blob_url='. $document->attachment_path .'" 
                                style="color:#6e297b;"
                                target="_blank">'
                                . $document->attachment_name .
                            '</a>
                        </div>' ?? '';
                    }
                }
                $assessor_info = '';
                $assessor_comments = '';
                $feedbacks_list = $assessor_feedbacks[$quiz->parent_id][$quiz->quiz_id] ?? array();
                if (!empty($feedbacks_list)) {
                    foreach ($feedbacks_list as $feedback) {
                        $assessor_name = $feedback['user_name'] ?? '';
                        $date_time = $feedback['time'] ?? '';
                        $assessor_info .= '<tr><td>'. $assessor_name .'<br>'. $date_time .'</td></tr>';

                        $comment = $feedback['feedback'] ?? '';
                        $assessor_comments .= '<tr><td>'. $comment .'</td></tr>';
                    }
                }

                $draft_table .=
                '<tr>
                    <td>'. $group_title .'</td>
                    <td>'. $quiz->parent_id .'.'. $quiz->quiz_id .'</td>
                    <td>'. $quiz->submission_id .'</td>
                    <td>'. $first_name .' '. $last_name .'<br>'. $quiz->time .'</td>
                    <td>'. wpa_stripslashes_string($quiz->description) .'</td>
                    <td><div class="docs-upload">'. $documents_list .'</div></td>
                    <td class="no-padding"><table class="dcr-table">'. $assessor_info .'</table></td>
                    <td class="no-padding"><table class="dcr-table">'. $assessor_comments .'</table></td>
                    <td class="'.$quiz->status.'">'. ucfirst($quiz->status) .'</td> 
                </tr>';
                $count_quiz++;
            }
        }
        if ($count_quiz <= 0) {
            $draft_table .= '<tr><td>No data.</td></tr>';
        }
    $draft_table .= 
        '</table>
        <caption>Table '. $group_id .' - '. $group_title .' entries</caption>
    </div>';

    // Add to table of contents
    $mpdf->TOC_Entry('Section '. $group_id .': '. $group_title, 0);

    // Render HTML
    $mpdf->WriteHTML($draft_table);

    // Insert page break
    $mpdf->AddPage(); 
}

