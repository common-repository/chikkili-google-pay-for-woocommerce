<?php

/**
 * @link       http://sevengits.com/
 * @since      1.0.0
 *
 * @package    Chikkili
 * @subpackage Chikkili/public/partials
 */
//load only on order pay page
if( !is_wc_endpoint_url( 'order-pay' ) ) {
	return;
}
global $wp;
$order_id = $wp->query_vars['order-pay'];
$order = wc_get_order( $order_id );
if($order->get_payment_method()!=='sg_gpay'){
	return;
}
// Get the desired gpay object
$selected_payment_method = WC()->payment_gateways->payment_gateways()[ 'sg_gpay'];
$gpay_gateway=$selected_payment_method ->settings;
// add additional details
$gpay_gateway['url']=stripslashes(get_site_url());
$gpay_gateway['tr']= $order_id;
$gpay_gateway['tn']= "Order #".$order_id;
$gpay_gateway['tid']=(string)time();

//remove unwanted values
unset($gpay_gateway['enabled']);
unset($gpay_gateway['title']);
unset($gpay_gateway['description']);
unset($gpay_gateway['instructions']);

$payment_object=json_encode($gpay_gateway,JSON_UNESCAPED_SLASHES);
$return_url_order=$order->get_checkout_order_received_url();
$callback_webhook=get_site_url().'/wc-api/sg_gpay'.'/?order_id='.$order_id.'&transaction_id='.$gpay_gateway['tid'];//get_site_url().'/wc-api/phonepe-payment-complete/?order_id='.$order_id.'&transaction_id='.$transaction_id
$order_total=$order->get_total();

?>

<script type="text/javascript">

		
		
                // Global key for canMakepayment cache.
				const canMakePaymentCache = 'canMakePaymentCache';
				jQuery(document).ready(function($) {
					 onBuyClicked();
					return true;
				});
						
				/**
				 * Read data for supported instruments from input from.
				 */
				
				
				/**
				 * Read the amount from input form.
				 */
				function readAmount() {
				  return <?php echo esc_attr($order_total); ?>
				}
				
				/**
				 * Launches payment request.
				 */
				function onBuyClicked() {
				  if (!window.PaymentRequest) {
					console.log('Web payments are not supported in this browser.');
					return;
				  }
				
				  let formValue = <?php echo $payment_object;?>;
				  
				
				  const supportedInstruments = [
					
					{
					  supportedMethods: ['https://tez.google.com/pay'],
					  data: formValue,
					},
				  ];
				
				  const details = {
					total: {
					  label: 'Total',
					  amount: {
						currency: 'INR',
						value: readAmount(),
					  },
					},
					displayItems: [
					  {
						label: 'Original amount',
						amount: {
						  currency: 'INR',
						  value: readAmount(),
						},
					  },
					],
				  };
				
				  const options = {
					requestShipping: false,
					requestPayerName: false,
					requestPayerPhone: false,
					requestPayerEmail: false,
					shippingType: 'shipping',
				  };
				
				  let request = null;
				  try {
					request = new PaymentRequest(supportedInstruments, details, options);
				  } catch (e) {
					console.log('Payment Request Error: ' + e.message);
					return;
				  }
				  if (!request) {
					console.log('Web payments are not supported in this browser.');
					return;
				  }
				
				  
				  var canMakePaymentPromise = checkCanMakePayment(request);
				  canMakePaymentPromise
					  .then((result) => {
						showPaymentUI(request, result);
					  })
					  .catch((err) => {
						console.log('Error calling checkCanMakePayment: ' + err);
					  });
				}
				
				/**
				 * Checks whether can make a payment with Tez on this device. It checks the
				 * session storage cache first and uses the cached information if it exists.
				 * Otherwise, it calls canMakePayment method from the Payment Request object and
				 * returns the result. The result is also stored in the session storage cache
				 * for future use.
				 *
				 * @private
				 * @param {PaymentRequest} request The payment request object.
				 * @return {Promise} a promise containing the result of whether can make payment.
				 */
				function checkCanMakePayment(request) {
				  // Checks canMakePayment cache, and use the cache result if it exists.
				  if (sessionStorage.hasOwnProperty(canMakePaymentCache)) {
					return Promise.resolve(JSON.parse(sessionStorage[canMakePaymentCache]));
				  }
				
				  // If canMakePayment() isn't available, default to assuming that the method is
				  // supported.
				  var canMakePaymentPromise = Promise.resolve(true);
				
				  // Feature detect canMakePayment().
				  if (request.canMakePayment) {
					canMakePaymentPromise = request.canMakePayment();
				  }
				
				  return canMakePaymentPromise
					  .then((result) => {
						// Store the result in cache for future usage.
						sessionStorage[canMakePaymentCache] = result;
						return result;
					  })
					  .catch((err) => {
						console.log('Error calling canMakePayment: ' + err);
					  });
				}
				
				/**
				 * Show the payment request UI.
				 *
				 * @private
				 * @param {PaymentRequest} request The payment request object.
				 * @param {Promise} canMakePayment The promise for whether can make payment.
				 */
				function showPaymentUI(request, canMakePayment) {
				  // Redirect to play store if can't make payment.
				  if (!canMakePayment) {
					alert('Google Pay not in phone');
					return;
				  }
				
				  // Set payment timeout.
				  let paymentTimeout = window.setTimeout(function() {
					window.clearTimeout(paymentTimeout);
					request.abort()
						.then(function() {
						  console.log('Payment timed out after 20 minutes.');
						})
						.catch(function() {
						  console.log('Unable to abort, user is in the process of paying.');
						});
				  }, 20 * 60 * 1000); /* 20 minutes */
				
				  request.show()
					  .then(function(instrument) {
						window.clearTimeout(paymentTimeout);
						processResponse(instrument);  // Handle response from browser.
					  })
					  .catch(function(err) {
						console.log(err);
					  });
				}
				
				/**
				 * Process the response from browser.
				 *
				 * @private
				 * @param {PaymentResponse} instrument The payment instrument that was authed.
				 */
				function processResponse(instrument) {
					console.log(instrument);
					console.log(JSON.stringify(instrument));

				  fetch('<?php echo esc_url($callback_webhook); ?>', {
					method: 'POST',
					headers: new Headers({'Content-Type': 'application/json'}),
					body: JSON.stringify(instrument),
					//credentials: 'include',
				  })
					  .then(function(buyResult) {
						if (buyResult.ok) {
						  return buyResult.json();
						}
						console.log('Error sending instrument to server.');
					  })
					  .then(function(buyResultJson) {
						completePayment(
							instrument, buyResultJson.status, buyResultJson.message);
					  })
					  .catch(function(err) {
						console.log('Unable to process payment. ' + err);
					  });
				}
				
				/**
				 * Notify browser that the instrument authorization has completed.
				 *
				 * @private
				 * @param {PaymentResponse} instrument The payment instrument that was authed.
				 * @param {string} result Whether the auth was successful. Should be either
				 * 'success' or 'fail'.
				 * @param {string} msg The message to log in console.
				 */
				function completePayment(instrument, result, msg) {
				  instrument.complete(result)
					  .then(function() {
						console.log('Payment completes.Redirect here');
						window.location.replace("<?php echo esc_url($return_url_order); ?>");
						console.log(msg);
						
					  })
					  .catch(function(err) {
						console.log(err);
					  });
				}
				
</script>

