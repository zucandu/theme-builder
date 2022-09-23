<?php 

namespace Advshipper;
use DB;

class AdvancedShipperFedExCalculator extends \Advancedshipper
{
	/**
	 * The configuration settings for this instance.
	 *
	 * @var     array
	 * @access  public
	 */
    public $_config = null;

    public $insurance = 0;
	
	/**
	 * Create a new instance of the AdvancedShipperFedExCalculator class
	 *
	 * @param   array     $fedex_config   An associative array with the configuration settings for
	 *                                    this instance.
	 */
	function __construct($fedex_config)
    {
        parent::__construct();

        $this->_config = $fedex_config;
    }


    /**
	 * Contacts Fedex and gets a quote for the specified weight and configuration settings.
	 *
	 * @access  public
	 * @param   float     $weight    The weight of the package to be shipped.
	 * @param   float     $price     The total price of the items in package to be shipped.
	 * @param   array     $min_max   Any minimum/maximum limits which should be applied to the final
	 *                               rate calculated.
	 * @return  none
	 */
	function quote($weight, $price, $min_max)
    {
        
        if ($weight < 0.1) {
            $weight = 0.1;
        }
        
        $rate_info = $this->_getQuote($weight, $min_max);

        return $rate_info;
    }


    /**
	 * Contacts FedEx, gets a quote, parses the response and builds the list of quotes.
	 *
	 * @access  protected
	 * @return  array|boolean   Array of results or boolean false if no results.
     */
	function _getQuote($weight, $min_max)
	{

        // Calculate shipping boxes
        $shipping_num_boxes = 1;
        $shipping_weight = $weight;
        $shipping_max_weight = 50;

        if ($shipping_weight > $shipping_max_weight) { // Split into many boxes
            $zc_boxes =  round(($shipping_weight/$shipping_max_weight) * pow(10, 2), 0);
            $zc_boxes = $zc_boxes/pow(10, 2);
            $shipping_num_boxes = ceil($zc_boxes);
            $shipping_weight = $shipping_weight/$shipping_num_boxes;
        }

        // Customer shipping address
        $shippingAddress = $this->getShippingAddress();

        // BEGIN PO Box Ban street address
        $_z__is_po_box = false;
        $street_address = $shippingAddress['address_line_1'];
        if ( preg_match('/PO BOX/si', $street_address) ) {
            $_z__is_po_box = true;
        } else if ( preg_match('/BOX/si', $street_address) ) {
            $_z__is_po_box = true;
        } else if ( preg_match('/POBOX/si', $street_address) ) {
            $_z__is_po_box = true;
        } else if ( preg_match('/P\.O\./si', $street_address) ) {
            $_z__is_po_box = true;
        } else if ( preg_match('/P\.O/si', $street_address) ) {
            $_z__is_po_box = true;
        } else if ( preg_match('/PO\./si', $street_address) ) {
            $_z__is_po_box = true;
        }

        // BEGIN PO Box Ban suburb
        $suburb = $shippingAddress['address_line_2'];
        if ( preg_match('/PO BOX/si', $suburb) ) {
            $_z__is_po_box = true;
        } else if ( preg_match('/BOX/si', $suburb) ) {
            $_z__is_po_box = true;
        } else if ( preg_match('/POBOX/si', $suburb) ) {
            $_z__is_po_box = true;
        } else if ( preg_match('/P\.O\./si', $suburb) ) {
            $_z__is_po_box = true;
        } else if ( preg_match('/P\.O/si', $suburb) ) {
            $_z__is_po_box = true;
        } else if ( preg_match('/PO\./si', $suburb) ) {
            $_z__is_po_box = true;
        }

        if($_z__is_po_box === true) {
            return false;
        }

        // Environment
        switch ($this->_config['environment']) {
            case '1':
                $path_to_wsdl = "/FedExRateService_v20.wsdl";
            break;
            case '2':
            default:
                $path_to_wsdl = "RateService_v13_test.wsdl";
            break;
          }

        ini_set("soap.wsdl_cache_enabled", "0");
        
        // Setting a connection timeout (five seconds on the example)
        try {
            $client = new \SoapClient(dirname(__FILE__) . $path_to_wsdl, [
                'trace' => 1, 
                'connection_timeout' => 5, 
                'exceptions' => true
            ]);
        } catch (\SoapFault $e) {
            //var_dump($e->getMessage());die;
        }
        

        // customer details      
        $street_address = $shippingAddress['address_line_1'];
        $street_address2 = $shippingAddress['address_line_2'];
        $city = $shippingAddress['city'];
        $state = $shippingAddress['zone_code'];
        if ($state == "QC") $state = "PQ";
        $postcode = str_replace(array(' ', '-'), '', $shippingAddress['postcode']);
        $country_code = $shippingAddress['country_code'];
        
        $totals = $this->order['subtotal'];

        $request['WebAuthenticationDetail'] = array(
            'UserCredential' => array(
                'Key' =>  $this->_config['fedex_key'], 
                'Password' => $this->_config['fedex_password']
            )
        );
        $request['ClientDetail'] = array(
            'AccountNumber' => $this->_config['fedex_account_number'], 
            'MeterNumber' => $this->_config['fedex_meter_number']
        );
        $request['TransactionDetail'] = array('CustomerTransactionId' => 'A noted from FedEx #11111');
        $request['Version'] = array('ServiceId' => 'crs', 'Major' => '20', 'Intermediate' => '0', 'Minor' => '0');
        $request['ReturnTransitAndCommit'] = true;
        $request['RequestedShipment']['DropoffType'] = $this->_config['drop_off_type']; // valid values REGULAR_PICKUP, REQUEST_COURIER, ...
        $request['RequestedShipment']['ShipTimestamp'] = date('c');
        $request['RequestedShipment']['PackagingType'] = $this->_config['packaging_type']; // valid values FEDEX_BOX, FEDEX_PAK, FEDEX_TUBE, YOUR_PACKAGING, ...
        $request['RequestedShipment']['TotalInsuredValue'] = array(
            'Amount'=> $this->insurance, 
            'Currency' => $this->order['currency']
        ); 

        // Address Validation
        $validate = "false";
        //$validate = 'false';
        $residential_address = true;
        $address_validation = false;

        if ($validate == 'true') {
            $av_client = new \SoapClient("FedExAddressValidationService_v4.wsdl", array('trace' => 1));
            $av_request['WebAuthenticationDetail'] = array(
                'UserCredential' => array(
                    'Key' => $this->_config['fedex_key'], 
                    'Password' => $this->_config['fedex_password']
                )
            );
            $av_request['ClientDetail'] = array('AccountNumber' => $this->_config['fedex_account_number'], 'MeterNumber' => $this->_config['fedex_meter_number']);
            $av_request['TransactionDetail'] = array('CustomerTransactionId' => ' *** Address Validation Request v2 using PHP ***');
            $av_request['Version'] = array('ServiceId' => 'aval', 'Major' => '2', 'Intermediate' => '0', 'Minor' => '0');
            $av_request['RequestTimestamp'] = date('c');
            $av_request['Options'] = array(
                'CheckResidentialStatus' => 1,
                'MaximumNumberOfMatches' => 5,
                'StreetAccuracy' => 'LOOSE',
                'DirectionalAccuracy' => 'LOOSE',
                'CompanyNameAccuracy' => 'LOOSE',
                'ConvertToUpperCase' => 1,
                'RecognizeAlternateCityNames' => 1,
                'ReturnParsedElements' => 1
            );
            $av_request['AddressesToValidate'] = array(
                array(
                    'AddressId' => 'Customer Address',
                    'Address' => array(
                        'StreetLines' => array(utf8_encode($street_address), utf8_encode($street_address2)),
                        'PostalCode' => $postcode,
                        'CompanyName' => $shippingAddress['company']
                    )
                )
            );

            try {

                $av_response = $av_client->addressValidation($av_request);

                if ($av_response->HighestSeverity == 'SUCCESS') {
                    $address_validation = true;
                    if ($av_response->AddressResults->ProposedAddressDetails->ResidentialStatus == 'BUSINESS') {
                        $residential_address = false;
                    } // already set to true so no need for else statement
                }
            } catch (Exception $e) {
                $this->quotes = array('module' => $this->title, 'error'  => 'Sorry, the FedEx.com server is currently not responding, please try again later.');
            }
        }

        if ($address_validation == false) {
            if (!empty($shippingAddress['company'])) {
                $residential_address = false;
            } else {
                $residential_address = true;
            }
        }

        $request['RequestedShipment']['Shipper'] = array(
            'Address' => array(
                'StreetLines' => '', // Origin details
                'City' => '',
                'StateOrProvinceCode' => '',
                'PostalCode' => $this->_config['source_postcode'],
                'CountryCode' => $this->_config['source_country']
            )
        );          
        $request['RequestedShipment']['Recipient'] = array(
            'Address' => array (
                'StreetLines' => array(utf8_encode($street_address), utf8_encode($street_address2)),
                'City' => utf8_encode($city),
                'PostalCode' => $postcode,
                'CountryCode' => $country_code,
                'Residential' => $residential_address
            )
        ); 
        if (in_array($country_code, array('US', 'CA'))) {
            $request['RequestedShipment']['Recipient']['StateOrProvinceCode'] = $state;
        }
              
        $request['RequestedShipment']['ShippingChargesPayment'] = array(
            'PaymentType' => 'SENDER',
            'Payor' => array(
                'AccountNumber' => $this->_config['fedex_account_number'],
                'CountryCode' => $this->_config['source_country']
            )
        );
        $request['RequestedShipment']['RateRequestTypes'] = 'LIST'; 
        $request['RequestedShipment']['PackageDetail'] = 'INDIVIDUAL_PACKAGES';
        $request['RequestedShipment']['RequestedPackageLineItems'] = array();
        
        $dimensions_failed = false;
        
        $boxed_value = 0;//sprintf("%01.2f", $this->insurance / $shipping_num_boxes);
        if ($shipping_weight == 0) $shipping_weight = 0.01;
        
        for ($i=0; $i<$shipping_num_boxes; $i++) {
            $request['RequestedShipment']['RequestedPackageLineItems'][] = array(
                'Weight' => array(
                    'Value' => $shipping_weight,
                    'Units' => strtoupper($this->_config['weight_units'])
                ),
                'GroupPackageCount' => 1,
                'InsuredValue' => array(
                    'Currency' => $this->order['currency'],
                    'Amount' => $boxed_value
                ),                  
            );
        }

        $request['RequestedShipment']['PackageCount'] = $shipping_num_boxes;
    
        if ($this->_config['saturday_delivery'] == 1) {
            $request['RequestedShipment']['ServiceOptionType'] = 'SATURDAY_DELIVERY';
        }

        if ((int)$this->_config['signature_option'] >= 0 && $totals >= (int)$this->_config['signature_option']) { 
            $request['RequestedShipment']['SpecialServicesRequested'] = 'SIGNATURE_OPTION'; 
        }

        if($shipping_weight <= 1){
            $shipping_weight = round($shipping_weight, 2);
            $request['RequestedShipment']['SmartPostDetail']['Indicia'] = 'PRESORTED_STANDARD';
        }
        else{
            $request['RequestedShipment']['SmartPostDetail']['Indicia'] = 'PARCEL_SELECT';
        }

        $request['RequestedShipment']['SmartPostDetail']['AncillaryEndorsement'] = 'ADDRESS_CORRECTION';
        $request['RequestedShipment']['SmartPostDetail']['HubId'] = 5431;

        /**
         * Rates details
         */
        $rate_info = [];

        foreach($this->_config['fedex_services'] as $servccalld){   

            $request['RequestedShipment']['ServiceType'] = $servccalld;

            try {
                $response = $client->getRates($request);

                if($response->HighestSeverity === "ERROR" || $response->HighestSeverity === "FAILURE") {
                    continue;
                }

                if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR' && is_array($response->RateReplyDetails) || is_object($response->RateReplyDetails)) {
                    
                    if (is_object($response->RateReplyDetails)) {
                      $response->RateReplyDetails = get_object_vars($response->RateReplyDetails);
                    }

                    $shipping_box_weight_display = 0;
                    switch ($shipping_box_weight_display) {
                        case (0):
                            $show_box_weight = '';
                        break;
                        case (1):
                            $show_box_weight = ' (' . $shipping_num_boxes . ' ' . TEXT_SHIPPING_BOXES . ')';
                        break;
                        case (2):
                            $show_box_weight = ' (' . number_format($shipping_weight * $shipping_num_boxes,2) . TEXT_SHIPPING_WEIGHT . ')';
                        break;
                        default:
                            $show_box_weight = ' (' . $shipping_num_boxes . ' x ' . number_format($shipping_weight,2) . TEXT_SHIPPING_WEIGHT . ')';
                        break;
                    }

                    if (!$response) {
                        $this->_debug("Error: " . $servccalld);
                        continue;
                    }

                    $severity = $response->HighestSeverity;
                    $message = "";
                    foreach($response->Notifications as $noti) {
                        $message .= "{$noti->Code}: {$noti->Message} ";
                    }

                    $service_name = ucwords(strtolower(str_replace('_', ' ', str_replace('FEDEX', '', $response->RateReplyDetails["ServiceType"]))));

                    if (isset($rated->AppliedOptions) && $rated->AppliedOptions === 'SATURDAY_DELIVERY') {
                        $service_name .= " (Saturday Delivery)";
                    }

                    $ratedshipment = $response->RateReplyDetails["RatedShipmentDetails"];
                    $ratedetails = get_object_vars($ratedshipment[0]);
                    $shipdetail = get_object_vars($ratedetails["ShipmentRateDetail"]);
                    $totalbillweight = get_object_vars($shipdetail["TotalBillingWeight"]);
                    $weight = $totalbillweight["Value"];
                    $netcharge = get_object_vars($shipdetail["TotalNetFedExCharge"]);
                    $rate = $netcharge["Amount"];

                    // Rate is always returned in currency of account holder. Must convert to currency in
                    // use
                    $rate_currency = $netcharge["Currency"];

                    $currencyObj = DB::table('currencies')->where('code', $rate_currency)->first();

                    $current_display_currency = strtoupper($this->order['currency']);

                    if ($rate_currency !== $current_display_currency) {
                        // Must convert from FedEx currency to base currency
                        if (isset($currencyObj->rate) && $currencyObj->rate <= 0) {
                            // FedEx currency doesn't have a conversion value, can't proceed!
                            continue;
                        }
                        $rate_base_conv_rate = 1 / $currencyObj->rate;
                        $rate = number_format($rate * $rate_base_conv_rate, 2, '.', '');
                    }

                    
                    if ($min_max != false) {
                        // Apply the limit(s) to the rate
                        $rate_limited = $this->calcMinMaxValue($rate, $min_max['min'], $min_max['max']);
                        
                        if ($rate_limited != $rate) {
                            $rate = $rate_limited;
                        }
                    }

                    $rate_info[] = array(
                        'rate' => $rate,
                        'rate_components_info' => array(
                            array(
                                'value_band_total' => $rate,
                                'individual_value' => null,
                                'num_individual_values' => $weight,
                                'additional_charge' => null,
                                'calc_method' => "fedex"
                            )
                        ),
                        'rate_extra_title' => " {$service_name}"
                    );

                }
                
            } catch (\SoapFault $e) {
                //trigger_error($e->detail->desc, E_USER_ERROR);
            }
            // end try catch

        }

        return $rate_info;

    }

    /**
     * 
     */
    public function tracking($order)
    {
        $request = [];

        $path_to_wsdl = "/FedExTrackService_v19.wsdl";

        ini_set("soap.wsdl_cache_enabled", "0");
        
        // Setting a connection timeout (five seconds on the example)
        try {

            $client = new \SoapClient(dirname(__FILE__) . $path_to_wsdl, [
                'trace' => 1, 
                'connection_timeout' => 5, 
                'exceptions' => true
            ]);

            $request['WebAuthenticationDetail'] = array(
                'UserCredential' => array(
                    'Key' =>  $this->_config['fedex_key'], 
                    'Password' => $this->_config['fedex_password']
                )
            );
            $request['ClientDetail'] = array(
                'AccountNumber' => $this->_config['fedex_account_number'], 
                'MeterNumber' => $this->_config['fedex_meter_number']
            );
            
            // Build Customer Transaction Id
            $request['TransactionDetail'] = array(
                'CustomerTransactionId' => "Order #: {$order->order_id}"
            );

            $request['Version'] = array(
                'ServiceId'    => "trck",
                'Major'        => 19,
                'Intermediate' => 0,
                'Minor'        => 0
            );

            // Build Tracking Number info
            $request['SelectionDetails'] = array(
                'PackageIdentifier' => array(
                    'Type'  => 'TRACKING_NUMBER_OR_DOORTAG',
                    'Value' => $order->tracking_number
                )
            );
            
            $trackDetails = $client->track($request);
            //var_dump($trackDetails->CompletedTrackDetails->TrackDetails->StatusDetail);
            return $trackDetails;

        } catch (\SoapFault $e) {
            //var_dump($e->getMessage());die;
        }
    }

}