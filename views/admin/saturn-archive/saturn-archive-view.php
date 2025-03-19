<?php 
/**
 * Template Saturn Archive view
 *
 * @author Tuan
 */

$archive = new WPA_Saturn_Archive();
$all_client_orgs = $archive->get_all_existing_client_orgs();
$achived_orgs = $archive->get_all_archived_client_orgs();
$logs_file_path = wp_upload_dir()['baseurl'] . '/saturn-archive/saturn-archive-history.txt?' . time();
?>
<?php if ($_GET['action'] === 'view-history'): ?>
<div class="saturn-archive-page wrap">
    <div class="top-bar">
        <h1>Saturn Archive Activity History</h1>
        <a id="btn-back-to-saturn-archive" class="button button-medium" 
            href="<?php echo strtok( $_SERVER['REQUEST_URI'], '?') . '?page=saturn-archive'; ?>">
            <span><i class="fa-solid fa-arrow-left"></i></span>
            <span>Back to Saturn Archive</span>
        </a>
    </div>
    <div class="history-content"> <?php echo file_get_contents($logs_file_path); ?></div>
</div>
<?php else: ?>
<div class="saturn-archive-page wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <form action="options.php" method="post">
        <table class="form-table">
            <tr>
                <th><label for="select_org">Select an organisation to archive/delete:</label></th>
                <td>
                    <select name="select_org" id="select_org">
                        <option value="">Choose organisation</option>
                    <?php foreach($all_client_orgs as $org): ?>
                        <option value="<?php echo $org['Id'] ?>" ><?php echo $org['Name'] ?></option>
                    <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        
        <div class="org-table-wrapper">
            <div class="wpa-spinner"></div>
            <div id="org-data-result">
                <h3>Organisation: </h3>
                <table class="widefat placeholder">
                    <thead>
                        <tr>
                            <th>Contacts</th>
                            <th>Index Submissions</th>
                            <th>DCR Submissions</th>
                            <th>Index Reports</th>
                            <th>DCR Reports</th>
                            <th>Documents uploaded</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="form-actions">
            <div>
                <button id="btn-archive-org" class="button button-primary button-large">
                    <span><i class="fa-solid fa-box-archive"></i></span>
                    <span>Archive</span>
                </button>
                <button id="btn-delete-org" class="button button-large">
                    <span><i class="fa-solid fa-trash"></i></span>
                    <span>Delete</span>
                </button>
            </div>
            <a id="btn-view-history" class="button button-medium" 
                href="<?php echo $_SERVER['REQUEST_URI'] . '&action=view-history'; ?>">
                <span><i class="fa-solid fa-rotate-left"></i></span>
                <span>View History</span>
            </a>
        </div>
    </form>

    <div class="archive-tables">
        <div class="archived-orgs">
            <h3>Archived Organisations</h3>
            <table id="archived-orgs-table" class="widefat">
                <thead>
                    <tr>
                        <th>#</th>
                        <th class="btn-sort-by-name">Organisation <span class="icon"><i class="fa-solid fa-sort"></i></span></th>
                        <th class="btn-sort-by-time">Archived on <span class="icon"><i class="fa-solid fa-sort"></i></span></th>
                        <th>Archived by</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($achived_orgs)): 
                    // Sort the array before the loop
                    usort($achived_orgs, function($a, $b) {
                        return strcmp($a['org_name'], $b['org_name']);
                    });
                    ?>
                    <?php foreach ($achived_orgs as $index => $row): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $row['org_name'] ?? ''; ?></td>
                        <td><?php echo date("M d Y, H:i a", strtotime($row['archived_date'])) ?? ''; ?></td>
                        <td><?php echo $row['archived_by_name'] ?? ''; ?></td>
                        <td>
                            <button class="btn-restore-archived-org" data-row-id="<?php echo $row['id'] ?? ''; ?>">Restore</button>
                            <button class="btn-delete-archived-org" data-row-id="<?php echo $row['id'] ?? ''; ?>">Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>