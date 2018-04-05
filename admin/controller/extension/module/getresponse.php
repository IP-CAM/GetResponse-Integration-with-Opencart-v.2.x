<?php

/**
 * Class ControllerExtensionModuleGetresponse
 */
class ControllerExtensionModuleGetresponse extends Controller
{
	private $error = [];
	private $gr_apikey;
	private $get_response;
	private $campaigns;
	private $campaign;
	private $custom_fields = [];
	private $allow_fields = ['telephone', 'country', 'city', 'address', 'postcode'];

    /**
     * @param $registry
     */
	public function __construct($registry) {
		parent::__construct($registry);

		$this->gr_apikey = $this->config->get('module_getresponse_apikey');

		if (!empty($this->gr_apikey)) {
			$this->get_response = new GetResponseApiV3($this->gr_apikey);
		}
	}

	public function index() {

		$this->load->language('extension/module/getresponse');
		$this->load->model('localisation/language');
		$this->load->model('design/layout');
		$this->document->setTitle($this->language->get('heading_title'));

		$data = [];
        $data['is_connected'] = false;
		$data = $this->saveSettings($data);
		$data = $this->assignLanguage($data);
		$data = $this->assignSettings($data);
		$data = $this->assignBreadcrumbs($data);

		if (!empty($this->gr_apikey)) {
		    $data['is_connected'] = true;
			$data = $this->assignAutoresponders($data);
			$data = $this->assignForms($data);
			$data['campaigns'] = $this->getCampaigns();
		}

		$data['disconnect_link'] = $this->url->link('extension/module/getresponse/disconnect', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['layouts'] = $this->model_design_layout->getLayouts();
		$data['action'] = $this->url->link('extension/module/getresponse', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['cancel'] = $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL');
		$data['languages'] = $this->model_localisation_language->getLanguages();
		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/getresponse', $data));
	}

    public function disconnect() {
        $this->load->language('extension/module/getresponse');
        $this->load->model('localisation/language');
        $this->load->model('setting/setting');

        $apiKey = $this->config->get('module_getresponse_apikey');

        if (empty($apiKey)) {
            $this->response->redirect(
                $this->url->link(
                    'extension/module/getresponse', 'user_token=' . $this->session->data['user_token'],
                    'SSL'
                )
            );
        }

        $this->model_setting_setting->editSetting('module_getresponse', [
            'module_getresponse_form' => null,
            'module_getresponse_apikey' => null,
            'module_getresponse_status' => 0
        ]);

        $this->session->data['success'] = $this->language->get('text_disconnect_success');

        $this->response->redirect($this->url->link(
            'extension/module/getresponse', 'user_token=' . $this->session->data['user_token'],
            'SSL'
        ));
	}

    /**
     * @param array $data
     *
     * @return mixed
     */
	private function assignAutoresponders($data) {
		$autoresponders = $this->get_response->getAutoresponders();
		$data['campaign_days'] = [];

		if (isset($autoresponders->httpStatus) && $autoresponders->httpStatus != 200) {
			$this->session->data['error_warning'] = $autoresponders->codeDescription;

			return $data;
		}

		if (!empty($autoresponders) && is_object($autoresponders)) {
			foreach ($autoresponders as $autoresponder) {
				if ($autoresponder->triggerSettings->dayOfCycle == null) {
					continue;
				}

				$data['campaign_days'][$autoresponder->triggerSettings->subscribedCampaign->campaignId][$autoresponder->triggerSettings->dayOfCycle] = [
						'day' => $autoresponder->triggerSettings->dayOfCycle,
						'name' => $autoresponder->subject,
						'status' => $autoresponder->status
                ];
			}
		}

		return $data;
	}

    /**
     * @param array $data
     *
     * @return mixed
     */
	private function assignForms($data) {
		$forms = $this->get_response->getForms();
		$new_forms = [];
		$old_forms = [];

		if (!empty($forms)) {
			foreach ($forms as $form) {
				if (isset($form->formId) && !empty($form->formId) && $form->status == 'published') {
					$new_forms[] = ['id' => $form->formId, 'name' => $form->name, 'url' => $form->scriptUrl];
				}
			}
		}

		$webforms = $this->get_response->getWebForms();

		if (!empty($webforms)) {
			foreach ($webforms as $form) {
				if (isset($form->webformId) && !empty($form->webformId) && $form->status == 'enabled') {
					$old_forms[] = ['id' => $form->webformId, 'name' => $form->name, 'url' => $form->scriptUrl];
				}
			}
		}

		$data['new_forms'] = $new_forms;
		$data['old_forms'] = $old_forms;

		return $data;
	}

	/**
	 * @param array $data
     *
	 * @return array
	 */
	private function assignBreadcrumbs($data) {
		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
				'separator' => false
        ];
		$data['breadcrumbs'][] = [
				'text' => $this->language->get('text_module'),
				'href' => $this->url->link('extension/module', 'user_token=' . $this->session->data['user_token'], 'SSL'),
				'separator' => ' :: '
        ];
		$data['breadcrumbs'][] = [
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/module/getresponse', 'user_token=' . $this->session->data['user_token'], 'SSL'),
				'separator' => ' :: '
        ];

		return $data;
	}

	/**
	 * @param array $data
     *
	 * @return array
	 */
	private function assignLanguage($data) {
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
	private function assignSettings($data) {
		$this->enable_module = $this->config->get('getresponse_enable_module');
		$this->campaign = $this->config->get('module_getresponse_campaign');

		$data['modules'] = $this->config->get('module_getresponse_module');

		if ($this->config->get('module_getresponse_form')) {
			$data['getresponse_form'] = $this->config->get('module_getresponse_form');
		} else {
			$data['getresponse_form'] = ['id' => 0, 'url' => '' , 'active' => 0];
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

        $apiKey = strlen($this->gr_apikey) > 0 ? str_repeat("*", strlen($this->gr_apikey) - 6) . substr($this->gr_apikey, -6) : '';
		$data['getresponse_apikey'] = $this->gr_apikey;
		$data['getresponse_hidden_apikey'] = $apiKey;
		$data['getresponse_campaign'] = $this->campaign;

		return $data;
	}

    /**
     * Module settings to read and/or write config
     * @param array $data
     *
     * @return array
     */
	private function saveSettings($data) {

		$this->load->model('setting/setting');
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            // update settings (connected)
		    if (!empty($this->gr_apikey)) {

		        $form_id = false;

		        if (isset($this->request->post['module_getresponse_form']['id'])) {
                    $form_id = $this->request->post['module_getresponse_form']['id'];
                }

		        if (!empty($form_id)) {
		            $params = explode('-', $form_id);

		            if (count($params) === 2) {

                        if ($params[0] === 'form') {
                            $webform = $this->get_response->getWebForm($params[1]);
                        } else {
                            $webform = $this->get_response->getForm($params[1]);
                        }

                        if (isset($webform->scriptUrl)) {
                            $this->request->post['module_getresponse_form']['url'] = $webform->scriptUrl;
                            $this->request->post['module_getresponse_form']['id'] = $params[1];
                        }
                    }
                }

		        $data = [
		            'module_getresponse_status' => 1,
                    'module_getresponse_apikey' => $this->gr_apikey,
		            'module_getresponse_campaign' => $this->request->post['module_getresponse_campaign'],
		            'module_getresponse_reg' => $this->request->post['module_getresponse_reg'],
		            'module_getresponse_form' => $this->request->post['module_getresponse_form']
                ];

                $this->model_setting_setting->editSetting('module_getresponse', $data);
                $this->session->data['success'] = $this->language->get('text_success');

            // new connection
            } else {

		        $apiKey = $this->request->post['module_getresponse_hidden_apikey'];

                if (!$this->checkApiKey($apiKey)) {
                    $this->session->data['error_warning'] = $this->language->get('error_incorrect_apikey');
                } else {
                    $data = [
                        'module_getresponse_status' => 1,
                        'module_getresponse_apikey' => $apiKey
                    ];

                    $this->model_setting_setting->editSetting('module_getresponse', $data);
                    $this->session->data['success'] = $this->language->get('text_success');
                }
            }

			$this->session->data['active_tab'] = $this->request->post['module_getresponse_form']['current_tab'];

			$this->response->redirect($this->url->link(
                'extension/module/getresponse', 'user_token=' . $this->session->data['user_token'],
                'SSL'
            ));
		}

		return $data;
	}

	/**
	 * @param string $apikey
	 * @return bool
	 */
	private function checkApiKey($apikey) {
		if (empty($apikey)) {
			return false;
		} elseif ($this->config->get('module_getresponse_apikey') == $apikey) {
			return true;
		}

		$get_response = new GetResponseApiV3($apikey);
		$campaigns = $get_response->getCampaigns();

		return !(isset($campaigns->httpStatus) && $campaigns->httpStatus != 200);
	}

	/**
	 * Validate permission
	 *
	 * @return bool
	 */
	private function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/getresponse')) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
		}
        return true;
	}

	/**
	 * Export contacts to campaign
	 */
	public function export() {

		$this->load->model('extension/getresponse');
		$contacts = $this->model_extension_getresponse->getContacts();
		$this->campaign = $this->request->post['campaign'];
		$gr_campaign = [];
		$campaigns = $this->getCampaigns();

		if (!empty($campaigns)) {
			foreach ($campaigns as $campaign) {
				if ($campaign->campaignId === $this->campaign) {
					$gr_campaign = $campaign;
				}
			}
		}

		if (empty($gr_campaign)) {
			$results = ['status' => 2, 'response' => '  No campaign with the specified name.'];
		} else {
			$duplicated = 0;
			$queued = 0;
			$contact = 0;
			$not_added = 0;

			foreach ($contacts as $row) {
				$customs = [];
				$customs[] = ['customFieldId' => $this->getCustomFieldId('origin'), 'value' => ['OpenCart']];

				foreach ($this->allow_fields as $af) {
					$custom_field_id = $this->getCustomFieldId($af);
					if (!empty($row[$af]) && $custom_field_id !== false) {
						$customs[] = ['customFieldId' => $custom_field_id, 'value' => [$row[$af]]];
					}
				}

				$grContact = $this->get_response->getContacts(
						['query' => ['campaignId' => $gr_campaign->campaignId, 'email' => $row['email']]]
				);

				$cycle_day = (!empty($grContact) && !empty($grContact->dayOfCycle)) ? $grContact->dayOfCycle : 0;

				$params = [
						'name' => $row['firstname'] . ' ' . $row['lastname'],
						'email' => $row['email'],
						'dayOfCycle' => $cycle_day,
						'campaign' => ['campaignId' => $gr_campaign->campaignId],
						'customFieldValues' => $customs,
						'ipAddress' => empty($row['ip']) ? '127.0.0.1' : $row['ip']
                ];

				try {
					$r = $this->get_response->addContact($params);

					if (is_object($r) && !isset($r->code)) {
                        $queued++;
                    } elseif (is_object($r) && isset($r->code) && $r->code == 1008) {
						$duplicated++;
					} else {
						$not_added++;
					}

					$contact++;
				} catch (Exception $e) {
					$not_added++;
				}
			}

			$results = [
					'status' => 1,
					'response' => '  Export completed. Contacts: ' . $contact . '. Queued: ' . $queued . '. Updated: ' .
							$duplicated . '. Not added (Contact already queued): ' . $not_added . '.'
            ];
		}

		$this->response->setOutput(json_encode($results));
	}

	private function getCustomFieldId($name) {
        $custom_field = (array) $this->get_response->getCustomFields(['query' => ['name' => $name]]);
        $custom_field = reset($custom_field);

		if (isset($custom_field->customFieldId) && !empty($custom_field->customFieldId)) {
			return $custom_field->customFieldId;
		}

		$newCustom = ['name' => $name, 'type' => 'text', 'hidden' => false, 'values' => []];

		$result = $this->get_response->setCustomField($newCustom);

		if (!empty($this->custom_fields) && isset($result->customFieldId)) {
			$this->custom_fields[$result->name] = $result->customFieldId;
			return $result->customFieldId;
		}
		return false;
	}

	/**
	 * @return array
	 */
	private function getCampaigns() {
		if (empty($this->gr_apikey)) {
			return [];
		}

		if (!empty($this->campaigns)) {
			return $this->campaigns;
		}

		$this->campaigns = (array) $this->get_response->getCampaigns();

		if (isset($this->campaigns->httpStatus) && $this->campaigns->httpStatus != 200) {
			$this->session->data['error_warning'] = $this->campaigns->codeDescription;
			$this->campaigns = [];
		}

		return $this->campaigns;
	}

	public function install() {
        $this->load->model('setting/event');
		$this->model_setting_event->addEvent('getresponse', 'catalog/model/account/customer/addCustomer/after', 'extension/module/getresponse/on_customer_add');

    }

	public function uninstall() {
        $this->load->model('setting/event');
		$this->model_setting_event->deleteEvent('getresponse');
	}
}
