<?php

defined('BASEPATH') or exit('No direct script access allowed');

$this->ci->load->model('gdpr_model');
$lockAfterConvert = get_option('lead_lock_after_convert_to_customer');
$has_permission_delete = has_permission('leads', '', 'delete');
$custom_fields = get_table_custom_fields('leads');
$consentLeads = get_option('gdpr_enable_consent_for_leads');
$statuses = $this->ci->leads_model->get_status();

$aColumns = [
    '1',
    'tblleads.id as id',
    'tblleads.name as name',
];
if (is_gdpr() && $consentLeads == '1') {
    $aColumns[] = '1';
}
$aColumns = array_merge($aColumns, ['company',
    'tblleads.email as email',
    'tblleads.phonenumber as phonenumber',
//    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM tbltags_in JOIN tbltags ON tbltags_in.tag_id = tbltags.id WHERE rel_id = tblleads.id and rel_type="lead" ORDER by tag_order ASC LIMIT 1) as tags',
    'tblleads.description as description',
    'firstname as assigned_firstname',
    'tblleadsstatus.name as status_name',
    'tblleads.date as date',
    'lastcontact',
    '(SELECT date FROM tblreminders WHERE rel_id = tblleads.id and rel_type="lead" ORDER by date desc LIMIT 1) as reminder_date',
    'tblleads.dateadded as dateadded',

]);

$sIndexColumn = 'id';
$sTable = 'tblleads';

$join = [

    'LEFT JOIN tblstaff ON tblstaff.staffid = tblleads.assigned',
    'LEFT JOIN tblleadsstatus ON tblleadsstatus.id = tblleads.status',
    'LEFT JOIN tblleadssources ON tblleadssources.id = tblleads.source',
    'LEFT JOIN tblfiles ON tblfiles.rel_id = tblleads.id AND tblfiles.rel_type = "lead_profile"',

];

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblleads.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where = [];
$filter = false;

if ($this->ci->input->post('custom_view')) {
    $filter = $this->ci->input->post('custom_view');
    if ($filter == 'lost') {
        array_push($where, 'AND lost = 1');
    } elseif ($filter == 'junk') {
        array_push($where, 'AND junk = 1');
    } elseif ($filter == 'not_assigned') {
        array_push($where, 'AND assigned = 0');
    } elseif ($filter == 'contacted_today') {
        array_push($where, 'AND lastcontact LIKE "' . date('Y-m-d') . '%"');
    } elseif ($filter == 'created_today') {
        array_push($where, 'AND dateadded LIKE "' . date('Y-m-d') . '%"');
    } elseif ($filter == 'public') {
        array_push($where, 'AND is_public = 1');
    } elseif (_startsWith($filter, 'consent_')) {
        array_push($where, 'AND tblleads.id IN (SELECT lead_id FROM tblconsents WHERE purpose_id=' . strafter($filter, 'consent_') . ' and action="opt-in" AND date IN (SELECT MAX(date) FROM tblconsents WHERE purpose_id=' . strafter($filter, 'consent_') . ' AND lead_id=tblleads.id))');
    }
}

if (!$filter || ($filter && $filter != 'lost' && $filter != 'junk')) {
    array_push($where, 'AND lost = 0 AND junk = 0');
}

if (has_permission('leads', '', 'view') && $this->ci->input->post('assigned')) {
    array_push($where, 'AND assigned =' . $this->ci->input->post('assigned'));
}

if ($this->ci->input->post('status')
    && count($this->ci->input->post('status')) > 0
    && ($filter != 'lost' && $filter != 'junk')) {
    array_push($where, 'AND status IN (' . implode(',', $this->ci->input->post('status')) . ')');
}

if ($this->ci->input->post('source')) {
    array_push($where, 'AND source =' . $this->ci->input->post('source'));
}

if (!has_permission('leads', '', 'view')) {
    array_push($where, 'AND (assigned =' . get_staff_user_id() . ' OR addedfrom = ' . get_staff_user_id() . ' OR is_public = 1)');
}

$aColumns = do_action('leads_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}
$additionalColumns = do_action('leads_table_additional_columns_sql', [
    'junk',
    'lost',
    'color',
    'status',
    'assigned',
    'lastname as assigned_lastname',
    'tblleads.addedfrom as addedfrom',
    '(SELECT count(leadid) FROM tblclients WHERE tblclients.leadid=tblleads.id) as is_converted',
    'zip',
]);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns);

$output = $result['output'];
$rResult = $result['rResult'];


foreach ($rResult as $aRow) {
    $row = [];

    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';

    $hrefAttr = 'href="' . admin_url('leads/index/' . $aRow['id']) . '" onclick="init_lead(' . $aRow['id'] . ');return false;"';
    $row[] = '<a ' . $hrefAttr . '>' . $aRow['id'] . '</a>';

    $nameRow = '<a ' . $hrefAttr . '>' . $aRow['name'] . '</a>';

    $nameRow .= '<div class="row-options">';
    $nameRow .= '<a ' . $hrefAttr . '>' . _l('view') . '</a>';

    $locked = false;

    if ($aRow['is_converted'] > 0) {
        $locked = ((!is_admin() && $lockAfterConvert == 1) ? true : false);
    }

    if (!$locked) {
        $nameRow .= ' | <a href="' . admin_url('leads/index/' . $aRow['id'] . '?edit=true') . '" onclick="init_lead(' . $aRow['id'] . ', true);return false;">' . _l('edit') . '</a>';
    }

    if ($aRow['addedfrom'] == get_staff_user_id() || $has_permission_delete) {
        $nameRow .= ' | <a href="' . admin_url('leads/delete/' . $aRow['id']) . '" class="_delete text-danger">' . _l('delete') . '</a>';
    }
    $nameRow .= '</div>';


    $row[] = $nameRow;

    if (is_gdpr() && $consentLeads == '1') {
        $consentHTML = '<p class="bold"><a href="#" onclick="view_lead_consent(' . $aRow['id'] . '); return false;">' . _l('view_consent') . '</a></p>';
        $consents = $this->ci->gdpr_model->get_consent_purposes($aRow['id'], 'lead');

        foreach ($consents as $consent) {
            $consentHTML .= '<p style="margin-bottom:0px;">' . $consent['name'] . (!empty($consent['consent_given']) ? '<i class="fa fa-check text-success pull-right"></i>' : '<i class="fa fa-remove text-danger pull-right"></i>') . '</p>';
        }
        $row[] = $consentHTML;
    }
    $row[] = $aRow['company'];

    $row[] = ($aRow['email'] != '' ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');

    $row[] = ($aRow['phonenumber'] != '' ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');


    /*$lead_profileOutput = '';
    if (!empty($aRow['lead_profile_attachment'])) {


        $path = get_upload_path_by_type('lead') . '/' . $aRow['id'] . '/' . $aRow['lead_profile_attachment'];

        $href_url = site_url('download/file/lead_attachment/' . $aRow['lead_profile_attachment_id'] . '/' . $aRow['lead_profile_attachment']);

        $img_url = site_url('download/preview_image?path=' . protected_file_url_by_path($path, true) . '&type=' . $aRow['filetype']);


        $lead_profileOutput = '<div class="preview-image"><a href="' . $href_url . '" target="_blank" data-lightbox="task-attachment" class=""><img src="' . $img_url . '" class="img img-responsive" width="100px" height="80px"></a></div>';


    }
    $row[] = $lead_profileOutput;*/
//    $row[] .= render_tags($aRow['tags']);
    $row[] = $aRow['description'];
    $assignedOutput = '';
    if ($aRow['assigned'] != 0) {
        $full_name = $aRow['assigned_firstname'] . ' ' . $aRow['assigned_lastname'];

        $assignedOutput = '<a data-toggle="tooltip" data-title="' . $full_name . '" href="' . admin_url('profile/' . $aRow['assigned']) . '">' . staff_profile_image($aRow['assigned'], [
                'staff-profile-image-small',
            ]) . '</a>';

        // For exporting
        $assignedOutput .= '<span class="hide">' . $full_name . '</span>';
    }


    $row[] = $assignedOutput;


    if ($aRow['status_name'] == null) {
        if ($aRow['lost'] == 1) {
            $outputStatus = '<span class="label label-danger inline-block">' . _l('lead_lost') . '</span>';
        } elseif ($aRow['junk'] == 1) {
            $outputStatus = '<span class="label label-warning inline-block">' . _l('lead_junk') . '</span>';
        }
    } else {
        $outputStatus = '<span class="inline-block label label-' . (empty($aRow['color']) ? 'default' : '') . '" style="color:' . $aRow['color'] . ';border:1px solid ' . $aRow['color'] . '">' . $aRow['status_name'];
        if (!$locked) {
            $outputStatus .= '<div class="dropdown inline-block mleft5 table-export-exclude">';
            $outputStatus .= '<a href="#" style="font-size:14px;vertical-align:middle;" class="dropdown-toggle text-dark" id="tableLeadsStatus-' . $aRow['id'] . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">';
            $outputStatus .= '<span data-toggle="tooltip" title="' . _l('ticket_single_change_status') . '"><i class="fa fa-caret-down" aria-hidden="true"></i></span>';
            $outputStatus .= '</a>';

            $outputStatus .= '<ul class="dropdown-menu dropdown-menu-right" aria-labelledby="tableLeadsStatus-' . $aRow['id'] . '">';
            foreach ($statuses as $leadChangeStatus) {
                if ($aRow['status'] != $leadChangeStatus['id']) {
                    $outputStatus .= '<li>
                  <a href="#" onclick="lead_mark_as(' . $leadChangeStatus['id'] . ',' . $aRow['id'] . '); return false;">
                     ' . $leadChangeStatus['name'] . '
                  </a>
               </li>';
                }
            }
            $outputStatus .= '</ul>';
            $outputStatus .= '</div>';
        }
        $outputStatus .= '</span>';
    }

    $row[] = $outputStatus;

//    $row[] = $aRow['source_name'];

    $row[] = ($aRow['date'] == '0000-00-00 00:00:00' || !is_date($aRow['date']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['date']) . '" class="text-has-action">' . _dt($aRow['date']) . '</span>');
    $row[] = ($aRow['lastcontact'] == '0000-00-00 00:00:00' || !is_date($aRow['lastcontact']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['lastcontact']) . '" class="text-has-action">' . _dt($aRow['lastcontact']) . '</span>');
    $row[] = ($aRow['reminder_date'] == '0000-00-00 00:00:00' || !is_date($aRow['reminder_date']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['reminder_date']) . '" class="text-has-action">' . _dt($aRow['reminder_date']) . '</span>');

    $row[] = ($aRow['dateadded'] == '0000-00-00 00:00:00' || !is_date($aRow['dateadded']) ? '' : '<span data-toggle="tooltip" data-title="' . _dt($aRow['dateadded']) . '" class="text-has-action">' . time_ago($aRow['dateadded']) . '</span>');

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook_data = do_action('leads_table_row_data', [
        'output' => $row,
        'row' => $aRow,
    ]);

    $row = $hook_data['output'];

    $options = '';

    if ($aRow['addedfrom'] == get_staff_user_id() || $has_permission_delete) {
        $options .= icon_btn('leads/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }

    $row['DT_RowId'] = 'lead_' . $aRow['id'];

    if ($aRow['assigned'] == get_staff_user_id()) {
        $row['DT_RowClass'] = 'alert-info';
    }

    if (isset($row['DT_RowClass'])) {
        $row['DT_RowClass'] .= ' has-row-options';
    } else {
        $row['DT_RowClass'] = 'has-row-options';
    }

    $output['aaData'][] = $row;
}
