<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers_model extends CRM_Model
{
    private $contact_columns;

    public function __construct()
    {
        parent::__construct();

//        $this->contact_columns = do_action('contact_columns', ['firstname', 'lastname', 'email', 'phonenumber', 'title', 'password', 'send_set_password_email', 'donotsendwelcomeemail', 'permissions', 'direction', 'invoice_emails', 'estimate_emails', 'credit_note_emails', 'contract_emails', 'task_emails', 'project_emails', 'ticket_emails', 'is_primary']);

//        $this->load->model(['client_vault_entries_model', 'client_groups_model', 'statement_model']);
    }

    /**
     * Get client object based on passed clientid if not passed clientid return array of all clients
     * @param  mixed $id    client id
     * @param  array  $where
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        $this->db->select(implode(',', prefixed_table_fields_array('tblsuppliers')));

        $this->db->join('tblcountries', 'tblcountries.country_id = tblsuppliers.country', 'left');
//        $this->db->join('tblcontacts', 'tblcontacts.userid = tblclients.userid AND is_primary = 1', 'left');
        $this->db->where($where);

        if (is_numeric($id)) {
            $this->db->where('tblsuppliers.id', $id);
            $client = $this->db->get('tblsuppliers')->row();
            return $client;
        }

        $this->db->order_by('company', 'asc');

        return $this->db->get('tblsuppliers')->result_array();
    }

    public function search($q)
    {
        $this->db->select('id, company as name');
        $this->db->like('company', $q);

        $items = $this->db->get('tblsuppliers')->result_array();

        return $items;
    }

    /**
     * @param array $_POST data
     * @param client_request is this request from the customer area
     * @return integer Insert ID
     * Add new client to database
     */
    public function add($data, $client_or_lead_convert_request = false)
    {

        // From customer profile register
        if (isset($data['contact_phonenumber'])) {
            $contact_data['phonenumber'] = $data['contact_phonenumber'];
            unset($data['contact_phonenumber']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $data = $this->check_zero_columns($data);

        $data['datecreated'] = date('Y-m-d H:i:s');

        /*if (is_staff_logged_in()) {
            $data['addedfrom'] = get_staff_user_id();
        }*/

        $hook_data = do_action('before_client_added', ['data' => $data]);
        $data      = $hook_data['data'];

        $this->db->insert('tblsuppliers', $data);

        $userid = $this->db->insert_id();
        if ($userid) {
            if (isset($custom_fields)) {
                $_custom_fields = $custom_fields;
                // Possible request from the register area with 2 types of custom fields for contact and for comapny/customer
                if (count($custom_fields) == 2) {
                    unset($custom_fields);
                    $custom_fields['customers']                = $_custom_fields['customers'];
                    $contact_data['custom_fields']['contacts'] = $_custom_fields['contacts'];
                } elseif (count($custom_fields) == 1) {
                    if (isset($_custom_fields['contacts'])) {
                        $contact_data['custom_fields']['contacts'] = $_custom_fields['contacts'];
                        unset($custom_fields);
                    }
                }
                handle_custom_fields_post($userid, $custom_fields);
            }
            /**
             * Used in Import, Lead Convert, Register
             */
            do_action('after_client_added', $userid);
            $log = 'ID: ' . $userid;

            logActivity('New Suppliers Created [' . $log . ']');
        }

        return $userid;
    }

    private function check_zero_columns($data)
    {

        if (isset($data['country']) && $data['country'] == '' || !isset($data['country'])) {
            $data['country'] = 0;
        }

        return $data;
    }

    /**
     * @param  array $_POST data
     * @param  integer ID
     * @return boolean
     * Update client informations
     */
    public function update($data, $id, $client_request = false)
    {
        $affectedRows = 0;
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        $data = $this->check_zero_columns($data);

        $_data = do_action('before_client_updated', [
            'userid' => $id,
            'data'   => $data,
        ]);

        $data = $_data['data'];
        $this->db->where('id', $id);
        $this->db->update('tblsuppliers', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }

        if ($affectedRows > 0) {
            do_action('after_client_updated', $id);
            logActivity('Supplier Updated [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Update contact data
     * @param  array  $data           $_POST data
     * @param  mixed  $id             contact id
     * @param  boolean $client_request is request from customers area
     * @return mixed
     */
    public function update_contact($data, $id, $client_request = false)
    {
        $affectedRows = 0;
        $contact      = $this->get_contact($id);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $this->load->helper('phpass');
            $hasher                       = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
            $data['password']             = $hasher->HashPassword($data['password']);
            $data['last_password_change'] = date('Y-m-d H:i:s');
        }

        $send_set_password_email = isset($data['send_set_password_email']) ? true : false;
        $set_password_email_sent = false;

        $permissions        = isset($data['permissions']) ? $data['permissions'] : [];
        $data['is_primary'] = isset($data['is_primary']) ? 1 : 0;

        // Contact cant change if is primary or not
        if ($client_request == true) {
            unset($data['is_primary']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if ($client_request == false) {
            $data['invoice_emails']     = isset($data['invoice_emails']) ? 1 :0;
            $data['estimate_emails']    = isset($data['estimate_emails']) ? 1 :0;
            $data['credit_note_emails'] = isset($data['credit_note_emails']) ? 1 :0;
            $data['contract_emails']    = isset($data['contract_emails']) ? 1 :0;
            $data['task_emails']        = isset($data['task_emails']) ? 1 :0;
            $data['project_emails']     = isset($data['project_emails']) ? 1 :0;
            $data['ticket_emails']      = isset($data['ticket_emails']) ? 1 :0;
        }

        $hook_data = do_action('before_update_contact', ['data' => $data, 'id' => $id]);
        $data      = $hook_data['data'];

        $this->db->where('id', $id);
        $this->db->update('tblcontacts', $data);

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if (isset($data['is_primary']) && $data['is_primary'] == 1) {
                $this->db->where('userid', $contact->userid);
                $this->db->where('id !=', $id);
                $this->db->update('tblcontacts', [
                    'is_primary' => 0,
                ]);
            }
        }

        if ($client_request == false) {
            $customer_permissions = $this->roles_model->get_contact_permissions($id);
            if (sizeof($customer_permissions) > 0) {
                foreach ($customer_permissions as $customer_permission) {
                    if (!in_array($customer_permission['permission_id'], $permissions)) {
                        $this->db->where('userid', $id);
                        $this->db->where('permission_id', $customer_permission['permission_id']);
                        $this->db->delete('tblcontactpermissions');
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
                foreach ($permissions as $permission) {
                    $this->db->where('userid', $id);
                    $this->db->where('permission_id', $permission);
                    $_exists = $this->db->get('tblcontactpermissions')->row();
                    if (!$_exists) {
                        $this->db->insert('tblcontactpermissions', [
                            'userid'        => $id,
                            'permission_id' => $permission,
                        ]);
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                }
            } else {
                foreach ($permissions as $permission) {
                    $this->db->insert('tblcontactpermissions', [
                        'userid'        => $id,
                        'permission_id' => $permission,
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            if ($send_set_password_email) {
                $set_password_email_sent = $this->authentication_model->set_password_email($data['email'], 0);
            }
        }
        if ($affectedRows > 0 && !$set_password_email_sent) {
            logActivity('Contact Updated [ID: ' . $id . ']');

            return true;
        } elseif ($affectedRows > 0 && $set_password_email_sent) {
            return [
                'set_password_email_sent_and_profile_updated' => true,
            ];
        } elseif ($affectedRows == 0 && $set_password_email_sent) {
            return [
                'set_password_email_sent' => true,
            ];
        }

        return false;
    }

    /**
     * Get single contacts
     * @param  mixed $id contact id
     * @return object
     */
    public function get_contact($id)
    {
        $this->db->where('id', $id);

        return $this->db->get('tblcontacts')->row();
    }

    /**
     * Used to update company details from customers area
     * @param  array $data $_POST data
     * @param  mixed $id
     * @return boolean
     */
    public function update_company_details($data, $id)
    {
        $affectedRows = 0;
        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }
        if (isset($data['country']) && $data['country'] == '' || !isset($data['country'])) {
            $data['country'] = 0;
        }
        if (isset($data['billing_country']) && $data['billing_country'] == '') {
            $data['billing_country'] = 0;
        }
        if (isset($data['shipping_country']) && $data['shipping_country'] == '') {
            $data['shipping_country'] = 0;
        }

        // From v.1.9.4 these fields are textareas
        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);
        if (isset($data['billing_street'])) {
            $data['billing_street'] = trim($data['billing_street']);
            $data['billing_street'] = nl2br($data['billing_street']);
        }
        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }

        $this->db->where('userid', $id);
        $this->db->update('tblclients', $data);
        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            do_action('customer_updated_company_info', $id);
            logActivity('Customer Info Updated From Clients Area [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Get unique staff id's of customer admins
     * @return array
     */
    public function get_customers_admin_unique_ids()
    {
        return $this->db->query('SELECT DISTINCT(staff_id) FROM tblcustomeradmins')->result_array();
    }

    /**
     * Assign staff members as admin to customers
     * @param  array $data $_POST data
     * @param  mixed $id   customer id
     * @return boolean
     */
    public function assign_admins($data, $id)
    {
        $affectedRows = 0;

        if (count($data) == 0) {
            $this->db->where('customer_id', $id);
            $this->db->delete('tblcustomeradmins');
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
            }
        } else {
            $current_admins     = $this->get_admins($id);
            $current_admins_ids = [];
            foreach ($current_admins as $c_admin) {
                array_push($current_admins_ids, $c_admin['staff_id']);
            }
            foreach ($current_admins_ids as $c_admin_id) {
                if (!in_array($c_admin_id, $data['customer_admins'])) {
                    $this->db->where('staff_id', $c_admin_id);
                    $this->db->where('customer_id', $id);
                    $this->db->delete('tblcustomeradmins');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
            foreach ($data['customer_admins'] as $n_admin_id) {
                if (total_rows('tblcustomeradmins', [
                    'customer_id' => $id,
                    'staff_id' => $n_admin_id,
                ]) == 0) {
                    $this->db->insert('tblcustomeradmins', [
                        'customer_id'   => $id,
                        'staff_id'      => $n_admin_id,
                        'date_assigned' => date('Y-m-d H:i:s'),
                    ]);
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                }
            }
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get customer staff members that are added as customer admins
     * @param  mixed $id customer id
     * @return array
     */
    public function get_admins($id)
    {
        $this->db->where('customer_id', $id);

        return $this->db->get('tblcustomeradmins')->result_array();
    }

    /**
     * @param  integer ID
     * @return boolean
     * Delete client, also deleting rows from, dismissed client announcements, ticket replies, tickets, autologin, user notes
     */
    public function delete($id)
    {
        $affectedRows = 0;
        do_action('before_client_deleted', $id);

        $last_activity = get_last_system_activity_id();

        $this->db->where('id', $id);
        $this->db->delete('tblsuppliers');
        if ($this->db->affected_rows() > 0) {
            logActivity('Supplier Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete customer contact
     * @param  mixed $id contact id
     * @return boolean
     */
    public function delete_contact($id)
    {
        $this->db->where('id', $id);
        $result      = $this->db->get('tblcontacts')->row();
        $customer_id = $result->userid;

        do_action('before_delete_contact', $id);

        $last_activity = get_last_system_activity_id();

        $this->db->where('id', $id);
        $this->db->delete('tblcontacts');

        if ($this->db->affected_rows() > 0) {
            if (is_dir(get_upload_path_by_type('contact_profile_images') . $id)) {
                delete_dir(get_upload_path_by_type('contact_profile_images') . $id);
            }

            $this->db->where('contact_id', $id);
            $this->db->delete('tblconsents');

            $this->db->where('contact_id', $id);
            $this->db->delete('tblcustomerfiles_shares');

            $this->db->where('userid', $id);
            $this->db->where('staff', 0);
            $this->db->delete('tbldismissedannouncements');

            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'contacts');
            $this->db->delete('tblcustomfieldsvalues');

            $this->db->where('userid', $id);
            $this->db->delete('tblcontactpermissions');

            $this->db->where('user_id', $id);
            $this->db->where('staff', 0);
            $this->db->delete('tbluserautologin');

            $this->db->select('ticketid');
            $this->db->where('contactid', $id);
            $this->db->where('userid', $customer_id);
            $tickets = $this->db->get('tbltickets')->result_array();

            $this->load->model('tickets_model');
            foreach ($tickets as $ticket) {
                $this->tickets_model->delete($ticket['ticketid']);
            }

            $this->load->model('tasks_model');

            $this->db->where('addedfrom', $id);
            $this->db->where('is_added_from_contact', 1);
            $tasks = $this->db->get('tblstafftasks')->result_array();

            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id'], false);
            }

            // Added from contact in customer profile
            $this->db->where('contact_id', $id);
            $this->db->where('rel_type', 'customer');
            $attachments = $this->db->get('tblfiles')->result_array();

            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            // Remove contact files uploaded to tasks
            $this->db->where('rel_type', 'task');
            $this->db->where('contact_id', $id);
            $filesUploadedFromContactToTasks = $this->db->get('tblfiles')->result_array();

            foreach ($filesUploadedFromContactToTasks as $file) {
                $this->tasks_model->remove_task_attachment($file['id']);
            }

            $this->db->where('contact_id', $id);
            $tasksComments = $this->db->get('tblstafftaskcomments')->result_array();
            foreach ($tasksComments as $comment) {
                $this->tasks_model->remove_comment($comment['id'], true);
            }

            $this->load->model('projects_model');

            $this->db->where('contact_id', $id);
            $files = $this->db->get('tblprojectfiles')->result_array();
            foreach ($files as $file) {
                $this->projects_model->remove_file($file['id'], false);
            }

            $this->db->where('contact_id', $id);
            $discussions = $this->db->get('tblprojectdiscussions')->result_array();
            foreach ($discussions as $discussion) {
                $this->projects_model->delete_discussion($discussion['id'], false);
            }

            $this->db->where('contact_id', $id);
            $discussionsComments = $this->db->get('tblprojectdiscussioncomments')->result_array();
            foreach ($discussionsComments as $comment) {
                $this->projects_model->delete_discussion_comment($comment['id'], false);
            }

            $this->db->where('contact_id', $id);
            $this->db->delete('tblusermeta');

            $this->db->where('(email="' . $result->email . '" OR bcc LIKE "%' . $result->email . '%" OR cc LIKE "%' . $result->email . '%")');
            $this->db->delete('tblemailqueue');

            $this->db->where('email', $result->email);
            $this->db->delete('tblsurveysemailsendcron');

            if (is_gdpr()) {
                $this->db->where('email', $result->email);
                $this->db->delete('tbllistemails');

                if (!empty($result->last_ip)) {
                    $this->db->where('ip', $result->last_ip);
                    $this->db->delete('tblknowledgebasearticleanswers');

                    $this->db->where('ip', $result->last_ip);
                    $this->db->delete('tblsurveyresultsets');
                }

                $this->db->where('email', $result->email);
                $this->db->delete('tblticketpipelog');

                $this->db->where('email', $result->email);
                $this->db->delete('tblemailstracking');

                $this->db->where('contact_id', $id);
                $this->db->delete('tblprojectactivity');

                $this->db->where('(additional_data LIKE "%' . $result->email . '%" OR full_name LIKE "%' . $result->firstname . ' ' . $result->lastname . '%")');
                $this->db->where('additional_data != "" AND additional_data IS NOT NULL');
                $this->db->delete('tblsalesactivity');

                $whereActivityLog = '(description LIKE "%' . $result->email . '%" OR description LIKE "%' . $result->firstname . ' ' . $result->lastname . '%" OR description LIKE "%' . $result->firstname . '%" OR description LIKE "%' . $result->lastname . '%" OR description LIKE "%' . $result->phonenumber . '%"';
                if (!empty($result->last_ip)) {
                    $whereActivityLog .= ' OR description LIKE "%' . $result->last_ip . '%"';
                }
                $whereActivityLog .= ')';
                $this->db->where($whereActivityLog);
                $this->db->delete('tblactivitylog');
            }



            // Delete activity log caused by delete contact function
            if ($last_activity) {
                $this->db->where('id >', $last_activity->id);
                $this->db->delete('tblactivitylog');
            }

            return true;
        }

        return false;
    }

    /**
     * Delete customer attachment uploaded from the customer profile
     * @param  mixed $id attachment id
     * @return boolean
     */
    public function delete_attachment($id)
    {
        $this->db->where('id', $id);
        $attachment = $this->db->get('tblfiles')->row();
        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                $relPath  = get_upload_path_by_type('customer') . $attachment->rel_id . '/';
                $fullPath = $relPath . $attachment->file_name;
                unlink($fullPath);
                $fname     = pathinfo($fullPath, PATHINFO_FILENAME);
                $fext      = pathinfo($fullPath, PATHINFO_EXTENSION);
                $thumbPath = $relPath . $fname . '_thumb.' . $fext;
                if (file_exists($thumbPath)) {
                    unlink($thumbPath);
                }
            }

            $this->db->where('id', $id);
            $this->db->delete('tblfiles');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                $this->db->where('file_id', $id);
                $this->db->delete('tblcustomerfiles_shares');
                logActivity('Customer Attachment Deleted [ID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('customer') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('customer') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    delete_dir(get_upload_path_by_type('customer') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * Get customer default currency
     * @param  mixed $id customer id
     * @return mixed
     */
    public function get_customer_default_currency($id)
    {
        $this->db->select('default_currency');
        $this->db->where('userid', $id);
        $result = $this->db->get('tblclients')->row();
        if ($result) {
            return $result->default_currency;
        }

        return false;
    }

    /**
     *  Get customer billing details
     * @param   mixed $id   customer id
     * @return  array
     */
    public function get_customer_billing_and_shipping_details($id)
    {
        $this->db->select('billing_street,billing_city,billing_state,billing_zip,billing_country,shipping_street,shipping_city,shipping_state,shipping_zip,shipping_country');
        $this->db->from('tblclients');
        $this->db->where('userid', $id);

        $result = $this->db->get()->result_array();
        if (count($result) > 0) {
            $result[0]['billing_street']  = clear_textarea_breaks($result[0]['billing_street']);
            $result[0]['shipping_street'] = clear_textarea_breaks($result[0]['shipping_street']);
        }

        return $result;
    }

    /**
     * Get customer files uploaded in the customer profile
     * @param  mixed $id    customer id
     * @param  array  $where perform where
     * @return array
     */
    public function get_customer_files($id, $where = [])
    {
        $this->db->where($where);
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'customer');
        $this->db->order_by('dateadded', 'desc');

        return $this->db->get('tblfiles')->result_array();
    }

    /**
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update contact status Active/Inactive
     */
    public function change_contact_status($id, $status)
    {
        $hook_data['id']     = $id;
        $hook_data['status'] = $status;
        $hook_data           = do_action('change_contact_status', $hook_data);
        $status              = $hook_data['status'];
        $id                  = $hook_data['id'];
        $this->db->where('id', $id);
        $this->db->update('tblcontacts', [
            'active' => $status,
        ]);
        if ($this->db->affected_rows() > 0) {
            logActivity('Contact Status Changed [ContactID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  integer ID
     * @param  integer Status ID
     * @return boolean
     * Update client status Active/Inactive
     */
    public function change_client_status($id, $status)
    {
        $this->db->where('userid', $id);
        $this->db->update('tblclients', [
            'active' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            logActivity('Customer Status Changed [ID: ' . $id . ' Status(Active/Inactive): ' . $status . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  mixed $_POST data
     * @return mixed
     * Change contact password, used from client area
     */
    public function change_contact_password($data)
    {
        $hook_data['data'] = $data;
        $hook_data         = do_action('before_contact_change_password', $hook_data);
        $data              = $hook_data['data'];

        // Get current password
        $this->db->where('id', get_contact_user_id());
        $client = $this->db->get('tblcontacts')->row();
        $this->load->helper('phpass');
        $hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
        if (!$hasher->CheckPassword($data['oldpassword'], $client->password)) {
            return [
                'old_password_not_match' => true,
            ];
        }
        $update_data['password']             = $hasher->HashPassword($data['newpasswordr']);
        $update_data['last_password_change'] = date('Y-m-d H:i:s');
        $this->db->where('id', get_contact_user_id());
        $this->db->update('tblcontacts', $update_data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Contact Password Changed [ContactID: ' . get_contact_user_id() . ']');

            return true;
        }

        return false;
    }

    /**
     * Get customer groups where customer belongs
     * @param  mixed $id customer id
     * @return array
     */
    public function get_customer_groups($id)
    {
        return $this->client_groups_model->get_customer_groups($id);
    }

    /**
     * Get all customer groups
     * @param  string $id
     * @return mixed
     */
    public function get_groups($id = '')
    {
        return $this->client_groups_model->get_groups($id);
    }

    /**
     * Delete customer groups
     * @param  mixed $id group id
     * @return boolean
     */
    public function delete_group($id)
    {
        return $this->client_groups_model->delete($id);
    }

    /**
     * Add new customer groups
     * @param array $data $_POST data
     */
    public function add_group($data)
    {
        return $this->client_groups_model->add($data);
    }

    /**
     * Edit customer group
     * @param  array $data $_POST data
     * @return boolean
     */
    public function edit_group($data)
    {
        return $this->client_groups_model->edit($data);
    }

    /**
    * Create new vault entry
    * @param  array $data        $_POST data
    * @param  mixed $customer_id customer id
    * @return boolean
    */
    public function vault_entry_create($data, $customer_id)
    {
        return $this->client_vault_entries_model->create($data, $customer_id);
    }

    /**
     * Update vault entry
     * @param  mixed $id   vault entry id
     * @param  array $data $_POST data
     * @return boolean
     */
    public function vault_entry_update($id, $data)
    {
        return $this->client_vault_entries_model->update($id, $data);
    }

    /**
     * Delete vault entry
     * @param  mixed $id entry id
     * @return boolean
     */
    public function vault_entry_delete($id)
    {
        return $this->client_vault_entries_model->delete($id);
    }

    /**
     * Get customer vault entries
     * @param  mixed $customer_id
     * @param  array  $where       additional wher
     * @return array
     */
    public function get_vault_entries($customer_id, $where = [])
    {
        return $this->client_vault_entries_model->get_by_customer_id($customer_id, $where);
    }

    /**
     * Get single vault entry
     * @param  mixed $id vault entry id
     * @return object
     */
    public function get_vault_entry($id)
    {
        return $this->client_vault_entries_model->get($id);
    }

    /**
    * Get customer statement formatted
    * @param  mixed $customer_id customer id
    * @param  string $from        date from
    * @param  string $to          date to
    * @return array
    */
    public function get_statement($customer_id, $from, $to)
    {
        return $this->statement_model->get_statement($customer_id, $from, $to);
    }

    /**
    * Send customer statement to email
    * @param  mixed $customer_id customer id
    * @param  array $send_to     array of contact emails to send
    * @param  string $from        date from
    * @param  string $to          date to
    * @param  string $cc          email CC
    * @return boolean
    */
    public function send_statement_to_email($customer_id, $send_to, $from, $to, $cc = '')
    {
        return $this->statement_model->send_statement_to_email($customer_id, $send_to, $from, $to, $cc);
    }

    /**
     * When customer register, mark the contact and the customer as inactive and set the registration_confirmed field to 0
     * @param  mixed $client_id  the customer id
     * @return boolean
     */
    public function require_confirmation($client_id)
    {
        $contact_id = get_primary_contact_user_id($client_id);
        $this->db->where('userid', $client_id);
        $this->db->update('tblclients', ['active' => 0, 'registration_confirmed' => 0]);

        $this->db->where('id', $contact_id);
        $this->db->update('tblcontacts', ['active' => 0]);

        return true;
    }

    public function confirm_registration($client_id)
    {
        $contact_id = get_primary_contact_user_id($client_id);
        $this->db->where('userid', $client_id);
        $this->db->update('tblclients', ['active' => 1, 'registration_confirmed' => 1]);

        $this->db->where('id', $contact_id);
        $this->db->update('tblcontacts', ['active' => 1]);

        $this->db->where('id', $contact_id);
        $contact = $this->db->get('tblcontacts')->row();

        if ($contact) {
            $this->load->model('emails_model');
            $merge_fields = [];
            $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($client_id, $contact_id));
            $this->emails_model->send_email_template('client-registration-confirmed', $contact->email, $merge_fields);

            return true;
        }

        return false;
    }

    public function get_clients_distinct_countries()
    {
        return $this->db->query('SELECT DISTINCT(country_id), short_name FROM tblclients JOIN tblcountries ON tblcountries.country_id=tblclients.country')->result_array();
    }

	 /**
     * Get customer default currency
     * @param  mixed $id customer id
     * @return mixed
     */
    public function get_customer_name($id)
    {
        $this->db->select('company');
        $this->db->where('userid', $id);
        $result = $this->db->get('tblclients')->row();
        if ($result) {
            return $result->company;
        }

        return false;
    }

    /**
     * Get Custom Divisions
     * @param  mixed $id status id
     * @return mixed      object if id passed else array
     */
    public function get_custom_divisions($id = '', $where = [])
    {
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblitem_division')->row();
        }

        $statuses = $this->object_cache->get('custom-division');

        if(!$statuses) {
            $this->db->order_by('statusorder', 'asc');

            $statuses = $this->db->get('tblitem_division')->result_array();
            $this->object_cache->add('custom-division', $statuses);
        }

        return $statuses;

    }


    /**
     * Add new lead status
     * @param array $data lead status data
     */
    public function add_status($data)
    {

        if (!isset($data['statusorder'])) {
            $data['statusorder'] = total_rows('tblitem_division') + 1;
        }

        $this->db->insert('tblitem_division', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            logActivity('New Division Added [DivisionID: ' . $insert_id . ', Name: ' . $data['name'] . ']');

            return $insert_id;
        }

        return false;
    }

    public function update_status($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('tblitem_division', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Division Updated [DivisionID: ' . $id . ', Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }
}
