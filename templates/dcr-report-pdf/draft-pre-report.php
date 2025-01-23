<?php
/**
 * The template for displaying Draft Preliminary Report PDF - Saturn
 *
 * @author Tuan
 * 
 */

if (empty($questions) || empty($reorganize_quizzes)) {
    return;
}
foreach ($questions as $group_id => $field_group) {
    $group_title = wpa_stripslashes_string($field_group['title']) ?? '';
    $sub_questions = $field_group['list'] ?? [];
    $count_quiz = 0;
    $table_row_html = '';

    foreach ($sub_questions as $sub_id => $sub_field) { 
        $current_quiz_rows = $reorganize_quizzes[$group_id][$sub_id] ?? [];
        $documents_uploaded = $azure_documents_uploaded[$group_id][$sub_id] ?? [];
        $feedbacks_list = $assessor_feedbacks[$group_id][$sub_id] ?? [];

        if (!empty($current_quiz_rows)) {
            $wp_user_id = null;
            $first_name = '';
            $last_name = '';
            $user_comment_entries = '';
            $documents_list = '';
            $assessor_comment_entries = '';
            $status_entries = '';
            $submission_ids = '';
            $latest_row_time = $current_quiz_rows[0]->time ?? '';

            foreach ($current_quiz_rows as $row) {
                $wp_user_id = get_current_user_by_salesforce_id($row->user_id) ?? '';
                $first_name = get_user_meta($wp_user_id, 'first_name', true);
                $last_name = get_user_meta($wp_user_id, 'last_name', true);
                $user_comment_entries .=
                '<tr>
                    <td' . ($row === end($current_quiz_rows) ? ' style="border-bottom:none;"' : '') . '>
                        <strong style="font-family:avenir-heavy;">'. $first_name .' '. $last_name .'</strong> - '. date("M d Y H:i a", strtotime($row->time)) .'<br>'. wp_unslash($row->description).
                    '</td>
                </tr>';
                $status_entries .= '<li>'. ucwords($row->status) .'</li><br>';
                $submission_ids .= '<li>'. $row->submission_id .'</li><br>';
            }
            if (!empty($documents_uploaded)) {
                foreach ($documents_uploaded as $document) {
                    $documents_list .= 
                    '<tr>
                        <td' . ($document === end($documents_uploaded) ? ' style="border-bottom:none;"' : '') . '>
                            <a href="?action=create_sas_blob_url&blob_url='. $document->attachment_path .'" style="color:#6e297b;">'
                                . $document->attachment_name .
                            '</a>
                        </td>
                    </tr>' ?? '';
                }
            }
            if (!empty($feedbacks_list)) {
                foreach ($feedbacks_list as $feedback) {
                    $assessor_name = $feedback['user_name'] ?? '';
                    $date_time = date("M d Y H:i a", strtotime($feedback['time'])) ?? '';
                    $comment = $feedback['feedback'] ?? '';
                    $assessor_comment_entries .= 
                    '<tr>
                        <td' . ($feedback === end($feedbacks_list) ? ' style="border-bottom:none;"' : '') . '>
                            <strong style="font-family:avenir-heavy;">'.$assessor_name .'</strong> - '. $date_time .'<br>'. wp_kses_post(htmlspecialchars_decode($comment)) .
                        '</td>
                    </tr>';
                }
            }
            $table_row_html .=
            '<tr>
                <td>'. $group_title .'</td>
                <td>'. $group_id .'.'. $sub_id .'</td>
                <td><ul>'. $submission_ids .'</ul></td>
                <td class="no-padding"><table class="dcr-table">'. $user_comment_entries .'</table></td>
                <td class="no-padding"><table class="dcr-table">'. $documents_list .'</table></td>
                <td class="no-padding"><table class="dcr-table">'. $assessor_comment_entries .'</table></td>
                <td><ul>'. $status_entries .'</ul>Latest changed at '. $latest_row_time .'</td> 
            </tr>';
            $count_quiz++;
        }
    }
    if ($count_quiz <= 0) {
        $table_row_html = '<tr><td colspan="7" style="text-align:center;">No data available.</td></tr>';
    }
    
    $dcr_report_table = 
    '<div class="page">
        <h2>Section '. $group_id .': '. $group_title .'</h2>
        <table class="recom-table dcr-table" width="100%">
            <tr>
                <th width="10%">Section <br> Name</th>
                <th width="9%">Section <br> Number</th>
                <th width="8%">Submission #</th>
                <th width="22%">User Name & Comment Entries</th>
                <th width="18%">Documents Upload</th>
                <th width="20%">Assessor Name & Comment Entries</th>
                <th width="12%">Criteria Status & Date of Entry</th>
            </tr>'
            . $table_row_html .
        '</table>
        <caption>Table '. $group_id .' - '. $group_title .' entries</caption>
    </div>';

    // Add to table of contents
    $mpdf->TOC_Entry('Section '. $group_id .': '. $group_title, 0);

    // Render HTML
    $mpdf->WriteHTML($dcr_report_table);

    if ($field_group !== end($questions)) {
        // Insert page break
        $mpdf->AddPage(); 
    }
}

