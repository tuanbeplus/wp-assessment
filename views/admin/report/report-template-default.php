
<?php ob_start(); 

global $sub_id;
$post_id = $sub_id;

$post_meta = get_post_meta($post_id);
$user_id = get_post_meta($post_id, 'user_id', true);
$assessment_id = get_post_meta($post_id, 'assessment_id', true);
$assessment_meta = get_post_meta($assessment_id, 'question_templates', true);
$report_template_content = get_post_meta($assessment_id, 'report_template_content', true);
$report_recommendation = get_post_meta($assessment_id, 'report_recommendation', true);
$executive_summary = get_post_meta($assessment_id, 'executive_summary', true);
$evalution_findings = get_post_meta($assessment_id, 'evalution_findings', true);
$assessment_total_score = get_post_meta($post_id, 'assessment_total_score', true);

// Organization data
$sf_organization_name = $_COOKIE['sf_organization_name'];
$sf_membership_level = $_COOKIE['sf_membership_level'];
$sf_year_member_in = $_COOKIE['sf_year_member_in'];

$main = new WP_Assessment();
$questions = get_post_meta($assessment_id, 'question_group_repeater', true);
$questions = $main->wpa_unserialize_metadata($questions);
?>

<div class="report-content-container">
    <h2>Message from the CEO</h2>
    <p class="placeholder-input">[Moderator input]</p>
    
    <h2><strong>Executive Summary</strong></h2>
    <span style="color: #ff0000;">[Organisation]</span> joined the Australian Network on Disability (AND) as a <span
        style="color: #ff0000;">[membership level]</span> Member in <span style="color: #ff0000;">[Year]</span>. <span
        style="color: #ff0000;">[Organisation]</span> has used their membership to…
    <ul>
        <li>[Provide overall summary of the organisation's performance and benchmarking</li>
        <li>Highlight the key areas that they perform very well in (i.e. Programmatic or Strategic)</li>
        <li>Highlight the key areas for opportunities to increase disability confidence (i.e. Not Participating or Basic
            (compliant), and/or opportunities to move from Programmatic to Strategic)</li>
        <li>Speak to Innovation and where the organisation could be creating new ways to engage and support their
            employees and customers with disability noting that it does not matter what point of their disability
            confidence journey they are at.]</li>
    </ul>
    AND would like to acknowledge the commitment that <span style="color: #ff0000;">[Organisation]</span> has shown in
    submitting for the 2021 Index. AND recognise the time and effort that goes into the submission process. <span
        style="color: #ff0000;">[Organisation]</span>'s deidentified data has been included in the Access and Inclusion
    Index 2021 Benchmark data and contributes to reflecting the Australian best practice standard for the inclusion of
    people with disability in business.

    We commend <span style="color: #ff0000;">[Organisation]</span> for your participation and look forward to working
    with you to achieve your access and inclusion goals.
    
    <h2>Key Recommendations</h2>
    <p>The below table highlights the top priorities/opportunities identified through the evaluation process for each key area.</p>
    <table>
        <thead>
            <tr>
                <th width="35%"><strong>Key Area</strong></th>
                <th width="65%"><strong>Priorities</strong></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $field): ?>
                <tr>
                    <td><?php echo $field['title']; ?></td>
                    <td><span style="color: #ff0000;">[Priorities]</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    Table 1- Key Recommendations

    <h2>Part A - Organisational Dashboard</h2>
    <p>This section contains an overview of your organisation's performance across the ten key areas and the benchmarked 
        data against all participating organisations in 2020.</p>
    <h3>Total Index Score</h3>
    <table>
        <tbody>
            <tr>
                <th></th>
                <th>Organisation self-assessment (/100)</th>
                <th>AND assessment and final score (/100)</th>
                <th>Rank (/28)</th>
                <th>Average of other organisations</th>
            </tr>
            <tr>
                <td>Total Index score</td>
                <td><span style="color: #ff0000;"><strong>[x]</strong></span></td>
                <td><?php echo $assessment_total_score; ?></td>
                <td><span style="color: #ff0000;"><strong>[x]</strong></span></td>
                <td><span style="color: #ff0000;"><strong>[x]</strong></span></td>
            </tr>
        </tbody>
    </table>
    Table 2 - Total Index Score and Benchmark

    <span style="color: #ff0000;">[Organisation]</span> scored <span style="color: #ff0000;">[X]</span>/100 in the
    Access and Inclusion Index, which ranked <span style="color: #ff0000;">[X]</span> overall. The average Access and
    Inclusion Index score for participating organisations is <span style="color: #ff0000;">[X]</span> overall.

    The relative performance compared to other organisations is shown in Figure 1 below in the solid bar.

    <img class="aligncenter wp-image-119"
        src="http://quiz.pluton.ltd/wp-content/uploads/2023/01/bar-chart-with-distribution-of-scores-and-solid-ba.jpeg"
        alt="Bar chart with distribution of scores and solid bar to represent organisation score" width="598"
        height="432" />

    Figure 1 - Distribution of Index Scores
    <h3>Self-assessed score and final AND score</h3>
    The self-assessed score and AND score have been provided as maturity levels across the ten key areas (Table 3) and
    percentage scores (Table 4). Please note the percentage scores in Table 3 have been rounded up.
    <table>
        <tbody>
            <tr>
                <th width="33%">Key Area</th>
                <th width="33%">Organisation self-assessment</th>
                <th width="33%">AND assessed level</th>
            </tr>
            <?php foreach ($questions as $field): ?>
                <tr>
                    <td><?php echo $field['title']; ?></td>
                    <td><span style="color: #ff0000;">[x.x]</span></td>
                    <td><span style="color: #ff0000;">[x.x]</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    Table 3 - Scorecard for ten key areas shown as maturity levels
    <table>
        <tbody>
            <tr>
                <th>Key Area</th>
                <th>Organisation self-assessment</th>
                <th>AND assessed score</th>
            </tr>
            <?php foreach ($questions as $field): ?>
                <tr>
                    <td><?php echo $field['title']; ?></td>
                    <td><span style="color: #ff0000;">[x%]</span></td>
                    <td><span style="color: #ff0000;">[x%]</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    Table 4 - Scorecard for the ten Key Areas shown as percentages

    Your self-assessed score and the AND evaluated score may have differed. This can be attributed to reasons such as:
    <ol>
        <li>Insufficient evidence was provided to accurately validate your self-assessment.</li>
        <li>We could not find the answer within the evidence provided.</li>
        <li>Varying interpretation of the Index questions.</li>
    </ol>
    <h3>Benchmark Results</h3>
    <p>The scorecard in this section is an overview of the final 2020 Index score for your organisation in comparison to
    the performance of all participating organisations with respect to the ten Key Areas. Table 5 below provides an
    overall ranking for your organisation within each area.</p>
    <table>
        <tbody>
            <tr>
                <th>Key Area</th>
                <th>Maturity Level</th>
                <th>Rank (/28)</th>
                <th>Orgs at Level 1</th>
                <th>Orgs at Level 2</th>
                <th>Orgs at Level 3</th>
                <th>Orgs at Level 4</th>
            </tr>
            <?php foreach ($questions as $field): ?>
                <tr>
                    <td><?php echo $field['title']; ?></td>
                    <td><span style="color: #ff0000;">[Level x]</span></td>
                    <td><span style="color: #ff0000;">[x]</span></td>
                    <td><span style="color: #ff0000;">[x]</span></td>
                    <td><span style="color: #ff0000;">[x]</span></td>
                    <td><span style="color: #ff0000;">[x]</span></td>
                    <td><span style="color: #ff0000;">[x]</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    Table 5 - Benchmark results for the ten Key Areas
    <h3>Maturity Level for Framework, Implementation and Review</h3>
    Questions within each of the Key Areas of the Index are grouped into three sections: Framework, Implementation and
    Review. Table 6 below provides an overview of your maturity level for each of the three sections.
    <table>
        <tbody>
            <tr>
                <th>Key Area</th>
                <th>Framework</th>
                <th>Implementation</th>
                <th>Review</th>
                <th>Overall</th>
            </tr>
            <?php foreach ($questions as $field): ?>
                <tr>
                    <td><?php echo $field['title']; ?></td>
                    <td><span style="color: #ff0000;">[Level x]</span></td>
                    <td><span style="color: #ff0000;">[Level x]</span></td>
                    <td><span style="color: #ff0000;">[Level x]</span></td>
                    <td><span style="color: #ff0000;">[Level x]</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    Table 6 - Maturity level for Framework, Implementation and Review
    <h3>Overall Maturity Dashboard</h3>
    <table>
        <tbody>
            <tr>
                <th>Overall Maturity by Key Area</th>
                <th>Your Organisation Maturity Level</th>
                <th>Average Maturity Level - All orgs</th>
                <th>Variance (+/-)</th>
            </tr>
            <?php foreach ($questions as $field): ?>
                <tr>
                    <td><?php echo $field['title']; ?></td>
                    <td><span style="color: #ff0000;">[x.x]</span></td>
                    <td><span style="color: #ff0000;">[x.x]</span></td>
                    <td><span style="color: #ff0000;">[x.x]</span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    Table 7 - Overall maturity level

    <h2>Part B - Evaluation Findings</h2>
    <p>The Access and Inclusion Index comprises of ten key areas determined to drive the greatest benefits for access and 
        inclusion of people with disability. The listing of the ten areas below is hyperlinked for your convenience.</p>
    
    <ol class="report-toc">
    <?php foreach ($questions as $field): ?>
        <?php $question_title_strip = str_replace(' ', '-', $field['title']); ?>
        <li><a href="#<?php echo $question_title_strip; ?>"><?php echo $field['title']; ?></a></li>
    <?php endforeach; ?>
    </ol>

    <p>What follows is an evaluation of your assessment, with particular emphasis given to provide suggestions that support
    your organisation to build disability confidence in identified areas. A detailed overview of your scores and
    benchmark results for each question is tabled in the <a href="#Appendix">Appendix</a>.</p>

    <?php foreach ($questions as $field): ?>
        <?php $question_title_strip = str_replace(' ', '-', $field['title']); ?>
        <h3 id="<?php echo $question_title_strip; ?>"><?php echo $field['title']; ?></h3>
        <p class="placeholder-input">[Moderator input]</p>
    <?php endforeach; ?>
    
    <h2>Next Steps</h2>
    Australian Network on Disability (AND) would like to acknowledge the commitment of <span
        style="color: #ff0000;">[Organisation]</span> in completing the Access and Inclusion Index, and to congratulate
    the contributors on achieving this step.

    The results of this Index will be a useful and insightful tool to enable cross-business discussion and collaboration
    to further progress the access and inclusion goals of <span style="color: #ff0000;">[Organisation]</span>.

    AND welcomes the opportunity to work with <span style="color: #ff0000;">[Organisation]</span> progressing the
    opportunities outlined in this report. We look forward to engaging the business on the results of this Index, and in
    debriefing key stakeholders.

    Congratulations again for participating in the 2020 Access and Inclusion Index and taking this step towards
    advancing the inclusion of people with disability in Australia.

    Best wishes,

    [RM name or The Team from Australian Network on Disability]

    <h2>How to connect with us</h2>
    Phone: 1300 363 645

    Website: <a href="http://www.and.org.au/">www.and.org.au</a>

    Email: <a href="mailto:info@and.org.au">info@and.org.au</a>

    Facebook: <a href="http://www.facebook.com/ANDisability">www.facebook.com/ANDisability</a>

    Twitter: @ANDisability

    LinkedIn: <a href="https://www.linkedin.com/company/andisability/">www.linkedin.com/company/ANDisability</a>

    YouTube: <a href="https://www.youtube.com/channel/UCAwQbz6HEc1gmPjE_elzf7A">Australian Network on Disability</a>

    Head office: Level 3, 80 Clarence Street, Sydney, NSW, 2000

    Postal address: PO Box Q203, QVB Post Office NSW 1230

    <h2>Disclaimer</h2>
    While every effort has been made to ensure that the report is accurate, the Australian Network on Disability makes
    no warranty about its accuracy or completeness.

    To the extent permitted by law, the Australian Network on Disability, its directors, officers, employees and agents
    exclude all liability (whether in negligence or otherwise) for:

    Any error or inaccuracy in, or omission from, the report; and

    Any loss or damage suffered by any person, directly or indirectly, through use of the report, including reliance
    upon the information contained in the report, and any donation decisions made on the basis of its content.

    Copyright Australian Network on Disability 2020

    ACN 605 683 645

    ABN 924 564 573 35

    <h2 id="Appendix">Appendix</h2>

</div>

<?php
return ob_get_clean();