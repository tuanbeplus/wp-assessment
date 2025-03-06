<?php
/**
 * The template for displaying Draft Preliminary Report PDF - Saturn
 *
 * @author Tuan
 */

// Exit if required data is missing
if (empty($questions) || empty($reorganize_quizzes)) {
    return;
}

// Get the status of all quizzes for this submission
$all_quizzes_status = get_post_meta($submission_id, 'quizzes_status', true);

// Loop through each group of questions
foreach ($questions as $group_id => $field_group) {
    // Get the group title and sub-questions
    $group_title = wpa_stripslashes_string($field_group['title']) ?? '';
    $sub_questions = $field_group['list'] ?? [];
    $count_quiz = 0;
    $table_row_html = '';

    // Loop through each sub-question
    foreach ($sub_questions as $sub_id => $sub_field) {
        // Get relevant data for the current sub-question
        $current_quiz_rows = $reorganize_quizzes[$group_id][$sub_id] ?? [];
        $documents_uploaded = $azure_documents_uploaded[$group_id][$sub_id] ?? [];
        $feedbacks_list = $assessor_feedbacks[$group_id][$sub_id] ?? [];
        $meta_quiz_status = $all_quizzes_status[$group_id][$sub_id]['meta_status'] ?? '';
        $meta_status_time = $all_quizzes_status[$group_id][$sub_id]['datetime'] ?? '';

        // Process only if there are quiz rows to display
        if (!empty($current_quiz_rows)) {
            // Initialize variables
            $wp_user_id = null;
            $first_name = '';
            $last_name = '';
            $user_comment_entries = '';
            $documents_list = '';
            $assessor_comment_entries = '';
            $status_entries = '';
            $submission_ids = '';
            $latest_row_time = $current_quiz_rows[0]->time ?? '';

            // Loop through each row of the current quiz
            foreach ($current_quiz_rows as $row) {
                // Get user details
                $wp_user_id = get_current_user_by_salesforce_id($row->user_id) ?? '';
                $first_name = get_user_meta($wp_user_id, 'first_name', true);
                $last_name = get_user_meta($wp_user_id, 'last_name', true);

                // Compile user comments
                $user_comment_entries .=
                '<tr>
                    <td' . ($row === end($current_quiz_rows) ? ' style="border-bottom:none;"' : '') . '>
                        <strong style="font-family:avenir-heavy;">'. $first_name .' '. $last_name .'</strong> - '. date("M d Y H:i a", strtotime($row->time)) .'<br>'. wp_unslash($row->description).
                    '</td>
                </tr>';

                // Check and style quiz status
                if (wpa_convert_to_slug($row->status) === 'accepted' || wpa_convert_to_slug($row->status) === 'criteria-satisfied') {
                    $status_entries = '<li style="color:#007000;">'. ucwords($row->status) .'</li> on '. date("M d Y H:i a", strtotime($row->time));
                }

                // Record submission dates
                $submission_ids .= '<li>'. get_the_date('M d Y H:i a', $row->submission_id) .'</li><br>';
            }

            // Check meta quiz status if available
            if (!empty($meta_quiz_status)) {
                if (wpa_convert_to_slug($meta_quiz_status) === 'accepted' || wpa_convert_to_slug($meta_quiz_status) === 'criteria-satisfied') {
                    $status_entries = '<li style="color:#007000;">'. ucwords($meta_quiz_status) .'</li> on '. $meta_status_time;
                }
            }

            // Compile documents uploaded
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

            // Compile assessor feedbacks
            if (!empty($feedbacks_list)) {
                $feedbacks_list = array_reverse($feedbacks_list) ?? [];
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

            // Build the table row for this sub-question
            $table_row_html .=
            '<tr>
                <td>'. $group_title .'</td>
                <td>'. $group_id .'.'. $sub_id .'</td>
                <td><ul>'. $submission_ids .'</ul></td>
                <td class="no-padding"><table class="dcr-table">'. $user_comment_entries .'</table></td>
                <td class="no-padding"><table class="dcr-table">'. $documents_list .'</table></td>
                <td class="no-padding"><table class="dcr-table">'. $assessor_comment_entries .'</table></td>
                <td><ul>'. $status_entries .'</ul></td> 
            </tr>';
            $count_quiz++;
        }
    }

    // If no quizzes were found for this group, show a placeholder row
    if ($count_quiz <= 0) {
        $table_row_html = '<tr><td colspan="7" style="text-align:center;">No data available.</td></tr>';
    }
    
    // Create the HTML table for this section
    $dcr_report_table = 
    '<div class="page">
        <h2>Section '. $group_id .': '. $group_title .'</h2>
        <table class="recom-table dcr-table" width="100%">
            <tr>
                <th width="10%">Section <br> Name</th>
                <th width="9%">Section <br> Number</th>
                <th width="11%">Submited on</th>
                <th width="20%">User Name & Comment Entries</th>
                <th width="18%">Documents Upload</th>
                <th width="20%">Assessor Name & Comment Entries</th>
                <th width="12%">Criteria Status & Date of Entry</th>
            </tr>'
            . $table_row_html .
        '</table>
        <caption>Table '. $group_id .' - '. $group_title .' entries</caption>
    </div>';

    // Add this section to the table of contents
    $mpdf->TOC_Entry('Section '. $group_id .': '. $group_title, 0);

    // Render the HTML for this section in the PDF
    $mpdf->WriteHTML($dcr_report_table);

    // Add a page break if this isn't the last section
    if ($field_group !== end($questions)) {
        $mpdf->AddPage(); 
    }
}