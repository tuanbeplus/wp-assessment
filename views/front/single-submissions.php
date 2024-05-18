<?php
/**
 * The template for displaying single submission posts
 */

get_header();
global $wpdb;
$main = new WP_Assessment();
$subs_id = get_the_ID();
$assessment_total_score = get_post_meta($subs_id, 'assessment_total_score', true);
$assessment_total_point = get_post_meta($subs_id, 'assessment_total_point', true);
$total_submission_score = get_post_meta($subs_id, 'total_submission_score', true);
$total_submission_score = is_int($total_submission_score) ? $total_submission_score : 'Pending';
$assessment_id = get_post_meta($subs_id, 'assessment_id', true);
$assessment_questions_data = get_post_meta($assessment_id, 'question_group_repeater', true);
$assessment_questions_data = $main->wpa_unserialize_metadata($assessment_questions_data);
$question_templates = get_post_meta($assessment_id, 'question_templates', true);
$report_id = get_post_meta($subs_id, 'report_id', true);
$user_id = get_post_meta($subs_id, 'user_id', true);
$assessment_terms = get_assessment_terms($assessment_id);
$table_name = $main->get_quiz_submission_table_name($assessment_id);
$sql_query = "SELECT * FROM $table_name WHERE submission_id = '{$subs_id}'";
$submission_data = $wpdb->get_results($sql_query);

$invalid_answers = array();

$submission_data = json_encode($submission_data);
$submission_data_arr = json_decode($submission_data, true);

$self_assessed_score = $main->get_self_assessed_score($assessment_id, $submission_data_arr);

$count_quiz = count($submission_data_arr);

for($i = 0; $i < $count_quiz; $i++) {
	if ($submission_data_arr[$i]['answers'] == null) {
		$invalid_answers[$i] = $submission_data_arr[$i]['parent_id'];
	}
}
$invalid_answers = array();
?>

<section id="primary">
	<div class="container holder">
			<div class="submission-success-wrapper">
				<div class="submission-content">
				<?php
				 if ( current_user_can( 'administrator' ) || $user_id == $_COOKIE['userId'] ) {
				?>
				<h1><?php echo get_the_title(); ?></h1>
				<div>
					<p>Thank you for the recent submission on <span style="font-family: 'Avenir-Medium';"><?php echo get_the_title($assessment_id); ?></span></p>
					<p>We appreciate the time and effort you have put into this and your commitment to building a disability confident Australia.</p>
					<h3>
						<?php if (in_array('self-assessed', $assessment_terms)): ?>
							Your Self-Assessed Score: 
							<span class="status" style="font-family: Avenir-Heavy;">
								<?php echo $self_assessed_score .' out of 100'; ?>
							</span>
						<?php else: ?>
							Your assessment outcome: 
							<span class="status" style="font-family: Avenir-Heavy;">
								<?php echo $total_submission_score; ?>
							</span>
						<?php endif; ?>
					</h3>
					<?php if ($invalid_answers): ?>
						<p><strong>Sections that you did not answer:</strong></p>
						<ul class="quiz-invalid-answer">
							<?php foreach ($assessment_questions_data as $group_id => $sub_field): ?>
								<?php if (in_array($group_id, $invalid_answers)): ?>
									<li class="item-section">
										<?php echo 'Section '. $group_id .':'; ?>
										<?php 
											for($i = 0; $i < $count_quiz; $i++) {
												if ($submission_data_arr[$i]['answers'] == null) {
													if ($group_id == $submission_data_arr[$i]['parent_id']) {
														echo '<span class="item-quiz">'. $group_id .'.'. $submission_data_arr[$i]['quiz_id'] .'</span>';
													}
												}
											}
										?>
									</li>
								<?php endif; ?>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
					<?php if($report_id == 0): ?>
						<!-- <p>
							Preliminary Report: 
							<a href="<?php// echo get_the_permalink($report_id); ?>"><?php// echo get_the_title($report_id); ?></a>
						</p> -->
					<?php endif; ?>

					<p>The Australian Network on Disability team will be in contact with you once evidence/this submission has been reviewed (for Index and Disability Confident Recruiter).*</p>
					<p>If you have any questions at all, please reach out to your key contact person.</p>
					<p>*Quick10 are self-assessments only and organisations will not be contacted about results.</p>
					<p><strong>Contact us</strong></p>
					<a href="mailto:info@and.org.au">info@and.org.au</a>
					<a href="tel:(02) 8270 9200">(02) 8270 9200</a>

					<?php //if (!in_array('index', $assessment_terms)): ?>
						<!-- <a id="printSubmissionEntry" href="#Print">Print Preliminary Report (.PDF)</a> -->
					<?php //endif; ?>
				</div>
				<div class="wrapperTablePrint">
					<img id="logoPrintPage" style="margin: 0 auto 20px;display:block" src="<?php echo get_template_directory_uri().'/assets/imgs/logo.svg'; ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
					
					<div id="printSubmissionHtml" class="wrap-print">
						<h2><?php echo get_the_title(); ?></h2>

						<table style='width:100%;'>
							<tbody>
								<?php
								if ($submission_data_arr && is_array($submission_data_arr)) :
									foreach ($submission_data_arr as $field) :
									$answers = [];
									if ($field['answers']) $answers = json_decode($field['answers']);
									$parent_id = $field['parent_id'];
									$quiz_id = $field['quiz_id'];
									?>
									<tr>
										<td style="width: 80px!important; padding:12px 10px;vertical-align: baseline;border-bottom:1px solid #DFDFDF;">
											<?php if ($question_templates == 'Simple Assessment'): ?>
												<h4>Quiz <?php echo $quiz_id; ?></h4>
											<?php endif; ?>

											<?php if ($question_templates == 'Comprehensive Assessment'): ?>
												<h4>Quiz <?php echo $parent_id. '.' .$quiz_id; ?></h4>
											<?php endif; ?>											
										</td>
										<td style="padding:12px 10px;vertical-align: baseline;border-bottom:1px solid #DFDFDF;">
											<?php if (is_array($answers)) : ?>
												<p style="margin-bottom:8px;"><strong>Answer</strong></p>
												<?php foreach ($answers as $answer) : ?>
													<span><?php echo $answer->title; ?></span>
												<?php endforeach; ?>
											<?php endif; ?>
										</td>
										<td style="padding:12px 10px;vertical-align: baseline;border-bottom:1px solid #DFDFDF;">
												<p style="margin-bottom:8px;"><strong>Your comment</strong></p>
												<p style="margin-top:0;" class="description-thin"><?php echo $field['description']; ?></p>
										</td>
									</tr>
									<?php
									endforeach;
								endif; ?>
							</tbody>
						</table>
					</div>
				</div>
				</div>
				<div class="sidebar">
					<div class="inner">
						<p class="heading">In this section</p>
						<ul>
							<li>
								<a href="/dashboard/" class="circle dark-red">
									<span class="material-icons">arrow_forward</span>
									<span class="text">Dashboard</span>
								</a>
							</li>
						</ul>
					</div>
				</div>
				<?php
					} 
					else {
						echo "<h3 style='text-align:center;'>Oops! You can't access this submission.</h3>";
					}
				?>
			</div>
		</div>
	<style media="screen">
		#printSubmissionEntry {
		    display: block; width: fit-content;
		    margin-top: 32px; padding: 12px 40px;
		    background-color: #663077; color: #fff;
		    text-decoration: none; border-radius: 24px;
		    transition: all 0.3s;
		}
		#printSubmissionEntry:hover {
		    background-color: #a22f2c;
		}
		#printSubmissionTable {
			border-radius: 16px; background: #dfdfdf;
			overflow: hidden; border: 1px solid #dfdfdf;
		}
		#printSubmissionTable .heading {
			font-size: 14px; font-weight: bold;
			text-shadow: 0 1px 0 #fff;
			text-align: left; padding: 12px;
		}
		#printSubmissionTable .label {
			font-weight: 700; background-color: #eaf2fa;
			border-bottom: 1px solid #fff;
			line-height: 150%; padding: 7px 7px;
		}
		#printSubmissionTable th{ padding: 10px; }
		#printSubmissionTable .label td { width: 100% !important; }
		#printSubmissionTable .value {
			border-bottom: 1px solid #dfdfdf;
			padding: 7px 7px 7px 40px;
			line-height: 150%; background: #fff;
		}
		.entry-view-section-break{
			font-size: 14px; font-weight: 700;
			background-color: #eee; padding: 7px 7px;
			border-bottom: 1px solid #dfdfdf;
		}
		.wrapperTablePrint{
			display: none;
			position: absolute; 
			z-index: -999;
			top: 0; 
			right: -100%;
		}
	</style>

	<script>
		jQuery(document).ready( function() {
			function printSubmissionData(){
				var contentToPrint=document.getElementById("printSubmissionHtml");
				var logoToPrint = document.getElementById("logoPrintPage");
				newWin= window.open("Submission detail");
				newWin.document.write(logoToPrint.outerHTML);
				newWin.document.write(contentToPrint.outerHTML);
				newWin.print();
				newWin.close();

				return true;
			}
			jQuery('a#printSubmissionEntry').on('click',function(e){
				e.preventDefault();
				printSubmissionData();
			});
		});
	</script>
	
</section><!-- #primary -->

<?php
    get_footer();
