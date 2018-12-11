<?php

/**
 * Class ControllerExtensionModuleGetresponse
 */
class ControllerExtensionModuleGetresponse extends Controller
{
    /** @var GetResponseApiV3 */
    private $get_response;
    private $allow_fields = ['telephone', 'country', 'city', 'address', 'postcode'];

    /**
     * @param $registry
     */
    public function __construct($registry)
    {
        parent::__construct($registry);

        if ($this->isConnected()) {
            $this->get_response = new GetResponseApiV3(
                $this->config->get('module_getresponse_apikey'),
                $this->config->get('module_getresponse_apiurl'),
                $this->config->get('module_getresponse_domain')
            );
        }
    }

    public function index()
    {

        $this->load->language('extension/module/getresponse');
        $this->load->model('localisation/language');
        $this->load->model('design/layout');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->saveSettings();

        $data = ['is_connected' => $this->isConnected()];
        $data = $this->assignLanguage($data);
        $data = $this->assignSettings($data);
        $data = $this->assignBreadcrumbs($data);

        if ($this->isConnected()) {
            $data = $this->assignAutoresponders($data);
            $data['campaign_days_json'] = json_encode($data['campaign_days']);
            $data = $this->assignForms($data);
            $data['campaigns'] = $this->getCampaigns();
        }

        $data['disconnect_link'] = $this->url->link('extension/module/getresponse/disconnect',
            'user_token='.$this->session->data['user_token'], 'SSL');
        $data['layouts'] = $this->model_design_layout->getLayouts();
        $data['action'] = $this->url->link('extension/module/getresponse',
            'user_token='.$this->session->data['user_token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/module', 'user_token='.$this->session->data['user_token'], 'SSL');
        $data['languages'] = $this->model_localisation_language->getLanguages();
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/module/getresponse', $data));
    }

    public function disconnect()
    {
        $this->load->language('extension/module/getresponse');
        $this->load->model('localisation/language');
        $this->load->model('setting/setting');

        $apiKey = $this->config->get('module_getresponse_apikey');

        if (empty($apiKey)) {
            $this->response->redirect(
                $this->url->link(
                    'extension/module/getresponse', 'user_token='.$this->session->data['user_token'],
                    'SSL'
                )
            );
        }

        $this->model_setting_setting->editSetting('module_getresponse', [
            'module_getresponse_form' => null,
            'module_getresponse_apikey' => null,
            'module_getresponse_domain' => null,
            'module_getresponse_apiurl' => null,
            'module_getresponse_status' => 0,
            'module_getresponse_campaign' => null,
            'module_getresponse_reg' => null,
        ]);

        $this->session->data['success'] = $this->language->get('text_disconnect_success');

        $this->response->redirect($this->url->link(
            'extension/module/getresponse', 'user_token='.$this->session->data['user_token'],
            'SSL'
        ));
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    private function assignAutoresponders($data)
    {
        try {
            $autoresponders = (array)$this->get_response->getAutoresponders();

            $data['campaign_days'] = [];

            foreach ($autoresponders as $autoresponder) {
                if ($autoresponder['triggerSettings']['dayOfCycle'] == null) {
                    continue;
                }

                $data['campaign_days'][$autoresponder['triggerSettings']['subscribedCampaign']['campaignId']][$autoresponder['triggerSettings']['dayOfCycle']] = [
                    'day' => $autoresponder['triggerSettings']['dayOfCycle'],
                    'name' => $autoresponder['subject'],
                    'status' => $autoresponder['status'],
                ];
            }

            return $data;
        } catch (GetresponseApiException $e) {
            $this->session->data['error_warning'] = $e->getMessage();

            return $data;
        }
    }

    /**
     * @param array $data
     *
     * @return mixed
     */
    private function assignForms($data)
    {

        try {
            $new_forms = $old_forms = [];
            $forms = (array)$this->get_response->getForms();
            $webforms = (array)$this->get_response->getWebForms();

            foreach ($forms as $form) {
                if (isset($form['formId']) && !empty($form['formId']) && $form['status'] == 'published') {
                    $new_forms[] = [
                        'id' => $form['formId'],
                        'name' => htmlspecialchars($form['name']),
                        'url' => $form['scriptUrl'],
                    ];
                }
            }

            foreach ($webforms as $form) {
                if (isset($form['webformId']) && !empty($form['webformId']) && $form['status'] == 'enabled') {
                    $old_forms[] = [
                        'id' => $form['webformId'],
                        'name' => htmlspecialchars($form['name']),
                        'url' => $form['scriptUrl'],
                    ];
                }
            }

            $data['new_forms'] = $new_forms;
            $data['old_forms'] = $old_forms;

            return $data;

        } catch (GetresponseApiException $e) {
            $this->session->data['error_warning'] = $e->getMessage();

            return $data;
        }
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function assignBreadcrumbs($data)
    {
        $data['breadcrumbs'] = [];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home', 'user_token='.$this->session->data['user_token'], 'SSL'),
            'separator' => false,
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_module'),
            'href' => $this->url->link('extension/module', 'user_token='.$this->session->data['user_token'], 'SSL'),
            'separator' => ' :: ',
        ];
        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/getresponse', 'user_token='.$this->session->data['user_token'],
                'SSL'),
            'separator' => ' :: ',
        ];

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function assignLanguage($data)
    {
        $data['heading_title'] = $this->language->get('heading_title');
        $data['text_module'] = $this->language->get('text_module');
        $data['text_success'] = $this->language->get('text_success');
        $data['text_none'] = $this->language->get('text_none');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['entry_title'] = $this->language->get('entry_title');
        $data['entry_export'] = $this->language->get('entry_export');
        $data['entry_apikey'] = $this->language->get('entry_apikey');
        $data['entry_apikey_hint'] = $this->language->get('entry_apikey_hint');
        $data['entry_campaign'] = $this->language->get('entry_campaign');
        $data['entry_campaign_hint'] = $this->language->get('entry_campaign_hint');
        $data['button_save'] = $this->language->get('button_save');
        $data['button_disconnect'] = $this->language->get('button_disconnect');
        $data['button_cancel'] = $this->language->get('button_cancel');
        $data['button_export'] = $this->language->get('button_export');
        $data['apikey_info'] = $this->language->get('apikey_info');
        $data['export_info'] = $this->language->get('export_info');
        $data['register_info'] = $this->language->get('register_info');
        $data['webform_info'] = $this->language->get('webform_info');
        $data['apikey_title'] = $this->language->get('apikey_title');
        $data['export_title'] = $this->language->get('export_title');
        $data['register_title'] = $this->language->get('register_title');
        $data['webform_title'] = $this->language->get('webform_title');
        $data['label_active'] = $this->language->get('label_active');
        $data['label_form'] = $this->language->get('label_form');
        $data['label_campaign'] = $this->language->get('label_campaign');
        $data['label_day_of_cycle'] = $this->language->get('label_day_of_cycle');
        $data['label_auto_queue'] = $this->language->get('label_auto_queue');
        $data['label_yes'] = $this->language->get('label_yes');
        $data['label_no'] = $this->language->get('label_no');
        $data['label_none'] = $this->language->get('label_none');
        $data['label_new_forms'] = $this->language->get('label_new_forms');
        $data['label_old_forms'] = $this->language->get('label_old_forms');
        $data['info_ajax_error'] = $this->language->get('info_ajax_error');
        $data['text_edit'] = $this->language->get('text_edit');
        $data['exporting_button'] = $this->language->get('exporting_button');

        return $data;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function assignSettings($data)
    {
        $this->enable_module = $this->config->get('getresponse_enable_module');

        $data['modules'] = $this->config->get('module_getresponse_module');

        if ($this->config->get('module_getresponse_form')) {
            $data['getresponse_form'] = $this->config->get('module_getresponse_form');
        } else {
            $data['getresponse_form'] = ['id' => 0, 'url' => '', 'active' => 0];
        }

        if ($this->config->get('module_getresponse_reg')) {
            $data['getresponse_reg'] = $this->config->get('module_getresponse_reg');
        } else {
            $data['getresponse_reg'] = ['campaign' => '', 'day' => '', 'sequence_active' => 0];
        }

        if (isset($this->session->data['success'])) {
            $data['save_success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        }

        if (isset($this->session->data['error_warning'])) {
            $data['error_warning'] = $this->session->data['error_warning'];
            unset($this->session->data['error_warning']);
        }

        $data['token'] = $this->session->data['user_token'];

        if (isset($data['getresponse_apikey']) && strlen($data['getresponse_apikey']) > 0 && isset($this->session->data['active_tab'])) {
            $data['active_tab'] = $this->session->data['active_tab'];
        } else {
            $data['active_tab'] = 'home';
        }

        $apiKey = !empty($data['module_getresponse_apikey']) ? $data['module_getresponse_apikey'] : $this->config->get('module_getresponse_apikey');
        $hiddenApiKey = strlen($apiKey) > 0 ? str_repeat("*", strlen($apiKey) - 6).substr($apiKey, -6) : '';

        $data['getresponse_apikey'] = $apiKey;
        $data['getresponse_apiurl'] = $this->config->get('module_getresponse_apiurl');
        $data['getresponse_domain'] = $this->config->get('module_getresponse_domain');
        $data['getresponse_hidden_apikey'] = $hiddenApiKey;
        $data['getresponse_campaign'] = $this->config->get('module_getresponse_campaign');

        return $data;
    }

    private function saveSettings()
    {

        $this->load->model('setting/setting');

        // get data from db
        $selectedWebForm = $this->config->get('module_getresponse_form');
        $campaign = $this->config->get('module_getresponse_apikey');
        $registration = $this->config->get('module_getresponse_reg');
        $domain = $this->config->get('module_getresponse_domain');
        $apiUrl = $this->config->get('module_getresponse_apiurl');

        /** @var array $post */
        $post = $this->request->post;

        if (empty($post['module_getresponse_apikey']) && !empty($post['module_getresponse_hidden_apikey'])) {
            $post['module_getresponse_apikey'] = $post['module_getresponse_hidden_apikey'];
        }

        if (isset($post['module_getresponse_form'])) {
            $this->session->data['active_tab'] = $post['module_getresponse_form']['current_tab'];
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $error = $this->validateForm($post);

            if (!empty($error)) {
                $this->session->data['error_warning'] = $error;
                $this->response->redirect($this->url->link(
                    'extension/module/getresponse', 'user_token='.$this->session->data['user_token'],
                    'SSL'
                ));
            }

            try {
                $apiKey = trim($post['module_getresponse_apikey']);

                $isEnterprise = false;
                if (isset($post['getresponse-enterprise']) && 'on' === $post['getresponse-enterprise']) {
                    $isEnterprise = true;
                    $apiUrl = trim($post['getresponse-account-type']);
                    $domain = trim($post['getresponse-account-domain']);
                }

                if (isset($post['module_getresponse_campaign'])) {
                    $campaign = $post['module_getresponse_campaign'];
                }

                if (isset($post['module_getresponse_reg'])) {
                    $registration = $post['module_getresponse_reg'];
                }

                if (isset($post['module_getresponse_form'], $post['module_getresponse_form']['id'])) {
                    $form_id = $post['module_getresponse_form']['id'];

                    if (!empty($form_id)) {
                        $params = explode('-', $form_id);

                        if (count($params) === 2) {

                            if ($params[0] === 'form') {
                                $webform = $this->get_response->getWebForm($params[1]);
                            } else {
                                $webform = $this->get_response->getForm($params[1]);
                            }

                            if (isset($webform['scriptUrl'])) {
                                $selectedWebForm = [
                                    'url' => $webform['scriptUrl'],
                                    'id' => $params[1],
                                    'active' => $post['module_getresponse_form']['active'],
                                ];
                            }
                        }
                    }
                }

                $data = [
                    'module_getresponse_status' => 1,
                    'module_getresponse_apikey' => $apiKey,
                    'module_getresponse_campaign' => $campaign,
                    'module_getresponse_reg' => $registration,
                    'module_getresponse_form' => $selectedWebForm,
                    'module_getresponse_domain' => $isEnterprise ? $domain : null,
                    'module_getresponse_apiurl' => $isEnterprise ? $apiUrl : null,
                ];

                $this->model_setting_setting->editSetting('module_getresponse', $data);

                if ($post['module_getresponse_hidden_apikey'] !== $post['module_getresponse_apikey']) {
                    $this->session->data['success'] = $this->language->get('settings_saved');
                } else {
                    $this->session->data['success'] = $this->language->get('text_success');
                }
                $this->response->redirect($this->url->link(
                    'extension/module/getresponse', 'user_token='.$this->session->data['user_token'],
                    'SSL'
                ));
            } catch (GetresponseApiException $e) {
                $this->session->data['error_warning'] = $e->getMessage();
                $this->response->redirect($this->url->link(
                    'extension/module/getresponse', 'user_token='.$this->session->data['user_token'],
                    'SSL'
                ));
            }
        }
    }

    /**
     * @param string $api_key
     * @param string|null $api_url
     * @param string|null $domain
     * @return bool
     */
    private function checkConnection($api_key, $api_url, $domain)
    {
        $get_response = new GetResponseApiV3($api_key, $api_url, $domain);
        try {
            $get_response->getAccount();
        } catch (GetresponseApiException $e) {
            return false;
        }

        return true;
    }

    /**
     * Validate permission
     *
     * @return bool
     */
    private function validate()
    {
        if (!$this->user->hasPermission('modify', 'extension/module/getresponse')) {
            $this->session->data['error_warning'] = $this->language->get('error_permission');

            return false;
        }

        return true;
    }

    /**
     * Export contacts to campaign
     */
    public function export()
    {
        $mapping = [];
        $this->load->model('extension/getresponse');
        $contacts = $this->model_extension_getresponse->getContacts();
        $campaignId = $this->request->post['campaign'];

        $duplicated = 0;
        $queued = 0;
        $contact = 0;
        $not_added = 0;

        $origin = [
            'customFieldId' => $this->getCustomFieldId('origin'),
            'value' => ['OpenCart'],
        ];

        foreach ($this->allow_fields as $af) {
            $customFieldId = $this->getCustomFieldId($af);
            if (!empty($customFieldId)) {
                $mapping[$af] = $customFieldId;
            }
        }

        foreach ($contacts as $row) {

            $customs = [$origin];
            foreach ($this->allow_fields as $af) {
                if (isset($mapping[$af]) && !empty($row[$af])) {
                    $customs[] = ['customFieldId' => $mapping[$af], 'value' => [$row[$af]]];
                }
            }

            try {
                $params = [
                    'name' => trim($row['firstname'].' '.$row['lastname']),
                    'email' => $row['email'],
                    'campaign' => ['campaignId' => $campaignId],
                    'customFieldValues' => $customs,
                    'ipAddress' => empty($row['ip']) ? '127.0.0.1' : $row['ip'],
                ];

                $r = $this->get_response->addContact($params);

                if (!isset($r['code'])) {
                    $queued++;
                } elseif (isset($r['code']) && $r['code'] == 1008) {
                    $duplicated++;
                } else {
                    $not_added++;
                }

                $contact++;
            } catch (Exception $e) {
                $not_added++;
            }

            $results = [
                'status' => 1,
                'response' => '  Export completed. Contacts: '.$contact.'. Queued: '.$queued.'. Updated: '.
                    $duplicated.'. Not added (Contact already queued): '.$not_added.'.',
            ];
        }

        $this->response->setOutput(json_encode($results));
    }

    private function getCustomFieldId($name)
    {
        try {
            $custom_field = $this->get_response->getCustomFields(['query' => ['name' => $name]]);
            $custom_field = reset($custom_field);

            if (isset($custom_field['customFieldId']) && !empty($custom_field['customFieldId'])) {
                return $custom_field['customFieldId'];
            }

            $newCustom = ['name' => $name, 'type' => 'text', 'hidden' => false, 'values' => []];

            $result = $this->get_response->setCustomField($newCustom);

            if (isset($result['customFieldId'])) {
                return $result['customFieldId'];
            }

            return false;
        } catch (GetresponseApiException $e) {
            $this->session->data['error_warning'] = $e->getMessage();

            return false;
        }
    }

    /**
     * @return array
     */
    private function getCampaigns()
    {
        try {
            $campaigns = [];
            $response = $this->get_response->getCampaigns();

            foreach ($response as $campaign) {
                $campaigns[$campaign['campaignId']] = $campaign;
            }

            return $campaigns;
        } catch (GetresponseApiException $e) {
            $this->session->data['error_warning'] = $e->getMessage();

            return [];
        }
    }

    public function install()
    {
        $this->load->model('setting/event');
        $this->model_setting_event->addEvent('getresponse', 'catalog/model/account/customer/addCustomer/after',
            'extension/module/getresponse/on_customer_add');

    }

    public function uninstall()
    {
        $this->load->model('setting/event');
        $this->model_setting_event->deleteEvent('getresponse');
    }

    /**
     * @param array $post
     * @return string
     */
    private function validateForm($post)
    {

        if (empty($post['module_getresponse_apikey'])) {
            return $this->language->get('error_apikey');
        }

        $isEnterprise = false;

        if (isset($post['getresponse-enterprise']) && 'on' === $post['getresponse-enterprise']) {

            $isEnterprise = true;

            if (empty($post['getresponse-account-domain'])) {
                return $this->language->get('error_domain');
            }
        }

        if (!$this->checkConnection(
            $post['module_getresponse_apikey'],
            $isEnterprise ? trim($post['getresponse-account-type']) : null,
            $isEnterprise ? trim($post['getresponse-account-domain']) : null
        )) {
            return $this->language->get('error_incorrect_apikey');
        }

        return '';
    }

    /**
     * @return bool
     */
    private function isConnected()
    {
        return !empty($this->config->get('module_getresponse_apikey'));
    }
}
