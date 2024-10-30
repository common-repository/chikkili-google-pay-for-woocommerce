<?php
/**
 * Google Pay Payment Gateway Class
 * @since 1.0.0
 */
class Sgpay_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $this->id = 'sg_gpay';
        $this->method_title = __('Google Pay', 'chikkili-google-pay-for-woocommerce');
        $this->method_description = __('Take payments via Gpay (Google Pay).', 'chikkili-google-pay-for-woocommerce'); // will be displayed on the options page
        $this->title = __('Google Pay', 'chikkili-google-pay-for-woocommerce');
        $this->icon = apply_filters( 'woocommerce_gateway_icon',plugin_dir_url( dirname( __FILE__ ) ) . 'assets/gpay.png');
        $this->has_fields = true;
        $this->supports = array(
            'products'
        );
        $this->init_form_fields();
        $this->init_settings();
        $this->enabled = $this->get_option('enabled');
        $this->title = $this->get_option('title');
        
       $this->description = $this->get_option('description');
        $this->instructions = $this->get_option( 'instructions', $this->description );
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        // You can also register a webhook here
        add_action( 'woocommerce_api_sg_gpay', array( $this, 'webhook' ) );
       
    }

    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', 'chikkili-google-pay-for-woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable google pay', 'chikkili-google-pay-for-woocommerce'),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __('Method Title', 'chikkili-google-pay-for-woocommerce'),
                'type' => 'text',
                'description' => __('This controls the title', 'chikkili-google-pay-for-woocommerce'),
                'default' => __('Google Pay', 'chikkili-google-pay-for-woocommerce'),
                'desc_tip' => true,
            ),
            'pa' => array(
                'title'       => __('VPA','chikkili-google-pay-for-woocommerce'),
                'description' => __('Payee address or business virtual payment address(pa)', 'chikkili-google-pay-for-woocommerce'),
                'type'        => 'text'
            ),
            'pn' => array(
                'title'       => __('Payee Name','chikkili-google-pay-for-woocommerce'),
                'description' => __('Payee name or business name.', 'chikkili-google-pay-for-woocommerce'),
                'type'        => 'text'
            ),
            'mc' => array(
                'title'       => __('Merchant Category Code (MCC)','chikkili-google-pay-for-woocommerce'),
                'description' => __('Business retailer category code', 'chikkili-google-pay-for-woocommerce'),
                'type'        => 'number'
            )
            
        );
    }

    /**
     * Admin Panel Options
     * - Options for bits like 'title' and availability on a country-by-country basis
     *
     * @since 1.0.0
     * @return void
     */
    public function admin_options() {
        ?>
        <h3><?php _e('Google Pay - India Settings', 'chikkili-google-pay-for-woocommerce'); ?></h3>
        <div id="poststuff">
            <div id="post-body" class="metabox-holder columns-2">
                <div id="post-body-content">
                    <table class="form-table">
                        <?php $this->generate_settings_html(); ?>
                    </table><!--/.form-table-->
                </div>
            </div>
            <div class="clear"></div>
            <style type="text/css">
                .wpruby_button{
                    background-color:#4CAF50 !important;
                    border-color:#4CAF50 !important;
                    color:#ffffff !important;
                    width:100%;
                    padding:5px !important;
                    text-align:center;
                    height:35px !important;
                    font-size:12pt !important;
                }
            </style>
            <?php
        }
        
        public function process_payment($order_id) {
            global $woocommerce;
            $order = new WC_Order($order_id);
            // Mark as on-hold (we're awaiting the cheque)
            $order->update_status('pending', __('pending payment', 'chikkili-google-pay-for-woocommerce'));
           
            // Remove cart
            $woocommerce->cart->empty_cart();
            // Return thankyou redirect
            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true ),
            ); 
           
            
        }

        public function payment_fields() {

        }
         /*
        * In case you need a webhook
        */
         public function webhook() {
             // if payment is successfull gpay make this call.
             $order_id = isset($_REQUEST['order_id']) ? intval($_REQUEST['order_id']) : null;
            if (is_null($order_id)) return;
            $transaction_id = isset($_REQUEST['transaction_id']) ? intval($_REQUEST['transaction_id']) : null;
            $post = file_get_contents('php://input');
            $data = json_decode($post, true);

            if (is_null($transaction_id)) return;
            
             if(isset($data['details']['signature'])){
                    $tezResponse=json_decode($data['details']['tezResponse'], true);
                    $order = wc_get_order($order_id);
                    $order_total=$order->get_total();

                    if($tezResponse['Status']=='SUCCESS' && $tezResponse['amount']==$order_total && $tezResponse['txnId']==$transaction_id)
                        {
                            $order->payment_complete();
                            wc_reduce_stock_levels($order_id);
                            $order->add_order_note( "Google Pay transaction status ".$tezResponse['Status'] ." Transaction id ".$tezResponse['txnId']);
                            wp_send_json_success($order, 200);
                            
                        }else{
                            $order->add_order_note( "Google Pay transaction status ".$tezResponse['Status'] ." Transaction id ".$tezResponse['txnId']);
                        }
                    
                }
            

         }

}



?>