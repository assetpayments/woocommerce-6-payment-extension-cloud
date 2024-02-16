<?php

class WC_Gateway_kmnd_Assetpayments extends WC_Payment_Gateway {

    private $_checkout_url = 'https://assetpayments.us/checkout/pay';
    protected $_supportedCurrencies = array('EUR','UAH','USD','RUB','RUR');

    public function __construct() {

            global $woocommerce;
            $this->id = 'assetpayments';
            $this->has_fields = false;
            $this->method_title = 'AssetPayments (https://assetpayments.com)';
            $this->method_description = __('Payment system AssetPayments', 'wc-assetpayments');
            $this->init_form_fields();
            $this->init_settings();
            $this->public_key = $this->get_option('public_key');
            $this->private_key = $this->get_option('private_key');
            $this->template_id = $this->get_option('template_id');
            $this->processing_id = $this->get_option('processing_id');
            $this->skip_checkout = $this->get_option('skip_checkout');
            $this->alternative_callback = $this->get_option('alternative_callback');
            $this->callback_url = $this->get_option('callback_url');
            $this->connection_status = $this->get_option('connection_status');

            if ($this->get_option('lang') == 'uk/en' && !is_admin()) {
                $this->lang = call_user_func($this->get_option('lang_function'));
                if ($this->lang == 'uk') {
                    $key = 0;
                } else {
                    $key = 1;
                }

                $array_explode = explode('::', $this->get_option('title'));
                $this->title = $array_explode[$key];
                $array_explode = explode('::', $this->get_option('description'));
                $this->description = $array_explode[$key];
                $array_explode = explode('::', $this->get_option('pay_message'));
                $this->pay_message = $array_explode[$key];

            } else {

                $this->lang = $this->get_option('lang');
                $this->title = $this->get_option('title');
                $this->description = $this->get_option('description');
                $this->pay_message = $this->get_option('pay_message');

            }

            // $this->icon = $this->get_option('icon');
            $this->status = $this->get_option('status');
            $this->redirect_page = $this->get_option('redirect_page');
            $this->redirect_page_error = $this->get_option('redirect_page_error');
            $this->button = $this->get_option('button');


            add_action('woocommerce_receipt_assetpayments', array($this, 'receipt_page'));
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            // add_action('woocommerce_api_wc_gateway_' . $this->id, array($this, 'check_ipn_response'));
            add_action('woocommerce_api_wc_gateway_assetpayments', array($this, 'check_ipn_response'));

            if (!$this->is_valid_for_use()) {
                $this->enabled = false;
            }

    }

    public function admin_options() { ?>
        <h3><?php esc_html_e('Payment system AssetPayments', 'wc-assetpayments'); ?></h3>
        <?php if(!empty($this->connection_status) && $this->connection_status !='success') : ?>
            <div class="inline error">
                <p class='warning'><?php esc_html_e('Last returned result is assetpayments:', 'wc-assetpayments'); ?>
                    <a href="https://www.assetpayments.ua/uk/documentation/api/information/status/doc"
                    target="_blank"
                    rel="noopener noreferrer"><?php echo esc_html($this->connection_status);?></a>
                </p>
            </div>
        <?php endif;
            if ( $this->is_valid_for_use() ) : ?>

        <table class="form-table"><?php $this->generate_settings_html(); ?></table>

        <?php  else : ?>
        <div class="inline error">
            <p>
                <strong><?php esc_html_e('Gateway disabled', 'wc-assetpayments'); ?></strong>:
                <?php esc_html_e('AssetPayments does not support your stores currencies .', 'wc-assetpayments'); ?>
            </p>
        </div>
    <?php endif;

    }

    /**
     * form_fields
     * */

    public function init_form_fields() {

        $this->form_fields = array(
                'enabled'     => array(
                    'title'   => __('Turn on/Switch off', 'wc-assetpayments'),
                    'type'    => 'checkbox',
                    'label'   => __('Turn on', 'wc-assetpayments'),
                    'default' => 'yes',
                ),

                'title'       => array(
                    'title'       => __('Bank card Visa/MasterCard', 'wc-assetpayments'),
                    'type'        => 'textarea',
                    'description' => __('Title that appears on the checkout page', 'wc-assetpayments'),
                    'default'     => __('Card Visa/MasterCard (AssetPayments)'),
                    'desc_tip'    => true,
                ),

                'description' => array(
                    'title'       => __('Card Visa/MasterCard (AssetPayments)', 'wc-assetpayments'),
                    'type'        => 'textarea',
                    'description' => __('Description that appears on the checkout page', 'wc-assetpayments'),
                    'default'     => __('Pay using the payment system AssetPayments::Pay with AssetPayments payment system', 'wc-assetpayments'),
                    'desc_tip'    => true,
                ),

                'pay_message' => array(
                    'title'       => __('Message before payment', 'wc-assetpayments'),
                    'type'        => 'textarea',
                    'description' => __('Message before payment', 'wc-assetpayments'),
                    'default'     => __('Thank you for your order, click the button below to continue::Thank you for your order, click the button'),
                    'desc_tip'    => true,
                ),

                'public_key'  => array(
                    'title'       => __('Public key', 'wc-assetpayments'),
                    'type'        => 'text',
                    'description' => __('Public key AssetPayments. Required parameter', 'wc-assetpayments'),
                    'desc_tip'    => true,
                ),

                'private_key' => array(
                    'title'       => __('Private key', 'wc-assetpayments'),
                    'type'        => 'text',
                    'description' => __('Private key AssetPayments. Required parameter', 'wc-assetpayments'),
                    'desc_tip'    => true,
                ),

                'processing_id' => array(
                    'title'       => __('Processing ID', 'wc-assetpayments'),
                    'type'        => 'text',
                    'description' => __('Processing ID AssetPayments. Required parameter', 'wc-assetpayments'),
                    'desc_tip'    => true,
                ),

                'template_id' => array(
                    'title'       => __('Template ID', 'wc-assetpayments'),
                    'type'        => 'text',
                    'description' => __('Template ID AssetPayments. Required parameter', 'wc-assetpayments'),
                    'desc_tip'    => true,
                ),

                'skip_checkout'     => array(
                    'title'       => __('Skip checkout page', 'wc-assetpayments'),
                    'label'       => __('Set ON to skip AssetPayments checkout', 'wc-assetpayments'),
                    'type'        => 'checkbox',
                    'description' => __('Turn this switch on to skip AssetPayments checkout page', 'wc-assetpayments'),
                    'desc_tip'    => true,
                ),

                'alternative_callback'     => array(
                    'title'       => __('Alternative callback', 'wc-assetpayments'),
                    'label'       => __('Set ON to use alternative callback URL', 'wc-assetpayments'),
                    'type'        => 'checkbox',
                    'description' => __('Turn this switch on to use alternative callback', 'wc-assetpayments'),
                    'desc_tip'    => true,
                ),

                'callback_url'     => array(
                    'title'       => __('Alternative callback URL', 'wc-assetpayments'),
                    'type'        => 'text',
                    'default'     => 'https://assetpayments.app/platform/rro2/callback/callback.php',
                    'description' => __('URL of alternative callback page', 'wc-assetPayments'),
                    'desc_tip'    => true,
                ),

                'lang' => array(
                    'title'       => __('Language', 'wc-assetpayments'),
                    'type'        => 'select',
                    'default'     => 'uk',
                    'options'     => array('uk'=> __('uk'), 'en'=> __('en')),
                    'description' => __('Interface language (For uk + en install multi-language plugin. Separating languages ​​with :: .)', 'wc-assetpayments'),
                    'desc_tip'    => true,
                ),

                'lang_function'     => array(
                    'title'       => __('Language detection function', 'wc-assetpayments'),
                    'type'        => 'text',
                    'default'     => 'pll_current_language',
                    'description' => __('The function of determining the language of your plugin', 'wc-assetpayments'),
                    'desc_tip'    => true,

                ),

                'button'     => array(
                    'title'       => __('Button', 'wc-assetPayments'),
                    'type'        => 'text',
                    'default'     => '',
                    'description' => __('Full path to the image of the button to go to AssetPayments', 'wc-assetPayments'),
                    'desc_tip'    => true,
                ),

                'status'     => array(
                    'title'       => __('Order status', 'wc-assetPayments'),
                    'type'        => 'text',
                    'default'     => 'processing',
                    'description' => __('Order status after successful payment', 'wc-assetPayments'),
                    'desc_tip'    => true,
                ),

                'redirect_page'     => array(
                    'title'       => __('Redirect page URL', 'wc-assetPayments'),
                    'type'        => 'url',
                    'default'     => '',
                    'description' => __('URL page to go to after gateway AssetPayments', 'wc-assetPayments'),
                    'desc_tip'    => true,
                ),

                'redirect_page_error'     => array(
                    'title'       => __('URL error Payment page', 'wc-assetPayments'),
                    'type'        => 'url',
                    'default'     => '',
                    'description' => __('URL page to go to after gateway AssetPayments', 'wc-assetPayments'),
                    'desc_tip'    => true,
                ),
        );

    }

    function is_valid_for_use() {

        if (!in_array(get_option('woocommerce_currency'), array('RUB', 'UAH', 'USD', 'EUR'))) {
            return false;
        }
        return true;
    }

    function process_payment($order_id) {
        $order = new WC_Order($order_id);
        return array(
            'result'   => 'success',
            'redirect' => add_query_arg('order-pay', $order->id, add_query_arg('key', $order->order_key, $order->get_checkout_payment_url(true)))
        );

    }

    public function receipt_page($order) {

        echo '<p>' . esc_html($this->pay_message) . '</p><br/>';
        echo $this->generate_form($order);

    }

    public function generate_form($order_id) {

        global $woocommerce;
        $order = new WC_Order($order_id);
        // $result_url = add_query_arg('wc-api', 'wc_gateway_' . $this->id, home_url('/'));
        $result_url = add_query_arg('wc-api', 'WC_Gateway_Assetpayments', home_url('/'));
        $orderdata = wc_get_order( $order_id );

        $address = $orderdata->get_billing_address_1().','.$orderdata->get_billing_city().','.$orderdata->get_billing_state().','.$orderdata->get_shipping_postcode().','.$orderdata->get_billing_country();

        $country = $orderdata->get_billing_country();
  			if ($country == '' || strlen($country) > 3) {
  				$country = 'UKR';
  			}

        $currency= get_woocommerce_currency();
        if ($currency == 'RUR' ) {
  				$currency = 'RUB';
  			}

        if (trim($this->redirect_page) == '') {
                $redirect_page_url = $order->get_checkout_order_received_url();
        } else {
                $redirect_page_url = trim($this->redirect_page) . '?wc_order_id=' .$order_id;
        }

        //****Adding cart details****//
        $orderdata = wc_get_order( $order_id );
  			foreach ($orderdata->get_items() as $product) {
    			$image = wp_get_attachment_image_src( get_post_thumbnail_id( $product['product_id'] ), 'single-post-thumbnail' );
    			$request_cart['Products'][] = array(
  					"ProductId" => $product['product_id'],
  					"ProductName" => $product['name'],
  					"ProductPrice" => $product['line_total'] / $product['quantity'],
  					"ProductItemsNum" => $product['quantity'],
  					"ImageUrl" => $image[0],
  				);
  			}

        $deliveryName = ($orderdata->get_shipping_method() == '') ? 'Достава' : $orderdata->get_shipping_method();

  			//****Adding shipping method****//
  			$request_cart['Products'][] = array(
  				"ProductId" => '12345',
  				"ProductName" =>  $deliveryName,
  				"ProductPrice" => $orderdata->get_shipping_total(),
  				"ImageUrl" => 'https://assetpayments.com/img/delivery.png',
  				"ProductItemsNum" => 1,
  			);

  			$phone = preg_replace('/[^\d]+/', '', $orderdata->get_billing_phone());

        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $hostname = $_SERVER['HTTP_HOST'];
        $websiteURL = $protocol . '://' . $hostname;

        $statusUrl = ($this->alternative_callback == 'yes' && $this->callback_url != '') ? esc_attr($this->callback_url) : esc_attr($result_url);
        $skipCheckout = ($this->skip_checkout == 'yes') ? true : false;

        $html = $this->cnb_form(array(
            'TemplateId' => intval($this->template_id),
            'MerchantInternalOrderId' => esc_attr($order_id),
            'StatusURL' => $statusUrl,
            'ReturnURL' => esc_url($redirect_page_url),
            'SkipCheckout' => $skipCheckout,
            'FirstName' => $orderdata->get_billing_first_name(),
            'LastName' => $orderdata->get_billing_last_name(),
            'Email' => $orderdata->get_billing_email(),
            'Phone' => preg_replace('/[^\d]+/', '', $orderdata->get_billing_phone()),
            'Address' => esc_attr($address),
            'CountryISO' => esc_attr($country),
            'Amount' => $orderdata->get_total(),
            'Currency' => esc_attr($currency),
            'AssetPaymentsKey' => esc_attr($this->public_key),
            'ProcessingId' => intval($this->processing_id),
            'TemplateId' => intval($this->template_id),
            'IpAddress' => $orderdata->get_customer_ip_address(),
            'CustomMerchantInfo' => "Order# " . $order_id,
            'Products' => $request_cart['Products'],
            'Lang'    => $this->lang
        ));

        return $html;

    }

    function check_ipn_response() {

        global $woocommerce;
        $json = json_decode(file_get_contents('php://input'), true);

        if ($json) {

            $key = mb_strtolower($this->public_key);
            $secret = mb_strtolower($this->private_key);
            $transactionId = $json['Payment']['TransactionId'];
            $signature = $json['Payment']['Signature'];
            $order_id = $json['Order']['OrderId'];
            // $status = $json['Payment']['Status'];

            $requestSign = $key.':'.$transactionId.':'.strtoupper($secret);
            $sign = hash_hmac('md5',$requestSign,$secret);

            if ( $signature != $sign) {
                wp_die('Bad signature');
            }

            $order = new WC_Order($order_id);
            if ($json['Payment']['Status'] == 'Approved') {
                $order->update_status($this->status, esc_html__('Successful payment (AssetPayments)', 'wc-assetpayments'));
                $order->add_order_note(esc_html__('AssetPayments TransactionID: ' .$transactionId, 'wc-assetpayments'));
                $woocommerce->cart->empty_cart();

            } else {

                $order->update_status('failed', esc_html__('Payment has not been received', 'wc-assetpayments'));
                wp_redirect($order->get_cancel_order_url());
                exit;
            }

        } else {
                wp_die('IPN Request Failure');
        }
    }

    public function cnb_form($params) {

        $language = !isset($params['Lang']) ? $language = 'uk' : $params['Lang'];
        $data      = $this->encode_params($params) ;

        $template = sprintf('
            <div
            class="load_window_assetPayments"
            style="position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background-color: #f9f9f9;
                opacity: 1;
                z-index: 99999999999;
                display: -webkit-box;
                display: -ms-flexbox;
                display: flex;
                -webkit-box-align: center;
                -ms-flex-align: center;
                align-items: center;
                -webkit-box-pack: center;
                -ms-flex-pack: center;
                justify-content: center;
            "> <p style="color:#000;">Loading...</p></div>
            <form method="POST" action="%s" id="%s_payment_form" accept-charset="utf-8">
                %s
                %s'. $button . '
            </form>',
                $this->_checkout_url,
                $this->id,
                sprintf('<input type="hidden" name="%s" value="%s" />', 'data', $data),
                $language
        );

        $skip_script ='<script type="text/javascript">
              jQuery(function() {
                jQuery("#' . $this->id . '_payment_form").submit();
              })
            </script>';

        return $template . PHP_EOL . $skip_script;

    }

    /**
     * cnb_signature
     */

    public function cnb_signature($params) {

        $params      = $this->cnb_params($params);
        $private_key = $this->private_key;
        $json      = $this->encode_params($params );
        $signature = $this->str_to_sign($private_key . $json . $private_key);
        return $signature;
    }
    /**
     * str_to_sign
     */

    public function str_to_sign($str) {
        $signature = base64_encode(sha1($str,1));
        return $signature;
    }
    /**
     * encode_params
     */
    private function encode_params($params){
        return base64_encode(json_encode($params));
    }

   /**
    * decode_params
    */

    public function decode_params($params){
        return json_decode(base64_decode($params), true);
    }

    /**
     *  private function to sanitize a string from user input or from the database.
     *
     * @param string $str String to sanitize.
     * @return string Sanitized string.
     */

    private function clean_data($str){
        if ( is_object( $str ) || is_array( $str ) ) {
            return '';
        }
        $str = (string) $str;
        $filtered = wp_check_invalid_utf8( $str );
        $filtered = trim(preg_replace( '/[\r\n\t ]+/', ' ', $filtered ));
        $filtered = stripslashes($filtered);
        $filtered = htmlspecialchars($filtered);
        return $filtered;
    }
}
