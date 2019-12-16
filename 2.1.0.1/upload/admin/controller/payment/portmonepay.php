<?php
class ControllerPaymentPortmonepay extends Controller {
    public $version = '1.0.0';
    private $error = array();
    private $text_data = array(
    'heading_title'             ,
    'text_portmonepay'          ,
    'text_edit'                 ,
    'text_enabled'              ,
    'text_disabled'             ,
    'text_all_zones'            ,
    'text_pay'                  ,
    'text_card'                 ,
    'tab_general'               ,
    'tab_order_status'          ,
    'entry_status'              ,
    'h_entry_status'            ,
    'entry_payee_id'            ,
    'h_entry_payee_id'          ,
    'entry_login'               ,
    'h_entry_login'             ,
    'entry_pass'                ,
    'h_entry_pass'              ,
    'entry_order_stat'          ,
    'h_entry_order_stat'        ,
    'entry_order_stat_fa'       ,
    'h_entry_order_stat_fa'     ,
    'entry_geo_zone'            ,
    'entry_showlogo'            ,
    'h_entry_showlogo'          ,
    'entry_sort_order'          ,
    'h_entry_sort_order'        ,
    'OP_version'                ,
    'Plugin_version'            ,
    'help_total'                ,
    'button_save'               ,
    'button_cancel'             ,
    'h_entry_name'              ,
    'entry_name'                ,
    );
    private $error_data = array(
    'warning'   ,
    'payee_id'  ,
    'login'     ,
    'pass'      ,
    'type'      ,
    );
    private $post_data = array(
    'status'            ,
    'name'              ,
    'payee_id'          ,
    'login'             ,
    'pass'              ,
    'order_stat_id'     ,
    'order_stat_fal_id' ,
    'entry_showlogo'    ,
    'sort_order'        ,
    'geo_zone_id'       ,
    );
    private $currency_add_uan = array (
    'title'         => 'Гривна',
    'code'          => 'UAN',
    'symbol_left'   => '₴' ,
    'symbol_right'  => 'грн' ,
    'decimal_place' => '2' ,
    'value'         => '0.00000000' ,
    'status'        => '0',
    );

    public function index() {
        $this->load->language('payment/portmonepay');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');
        $this->load->model('localisation/currency');

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {

            $this->model_setting_setting->editSetting('portmonepay', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
        }

        $data['entry_OP_version'] = VERSION;
        $data['entry_Plugin_version'] = $this->version;

        foreach ($this->text_data as $value) {
            $data[$value] = $this->language->get($value);
        }

        $currency_uan = $this->model_localisation_currency->getCurrencyByCode('UAN');
        if(empty($currency_uan)){
            $this->currency_add_uan();
        }

        foreach ($this->error_data as $value) {
            if (isset($this->error[$value])) {
                $data['error_'.$value] = $this->error[$value];
            } else {
                $data['error_'.$value] = '';
            }
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_payment'),
            'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('payment/portmonepay', 'token=' . $this->session->data['token'], 'SSL')
        );

        $data['action'] = $this->url->link('payment/portmonepay', 'token=' . $this->session->data['token'], 'SSL');
        $data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
        $this->load->model('localisation/order_status');
        $this->load->model('localisation/geo_zone');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['portmonepay_order_stat_fa'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        foreach ($this->post_data as $value) {
            if (isset($this->request->post['portmonepay_'.$value])) {
                $data['portmonepay_'.$value] = $this->request->post['portmonepay_'.$value];
            } else {
                $data['portmonepay_'.$value] = $this->config->get('portmonepay_'.$value);
            }
        }

        $portmonepay_name_val = $this->config->get('portmonepay_name');
        if(!isset($portmonepay_name_val)){
            $data['portmonepay_name'] = 'Portmone';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('payment/portmonepay.tpl', $data));
    }

    private function currency_add_uan() {
        $this->model_localisation_currency->addCurrency($this->currency_add_uan);
    }

    protected function validate() {
        if (!$this->user->hasPermission('modify', 'payment/portmonepay')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (!$this->request->post['portmonepay_payee_id']) {
            $this->error['payee_id'] = $this->language->get('error_payee_id');
        }
        if (!$this->request->post['portmonepay_login']) {
            $this->error['login'] = $this->language->get('error_login');
        }
        if (!$this->request->post['portmonepay_pass']) {
            $this->error['pass'] = $this->language->get('error_pass');
        }

        return !$this->error;
    }
}