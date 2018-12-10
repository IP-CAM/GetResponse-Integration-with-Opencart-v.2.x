<?php

/**
 * Class ControllerExtensionModuleGetresponse
 */
class ControllerExtensionModuleGetresponse extends Controller
{
	private $allow_fields = ['telephone', 'country', 'city', 'address', 'postcode'];

	public function index() {
		$form = $this->config->get('module_getresponse_form');

		if (!isset($form['active']) || $form['active'] == 0 || strlen($form['url']) < 15) {
			return false;
		}

		$data = [];
		$data['form_url'] = $form['url'];

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/extension/module/getresponse')) {
			return $this->load->view($this->config->get('config_template') . '/extension/module/getresponse', $data);
		}

		return $this->load->view('extension/module/getresponse', $data);
	}

    /**
     * @param $route
     * @param $output
     * @param $customer_id
     *
     * @return bool
     */
	public function on_customer_add($route, $output, $customer_id) {

		$this->load->model('account/customer');
		$customer = $this->model_account_customer->getCustomer($customer_id);
		$settings = $this->config->get('module_getresponse_reg');

		if ($settings['active'] == 0 || $customer['newsletter'] == 0) {
			return true;
		}

		$get_response = new GetResponseApiV3(
            $this->config->get('module_getresponse_apikey'),
            $this->config->get('module_getresponse_apiurl'),
            $this->config->get('module_getresponse_domain')
        );
		$customs = [];
		$customs[] = ['customFieldId' => $this->getCustomFieldId('origin'), 'value' => ['OpenCart']];

		foreach ($this->allow_fields as $af) {
			if (!empty($row[$af])) {
				$customs[] = ['customFieldId' => $this->getCustomFieldId($af), 'value' => [$customer[$af]]];
			}
		}

		$params = [
            'name' => $customer['firstname'] . ' ' . $customer['lastname'],
            'email' => $customer['email'],
            'campaign' => ['campaignId' => $settings['campaign']],
            'customFieldValues' => $customs,
            'ipAddress' => empty($customer['ip']) ? '127.0.0.1' : $customer['ip']
        ];

		if (isset($settings['sequence_active']) && $settings['sequence_active'] == 1 && isset($settings['day'])) {
			$params['dayOfCycle'] = (int)$settings['day'];
		}

        try {
            $get_response->addContact($params);
        } catch (GetresponseApiException $e) {
		    return false;
        }

        return true;
	}

    /**
     * @param string $name
     *
     * @return string
     */
	private function getCustomFieldId($name) {
	    try {
            $get_response = new GetResponseApiV3(
                $this->config->get('module_getresponse_apikey'),
                $this->config->get('module_getresponse_apiurl'),
                $this->config->get('module_getresponse_domain')
            );

            $custom_field = $get_response->getCustomFields(['query' => ['name' => $name]]);
            $custom_field = reset($custom_field);

            if (isset($custom_field['customFieldId']) && !empty($custom_field['customFieldId'])) {
                return $custom_field['customFieldId'];
            }

            $newCustom = ['name' => $name, 'type' => 'text', 'hidden' => false, 'values' => []];

            $result = $get_response->setCustomField($newCustom);

            if (isset($result['customFieldId'])) {
                return $result['customFieldId'];
            }

            return '';
        } catch (GetresponseApiException $e) {
	        return '';
        }
    }
}
