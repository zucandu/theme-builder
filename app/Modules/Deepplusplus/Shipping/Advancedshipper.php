<?php

define('ADVSHIPPER_AVAILABILITY_SCHEDULING_ONCE_ONLY', 2);
define('ADVSHIPPER_AVAILABILITY_SCHEDULING_RECURRING', 3);
define('ADVSHIPPER_AVAILABILITY_RECURRING_MODE_WEEKLY', 1);
define('ADVSHIPPER_AVAILABILITY_WEEKLY_SHIPPING_SCHEDULING_NONE', 1);

use Illuminate\Support\FacadesDB;

/**
 * Class Rate.
 *
 */
class Advancedshipper
{
    
    public $code, $name, $description, 
            $order, $fields, $status, $_time_adjust;
	public $quotes = [], $_methods = [];
	
	public $tax_class = 0;

    /**
     * Constructor
     */
    public function __construct() {

        $this->order = session('order');
        
        $this->code  = 'Advancedshipper';
        $this->name = 'Advanced Shipper';
        $this->description = 'Advanced Shipper';
    }

    /**
     * Config fields then show on the backend to set config
     */
    public function fields() {

        // USPS services
        $methodSorting = [
            ['id' => 1, 'name' => 'By Cost - lowest to highest'],
            ['id' => 2, 'name' => 'By Cost - highest to lowest']
        ];

        // USPS services
        $debugItems = [
            ['id' => 1, 'name' => 'True'],
            ['id' => 0, 'name' => 'False']
        ];

        return $this->fields = [
            [
                'label' => 'Enable Advanced Shipper Shipping', 
                'description' => 'Do you want to offer Advanced Shipper rate shipping?',
                'key' => 'module_shipping_advancedshipper_active',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_advancedshipper_active'),
                'input' => 'select',
                'options' => [
                    ['id' => 1, 'name' => 'Enabled'],
                    ['id' => 0, 'name' => 'Disabled']
                ]
            ],
            [
                'label' => 'Method Sorting', 
                'description' => 'Select your method sorting for the shipping rates.',
                'key' => 'module_shipping_advancedshipper_method_sorting',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_advancedshipper_method_sorting'),
                'options' => $methodSorting,
                'input' => 'select'
            ],
            [
                'label' => 'Enable Debug?', 
                'description' => 'Turn on debug mode',
                'key' => 'module_shipping_advancedshipper_debug',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_advancedshipper_debug'),
                'options' => $debugItems,
                'input' => 'select'
            ],
            [
                'label' => 'Sort Order', 
                'description' => 'Sort order of display',
                'key' => 'module_shipping_advancedshipper_sort_order',
                'value' => \Deepplusplus\Setting\Helpers::config('module_shipping_advancedshipper_sort_order'),
                'input' => 'text'
            ],
        ];
    }

    /**
     * Check status
     */
    public function status($isBackend = false) {
        
        $this->status = \Deepplusplus\Setting\Helpers::config('module_shipping_advancedshipper_active') == 1 ? true : false;
        if($isBackend === true) {
            return $this->status;
        }

        return $this->status;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  array  $params
     * @return true
     */
    public function storeAdvshipperMethod($params)
    {
        $methodId = DB::table('advshipper_methods')->insertGetId([
            'method_admin_title' => $params['method_admin_title'],
            'method_title' => $params['method_title'],
            'apply' => $params['apply']
        ]);
        
        if(count($params['categories']) > 0) {
            //DB::table('advshipper_categories')->where('method_id', $methodId)->delete();
            foreach($params['categories'] as $id) {
                DB::table('advshipper_categories')->insertOrIgnore([
                    'method_id' => $methodId,
                    'category_id' => $id
                ]);
            }
        }

        if(count($params['manufacturers']) > 0) {
            //DB::table('advshipper_manufacturers')->where('method_id', $methodId)->delete();
            foreach($params['manufacturers'] as $id) {
                DB::table('advshipper_manufacturers')->insertOrIgnore([
                    'method_id' => $methodId,
                    'manufacturer_id' => $id
                ]);
            }
        }

        if(count($params['products']) > 0) {
            //DB::table('advshipper_products')->where('method_id', $methodId)->delete();
            foreach($params['products'] as $product) {
                DB::table('advshipper_products')->insertOrIgnore([
                    'method_id' => $methodId,
                    'product_id' => $product['id']
                ]);
            }
        }

        return $this->methodDetailsById($methodId);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  array  $params
     * @return true
     */
    public function storeAdvshipperRegion($params)
    {
        DB::table('advshipper_regions')->insert([
            'method_id' => $params['method_id'],
            'region_title' => $params['region_title'],
            'definition_method' => $params['definition_method'],
            'country_postcode' => $params['country_postcode'],
            'geolocation' => isset($params['geolocation']) ? $params['geolocation'] : null,
            'tax_class' => isset($params['tax_class']) ? $params['tax_class'] : 0,
            'rate_include_tax' => $params['rate_include_tax'],
            'rate_limits' => $params['rate_limits'],
            'total_price_of_applicable' => $params['total_price_of_applicable'],
            'table_of_rates' => $params['table_of_rates'],
            'max_weight_per_package' => $params['max_weight_per_package'],
            'packaging_weights' => $params['packaging_weights'],
            'surcharge_discount' => $params['surcharge_discount'],
            'usps_calculator' => $params['usps_calculator'],
            'usps_configuration' => (int)$params['usps_calculator'] === 1 ? serialize($params['usps_configuration']) : null,
            'ups_calculator' => $params['ups_calculator'],
            'ups_configuration' => (int)$params['ups_calculator'] === 1 ? serialize($params['ups_configuration']) : null,
            'fedex_calculator' => $params['fedex_calculator'],
            'fedex_configuration' => (int)$params['fedex_calculator'] === 1 ? serialize($params['fedex_configuration']) : null,
        ]);

        return $this->methodDetailsById($params['method_id']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  array  $params
     * @return true
     */
    public function store($params)
    {
        switch($params['side']) {
            case 'method':
                return $this->storeAdvshipperMethod($params);
            break;
            case 'region':
                return $this->storeAdvshipperRegion($params);
            break;
        }

        return false;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $methods = DB::table('advshipper_methods')->get();
        $methods->map(function($method){
            $method->number_of_regions = DB::table('advshipper_regions')->where('method_id', $method->id)->count();
            return $method;
        });
        return $methods;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        list($side, $id) = explode('__', $id);
        switch($side) {
            case 'method':
                return $this->methodDetailsById($id);
            break;
            case 'region':
                return $this->regionDetailsById($id);
            break;
        }
        return true;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function methodDetailsById($id)
    {
        $method = DB::table('advshipper_methods')->where('id', $id)->first();
        $method->categories = DB::table('advshipper_categories')->where('method_id', $id)->pluck('category_id')->toArray();
        $method->manufacturers = DB::table('advshipper_manufacturers')->where('method_id', $id)->pluck('manufacturer_id')->toArray();
        $method->products = DB::table('advshipper_products')->where('method_id', $id)->pluck('product_id')->toArray();
		$method->regions = DB::table('advshipper_regions')->where('method_id', $id)->get();
        return $method;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function regionDetailsById($id)
    {
        $region = DB::table('advshipper_regions')->where('id', $id)->first();
        if($region->usps_calculator) {
            $region->usps_configuration = unserialize($region->usps_configuration);
        }
        if($region->ups_calculator) {
            $region->ups_configuration = unserialize($region->ups_configuration);
        }
        if($region->fedex_calculator) {
            $region->fedex_configuration = unserialize($region->fedex_configuration);
        }
        return $region;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($params)
    {
        switch($params['side']) {
            case 'method':
                return $this->updateMethod($params);
            break;
            case 'region':
                return $this->updateRegion($params);
            break;
        }
        return true;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateMethod($params)
    {
        $methodId = $params['id'];

        $method = DB::table('advshipper_methods')->where('id', $methodId)->update([
            'method_admin_title' => $params['method_admin_title'],
            'method_title' => $params['method_title'],
            'apply' => $params['apply'],
        ]);
        
        DB::table('advshipper_categories')->where('method_id', $methodId)->delete();
        if(count($params['categories']) > 0) {
            foreach($params['categories'] as $id) {
                DB::table('advshipper_categories')->insert([
                    'method_id' => $methodId,
                    'category_id' => $id
                ]);
            }
        }

        DB::table('advshipper_manufacturers')->where('method_id', $methodId)->delete();
        if(count($params['manufacturers']) > 0) {
            foreach($params['manufacturers'] as $id) {
                DB::table('advshipper_manufacturers')->insert([
                    'method_id' => $methodId,
                    'manufacturer_id' => $id
                ]);
            }
        }

        DB::table('advshipper_products')->where('method_id', $methodId)->delete();
        if(count($params['products']) > 0) {
            foreach($params['products'] as $id) {
                DB::table('advshipper_products')->insert([
                    'method_id' => $methodId,
                    'product_id' => $id
                ]);
            }
        }
        
		return $this->methodDetailsById($methodId);
    }

    public function updateRegion($params)
    {
        DB::table('advshipper_regions')->where('id', $params['id'])->update([
            'region_title' => $params['region_title'],
            'definition_method' => $params['definition_method'],
            'country_postcode' => $params['country_postcode'],
            'geolocation' => isset($params['geolocation']) ? $params['geolocation'] : null,
            'tax_class' => isset($params['tax_class']) ? $params['tax_class'] : 0,
            'rate_include_tax' => $params['rate_include_tax'],
            'rate_limits' => $params['rate_limits'],
            'total_price_of_applicable' => $params['total_price_of_applicable'],
            'table_of_rates' => $params['table_of_rates'],
            'max_weight_per_package' => $params['max_weight_per_package'],
            'packaging_weights' => $params['packaging_weights'],
            'surcharge_discount' => $params['surcharge_discount'],
            'usps_calculator' => $params['usps_calculator'],
            'usps_configuration' => (int)$params['usps_calculator'] === 1 ? serialize($params['usps_configuration']) : null,
            'ups_calculator' => $params['ups_calculator'],
            'ups_configuration' => (int)$params['ups_calculator'] === 1 ? serialize($params['ups_configuration']) : null,
            'fedex_calculator' => $params['fedex_calculator'],
            'fedex_configuration' => (int)$params['fedex_calculator'] === 1 ? serialize($params['fedex_configuration']) : null,
        ]);
        return true;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($params)
    {
        switch($params['side']) {
            case 'method':
                DB::table('advshipper_methods')->where('id', $params['id'])->delete();
            break;
            case 'region':
                DB::table('advshipper_regions')->where('id', $params['id'])->delete();
            break;
        }
        return $this->list();
        
    }

    /**
     * Get cart total weight
     * @return $totalWeight
     */
    public function orderTotalWeight() {
        
        $totalWeight = 0;

        $cartItems = $this->order['items'];
        foreach($cartItems as $item) {
            $totalWeight += $item['weight']*$item['qty'];
        }
        
        return $totalWeight;
    }

    /**
     * Get number of items
     * @return $numberOfItems
     */
    public function orderNumberOfItems() {
        $numberOfItems = 0;
        $cartItems = $this->order['items'];
        foreach($cartItems as $item) {
            $numberOfItems += $item['qty'];
        }
        
        return $numberOfItems;
    }

    /**
     * Get shipping address
     */
    public function getShippingAddress() {
        $key = array_search($this->order['profile']['default_shipping_address_id'], array_column($this->order['profile']['addresses'], 'id'));
        return $shippingAddress = $this->order['profile']['addresses'][$key];
    }

	/**
     * Calculate tax
     * @param Array $shippingAddress
     * @return Float taxRatePercentage
     */
    public function calculateTax($shippingAddress) {
        $taxRatePercentage = 0;
        if(\Deepplusplus\Setting\Helpers::config('module_shipping_advancedshipper_tax_class') > 0) {
            $taxRates = DB::table('tax_rates')->select('module_zone_id')->where('tax_class_id', DB::table('tax_classes')->where('id', \Deepplusplus\Setting\Helpers::config('module_shipping_advancedshipper_tax_class'))->pluck('id')->first())->get();
            foreach($taxRates as $rate) {
                $moduleRegions = DB::table('module_zone_regions')->where('module_zone_id', $rate->module_zone_id)->get();
                if(!$moduleRegions->isEmpty()) {
                    foreach($moduleRegions as $region) {
                        if(($region->country_code === $shippingAddress['country_code']) && ($region->zone_code === $shippingAddress['zone_code'])) {
                            $taxRatePercentage = $rate->rate;
                        }
                    }
                }
            }
        }
        return $taxRatePercentage;
    }

    /**
     * get quotes
     */
    public function quotes($selected_method_combination = '') {

        $this->quotes = [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'methods' => [],
        ];

        // Shipping address
        $shippingAddress = $this->getShippingAddress();

        // Products
        $products = $this->order['items'];

        $usable_methods = $this->_getUsableMethodsInfo();
        
        if ($usable_methods === false) {
            // An error occurred or no methods are usable
			// Add dummy default method so shipping estimator will display error!
			$this->quotes['methods'][0] = array();
			
			if (!isset($this->quotes['error'])) {
                // Check shipping address
                if(empty($shippingAddress['postcode']) && empty($shippingAddress['zone_code']) && empty($shippingAddress['zone_name'])) {
                    $this->quotes['error'] = "Please update your shipping address.";
                }
                return $this->quotes;
			}
			
			return $this->quotes;
        }
        
        // Now that all the data has been collected for the methods that may possibly be used for
		// this order/items in the order, work out any rates that can be used //////////////////////
		$rates_calc_success = $this->_calcMethodRates();
		
		if ($rates_calc_success === false) {
			// An error occurred when calculating the rates which must be displayed to the customer
			
			// Add dummy default method so shipping estimator will display error!
			$this->quotes['methods'][0] = array();
			
			return $this->quotes;
		}

		// Must check to see if any products no longer have any usable methods due to a rate not
		// being able to be calculated for its usable method(s)
		$usable_methods = $this->_verifyAllProductsHaveUsableMethods();
		
		if ($usable_methods === false) {
			// At least one product has no usable methods
			$this->quotes['error'] = "Please enter your Post/Zip Code.";
			
			// Add dummy default method so shipping estimator will display error!
			$this->quotes['methods'][0] = array();
			
			return $this->quotes;
		}
		
		// Create the "instances" of the shipping methods. Non-dated methods will have 1 instance
		// only but dated instances could have several instances created as options for several
		// weeks in advance ////////////////////////////////////////////////////////////////////////
		$this->_createMethodInstances();

		// Must check to see if any products no longer have any usable methods due to the method(s)
		// not being available at this time
		$usable_methods = $this->_verifyAllProductsHaveUsableMethods();
		
		if ($usable_methods === false) {
			// At least one product has no usable methods
			$this->quotes['error'] = "Sorry but there are no shipping options available to your address at this time!";
			
			// Add dummy default method so shipping estimator will display error!
			$this->quotes['methods'][0] = array();
			
			return $this->quotes;
		}
		
		// Build an array of all possible usable combinations for shipping methods for the products
		// in the cart. Each product must have a single shipping method, no more, no less!
		$method_combinations = $this->_getUsableCombinations();
		
		if ($method_combinations === false) {
			// There are no combinations of the usable shipping methods which will cover each
			// product in the cart once only
			$this->quotes['error'] = "Sorry but our shipping options don't cover the combination of products currently in the cart!";
			
			// Add dummy default method so shipping estimator will display error!
			$this->quotes['methods'][0] = array();
			
			return $this->quotes;
		}

		// Actually build the quotes! //////////////////////////////////////////////////////////////
		$this->_buildQuotes($method_combinations, $selected_method_combination);
		
		/* if (sizeof($this->quotes['methods']) == 1) {
			$this->quotes['name'] = $this->quotes['methods'][0]['title'];
		} */
		
		return $this->quotes;


	}
	
	/* Builds the quotes for this module, using the list of usable method combinations.
	 *
	 * @access  public
	 * @param   array     $method_combinations   The information about which method combinations to
	 *                                           be used.
	 * @param   string    $selected_method_combination   The ID of the method combination a quote
	 *                                                   should be generated for (if any)
	 * @return  boolean   True if first array item comes after second, false otherwise.
	 */
	public function _buildQuotes($method_combinations, $selected_method_combination)
	{
		
		$num_method_combinations = sizeof($method_combinations);
		
		foreach ($method_combinations as $method_comb_id => $method_comb) {
			// Has no method been selected by the customer yet or is this the selected method
			// combination?
			if ($selected_method_combination == '' || (!empty($selected_method_combination) && $selected_method_combination == $method_comb_id)) {
				
				$method_comb_title = '';
				$method_comb_cost = 0;
				$shipping_ts = null;
				
				$num_methods_in_comb = sizeof($method_comb);
				
				foreach ($method_comb as $method_instance) {

					$method_num = $method_instance['method'];
					
					$rate_i = $method_instance['rate_i'];
					
					$method_comb_cost += $this->_methods[$method_num]['rates'][$rate_i]['rate'];
					
					if (isset($this->_methods[$method_num]['rates'][$rate_i]['contact_after_order'])) {
						// Handle special case of asking customer to contact shop after completing
						// their order
						
						// Load the appropriate contact after order message
						$contact_after_order_message = null;
						
						if (preg_match('/contact_after_order([0-9]+)/i', $this->_methods[$method_num]['rates'][$rate_i]['contact_after_order'], $matches)) {
							$contact_after_order_message_num = $matches[1];
							
							if (defined('MODULE_ADVANCED_SHIPPER_TEXT_CONTACT_STORE_AFTER_ORDER' . $contact_after_order_message_num)) {
								// Specific message exists for this contact after order message
								// flag, use it!
								$contact_after_order_message = constant('MODULE_ADVANCED_SHIPPER_TEXT_CONTACT_STORE_AFTER_ORDER' . $contact_after_order_message_num);
							}
						}
						
						if (is_null($contact_after_order_message)) {
							// Use default (first) contact after order message
							$contact_after_order_message = "This method includes one or more products which require personal shipping arrangements. If you select this method, you must contact us to arrange shipping/finalise shipping costs after completing your order.";
						}
						
						$method_comb_title .= $contact_after_order_message;
						continue;
					}
					
					// Check if the tax classes for all methods in the combination are the same
					
					if (!is_null($this->_methods[$method_num]['tax_class']) && $this->_methods[$method_num]['tax_class'] != 0) {
						$this->tax_class = $this->_methods[$method_num]['tax_class'];
					}
					
					$current_method_title = $this->_methods[$method_num]['method_title'];
					
					if (is_null($current_method_title) || strlen($current_method_title) == 0) {
						// Method should always have a title! A new language must have been added
						// since the method was first added
						$current_method_title = "Standard Shipping - TITLE NEEDS UPDATING";
					}
					
					// Add any information specified about a shipping date/time
					if (!empty($method_instance['timestamp'])) {
						$current_method_title = strftime($current_method_title, $method_instance['timestamp']);
						
						// Use earliest timestamp from all methods in combination as the timestamp
						// for the overall combination
						if (is_null($shipping_ts)) {
							$shipping_ts = $method_instance['timestamp'];
						} else if ($method_instance['timestamp'] < $shipping_ts) {
							$shipping_ts = $method_instance['timestamp'];
						}
					}
					
					// Check if any surcharge title has been specified or if should fall back to
					// default
					// it's not necessary
					$surcharge_desc = "";
					/* if (!is_null($this->_methods[$method_num]['surcharge_title']) && strlen($this->_methods[$method_num]['surcharge_title']) > 0)  {
						$surcharge_desc = $this->_methods[$method_num]['surcharge_title'];
					} else {
						$surcharge_desc = "advanced shipper template surcharge";
					} */
					
					if ($num_methods_in_comb > 1) {
						$shipping_method_title = "{method_title}{product_info}";
					} else {
						$shipping_method_title = "{method_title}";
					}
					
					// Check if there are placement markers in the title
					$current_title_has_placement_markers = false;
					if (strpos($current_method_title, '{method_total}') !== false ||
							strpos($current_method_title, '{rate_calc_desc}') !== false ||
							strpos($current_method_title, '{surcharge_info}') !== false ||
							strpos($current_method_title, '{package_weights_desc}') !== false ||
							strpos($current_method_title, '{method_extra_title}') !== false ||
							strpos($shipping_method_title, '{method_total}') !== false ||
							strpos($shipping_method_title, '{rate_calc_desc}') !== false ||
							strpos($shipping_method_title, '{surcharge_info}') !== false ||
							strpos($shipping_method_title, '{package_weights_desc}') !== false ||
							strpos($shipping_method_title, '{method_extra_title}') !== false) {
						// Placement markers found
						$current_title_has_placement_markers = true;
					}
					
					// Should a default title template be constructed?
					if (!$current_title_has_placement_markers) {
						// Does this method have an additional title?
						if (strlen($this->_methods[$method_num]['rates'][$rate_i]['rate_extra_title']) > 0)  {
							$current_method_title .= '{method_extra_title}';
						}
						
						$current_method_title .= "";
						
						if (strlen($this->_methods[$method_num]['rates'][$rate_i]['rate_calc_desc']) > 0)  {
							$current_method_title .= "({num_packages_desc}{rate_calc_desc})";
						}
						
						if (strlen($this->_methods[$method_num]['rates'][$rate_i]['display_surcharge']) > 0)  {
							$current_method_title = trim($current_method_title) . ' ' . trim($surcharge_desc);
						}
					}
					
					$shipping_method_title = str_replace('{method_title}', $current_method_title, $shipping_method_title);
					
					if ($current_title_has_placement_markers) {
						if (strlen($this->_methods[$method_num]['rates'][$rate_i]['display_surcharge']) > 0) {
							// Add surcharge desc and placement to the title
							$shipping_method_title = str_replace('{surcharge_info}', $surcharge_desc, $shipping_method_title);
						}
					}
					
					// Add information to method's title
					$shipping_method_title = str_replace('{method_total}', $this->_methods[$method_num]['rates'][$rate_i]['display_rate'], $shipping_method_title);
					$shipping_method_title = str_replace('{rate_calc_desc}', $this->_methods[$method_num]['rates'][$rate_i]['rate_calc_desc'], $shipping_method_title);
					$shipping_method_title = str_replace('{surcharge_amount}', $this->_methods[$method_num]['rates'][$rate_i]['display_surcharge'], $shipping_method_title);
					$shipping_method_title = str_replace('{region_title}', $this->_methods[$method_num]['region_title'], $shipping_method_title);
					
					// Does this method have an additional title?
					if (strlen($this->_methods[$method_num]['rates'][$rate_i]['rate_extra_title']) > 0)  {
						if (strpos($shipping_method_title, '{method_extra_title}') !== false) {
							$shipping_method_title = str_replace('{method_extra_title}', $this->_methods[$method_num]['rates'][$rate_i]['rate_extra_title'], $shipping_method_title);
						} else {
							$shipping_method_title .= $this->_methods[$method_num]['rates'][$rate_i]['rate_extra_title'];
						}
					}
					
					// Build information about the weights of package(s) to be shipped
					$num_packages = sizeof($this->_methods[$method_num]['package_weights']);
					
					$package_weights_desc = '';
					for ($package_weight_i = 0; $package_weight_i < $num_packages; $package_weight_i++) {
						$package_weights_desc .= $this->_methods[$method_num]['package_weights'][$package_weight_i];
						
						if ($this->_methods[$method_num]['package_weights'][$package_weight_i] == 1) {
							$package_weights_desc .= substr(ucfirst(strtolower("lbs")), 0, 2);
						} else {
							$package_weights_desc .= ucfirst(strtolower("lbs"));
						}
						
						if ($package_weight_i < ($num_packages - 1)) {
							$package_weights_desc .= ' + ';
						}
					}
					
					if ($num_packages == 1) {
						// Add number of packages (to be used alongside rate calc desc )
						$shipping_method_title = str_replace('{num_packages_desc}', "", $shipping_method_title);
						
						// Build description of package weights (to be used instead of rate calc
						// desc)
						$package_weights_desc = str_replace('{package_weight}', $package_weights_desc, "{package_weight}");
						
						$shipping_method_title = str_replace('{package_weights_desc}', $package_weights_desc, $shipping_method_title);
					} else {
						// Using more than one package
						// Add number of packages (to be used alongside rate calc desc )
						$num_packages_desc = str_replace('{num_packages}', $num_packages, "{num_packages} Packages: ");
						
						$shipping_method_title = str_replace('{num_packages_desc}', $num_packages_desc, $shipping_method_title);
						
						// Build description of package weights (to be used instead of rate calc
						// desc)
						$package_weights_desc = str_replace('{package_weights}', $package_weights_desc, "{num_packages} Packages");
						
						$package_weights_desc = str_replace('{num_packages}', $num_packages, $package_weights_desc);
						
						$shipping_method_title = str_replace('{package_weights_desc}', $package_weights_desc, $shipping_method_title);
					}
					
					if ($num_methods_in_comb > 1) {
						// Add information about the products included in this method
						foreach ($this->_methods[$method_num]['app_product_indexes'] as $product_i) {

							$current_product_info = "<br />{quantity} x {name}{attribute_info}";
							
							$current_product_info = str_replace('{quantity}', $this->order['items'][$product_i]['qty'], $current_product_info);
							
							$current_product_info = str_replace('{name}', $this->order['items'][$product_i]['name'], $current_product_info);
							
							// Get information about attributes
							// DPP uses attribute as a seperate product
							/* if (isset($this->order['items'][$product_i]['attributes'])) {
								foreach ($this->order['items'][$product_i]['attributes'] as $attribute_name) {
									
									$current_attribute_info = "<br />&nbsp;&nbsp; {name} -- {value}";
									
									$current_attribute_info = str_replace('{name}', $attribute_name[0], $current_attribute_info);
									
									$current_attribute_info = str_replace('{value}', $attribute_name[1], $current_attribute_info);
									
									// Add tag for next attribute (if any)
									$current_attribute_info .= '{attribute_info}';
									
									$current_product_info = str_replace('{attribute_info}', $current_attribute_info, $current_product_info);
								}
							} else {
								$current_product_info = str_replace('{attribute_info}', '', $current_product_info);
							} */
							
							// Add tag for next product (if any)
							$current_product_info .= '{product_info}';
							
							$shipping_method_title = str_replace('{product_info}', $current_product_info, $shipping_method_title);
						}
					}
					
					// Add newlines before any <br /> or <p> tags so that, when stripped for the
					// plain text title, two lines won't join.
					$pattern = '|\<br[^\>]*\>|iU';
					$replace_pattern = "\n" . '\\0';
					$shipping_method_title = preg_replace($pattern, $replace_pattern, $shipping_method_title);
					
					$pattern = '|\<p[^\>]*\>|iU';
					$replace_pattern = "\n\n" . '\\0';
					$shipping_method_title = preg_replace($pattern, $replace_pattern, $shipping_method_title);
					
					// Remove any left-over placement tags
					$pattern = '|{[^}]+}|iU';
					$shipping_method_title = preg_replace($pattern, '', $shipping_method_title);
					
					$method_comb_title .= $shipping_method_title;
				}
				
				// Remove any trailing newline(s) so the wrapping bracket isn't forced onto a new 
				// line in the plain text version of the title
				$method_comb_title = trim($method_comb_title);
				
				if ($num_methods_in_comb > 1) {
					// Wrap method combination
					$overall_comb_title = "{method_comb}";
					$overall_comb_title = str_replace('{method_comb}', $method_comb_title, $overall_comb_title);
				} else {
					$overall_comb_title = $method_comb_title;
				}
				
				$this->quotes['methods'][] = array(
					'id' => $method_comb_id,
					'title' => $overall_comb_title,
					'cost' => $method_comb_cost,
					'icon' => '',
					'shipping_ts' => $shipping_ts,
					'quote_i' => sizeof($this->quotes['methods'])
				);
				
				if (!empty($selected_method_combination)) {
					// The selected shipping method has had its quote built, no need to
					// check any further quotes!
					break;
				}
			}
		}
		
		if ($this->tax_class > 0) {
			$this->quotes['tax'] = $this->calculateTax($this->getShippingAddress());
		}
		
		// Store tax class for use when no quote is generated.. see explanation in constructor
		//$_SESSION['advshipper_tax_class'] = $this->tax_class;
		
		if (sizeof($this->quotes["methods"]) > 0) {
			usort($this->quotes["methods"], array($this, "_orderMethods"));
		}
	}

	/**
	 * Orders shipping methods according to their timestamp, if they have one, otherwise by the
	 * specified sorting method in the admin.
	 *
	 * @access  public
	 * @param   array     $a   The first method to be compared.
	 * @param   array     $b   The second method to be compared.
	 * @return  boolean   True if first array item comes after second, false otherwise.
	 */
	public function _orderMethods($a, $b)
	{
		if (\Deepplusplus\Setting\Helpers::config('module_shipping_advancedshipper_method_sorting') == 1) {
			return $a['cost'] > $b['cost'];
		} else {
			return $a['cost'] < $b['cost'];
		}
		
		return true;
	}

    /**
	 * Calculates the applicable rates for the methods, based on their table of rates and the
	 * products they apply to. If the applicable products for a method have no rate defined then the
	 * method will be removed from the list of usable methods for this order.
	 *
	 * @access  protected
	 * @return  boolean   True if everything went okay, false if an error occurred which must be
	 *                    shown to the customer.
	 */
	public function _calcMethodRates()
	{
		
		// Record any methods which have no rates so they can be removed from the list of usable
		// methods
		$methods_with_no_rates = array();
		
		foreach ($this->_methods as $method_num => $method_info) {

			$rate_limits_inc = ($method_info['rate_limits'] == 1);
			
			$this->_debug("Region #" . $method_info['id'] . " being used" . (strlen($method_info['region_title']) > 0 ? ': ' . $method_info['region_title'] : ''));
			
			$this->_debug("Table of Rates for Region " . $method_info['id'] . ":\n\n" . htmlentities(str_replace(',', ', ', $method_info['table_of_rates'])) . "\n", true);
			
			$method_table_of_rates = preg_replace('/\s+/', '', $method_info['table_of_rates']);
			$method_table_of_rates = strtolower($method_table_of_rates);
			
			// Get the total price, number of items and weight this method applies to
			$method_products_info = array();
			
			foreach ($method_info['app_product_indexes'] as $product_i) {
				$method_products_info[] = $this->order['items'][$product_i];
				
				$this->_debug("\n<br />Product for Method $method_num: " . "Product Index: $product_i: " .  $this->order['items'][$product_i]['name'], true);
			}
			
			
			$products_total_price = 0;
			
			foreach ($method_products_info as $product_info) {
				// Should the total price of the applicable products include the tax?
				if ($method_info['total_price_of_applicable'] === 1) {
					$products_total_price += ($product_info['qty']*$product_info['finalprice']) + $product_info['tax'];
				} else {
					$products_total_price += $product_info['qty'] * $product_info['finalprice'];
				}
			}
			
			$this->_debug("\n<br />Total price of " . sizeof($method_products_info) . " applicable product(s) for method " . $method_num . ": " . $products_total_price, false);
			
			
			$products_num_items = 0;
			
			foreach ($method_products_info as $product_info) {
				$products_num_items += $product_info['qty'];
			}
			
			$this->_debug("\n<br />Total number of items of " . sizeof($method_products_info) . " applicable product(s) for method " . $method_num . ": " . $products_num_items, false);
			
			
			$products_total_weight = 0;
			
			foreach ($method_products_info as $product_info) {
				$products_total_weight += ($product_info['qty'] * (float)$product_info['weight']);
			}
			
			$this->_debug("\n<br />Total weight of " . sizeof($method_products_info) . " applicable product(s) for method " . $method_num . ": " . $products_total_weight, false);
			
			// Calculate the packaging weight (if specified)
			if (strlen($method_info['packaging_weights']) > 0) {
				$packaging_weight = $this->_parseCalcPackagingWeight($method_info['packaging_weights'], $products_total_weight, $rate_limits_inc);
				
				if ($packaging_weight === false) {
					$this->_debug("\n<br />Error occurred when trying to parse/calculate " .
						" packaging weight for " . sizeof($method_products_info) .
						" applicable product(s) in package for method " . $method_num .
						": " . $method_info['packaging_weights'], false);
				} else {
					$products_total_weight += $packaging_weight;
					
					$this->_debug("\n<br />Total weight of " . sizeof($method_products_info) .
						" applicable product(s) in package for method " . $method_num .
						" including packaging weight of " . $packaging_weight . ": " .
						$products_total_weight, false);
				}
            }
            
			// Get the total price of *all* products in the order, not just the applicable products
			$order_total_price = 0;
			
			foreach ($this->order['items'] as $product_info) {
                // Should the total price of all the products include their tax?
                if ($method_info['total_price_of_applicable'] === 1) {
					$order_total_price += ($product_info['qty']*$product_info['finalprice']) + $product_info['tax'];
				} else {
					$order_total_price += $product_info['qty'] * $product_info['finalprice'];
				}
			}
			
			$this->_debug("\n<br />Total price of all " . sizeof($this->order['items']) . " product(s) for the order, according to the tax settings for method " . $method_num . ": " . $order_total_price, false);
			
			
			$package_weights = array();
			
			// Is there a maximum weight for the applicable product(s) which can be shipped in a
			// single package?
			if (!is_null($method_info['max_weight_per_package']) && is_numeric($method_info['max_weight_per_package'])) {

				$this->_debug("\n<br />Maximum package weight: " . $method_info['max_weight_per_package'], false);
				
				if ($products_total_weight > $method_info['max_weight_per_package']) {
					// Applicable products can't be shipped in a single package, work out how many
					// packages would be needed and what the weight of each package would be
					$package_weights = $this->_calcPackageWeights($method_products_info, $method_info['max_weight_per_package'], $method_info['packaging_weights'], $rate_limits_inc);
					
					if ($package_weights == false) {
						// At least one product is too heavy to be shipped via this method!
						$methods_with_no_rates[] = $method_num;
						$this->_debug("\nAt least one product is too heavy to be included in any " . "package for method " . $method_num, false);
						continue;
					}
				}
			}
			
			if (sizeof($package_weights) == 0) {
				// Only one package is necessary for the applicable products
				$package_weights[] = $products_total_weight;
			}
			
			$num_packages = sizeof($package_weights);
			
			$this->_debug("\n<br />Num of packages required: " . $num_packages, false);
			
			
			// Calculate the rate for each package
			$rates_info = array();
			
			$total_weight_of_packages = 0;
			
			// Cache the rates calculated/returned by package weight, to save making unnecessary
			// calculations/rate requests just to get the same data!
			$package_weight_rates_info = array();
			
			foreach ($package_weights as $current_package_weight) {
				$this->_debug("\n<br />Package weight: " . $current_package_weight, false);
				
                $total_weight_of_packages += $current_package_weight;
				
				if (isset($package_weight_rates_info[$current_package_weight])) {
					// Rate has already been calculated/quoted for this package weight
					$current_package_rates_info = $package_weight_rates_info[$current_package_weight];
				} else {
                    
                    $this->_debug("\n<br />Advancedshipper.php method_table_of_rates: " . $method_table_of_rates . "-current_package_weight" . $current_package_weight
                    . "-products_total_price" . $products_total_price. "-products_num_items" . $products_num_items. "-order_total_price" . $order_total_price
                    . "-rate_limits_inc" . $rate_limits_inc. "-method_num" . $method_num. "-method_info id" . $method_info['id'], false);

					$current_package_rates_info = $this->_calcRates($method_table_of_rates, $current_package_weight, $products_total_price, $products_num_items, $order_total_price, $rate_limits_inc, $method_num, $method_info['id']);
				}
				
				if ($current_package_rates_info === false || (is_array($current_package_rates_info) && sizeof($current_package_rates_info) == 1 && $current_package_rates_info[0] == false)) {
					if (isset($this->quotes['error'])) {
						return false;
					}
				} else {
					// Make sure an error hasn't been returned
					if (isset($current_package_rates_info['error'])) {
						$this->quotes['error'] = $current_package_rates_info['error'];
						return false;
					}
					
					$this->_debug("\n<br />Package rate: " . (isset($current_package_rates_info[0]['rate']) ? $current_package_rates_info[0]['rate'] : ''), false);
					
					// Cache the calculated/returned rate
					$package_weight_rates_info[$current_package_weight] = $current_package_rates_info;
					
					$num_rates = sizeof($rates_info);
					
					if ($num_rates == 0) {
						// Either only 1 package is being used or this is the first package
						$rates_info = $current_package_rates_info;
					} else {
						// Add the rate(s) for this package onto the current rate(s)
						for ($rate_i = 0; $rate_i < $num_rates; $rate_i++) {
							if (isset($current_package_rates_info[0]['rate_extra_title'])) {
								// This rate is the result of a quote
								
								// Must match up the services used for this package with those used
								// for the other packages. It may be that one or more packages
								// aren't covered by the same services as the others due to their
								// weight being too high or too low
								// @TODO In future maybe different combinations of the carrier's
								// services could be used if a service doesn't cover all the package
								// weights in the order
								$num_package_rates = sizeof($current_package_rates_info);
								
								$rate_matched = false;
								
								for ($current_package_rate_i = 0; $current_package_rate_i < $num_package_rates; $current_package_rate_i++) {
									if ($rates_info[$rate_i]['rate_extra_title'] == $current_package_rates_info[$current_package_rate_i]['rate_extra_title']) {
										
										$rate_matched = true;
										
										$rates_info[$rate_i]['rate'] += $current_package_rates_info[$current_package_rate_i]['rate'];
										
										$rates_info[$rate_i]['rate_components_info'] = array_merge(
											$rates_info[$rate_i]['rate_components_info'], 
											$current_package_rates_info[$current_package_rate_i]['rate_components_info']
										);
										
										break;
									}
								}
								
								if (!$rate_matched && (!isset($rates_info[$rate_i]['usable']) || $rates_info[$rate_i]['usable'] != false)) {
									// This method can't be used as it doesn't cover all the
									// packages
									$rates_info[$rate_i]['usable'] = false;
									
									$this->_debug("\n<br />Method not usable as no rate returned" . " for at least one of the packages: " . $rates_info[$rate_i]['rate_extra_title'], false);
								}
							} else {
								// This rate is not the result of a quote but instead uses one of
								// the simple calculation methods
								$rates_info[$rate_i]['rate'] += $current_package_rates_info[0]['rate'];
								
								$rates_info[$rate_i]['rate_components_info'] = array_merge(
									$rates_info[$rate_i]['rate_components_info'],
									$current_package_rates_info[0]['rate_components_info']
								);
							}
						}
					}
				}
			}
			
			$num_rates = sizeof($rates_info);
			
			// Remove any unusable rates
			$usable_rates_info = array();
			
			for ($rate_i = 0; $rate_i < $num_rates; $rate_i++) {
				if (!isset($rates_info[$rate_i]['usable']) || $rates_info[$rate_i]['usable'] != false) {
					$usable_rates_info[] = $rates_info[$rate_i];
				}
			}
			
			$rates_info = $usable_rates_info;
			
			unset($usable_rates_info);
			
			$num_rates = sizeof($rates_info);
			
			if ($num_rates > 0) {
				$this->_methods[$method_num]['rates'] = array();
				$this->_methods[$method_num]['package_weights'] = $package_weights;
				
				$tax_rate = $this->calculateTax($this->getShippingAddress());
				
				$tax_multiplier = 1.0;
				
				if ($method_info['rate_include_tax'] == 1) {
					// If tax needs to be included, subtract the amount from the shipping quote so
					// that rates can be entered including tax
					if ($method_info['tax_class'] > 0) {
						$tax_multiplier = (100.0 / ($tax_rate + 100.0));
					}
				}
				
				for ($rate_i = 0; $rate_i < $num_rates; $rate_i++) {

					$rate = $rates_info[$rate_i]['rate'];
					
					$rate_components_info = (isset($rates_info[$rate_i]['rate_components_info']) ? $rates_info[$rate_i]['rate_components_info'] : null);
					
					$rate_extra_title = (isset($rates_info[$rate_i]['rate_extra_title']) ? $rates_info[$rate_i]['rate_extra_title'] : null);
					
					// This function does not exists _getRateForProductFromDatabase
					if (is_string($rate) && substr($rate, 0, 20) == 'products_table_value') {
						// This is a rate specified in the product's database itself
						$rate_info = $this->_getRateForProductFromDatabase($rate);
						
						if (isset($rate_info['error'])) {
							// Problem occurred getting the rate
							$this->quotes['error'] = $rate_info['error'];
							return false;
						}
						
						$this->_methods[$method_num]['rates'][$rate_i]['rate'] = $rate_info['$rate'];
						$this->_methods[$method_num]['rates'][$rate_i]['display_rate'] = $rate_info['$rate'];
						$this->_methods[$method_num]['rates'][$rate_i]['rate_calc_desc'] = '';
						$this->_methods[$method_num]['rates'][$rate_i]['display_surcharge'] = '';
						$this->_methods[$method_num]['rates'][$rate_i]['rate_extra_title'] = '';
						
						break;
						
					} else if (is_string($rate) && substr($rate, 0, 19) == 'contact_after_order') {
						$this->_methods[$method_num]['rates'][$rate_i]['contact_after_order'] = $rate;
						$this->_methods[$method_num]['rates'][$rate_i]['rate'] = 0;
						$this->_methods[$method_num]['rates'][$rate_i]['display_rate'] = 0;
						$this->_methods[$method_num]['rates'][$rate_i]['rate_calc_desc'] = '';
						$this->_methods[$method_num]['rates'][$rate_i]['display_surcharge'] = '';
						$this->_methods[$method_num]['rates'][$rate_i]['rate_extra_title'] = '';
						
						break;
						
					} else if (is_string($rate) && substr($rate, 0, 7) == 'contact') {
						// Shopping basket contains at least one product that requires the customer
						// to contact the store about shipping options before ordering
						$this->_debug("Method $method_num requires that the customer contact the store!", true);
						
						// Load the appropriate contact message
						$contact_message = null;
						
						if (preg_match('/contact([0-9]+)/i', $rate, $matches)) {
							$contact_message_num = $matches[1];
							
							if (defined('MODULE_ADVANCED_SHIPPER_TEXT_CONTACT_STORE' . $contact_message_num)) {
								// Specific message exists for this contact message flag, use it!
								$contact_message = constant('MODULE_ADVANCED_SHIPPER_TEXT_CONTACT_STORE' . $contact_message_num);
							}
						}
						
						if (is_null($contact_message)) {
							// Use default (first) contact message
							$contact_message = "One or more of the product(s) you are ordering require personal shipping arrangements. Please contact us to arrange shipping and complete your order.";
						}
						
						$this->quotes['error'] = $contact_message;
						
						return false;
					}
					
					$this->_debug("Rate identified for Method $method_num: $rate", true);

					// Adjust the rate for tax if necessary (no change will be made if this isn't
					// necessary as tax multiplier will be 1!)
					$rate = $rate * $tax_multiplier;
					
					$num_rate_components_info = sizeof($rate_components_info);
					
					// Build the information about the rate being used
					$rate_calc_desc = '';
					
					for ($rates_info_i = 0; $rates_info_i < $num_rate_components_info; $rates_info_i++) {
						$individual_rate = $rate_components_info[$rates_info_i]['individual_value'];
						
						$num_individual_rates = $rate_components_info[$rates_info_i]['num_individual_values'];
						
						if (strpos($num_individual_rates, '.') !== false) {
							$num_individual_rates = preg_replace('/[0]+$/', '', round($num_individual_rates, 2));
						}
						
						if (is_null($individual_rate)) {

							if ($rate_components_info[$rates_info_i]['calc_method'] === "weight" || $rate_components_info[$rates_info_i]['calc_method'] === "ups" || $rate_components_info[$rates_info_i]['calc_method'] === "usps" || $rate_components_info[$rates_info_i]['calc_method'] === "fedex") {
								
								$rate_calc_desc .= $num_individual_rates;
								
								if ($num_individual_rates == 1) {
									$rate_calc_desc .= substr(ucfirst(strtolower("lbs")), 0, 2);
								} else {
									$rate_calc_desc .= ucfirst(strtolower("lbs"));
								}
								
								if ($num_packages > 1 || $num_rate_components_info > 1) {
									$rate_calc_desc .= ': ';
								}
							}
							
							if ($num_rate_components_info > 1 || (($rate_components_info[$rates_info_i]['calc_method'] === "weight" || $rate_components_info[$rates_info_i]['calc_method'] === "ups" || $rate_components_info[$rates_info_i]['calc_method'] === "usps" || $rate_components_info[$rates_info_i]['calc_method'] === "fedex") && $num_packages > 1)) {
								
								$rate_band_flat_rate = $rate_components_info[$rates_info_i]['value_band_total'];
								
								if ($rate_band_flat_rate == 0) {
									$rate_calc_desc .= "Free";
								} else {

									// Adjust the display rate for tax
									$rate_band_flat_rate = $rate_band_flat_rate * $tax_multiplier;
									
									$rate_band_flat_rate += ($rate_band_flat_rate * $tax_rate / 100);
									
									$rate_calc_desc .= $rate_band_flat_rate;
								}
							}
						} else {
							if (isset($rate_components_info[$rates_info_i]['block_size'])) {
								if ($rate_components_info[$rates_info_i]['calc_method'] === "weight" || $rate_components_info[$rates_info_i]['calc_method'] === "ups" || $rate_components_info[$rates_info_i]['calc_method'] === "usps" || $rate_components_info[$rates_info_i]['calc_method'] === "fedex") {
									
									$rate_calc_desc .= $rate_components_info[$rates_info_i]['applicable_value'];
									
									if ($rate_components_info[$rates_info_i]['applicable_value'] == 1) {
										$rate_calc_desc .= substr(ucfirst(strtolower("lbs")), 0, 2);
									} else {
										$rate_calc_desc .= ucfirst(strtolower("lbs"));
									}
								} else if ($rate_components_info[$rates_info_i]['calc_method'] === "price") {
									$rate_calc_desc .= $rate_components_info[$rates_info_i]['applicable_value'] + ($rate_components_info[$rates_info_i]['applicable_value'] * $tax_rate / 100);
								} else {
									$rate_calc_desc .= $rate_components_info[$rates_info_i]['applicable_value'];
								}
								
								$rate_calc_desc .= ': ';
								
								$rate_calc_desc .= $num_individual_rates;
								
								$rate_calc_desc .= ' x ';
								
								if ($individual_rate == 0) {
									$rate_calc_desc .= "Free";
								} else {
									// Adjust the display rate for tax
									$individual_rate = $individual_rate * $tax_multiplier;
									
									$individual_rate = $individual_rate + ($individual_rate * $tax_rate / 100);
									
									$rate_calc_desc .= $individual_rate;
								}
								
								$rate_calc_desc .= " per ";
								
								if ($rate_components_info[$rates_info_i]['calc_method'] === "weight") {

									$rate_calc_desc .= $rate_components_info[$rates_info_i]['block_size'];
									
									if ($rate_components_info[$rates_info_i]['block_size'] == 1) {
										$rate_calc_desc .= substr(ucfirst(strtolower("lbs")), 0, 2);
									} else {
										$rate_calc_desc .= ucfirst(strtolower("lbs"));
									}
								} else if ($rate_components_info[$rates_info_i]['calc_method'] === "price") {
									$rate_calc_desc .= $rate_components_info[$rates_info_i]['block_size'];
								} else {
									$rate_calc_desc .= $rate_components_info[$rates_info_i]['block_size'];
								}
							} else if ($num_individual_rates > 0) {
								$rate_calc_desc .= $num_individual_rates;
								
								if ($rate_components_info[$rates_info_i]['calc_method'] === "weight") {	
									if ($num_individual_rates == 1) {
										$rate_calc_desc .= substr(ucfirst(strtolower("lbs")), 0, 2);
									} else {
										$rate_calc_desc .= ucfirst(strtolower("lbs"));
									}
								}
								
								$rate_calc_desc .= ' x ';
								
								if ($individual_rate == 0) {
									$rate_calc_desc .= "Free";
								} else {
									// Adjust the display rate for tax
									$individual_rate = $individual_rate * $tax_multiplier;
									
									$individual_rate = $individual_rate + ($individual_rate * $tax_rate / 100);
									
									$rate_calc_desc .= $individual_rate;
								}
								
								if ($rate_components_info[$rates_info_i]['calc_method'] === "weight") {	
									$rate_calc_desc .= '/' . substr(ucfirst(strtolower("lbs")), 0, 2);
								}
							}
							
							$additional_charge = $rate_components_info[$rates_info_i]['additional_value'];
							
							if ($additional_charge > 0) {
								$additional_charge = $additional_charge * $tax_multiplier;
								
								$additional_charge = $additional_charge + ($additional_charge * $tax_rate / 100);
								
								if ($num_individual_rates > 0) {
									$rate_calc_desc .= ' + ';
								}
								$rate_calc_desc .= $additional_charge;
							}
							
							if ($num_individual_rates == 0 && $additional_charge == 0) {
								// This rate band doesn't affect the total, remove any previous
								// joining string (' + ');
								if (strlen($rate_calc_desc) > 0) {
									$rate_calc_desc = substr($rate_calc_desc, 0, strlen($rate_calc_desc) - 3);
								}
							}
						}
						
						if ($rates_info_i < $num_rate_components_info - 1) {
							$rate_calc_desc .= ' + ';
						}
					}
					
					$display_surcharge = '';
					
					if (strlen($method_info['surcharge_discount']) > 0) {
						$surcharge_rate_string = $method_info['surcharge_discount'];
						
						$this->_debug("Surcharge rate string being used for method " . $method_num . ": $surcharge_rate_string", true);
						
						$surcharge = $this->_calcSurcharge($method_info['surcharge_discount'], $total_weight_of_packages, $products_total_price, $products_num_items, $order_total_price, $rate, $num_packages, $rate_limits_inc);
						
						if ($surcharge === false) {
							$surcharge = 0;
						}
						
						if ($surcharge != 0) {
							$surcharge = $surcharge * $tax_multiplier;
							
							// Is this a discount rather than a surcharge? If so, make sure it isn't
							// greater than the rate being charged!
							if ($surcharge < 0 && ($surcharge * -1) > $rate) {
								// Surcharge can't be greater than the rate itself or the rate would
								// be negative!
								$surcharge = -1 * $rate;
							}
							
							$rate += $surcharge;
							
							$display_surcharge = $surcharge + ($surcharge * $tax_rate / 100);//zen_add_tax($surcharge, $tax_rate);
						}
					}
					
					if ($rate == 0) {
						$display_rate = "Free";
					} else {
						$display_rate = $rate + ($rate * $tax_rate / 100);
					}
					
					$this->_methods[$method_num]['rates'][$rate_i]['rate'] = $rate;
					$this->_methods[$method_num]['rates'][$rate_i]['display_rate'] = $display_rate;
					$this->_methods[$method_num]['rates'][$rate_i]['rate_calc_desc'] = $rate_calc_desc;
					$this->_methods[$method_num]['rates'][$rate_i]['display_surcharge'] = $display_surcharge;
					$this->_methods[$method_num]['rates'][$rate_i]['rate_extra_title'] = $rate_extra_title;
					//$this->_methods[$method_num]['rates'][$rate_i]['package_weights_desc'] = $package_weights_desc;
				}
			} else {
				// Method can't be used for this order
				$methods_with_no_rates[] = $method_num;
				
				$this->_debug("\nNo rates matched by method " . $method_num, false);
			}
		}
		
		// Remove any methods which have no rates for the list of usable methods
		if (sizeof($methods_with_no_rates) > 0) {
			foreach($methods_with_no_rates as $method_with_no_rates) {
				unset($this->_methods[$method_with_no_rates]);
			}
		}

		return true;
	}
	
	/**
	 * Examines a table of rates to see if any of the limits for the calculation method(s) within
	 * match the weight/price/number of items/shipping rate/number of packages for a method. If so,
	 * the rate string is parsed into an array of info and returned.
	 *
	 * @access  protected
	 * @param   string    $table_of_rates             The table of rates.
	 * @param   float     $total_weight_of_packages   The total weight of the method's package(s).
	 * @param   float     $products_total_price       The total price of the method's products.
	 * @param   integer   $product_num_items          The number of items in the method.
	 * @param   float     $order_total_price   The total price of all products in the order, not
	 *                                         just the applicable products for a method.
	 * @param   float     $shipping_rate       The calculated shipping rate for the method.
	 * @param   integer   $num_packages        The number of packages being shipped.
	 * @param   boolean   $rate_limits_inc     Whether or not the limits are inclusive.
	 * @return  array|false   The rate info or false if an error occured parsing the table of rates.
	 */
	public function _getParsedRateStringInfoFromTableOfRates($table_of_rates, $total_weight_of_packages, $products_total_price, $products_num_items, $order_total_price, $shipping_rate, $num_packages, $rate_limits_inc)
	{
		// Get the calculation method for this table of rates
		$pattern = '|^\<([^\>]+)\>|iU';
		
		$calc_method = '';
		
		if (preg_match($pattern, $table_of_rates, $matches)) {
			$calc_method = $matches[1];
		}
		
		if ($calc_method != "weight" && $calc_method != "price" && $calc_method != "numitems" && $calc_method != "totalorderprice" && $calc_method != "shipping" && $calc_method != "numpackages") {
			// Couldn't determine calculation method!
			$this->_debug("Couldn't determine calculation method! " . $table_of_rates, true);
			return false;
		}
		
		$this->_debug("Calculation method being tried: " . $calc_method, true);
		
		// Remove the calculation method tags
		$table_of_rates = preg_replace($pattern, '', $table_of_rates);
		
		$pattern = '|\<\/' . $calc_method . '\>$|iU';
		$table_of_rates = preg_replace($pattern, '', $table_of_rates);
		
		$rate_string = null;
		
		$prev_max_limit = 0;
		
		do {
			$this->_debug("Remaining table of rates: " . htmlentities($table_of_rates), true);
			
			// Get the limits
			$limit_string = '';
			
			$limit_rate_divider_pos = strpos($table_of_rates, ':');
			
			if ($limit_rate_divider_pos !== false) {
				$limit_string = substr($table_of_rates, 0, $limit_rate_divider_pos);
				$rate_string = substr($table_of_rates, ($limit_rate_divider_pos + 1), strlen($table_of_rates) - $limit_rate_divider_pos);
			} else {
				// Improper format specified for limit/rate
				$this->_debug("Couldn't parse limits/rates: " . $table_of_rates, true);
				return false;
			}
			
			$this->_debug("Parsing limits string: " . $limit_string, true);
			
			// Has a limit range (minimum as well as maximum values) been specified?
			$limits = $this->_parseLimits($limit_string);
			
			if ($limits === false) {
				// Improper format specified for limits
				$this->_debug("Couldn't parse limits: " . $limit_string, true);
				return false;
			}
			
			$minimum_limit = $limits[0];
			$maximum_limit = $limits[1];
			
			if ($minimum_limit < $prev_max_limit) {
				$minimum_limit = $prev_max_limit;
			}
			
			if (is_numeric($maximum_limit)) {
				if ($minimum_limit > $maximum_limit) {
					$minimum_limit = $maximum_limit;
				}
				
				$prev_max_limit = $maximum_limit;
			}
			
			// Set value to be compared aginst the limits based on the calculation method
			switch ($calc_method) {
				case "weight":
					$calc_method_value = $total_weight_of_packages;
					break;
				case "price":
					$calc_method_value = $products_total_price;
					break;
				case "numitems":
					$calc_method_value = $products_num_items;
					break;
				case "totalorderprice":
					$calc_method_value = $order_total_price;
					break;
				case "shipping":
					$calc_method_value = $shipping_rate;
					break;
				case "numpackages":
					$calc_method_value = $num_packages;
					break;
			}
			
			$this->_debug("Calculation value being tested against: " . $calc_method_value, true);
			
			if ($calc_method_value < $minimum_limit) {
				// Assuming that limits are always entered in ascending order, so if value is too
				// low to match this rate band, it would be too low to match any further band
				$this->_debug('Value too low, assuming all following limits are higher so they' .
					' won\'t match either.', true);
				
				return false;
			}
			
			if ($maximum_limit != '*' && (($rate_limits_inc == true && $calc_method_value > $maximum_limit) || ($rate_limits_inc == false && $calc_method_value >= $maximum_limit))) {
				// Calculation method value doesn't fall within limits, rate not applicable
				// Move on to next set of limits
				// If rate is an embedded table of rates, must skip right past it
				if (substr($rate_string, 0, 1) == '<') {
					$pattern = '|^\<([^\>]+)\>|iU';
					
					if (!preg_match($pattern, $rate_string, $matches)) {
						// Couldn't parse the rate string!
						$this->_debug("Couldn't parse rate string: " . $rate_string, true);
						return false;
					}
					
					// Extract the table of rates
					$embedded_table_of_rates = $this->_extractElement($rate_string, 0, $matches[1]);
					
					// Remove this embedded table of rates, thereby skipping past it!
					$table_of_rates = substr($table_of_rates, strlen($embedded_table_of_rates));
				}
				
				// Next set of limits will come after the first comma
				$next_rate_comma_pos = strpos($table_of_rates, ',');
				
				if ($next_rate_comma_pos === false) {
					// No more limits/rates to match against
					return false;
				}
				
				// Remove the current limits and rate from the table of rates
				$table_of_rates = substr($table_of_rates, $next_rate_comma_pos + 1, strlen($table_of_rates) - 1);
				
				// Attempt to parse the next set of limits & rate
			} else {
				// Limits match
				break;
			}
		} while (1);
		
		// Limits have been matched so attempt to parse rate ///////////////////////////////////////
		
		// First off, check if this "rate" is itself a table of rates
		if (substr($rate_string, 0, 1) == '<') {
			$pattern = '|^\<([^\>]+)\>|iU';
			
			if (!preg_match($pattern, $rate_string, $matches)) {
				// Couldn't parse the rate string!
				$this->_debug("Couldn't parse rate string: " . $rate_string, true);
				return false;
			}
			
			// Extract the table of rates
			$table_of_rates = $this->_extractElement($rate_string, 0, $matches[1]);
			
			// Examine this embedded table of rates
			return $this->_getParsedRateStringInfoFromTableOfRates($table_of_rates, $total_weight_of_packages, $products_total_price, $products_num_items, $order_total_price, $shipping_rate, $num_packages, $rate_limits_inc);
		}
		
		// Strip any following limits/rates (which will come after a comma)
		$next_rate_comma_pos = strpos($rate_string, ',');
		
		if ($next_rate_comma_pos !== false) {
			$rate_string = substr($rate_string, 0, $next_rate_comma_pos);
		}
		
		$this->_debug('Rate string being used: ' . $rate_string, true);
		
		// Rate is a single rate, not a table of rates
		
		// Take a record of and remove any min/max limits on the rate before passing it for
		// calculation
		$min_max = $this->_parseMinMaxLimitsForValueFormat($rate_string);
		
		if (is_array($min_max)) {
			$rate_string = $min_max['value_format'];
		}
		
		return array(
			'calc_method' => $calc_method,
			'calc_method_value' => $calc_method_value,
			'rate_string' => $rate_string,
			'min_max' => $min_max
		);
	}

	/**
	 * Looks up the rate(s) for the applicable products of the specified method in the database
	 * field specified in the rate string, and adds them together to create the rate for sending
	 * those products via this method.
	 *
	 * @access  protected
	 * @param   string    $rate_string   The string defining the parameters needed to get the
	 *                                   rate(s).
	 * @param   integer   $method_num    The method number for which the oroducts' rates should be
	 *                                   looked up.
	 * @return  array|boolean   An array containing the rate and any extra info about individual
	 *                          parts of the rate and how they were calculated or false if there was
	 *                          a problem parsing the rate format or looking up the rate(s).
	 */
	public function _getRatesForProductsFromDatabase($rate_string, $method_num)
	{
		global $db;
		
		$rate = 0;
		$rates_info = array();
		
		// Get the name of the field which holds the values.
		// Format likely to be: products_table_value[product_table_field_name]
		$product_table_field = str_replace('products_table_value', '', $rate_string);
		
		$product_table_field = str_replace('[', '', $product_table_field);
		$product_table_field = str_replace(']', '', $product_table_field);
		
		$this->_debug("\n<br />Getting rates from products database for applicable products for" . ' method ' . $method_num . ' using table field ' . $product_table_field, true);
		
		// Sanity check to be sure identified column exists!
		// Get the list of columns in the database table
		$columns = DB::getSchemaBuilder()->getColumnListing('products');
		
		if (!in_array($product_table_field, $columns)) {
			$this->_debug("\n<br />Error: Products database table field does not exist: " . $product_table_field, true);
			return array(
				'error' => 'Error: Products database table field does not exist: ' . $product_table_field
			);
		}
		
		foreach ($this->_methods[$method_num]['app_product_indexes'] as $product_i) {
			$current_product_id = $this->order['items'][$product_i]['id'];
			$current_product_quantity = $this->order['items'][$product_i]['qty'];
			
			$product_rate_result = DB::table('products')->select($product_table_field)->where('id', $current_product_id)->first();
			
			if (!$product_rate_result) {
				$this->_debug("\n<br />Error occurred looking up specific shipping rate in" . $product_table_field . ' field for product ID ' . $current_product_id, true);
				$this->quotes['error'] = 'Unable to build quote as error occurred looking up' . ' specific shipping rate for ' . $this->order['items'][$product_i]['name'];
				return false;
			} else {
				$product_rate = $product_rate_result->{$product_table_field};
				
				if (is_null($product_rate)) {
					$this->_debug("\n<br />Error: Product ID " . $current_product_id . ' has no' . ' specific rate entered for it in the database.', true);
					$this->quotes['error'] = 'Unable to build quote as &ldquo;' . $this->order['items'][$product_i]['name'] . '&rdquo; has no specific rate' . ' entered for it in the database.';
					return false;
					
				} else if (!is_numeric($product_rate)) {
					$this->_debug("\n<br />Error: Product ID " . $current_product_id . ' does not' . ' have a numeric rate entered for it in the database.', true);
					$this->quotes['error'] = 'Unable to build quote as &ldquo;' . $this->order['items'][$product_i]['name'] . '&rdquo; does not have a numeric' . ' rate entered for it in the database.';
					return false;
				} else {
					$this->_debug("\n<br />Rate found for product ID " . $current_product_id . ': ' . $product_rate, true);
					$rate += $current_product_quantity * $product_rate;
					$rates_info[] = array(
						'value_band_total' => $current_product_quantity * $product_rate,
						'individual_value' => ($current_product_quantity > 1 ? $product_rate : null),
						'num_individual_values' => ($current_product_quantity > 1 ? $current_product_quantity : null),
						'additional_value' => null,
						'calc_method' => "products_table_value"
					);
				}
			}
		}
		
		return array(
			'rate' => $rate,
			'rate_components_info' => $rates_info
		);
	}

    /**
	 * Examines a table of rates to see if any of the limits for the calculation method(s) within
	 * match the weight/price/number of items for the applicable products. If so, the rate is
	 * calculated and returned.
	 *
	 * @access  protected
	 * @param   string    $table_of_rates         The table of rates.
	 * @param   float     $package_weight         The weight of the applicable products in the
	 *                                            package.
	 * @param   float     $products_total_price   The price of the applicable products.
	 * @param   integer   $products_num_items     The number of applicable products.
	 * @param   float     $order_total_price      The total price of all products in the order, not
	 *                                            just the applicable products for a method.
	 * @param   boolean   $rate_limits_inc        Whether or not the limits are inclusive.
	 * @param   integer   $method_num             The number of the method the rate is for.
	 * @param   integer   $region_num             The number of the region of the method the rate is
	 *                                            for.
	 * @return  array|boolean   An array of arrays containing the rate(s) and any extra info about
	 *                          individual components of the rate(s) and how they were calculated,
	 *                          or false if no limits matched and rate(s) calculated.
	 */
	public function _calcRates($table_of_rates, $package_weight, $products_total_price, $products_num_items, $order_total_price, $rate_limits_inc, $method_num, $region_num)
    {
        $rates_info = array();
        
        $parsed_rate_string_info = $this->_getParsedRateStringInfoFromTableOfRates($table_of_rates, $package_weight, $products_total_price, $products_num_items, $order_total_price, null, null, $rate_limits_inc);
        
        if (!is_array($parsed_rate_string_info)) {
            return $parsed_rate_string_info;
        }
        
        $calc_method = $parsed_rate_string_info['calc_method'];
        
        $calc_method_value = $parsed_rate_string_info['calc_method_value'];
        
        $rate_string = $parsed_rate_string_info['rate_string'];
        
        $min_max = $parsed_rate_string_info['min_max'];
        
        if (is_array($min_max)) {
            // Min and/or max values have been extracted from the rate format, must use updated
            // string for subsequent calculations
            $rate_string = $min_max['value_format'];
        }
        
		// Don't attempt to use live lookup services if essential delivery information is missing
		$shippingAddress = $this->getShippingAddress();
        if (($rate_string == "ups" || $rate_string == "usps" || $rate_string == "fedex") && (is_null($shippingAddress['postcode']) || strlen($shippingAddress['postcode']) == 0)) {
            return array(
                'error' => "Please enter your Post/Zip Code."
            );
        }
        
        if (is_string($rate_string) && substr($rate_string, 0, 20) == "products_table_value") {
            // This is a rate specified in the product's database itself
            $rates_info[0] = $this->_getRatesForProductsFromDatabase($rate_string, $method_num);
            
            if (isset($rates_info[0]['error'])) {
                $rates_info = $rates_info[0];
            }
            
        } else if (substr($rate_string, 0, 7) == 'contact') {
            // Single rate to be returned
            $rates_info[0] = array(
                'rate' => $rate_string
            );
            
        } else if ($rate_string == "ups") {
            // Multiple rates could be returned if multiple methods available
            $rates_info = $this->_calcUPSRate($package_weight, $method_num, $region_num, $min_max);
            
        } else if ($rate_string == "usps") {
            // Multiple rates could be returned if multiple methods available
            $rates_info = $this->_calcUSPSRate($package_weight, $products_total_price, $method_num, $region_num, $min_max);
            
        } else if ($rate_string == "fedex") {
            // Multiple rates could be returned if multiple methods available
            $rates_info = $this->_calcFedExRate($package_weight, $products_total_price, $method_num, $region_num, $min_max);
        } else {
            // Single rate to be calculated
            switch ($calc_method) {
                case "weight":
                    $rates_info[0] = $this->_getRateForWeight($calc_method_value, $rate_string, $rate_limits_inc, $min_max);
                    break;
                case "price":
                case "totalorderprice":
                    $rates_info[0] = $this->_getRateForPrice($calc_method_value, $rate_string, $rate_limits_inc, $min_max);
                    break;
                case "numitems":
                    $rates_info[0] = $this->_getRateForNumItems($calc_method_value, $rate_string, $rate_limits_inc, $min_max);
                    break;
            }
        }
        
        return $rates_info;
	}

	/**
	 * Calculates a rate based on the number of items and rate format string passed.
	 *
	 * @access  protected
	 * @param   float     $num_items     The num of items for which the rate should be calculated.
	 * @param   string    $rate_format   The string defining the rate.
	 * @param   boolean   $limits_inc    Whether any limits for combination rates are inclusive or
	 *                                   not.
	 * @param   array     $min_max       Any minimum/maximum limits which should be applied to
	 *                                   the final rate calculated.
	 * @return  array|boolean   An array containing the rate and any extra info about individual
	 *                          parts of the rate and how they were calculated or false if there was
	 *                          a problem parsing the rate format.
	 */
	public function _getRateForNumItems($num_items, $rate_format, $limits_inc, $min_max)
	{
		$rate = 0;
		$rates_info = array();
		
		// Check if a combination rate has been specified
		// Example format: (1-2:3.00)(3-*:2.00)
		if (substr($rate_format, 0, 1) == '(') {
			// Get the list of combination rates and their limits
			$combination_rates_info = $this->_parseCalcCombinationValue($rate_format, $num_items, $limits_inc);
			
			if ($combination_rates_info === false) {
				// Couldn't parse the rate properly!
				return false;
			}
			
			$rate = $combination_rates_info['value_total'];
			
			$rates_info = $combination_rates_info['values_info'];
			
			// Attribute the calculation method to the rate
			for ($i = 0, $n = sizeof($rates_info); $i < $n; $i++) {
				$rates_info[$i]['calc_method'] = "numitems";
			}
		} else if (strpos($rate_format, '[') !== false) {
			// Value is a block rate, based on the number of items
			$block_value_info = $this->_parseCalcBlockValue($rate_format, $num_items);
			
			if ($block_value_info === false) {
				// Couldn't parse the value properly!
				return false;
			}
			
			$rate_band_rate = $block_value_info['value'];
			
			$block_value = $block_value_info['block_value'];
			$num_blocks = $block_value_info['num_blocks'];
			$block_size = $block_value_info['block_size'];
			
			$rates_info[] = array(
				'value_band_total' => $rate_band_rate,
				'individual_value' => $block_value,
				'num_individual_values' => $num_blocks,
				'block_size' => $block_size,
				'applicable_value' => $num_items,
				'additional_value' => null,
				'calc_method' => "numitems"
			);
			
			$rate = $rate_band_rate;
		} else if (strpos($rate_format, '%') !== false) {
			// Rate is a percentage of the number of items
			$percentage_value = $this->_parseCalcPercentageValue($rate_format, $num_items);
			
			if ($percentage_value === false) {
				// Couldn't parse the rate properly!
				return false;
			}
			
			$rate_band_rate = $percentage_value['value'];
			$additional_charge = $percentage_value['additional_value'];
			
			$rates_info[] = array(
				'value_band_total' => $rate_band_rate + $additional_charge,
				'individual_value' => ($rate_band_rate / $num_items),
				'num_individual_values' => $num_items,
				'additional_value' => $additional_charge,
				'calc_method' => "numitems"
			);
			
			$rate = $rate_band_rate + $additional_charge;
		} else {
			$rate = $rate_format;
			
			$rates_info[] = array(
				'value_band_total' => $rate,
				'individual_value' => null,
				'num_individual_values' => $num_items,
				'additional_value' => null,
				'calc_method' => "numitems"
			);
		}
		
		if ($min_max != false) {
			// Apply the limit(s) to the rate
			$rate_limited = $this->calcMinMaxValue($rate, $min_max['min'], $min_max['max']);
			
			if ($rate_limited != $rate) {
				$rate = $rate_limited;
				
				$rates_info = array();
				
				$rates_info[] = array(
					'value_band_total' => $rate,
					'individual_value' => null,
					'num_individual_values' => $num_items,
					'additional_value' => null,
					'calc_method' => "numitems"
				);
			}
		}
		
		$rate_info = array(
			'rate' => $rate,
			'rate_components_info' => $rates_info
		);
		
		return $rate_info;
	}
	
	/**
	 * Calculates a rate based on the price and rate format string passed.
	 *
	 * @access  protected
	 * @param   float     $price         The price for which the rate should be calculated.
	 * @param   string    $rate_format   The string defining the rate.
	 * @param   boolean   $limits_inc    Whether any limits for combination rates are inclusive or
	 *                                   not.
	 * @param   array     $min_max       Any minimum/maximum limits which should be applied to
	 *                                   the final rate calculated.
	 * @return  array|boolean   An array containing the rate and any extra info about individual
	 *                          parts of the rate and how they were calculated or false if there was
	 *                          a problem parsing the rate format.
	 */
	public function _getRateForPrice($price, $rate_format, $limits_inc, $min_max)
	{
		$rates_info = array();
		
		// Check if a combination rate has been specified
		// Example format: (1-2:3.00)(3-*:2.00)
		if (substr($rate_format, 0, 1) == '(') {
			// Get the list of combination rates and their limits
			$combination_rates_info = $this->_parseCalcCombinationValue($rate_format, $price, $limits_inc);
			
			if ($combination_rates_info === false) {
				// Couldn't parse the rate properly!
				return false;
			}
			
			$rate = $combination_rates_info['value_total'];
			
			$rates_info = $combination_rates_info['values_info'];
			
			// Attribute the calculation method to the rate
			for ($i = 0, $n = sizeof($rates_info); $i < $n; $i++) {
				$rates_info[$i]['calc_method'] = "price";
			}
		} else if (strpos($rate_format, '[') !== false) {
			// Value is a block rate, based on the price
			$block_value_info = $this->_parseCalcBlockValue($rate_format, $price);
			
			if ($block_value_info === false) {
				// Couldn't parse the value properly!
				return false;
			}
			
			$rate_band_rate = $block_value_info['value'];
			
			$block_value = $block_value_info['block_value'];
			$num_blocks = $block_value_info['num_blocks'];
			$block_size = $block_value_info['block_size'];
			
			$rates_info[] = array(
				'value_band_total' => $rate_band_rate,
				'individual_value' => $block_value,
				'num_individual_values' => $num_blocks,
				'block_size' => $block_size,
				'applicable_value' => $price,
				'additional_value' => null,
				'calc_method' => "price"
			);
			
			$rate = $rate_band_rate;
		} else if (strpos($rate_format, '%') !== false) {
			// Rate is a percentage of the order total
			$percentage_value = $this->_parseCalcPercentageValue($rate_format, $price);
			
			if ($percentage_value === false) {
				// Couldn't parse the rate properly!
				return false;
			}
			
			$rate_band_rate = $percentage_value['value'];
			$additional_charge = $percentage_value['additional_value'];
			
			$rates_info[] = array(
				'value_band_total' => $rate_band_rate + $additional_charge,
				'individual_value' => ($price > 0 ? ($rate_band_rate / $price) : 0),
				'num_individual_values' => $price,
				'additional_value' => $additional_charge,
				'calc_method' => "price"
			);
			
			$rate = $rate_band_rate + $additional_charge;
		} else {
			$rate = $rate_format;
			
			$rates_info[] = array(
				'value_band_total' => $rate,
				'individual_value' => null,
				'num_individual_values' => $price,
				'additional_value' => null,
				'calc_method' => "price"
			);
		}
		
		if ($min_max != false) {
			// Apply the limit(s) to the rate
			$rate_limited = $this->calcMinMaxValue($rate, $min_max['min'], $min_max['max']);
			
			if ($rate_limited != $rate) {
				$rate = $rate_limited;
				
				$rates_info = array();
				
				$rates_info[] = array(
					'value_band_total' => $rate,
					'individual_value' => null,
					'num_individual_values' => $price,
					'additional_value' => null,
					'calc_method' => "price"
				);
			}
		}
		
		$rate_info = array(
			'rate' => $rate,
			'rate_components_info' => $rates_info
		);
		
		return $rate_info;
	}

	/**
	 * Checks that each method is available for use at the current time and creates a single
	 * instance for any non-dated method or as many instances as are required for any dated methods
	 * which can be shown for several weeks in advance.
	 *
	 * @access  protected
	 * @return  none
	 */
	public function _createMethodInstances()
	{

		// Record any methods which aren't available at the current time so they can be removed from
		// the list of usable methods
		$methods_unavailable = array();
		
		$current_timestamp = time();
		
		// Adjust the timestamp to correspond to the store's time, rather than the server's time
		$current_timestamp += ($this->_time_adjust * 3600);
		
		$current_day_of_week = date('w', $current_timestamp);
		
		foreach ($this->_methods as $method_num => $method_info) {
			// Should this method have more than one instance generated?
			$num_method_instances = 1;
			
			// Check if this method is available
			$availability_scheduling = $method_info['availability_scheduling'];
			
			if ($availability_scheduling == ADVSHIPPER_AVAILABILITY_SCHEDULING_ONCE_ONLY) {
				if ($current_timestamp < $method_info['once_only_start_timestamp'] || $current_timestamp > $method_info['once_only_end_timestamp']) {
					// The once only period for this method has either not yet started or is over
					$methods_unavailable[] = $method_num;
					continue;
				}
			}
			if ($availability_scheduling == ADVSHIPPER_AVAILABILITY_SCHEDULING_RECURRING) {
				$recurring_mode = $method_info['availability_recurring_mode'];
				$weekly_shipping_scheduling = $method_info['availability_weekly_shipping_scheduling'];
				if ($recurring_mode == ADVSHIPPER_AVAILABILITY_RECURRING_MODE_WEEKLY) {
					// Has a start date/time been specified? If so, check it!
					
					// Convert the current week's start day of the week and time into a
					// timestamp
					$start_day_of_week = $method_info['availability_weekly_start_day'];
					$start_time = $method_info['availability_weekly_start_time'];
					
					if (!is_null($start_day_of_week)) {
						$start_timestamp = $this->_calcDayOfWeekAndTimeTimestamp($start_day_of_week, $start_time);
					}
					
					// Convert the current week's cutoff day of the week and time into a
					// timestamp
					$cutoff_day_of_week = $method_info['availability_weekly_cutoff_day'];
					$cutoff_time = $method_info['availability_weekly_cutoff_time'];
					
					$current_week_cutoff_timestamp = $this->_calcDayOfWeekAndTimeTimestamp($cutoff_day_of_week, $cutoff_time);
					
					if (!is_null($start_day_of_week)) {
						if ($current_week_cutoff_timestamp < $start_timestamp) {
							if ($current_week_cutoff_timestamp < $current_timestamp) {
								$current_week_cutoff_timestamp += 7 * 24 * 3600;
							} else if ($current_week_cutoff_timestamp > $current_timestamp) {
								$start_timestamp -= 7 * 24 * 3600;
							}
						}
					}
					
					if ($current_timestamp < $start_timestamp) {
						// Method has scheduled start date/time which hasn't been reached yet
						$methods_unavailable[] = $method_num;	
						continue;
					}
					
					if (is_null($start_day_of_week) && $weekly_shipping_scheduling != ADVSHIPPER_AVAILABILITY_WEEKLY_SHIPPING_SCHEDULING_NONE) {
						$num_method_instances = $method_info['availability_weekly_shipping_show_num_weeks'];
					}
				}
			}
			
			$this->_methods[$method_num]['instances'] = array();
			
			for ($method_instance_i = 0; $method_instance_i < $num_method_instances;
					$method_instance_i++) {
				// Check if this method instance falls within an active recurring time period
				if ($availability_scheduling == ADVSHIPPER_AVAILABILITY_SCHEDULING_RECURRING) {
					if ($recurring_mode == ADVSHIPPER_AVAILABILITY_RECURRING_MODE_WEEKLY) {
						// Get the timestamp for the current instance's cutoff
						$cutoff_timestamp = $current_week_cutoff_timestamp + ($method_instance_i * (7 * 24 * 3600));
						
						if ($cutoff_timestamp < $current_timestamp) {
							// The instance for the current week is no longer valid
							continue;
						}
						
						if ($weekly_shipping_scheduling != 'none') {
							$shipping_day_of_week = $method_info['availability_weekly_shipping_regular_weekday_day'];
							$shipping_time = $method_info['availability_weekly_shipping_regular_weekday_time'];
							
							$shipping_timestamp = $this->_calcDayOfWeekAndTimeTimestamp($shipping_day_of_week, $shipping_time);
							
							// Get the timestamp for the current instance's shipping
							// date/time
							$shipping_timestamp = $shipping_timestamp + ($method_instance_i * (7 * 24 * 3600));
							
							while ($shipping_timestamp < $cutoff_timestamp) {
								$shipping_timestamp += 7 * 24 * 3600;
							}
						}
					}
				}
				
				// If this method instance has a shipping date, record information about the date
				// so the method instances can be sorted chronologically and any order using this
				// method instance can have its shipping date recorded.
				$shipping_ts = null;
				if ($availability_scheduling == ADVSHIPPER_AVAILABILITY_SCHEDULING_ONCE_ONLY && !is_null($method_info['once_only_shipping_datetime'])) {
					$shipping_ts = $method_info['once_only_shipping_datetime'];
				}
				
				if ($availability_scheduling == ADVSHIPPER_AVAILABILITY_SCHEDULING_RECURRING && $recurring_mode == ADVSHIPPER_AVAILABILITY_RECURRING_MODE_WEEKLY && $weekly_shipping_scheduling != ADVSHIPPER_AVAILABILITY_WEEKLY_SHIPPING_SCHEDULING_NONE) {
					$shipping_ts = $shipping_timestamp;
				}
					
				/* if (!is_null($shipping_ts)) {
					// Check if any limit for this method has been reached
					$usage_limit = $method_info['usage_limit'];
					
					if (!is_null($usage_limit) && is_numeric($usage_limit) && $usage_limit > 0) {
						$check_usage_limit_query = " SELECT count(*) AS usage_count FROM " . TABLE_ADVANCED_SHIPPER_ORDERS . " WHERE shipping_ts = '" . date('Y-m-d H:i:00', $shipping_ts) . "';";
						
						$check_usage_limit_result = $db->Execute($check_usage_limit_query);
						
						if (!$check_usage_limit_result->EOF) {
							$usage_count = $check_usage_limit_result->fields['usage_count'];
							
							if ($usage_count >= $usage_limit) {
								// Limit for this method reached
								continue;
							}
						}
					}
				} */
				
				$this->_methods[$method_num]['instances'][] = array(
					'timestamp' => $shipping_ts
				);
			}
			
			if (sizeof($this->_methods[$method_num]['instances']) == 0) {
				$this->_debug("\n<br />Method " . $method_num . " not available at this time! ", false);
				
				$methods_unavailable[] = $method_num;	
				continue;
			}
		}
		
		// Remove any methods which aren't available at the current time
		if (sizeof($methods_unavailable) > 0) {
			foreach($methods_unavailable as $method_unavailable) {
				unset($this->_methods[$method_unavailable]);
			}
		}
		
		ksort($this->_methods);
		
		$this->_debug('ksort($this->_methods)'); // Space out debug info
		
		foreach ($this->_methods as $method_num => $method_info) {
			$method_info_string = '';
			
			foreach ($this->_methods[$method_num]['app_product_indexes'] as $product_i) {
				$method_info_string .=  $product_i . ',';
			}
			$method_info_string = substr($method_info_string, 0, strlen($method_info_string) - 1);
			
			$this->_debug("Applicable Product Index(es) for Method $method_num... " . $method_info_string);
		}
	}

    /**
	 * Works out the minimum number of packages needed to package the applicable products, recording
	 * the weight for each package.
	 *
	 * @access  protected
	 * @param   array     $method_products_info      The information about the applicable products.
	 * @param   integer   $max_weight_per_package    The maximum weight a package can have.
	 * @param   string    $packaging_weight_string   The definition string to be used to calculate
	 *                                               the weight of the packaging for each package.
	 * @param   boolean   $rate_limits_inc           Whether or not the limits are inclusive.
	 * @return  array|false   An array of the weights of the packages or false if a product is too
	 *                        heavy to include in any package or an error occurs.
	 */
	public function _calcPackageWeights(&$method_products_info, $max_weight_per_package, $packaging_weight_string, $rate_limits_inc)
    {
        $package_weights = array();
        
        // Get the weight information for the products
        $product_weights = array();
        foreach ($method_products_info as $product_info) {
            for ($i = 0; $i < $product_info['qty']; $i++) {
                $product_weights[] = $product_info['weight'];
            }
        }
        
        // Sort the weights in reverse order so heaviest products can be allocated to a package
        // first
        rsort($product_weights);
        
        // Check that no product is too heavy to be included in any package
        $heaviest_product_weight = $product_weights[0];
        
        // Calculate and add on the packaging weight (if specified)
        if (strlen($packaging_weight_string) > 0) {
            $packaging_weight = $this->_parseCalcPackagingWeight($packaging_weight_string, $heaviest_product_weight, $rate_limits_inc);
            
            if ($packaging_weight === false) {
                $this->_debug("\n<br />Error occurred when trying to parse/calculate " . " packaging weight for heaviest product in package : " . $packaging_weight_string, false);
                return false;
            } else {
                $heaviest_product_weight += $packaging_weight;
            }
        }
        
        if ($heaviest_product_weight > $max_weight_per_package) {
            // At least one product is too heavy to be included in any package!
            return false;
        }
        
        while (sizeof($product_weights) > 0) {
            $num_package_weights = sizeof($package_weights);
            
            // Add the heaviest product first
            $package_weights[$num_package_weights] = $product_weights[0];
            
            array_splice($product_weights, 0, 1);
            
            // Attempt to add as many other products as possible for this package
            while (sizeof($product_weights) > 0) {

                $num_product_weights = sizeof($product_weights);
                
                for ($i = 0; $i < $num_product_weights; $i++) {

                    $current_package_weight_attempt = $package_weights[$num_package_weights] + $product_weights[$i];
                    
                    $this->_debug("\n<br />Attempting to add product weighing " . $product_weights[$i] . " to package " . ($num_package_weights + 1) . " whose contents currently weigh " . $package_weights[$num_package_weights], true);
                    
                    // Calculate and add on the packaging weight (if specified)
                    if (strlen($packaging_weight_string) > 0) {
                        $packaging_weight = $this->_parseCalcPackagingWeight($packaging_weight_string, $current_package_weight_attempt, $rate_limits_inc);
                        
                        if ($packaging_weight === false) {
                            // Should never get here as error in packaging weight string would have
                            // been detected before this point
                            return false;
                        } else {
                            $current_package_weight_attempt += $packaging_weight;
                            $this->_debug("\n<br />Total weight of product(s) in package " . ($num_package_weights + 1) . " including packaging weight of " . $packaging_weight . ": " . $current_package_weight_attempt, true);
                        }
                    }
                    
                    if ($current_package_weight_attempt < $max_weight_per_package) {
                        // This product can be included in the package
                        $package_weights[$num_package_weights] += $product_weights[$i];
                        array_splice($product_weights, $i, 1);
                        break;
                    }
                    
                    if ($i == ($num_product_weights - 1)) {
                        // No more products can be included in this package, they're all too heavy
                        $this->_debug("\n<br />Can't include any more products in package " . ($num_package_weights + 1), true);
                        break;
                    }
                }
            }
        }
        
        rsort($package_weights);
        
        // Finally, apply any packaging weights to each package
        if (strlen($packaging_weight_string) > 0) {
            for ($i = 0, $n = sizeof($package_weights); $i < $n; $i++) {
                $packaging_weight = $this->_parseCalcPackagingWeight($packaging_weight_string, $package_weights[$i], $rate_limits_inc);
                
                if ($packaging_weight === false) {
                    // Should never get here as error in packaging weight string would have
                    // been detected before this point
                    return false;
                } else {
                    $package_weights[$i] += $packaging_weight;
                }
            }
        }
        
        return $package_weights;
    }

    /**
	 * Analyses a limit string to see if a range has been specified and returns the minimum and
	 * maximum values for the limits.
	 *
	 * @access  protected
	 * @param   string    $limit_string   The string defining the limit(s).
	 * @return  array|boolean   An array containing the minimum and maximum limits or false if a
	 *                          parsing error occurred.
	 */
	public function _parseLimits($limit_string)
	{
		$minimum_limit = 0;
		$maximum_limit = 0;
		
		if (strpos($limit_string, '-') !== false) {
			// Get the minimum and maximum limits
			if (preg_match('/^([0-9\.]+)[\-]([0-9\.]+)/', $limit_string, $limits_array)) {
				$minimum_limit = $limits_array[1];
				$maximum_limit = $limits_array[2];
			} else if (preg_match('/^([0-9\.]+)[\-]\*/', $limit_string, $limits_array)) {
				$minimum_limit = $limits_array[1];
				$maximum_limit = '*';
			} else {
				// Limit(s) not specified properly!
				return false;
			}
		} else {
			// Limit is taken as a maximum limit
			$maximum_limit = $limit_string;
		}
		
		return array($minimum_limit, $maximum_limit);
    }
    
    /**
	 * Calculates the packaging weight based on the weight and packaging weight format string
	 * passed.
	 *
	 * @access  protected
	 * @param   float     $weight                    The weight for which the packaging weight
	 *                                               should be calculated.
	 * @param   string    $packaging_weight_format   The string defining the packaging weight
	 *                                               format.
	 * @param   boolean   $limits_inc   Whether any limits for combination values are inclusive or
	 *                                  not.
	 * @return  float|false    The packaging weight or false if the value couldn't be calculated.
	 */
	public function _getPackagingWeightForWeight($weight, $packaging_weight_format, $limits_inc)
	{
		$packaging_weight = 0.0;
		
		// Take a record of and remove any min/max limits on the weight before passing it for
        // calculation
		$min_max = $this->_parseMinMaxLimitsForValueFormat($packaging_weight_format);
		
		if (is_array($min_max)) {
			// Min and/or max values have been extracted from the weight format, must use updated
			// string for subsequent calculations
			$packaging_weight_format = $min_max['value_format'];
        }
        
		// Check if a combination value has been specified
		// Example format: (1-2:3.00)(3-*:2.00)
		if (substr($packaging_weight_format, 0, 1) == '(') {
			// Get the list of combination values and their limits
			$combination_weights_info = $this->_parseCalcCombinationValue($packaging_weight_format, $weight, $limits_inc);
			
			if ($combination_weights_info === false) {
				// Couldn't parse the value properly!
				return false;
			}
			
			$packaging_weight = $combination_weights_info['value_total'];
		} else if (strpos($packaging_weight_format, '[') !== false) {
			// Weight is a block value
			$block_weight_info = $this->_parseCalcBlockValue($packaging_weight_format, $weight);
			
			if ($block_weight_info === false) {
				// Couldn't parse the value properly!
				return false;
			}
			
			$packaging_weight = $block_weight_info['value'];
		} else if (strpos($packaging_weight_format, '%') !== false) {
			// Weight is a percentage
			$percentage_value = $this->_parseCalcPercentageValue($packaging_weight_format, $weight);
			
			if ($percentage_value === false) {
				// Couldn't parse the value properly!
				return false;
			}
			
			$packaging_weight = $percentage_value['value'] + $percentage_value['additional_value'];
		} else {
			$packaging_weight = $packaging_weight_format;
		}
		
		if ($min_max != false) {
			// Apply the limit(s) to the packaging weight
			$packaging_weight_limited = $this->calcMinMaxValue($packaging_weight, $min_max['min'],
				$min_max['max']);
			
			if ($packaging_weight_limited != $packaging_weight) {
				$packaging_weight = $packaging_weight_limited;
			}
		}
		
		return $packaging_weight;
    }

    /**
	 * Calculates a value according to the specified format, based on the base value specified. The
	 * calculated value is a percentage of the base value plus an optional flat rate.
	 *
	 * @access  protected
	 * @param   string    $value_format   A string containing the format of the value to be
	 *                                    calculated.
	 * @param   integer   $base_value     The base value to which the value calculation should be
	 *                                    applied.
	 * @return  array|boolean   The calculated value and any additional charge, or false if an error
	 *                          occurred.
	 */
	public function _parseCalcPercentageValue($value_format, $base_value)
	{
		$percentage_value = 0.0;
		$percentage_value_pos = strpos($value_format, '%');
		
		// Does the percentage value have an additional set charge? (E.g. 3.4% + 0.20)
		$additional_flat_rate_charge = 0.0;
		$additional_flat_rate_charge_pos = strpos($value_format, '+');
		
		if ($additional_flat_rate_charge_pos !== false) {
			if ($additional_flat_rate_charge_pos < $percentage_value_pos) {
				// Percentage value must follow additional flat charge (I.e. 0.20+3.4%)
				// Get the value of the additional set charge
				$additional_flat_rate_charge = substr($value_format, 0, $additional_flat_rate_charge_pos);
				
				// Get the percentage value
				$percentage_value = substr($value_format, ($additional_flat_rate_charge_pos + 1), $percentage_value_pos - ($additional_flat_rate_charge_pos + 1));
			} else {
				// Percentage value must precede additional flat charge (I.e. 3.4%+0.20)
				// Get the value of the additional set charge
				$additional_flat_rate_charge = substr($value_format, ($additional_flat_rate_charge_pos + 1), strlen($value_format) - ($additional_flat_rate_charge_pos + 1));
				
				// Get the percentage value
				$percentage_value = substr($value_format, 0, $percentage_value_pos);
			}
		} else {
			// Get the percentage value
			$percentage_value = substr($value_format, 0, $percentage_value_pos);
		}
		
		if (!is_numeric($additional_flat_rate_charge) || !is_numeric($percentage_value)) {
			// The value format hasn't been specified properly!
			return false;
		}
		
		$value = ($base_value * ($percentage_value / 100));
		
		return array(
			'value' => (float) $value,
			'additional_value' => (float) $additional_flat_rate_charge
		);
	}

    /**
	 * Parses a combination value and calculates the total value according to the specified
	 * combination.
	 *
	 * @access  protected
	 * @param   string    $combination_value_string   The string defining the limit(s) and value(s)
	 *                                                to  be combined.
	 * @param   float     $limit_num    The number to be used in calculating the value.
	 * @param   boolean   $limits_inc   Whether any limits for combination rates are inclusive or
	 *                                  not.
	 * @return  array|boolean   An array containing the total rate, and array of rates used (and the
	 *                          number of times each rate is used) or false if a parsing error
	 *                          occurred.
	 */
	public function _parseCalcCombinationValue($combination_value_string, $limit_num, $limits_inc)
	{
		$combination_values = array();
		
		$combination_value_string = str_replace(')(', '|', $combination_value_string);
		$combination_value_string = substr($combination_value_string, 1, strlen($combination_value_string) - 2);
		
		$combination_values_info = explode('|', $combination_value_string);
		
		$prev_max_limit = 0;
		
		for ($i = 0, $num_cri = sizeof($combination_values_info); $i < $num_cri; $i++) {
			$combination_value_divider_pos = strpos($combination_values_info[$i], ':');
			
			if ($combination_value_divider_pos === false || ($combination_value_divider_pos + 1) == strlen($combination_values_info[$i])) {
				// Improper format specified for limit(s)/value
				return false;
			}
			
			$limit_string = substr($combination_values_info[$i], 0, $combination_value_divider_pos);
			
			$current_value_string = substr($combination_values_info[$i], $combination_value_divider_pos + 1, strlen($combination_values_info[$i]) - ($combination_value_divider_pos + 1));
			
			$limits = $this->_parseLimits($limit_string);
			
			if ($limits === false) {
				// Improper format specified for limits
				$this->_debug("Improper format specified for limits: " . $limit_string);
				
				return false;
			}
			
			$minimum_limit = $limits[0];
			$maximum_limit = $limits[1];
			
			if ($minimum_limit < $prev_max_limit) {
				$minimum_limit = $prev_max_limit;
			}
			
			if ($limit_num < $minimum_limit) {
				// No limits match the number to be used to calcuate the value
				break;
			}
			
			// Determine how many times the current value should be added on
			if ($maximum_limit == '*') {
				$maximum_limit = $limit_num;
			} else if ($limit_num < $maximum_limit) {
				$maximum_limit = $limit_num;
			}
			
			if ($limit_num - $minimum_limit == 0) {
				if ($limits_inc && $minimum_limit == $prev_max_limit) {
					// Limits are inclusive so num within limits of 0 isn't included in this band,
					// as it is part of the previous band.
					$this->_debug('Current number for calculation has a value of zero and' .
						' inclusive limits are being used, with the previous band using its' .
						' maximum value of ' . $prev_max_limit . ', therefore no match made for ' .
						' current band (' . $limit_string . ').', true);
					
					break;
				} else if (!$limits_inc) {
					$this->_debug('Current number for calculation has a value of zero exactly and' .
						' exclusive limits are being used, with the previous band therefore not' .
						' using its exact maximum value of ' . $prev_max_limit . ', therefore' .
						' match made for current band (' . $limit_string . '), with a value of' .
						' zero!', true);
				}
			}
			
			$num_fall_within_limit = $maximum_limit - $prev_max_limit;
			
			if (strpos($current_value_string, '[') !== false) {
				// Value is a block rate, based on the limit number
				$block_value_info = $this->_parseCalcBlockValue($current_value_string, $num_fall_within_limit);
				
				if ($block_value_info === false) {
					// Couldn't parse the value properly!
					return false;
				}
				
				$current_combination_value = $block_value_info['value'];
				
				$block_value = $block_value_info['block_value'];
				$num_blocks = $block_value_info['num_blocks'];
				$block_size = $block_value_info['block_size'];
				
				$combination_values['values_info'][] = array(
					'value_band_total' => $current_combination_value,
					'individual_value' => $block_value,
					'num_individual_values' => $num_blocks,
					'block_size' => $block_size,
					'applicable_value' => $num_fall_within_limit,
					'additional_value' => null
				);
			} else if (strpos($current_value_string, '%') !== false) {
				// Value is a percentage of the limit number
				$percentage_value_info = $this->_parseCalcPercentageValue($current_value_string, $num_fall_within_limit);
				
				if ($percentage_value_info === false) {
					// Couldn't parse the value properly!
					return false;
				}
				
				$current_combination_value = $percentage_value_info['value'];
				$current_combination_additional_charge = $percentage_value_info['additional_value'];
				
				if ($num_fall_within_limit > 0) {
					$individual_value = ($current_combination_value / $num_fall_within_limit);
				} else {
					$individual_value = 0;
				}
				
				$combination_values['values_info'][] = array(
					'value_band_total' => $current_combination_value + $current_combination_additional_charge,
					'individual_value' => $individual_value,
					'num_individual_values' => $num_fall_within_limit,
					'additional_value' => $current_combination_additional_charge
				);
			} else {
				$current_combination_value = $current_value_string;
				
				$combination_values['values_info'][] = array(
					'value_band_total' => $current_combination_value,
					'individual_value' => null,
					'num_individual_values' => $num_fall_within_limit,
					'additional_value' => null
				);
			}
			
			$prev_max_limit = $maximum_limit;
		}
		
		if (sizeof($combination_values) == 0) {
			return false;
		}
		
		// Calculate the total value
		$combination_values['value_total'] = 0;
		foreach ($combination_values['values_info'] as $combination_value) {
			$combination_values['value_total'] += $combination_value['value_band_total'];
		}
		
		return $combination_values;
    }
    
    /**
	 * Calculates a value according to the specified format, based on the base value specified. The
	 * calculated value is a cumulative addition of a value specified in the format, totalled up
	 * according to the number of value "blocks" required to cover the base value.
	 *
	 * @access  protected
	 * @param   string    $value_format   A string containing the format of the value to be
	 *                                    calculated. Expected format: [block_size:block_value]
	 * @param   integer   $base_value     The base value to which the value calculation should be
	 *                                    applied.
	 * @return  array|boolean   The calculated value, or false if an error occurred.
	 */
	public function _parseCalcBlockValue($value_format, $base_value)
	{
		// Remove the wrapping brackets ([])
		$value_format = substr($value_format, 1, strlen($value_format) - 2);
		
		// Parse the block size and block value
		$block_value_divider_pos = strpos($value_format, ':');
		
		if ($block_value_divider_pos === false || ($block_value_divider_pos + 1) == strlen($value_format)) {
			// Improper format specified for block size/value
			return false;
		}
		
		$block_size = substr($value_format, 0, $block_value_divider_pos);
		
		$block_value = substr($value_format, $block_value_divider_pos + 1, strlen($value_format) - ($block_value_divider_pos + 1));
		
		// Get the number of "blocks" necessary to cover the base value
		$num_blocks = ceil($base_value / $block_size);
		
		$total_value = (float) $num_blocks * $block_value;
		
		return array(
            'value' => $total_value,
            'num_blocks' => $num_blocks,
            'block_value' => $block_value,
            'block_size' => $block_size
        );
	}

	/**
	 * Checks if every product has at least one usable shipping method.
	 *
	 * @access  protected
	 * @return  boolean   True if every product has at least one usable shipping method, false if
	 *                    not.
	 */
	public function _verifyAllProductsHaveUsableMethods()
	{
		$num_products = sizeof($this->order['items']);
		
		$products_usable_method_status = array();
		
		for ($product_i = 0; $product_i < $num_products; $product_i++) {
			if (isset($this->order['items'][$product_i]['free_shipping']) && $this->order['items'][$product_i]['free_shipping'] == true) {
				$products_usable_method_status[$product_i] = true;
			} else {
				$products_usable_method_status[$product_i] = false;
			}
		}
		
		// Examine the list of applicable products for each usable method
		foreach ($this->_methods as $method_num => $method_info) {
			foreach ($method_info['app_product_indexes'] as $app_product_index) {
				$products_usable_method_status[$app_product_index] = true;
			}
		}
		
		foreach ($products_usable_method_status as $product_i => $product_has_usable_method) {
			if (!$product_has_usable_method) {
				return false;
			}
		}
		
		return true;
		
	}
    
    /**
	 * Extracts any minimum or maxiumum limit specifications from a value specification string.
	 *
	 * @access  protected
	 * @param   string    $value_format   A string containing the format of the value to be
	 *                                    examined.
	 * @return  array|false   An array of the extracted limit(s) or false if the format string 
	 *                        contains no limits.
	 */
	public function _parseMinMaxLimitsForValueFormat($value_format)
	{
		$min = null;
		$max = null;
		
		$value_format = preg_replace('|\s|', '', $value_format);
		
		if (preg_match('/.*(min\-?([0-9\.]+)).*/i', $value_format, $match_array)) {
			$min = $match_array[2];
			$value_format = str_replace($match_array[1], '', $value_format);
		}
		
		if (preg_match('/.*(max\-?([0-9\.]+)).*/i', $value_format, $match_array)) {
			$max = $match_array[2];
			$value_format = str_replace($match_array[1], '', $value_format);
		}
		
		if (!is_null($min) || !is_null($max)) {
			return array(
				'value_format' => $value_format,
				'min' => $min,
				'max' => $max
			);
		}
		
		return false;
	}
    
    /**
	 * Parses a combination rate and calculates the total rate applicable for the order according
	 * to the specified combination.
	 *
	 * @access  protected
	 * @param   string    $packaging_weight_string   The string defining the limit(s) and weight(s)
	 *                                               to examined.
	 * @param   float     $products_total_weight     The total weight of the applicable products to
	 *                                               be used in calculating the packaging weight.
	 * @param   boolean   $rate_limits_inc           Whether or not the limits are inclusive.
	 * @return  float     The calculated weight of the packaging.
	 */
	public function _parseCalcPackagingWeight($packaging_weight_string, $products_total_weight, $rate_limits_inc)
    {
        
        $packaging_weight = 0.0;
        
        $packaging_weights_info = explode(',', $packaging_weight_string);
        
        $weight_string = null;
        
        for ($i = 0, $num_pwi = sizeof($packaging_weights_info); $i < $num_pwi; $i++) {
            // Get the limits
            $limit_string = '';
            
            $limit_rate_divider_pos = strpos($packaging_weights_info[$i], ':');
            
            if ($limit_rate_divider_pos !== false) {
                $limit_string = substr($packaging_weights_info[$i], 0, $limit_rate_divider_pos);
                $weight_string = substr($packaging_weights_info[$i], ($limit_rate_divider_pos + 1), strlen($packaging_weights_info[$i]) - $limit_rate_divider_pos);
            } else {
                // Improper format specified for limit/weight
                $this->_debug("Couldn't parse limits/weights: " . $packaging_weights_info[$i], true);
                return false;
            }
            
            $this->_debug("Parsing limits string: " . $limit_string, true);
            
            $limits = $this->_parseLimits($limit_string);
            
            if ($limits === false) {
                // Improper format specified for limits
                $this->_debug("Couldn't parse limits: " . $limit_string, true);
                return false;
            }
            
            $minimum_limit = $limits[0];
            $maximum_limit = $limits[1];
            
            if (($maximum_limit == '*' && $products_total_weight < $minimum_limit) ||
                    ($maximum_limit != '*' &&
                    (
                    ($rate_limits_inc == true &&
                    ($products_total_weight < $minimum_limit ||
                    $products_total_weight > $maximum_limit)) ||
                    ($rate_limits_inc == false &&
                    ($products_total_weight < $minimum_limit ||
                    $products_total_weight >= $maximum_limit))
                    )
                    )) {
                // Limits not matched so can't use this weight string
                $weight_string = null;
            } else {
                // Limit/weight string identified
                break;
            }
        }
        
        if (!is_null($weight_string)) {
            $packaging_weight = $this->_getPackagingWeightForWeight($products_total_weight, $weight_string, $rate_limits_inc);
            
            if ($packaging_weight === false) {
                return false;
            }
        }
        
        return $packaging_weight;
    }

    /**
	 * Examines all products and builds a list of all shipping methods which have rates for this
	 * order/items in the order and the customer's shipping region.
	 */
	public function _getUsableMethodsInfo()
	{
        // Perform simple configuration lookups to speed up module when product selections and/or
		// category selections and/or manufacturer selections and/or custom product field selections
		// haven't been availed of for any shipping method
		$use_product_selection_lookups = $this->_anyMethodsUseProductSelections();
		$use_category_selection_lookups = $this->_anyMethodsUseCategorySelections();
        $use_manufacturer_selection_lookups = $this->_anyMethodsUseManufacturerSelections();
        //$use_custom_product_field_selection_lookups = $this->_anyMethodsUseCustomProductFieldSelections();
        $fallover_methods = $this->_getMethodsUsingProductFallover();

        // Variable speeds up checks by recording which methods are disabled
        $disabled_methods = array();
        $disabled_methods_result = DB::table('advshipper_methods')->where('enabled', 0)->get();
        foreach($disabled_methods_result as $method) {
            $disabled_methods[] = $method->id;
        }

        // Variable speeds up checks by recording list of shipping methods which have been
		// identified as not being applicable for the customer
		$unapplicable_methods = array();
		
		$num_products = sizeof($this->order['items']);
		
		for ($product_i = 0; $product_i < $num_products; $product_i++) {

			// Determine which shipping methods include this product ///////////////////////////////
			// Variable holds final list of methods which apply for this product
			$product_methods = array();
			
			$product_id = $this->order['items'][$product_i]['id'];
			
			// First, determine if this product is included in any specific shipping methods
			if ($use_product_selection_lookups) {
				$product_methods = $this->_getProductSpecificShippingMethods($product_id);
            }
			
			// Determine if this product is included in any specific shipping methods by way of one
			// of its parent categories being included in a specific shipping method
			if ($use_category_selection_lookups) {
                $category_specific_methods = $this->_getCategorySpecificShippingMethods($product_id);
				for ($csm_i = 0, $num_csm = sizeof($category_specific_methods); $csm_i < $num_csm; $csm_i++) {
					if (!in_array($category_specific_methods[$csm_i], $product_methods)) {
						$product_methods[] = $category_specific_methods[$csm_i];
					}
				}
            }
			
			// Determine if this product is included in any specific shipping methods by way of its
			// manufacturer being included in a specific shipping method
			if ($use_manufacturer_selection_lookups) {
				$manufacturer_specific_methods = $this->_getManufacturerSpecificShippingMethods($product_id);
				for ($msm_i = 0, $num_msm = sizeof($manufacturer_specific_methods); $msm_i < $num_msm; $msm_i++) {
					if (!in_array($manufacturer_specific_methods[$msm_i], $product_methods)) {
						$product_methods[] = $manufacturer_specific_methods[$msm_i];
					}
				}
			}
			
			$num_product_specific_methods = sizeof($product_methods);
			
			if ($num_product_specific_methods == 0) {
				// Product not covered by any specific category/product selections so can only be
				// covered by a fallover
				$product_methods = $fallover_methods;
			}
			
			$num_product_methods = sizeof($product_methods);
			
			if ($num_product_methods == 1) {
				$this->_debug("\n<br />Product Index $product_i potentially covered by Method: " . $product_methods[0], true);
			} else if ($num_product_methods > 1) {
				$this->_debug("\n<br />Product Index $product_i potentially covered by Methods: " . implode(', ', $product_methods), true);
			}
			
			// Check each shipping method to check if it does NOT cover the customer's address /////
			$valid_product_methods = array();
			
			for ($method_i = 0; $method_i < $num_product_methods; $method_i++) {
				$method_num = $product_methods[$method_i];
				
				// Don't bother checking any disabled methods
				if (in_array($method_num, $disabled_methods)) {
					continue; // Check next method
				}
				
				$region_info = (array)$this->_getRegionAndRates($method_num);
				
                // Check if an error occurred when attempting to get the region and rates 
				if (isset($this->quotes['error'])) {
					return false;
				}
				
				if ($region_info == false) {
					// Method can't be used for this order
					$unapplicable_methods[] = $method_num;
					
					$this->_debug("\nMethod $method_num excluded as no Region matches the " . 
						"customer's address.", false);
				} else {
					// Method may be able to be used if any of its rates match, store rates for use
					// later
					if (!isset($this->_methods[$method_num])) {
						$this->_methods[$method_num] = $region_info;
						$this->_methods[$method_num]['app_product_indexes'] = array($product_i);
					} else {
						// Product may be specifically selected as well as being part of a category,
						// avoid adding it twice!
						if (!in_array($product_i,
								$this->_methods[$method_num]['app_product_indexes'])) {
							$this->_methods[$method_num]['app_product_indexes'][] = $product_i;
						}
					}
					
					$valid_product_methods[] = $method_num;
				}
			}
			
			$num_valid_product_methods = sizeof($valid_product_methods);
			
			if ($num_valid_product_methods == 0) {
				$this->_debug("\n<br />Product Index $product_i NOT covered by ANY methods!", true);
			} else if ($num_valid_product_methods == 1) {
				$this->_debug("\n<br />Product Index $product_i covered by method: " . $valid_product_methods[0], true);
			} else if ($num_valid_product_methods > 1) {
				$this->_debug("\n<br />Product Index $product_i covered by methods: " . implode(', ', $valid_product_methods), true);
			}
			
			if ($num_valid_product_methods == 0) {
				// This product cannot be shipped as no methods match it, at least for the
				// customer's address - Can't use this module!
				
				if ($num_product_methods == 0) {
					// Let the user know that this product caused the problem so they can let the
					// store owner know... no store should have unshippable products!
					$this->quotes['error'] = "Product in cart is not covered by any of the store's shipping rates: " . $this->order['items'][$product_i]['name'];
				}
				
				return false;
            }
            
		}
		
		// Now that the region has been identified for each method and its info stored, must store
		// method info for each method
		$load_methods_info_result = DB::table('advshipper_methods')->whereIn('id', array_keys($this->_methods))->get();
		foreach($load_methods_info_result as $method) {
			$method = (array)$method;
			$method_num = $method['id'];
				
			foreach ($method as $key => $value) {
				switch ($key) {
					case 'id':
					break;
					case 'once_only_start_datetime':
					case 'once_only_end_datetime':
					case 'once_only_shipping_datetime':
						$date = $value;
						$year = substr($date, 0, 4);
						if ($year != '0000' && !is_null($date)) {
							$month = substr($date, 5, 2);
							$day = substr($date, 8, 2);
							$hour = substr($date, 11, 2);
							$minute = substr($date, 14, 2);
							$timestamp = mktime($hour, $minute, 0, $month, $day, $year);
							switch ($key) {
								case 'once_only_start_datetime':
									$this->_methods[$method_num]['once_only_start_timestamp'] = $timestamp;
									break;
								case 'once_only_end_datetime':
									$this->_methods[$method_num]['once_only_end_timestamp'] = $timestamp;
									break;
								case 'once_only_shipping_datetime':
									$this->_methods[$method_num]['once_only_shipping_timestamp'] = $timestamp;
									break;
							}
							
						} else {
							$this->_methods[$method_num][$key] = null;
						}
					break;
					default:
						$this->_methods[$method_num][$key] = $value;
					break;
				}
			}
		}
        return true;
	}
	
	/**
	 * Contacts USPS to get a quote for shipping the applicable products from the source to the
	 * customer's shipping address.
	 *
	 * @access  public
	 * @param   float     $weight       The weight of the applicable products.
	 * @param   float     $price        The price of the applicable products.
	 * @param   integer   $method_num   The number of the method containing the region with the
	 *                                  USPS configuration.
	 * @param   integer   $region_num   The number of the region with the USPS configuration.
	 * @param   array     $min_max      Any minimum/maximum limits which should be applied to the 
	 *                                  final rate calculated.
	 * @return  array     An array of rates and method titles or an array containing an error
	 *                    message.
	 */
	public function _calcUSPSRate($weight, $price, $method_num, $region_num, $min_max)
	{
		$usps_config = unserialize($this->_methods[$method_num]['usps_configuration']);
		
		if (empty($usps_config)) {
			// Couldn't load config!
			return array(
				'error' => sprintf('Module configuration error: The USPS configuration has not been specified for method %s, region %s.', $method_num, $region_num) 
			);
		}

		$usps_calc = new \Advshipper\AdvancedShipperUSPSCalculator($usps_config);
		
		return $usps_calc->quote($weight, $price, $min_max);
	}

	/**
	 * Contacts UPS to get a quote for shipping the applicable products from the source to the
	 * customer's shipping address.
	 *
	 * @access  public
	 * @param   float     $weight       The weight of the applicable products.
	 * @param   integer   $method_num   The number of the method containing the region with the
	 *                                  UPS configuration.
	 * @param   integer   $region_num   The number of the region with the UPS configuration.
	 * @param   array     $min_max      Any minimum/maximum limits which should be applied to the 
	 *                                  final rate calculated.
	 * @return  array     An array of rates and method titles or an array containing an error
	 *                    message.
	 */
	public function _calcUPSRate($weight, $method_num, $region_num, $min_max)
	{
		$ups_config = unserialize($this->_methods[$method_num]['ups_configuration']);
		
		if (empty($ups_config)) {
			// Couldn't load config!
			return array(
				'error' => sprintf('Module configuration error: The UPS configuration has not been specified for method %s, region %s.', $method_num, $region_num) 
			);
		}

		$ups_calc = new \Advshipper\AdvancedShipperUPSCalculator($ups_config);
		
		return $ups_calc->quote($weight, $min_max);
	}

	/**
	 * Contacts FedEx to get a quote for shipping the applicable products from the source to the
	 * customer's shipping address.
	 *
	 * @access  public
	 * @param   float     $weight       The weight of the applicable products.
	 * @param   float     $price        The price of the applicable products.
	 * @param   integer   $method_num   The number of the method containing the region with the
	 *                                  FedEx configuration.
	 * @param   integer   $region_num   The number of the region with the FedEx configuration.
	 * @param   array     $min_max      Any minimum/maximum limits which should be applied to the 
	 *                                  final rate calculated.
	 * @return  array     An array of rates and method titles or an array containing an error
	 *                    message.
	 */
	public function _calcFedExRate($weight, $price, $method_num, $region_num, $min_max)
	{
		$fedex_config = unserialize($this->_methods[$method_num]['fedex_configuration']);
		
		if (empty($fedex_config)) {
			// Couldn't load config!
			return array(
				'error' => sprintf("Module configuration error: The FedEx configuration has not been specified for method %s, region %s.", $method_num, $region_num)
			);
		}
		
		$fedex_calc = new \Advshipper\AdvancedShipperFedExCalculator($fedex_config);
		
		return $fedex_calc->quote($weight, $price, $min_max);
	}

	/**
	 * Builds every possible combination of the usable methods and each method's instances which
	 * will satisfy the delivery options for the products in the cart.
	 *
	 * @access  protected
	 * @return  array|boolean   An array of all possible method and method instance combinations or
	 *                          false if there are no usable combinations.
	 */
	public function _getUsableCombinations()
	{
		// First, must take note of which shipping methods each product can use
		$num_products = sizeof($this->order['items']);
		
		$products_methods = array();
		
		for ($product_i = 0; $product_i < $num_products; $product_i++) {
			$products_methods[$product_i] = array();
		}
		
		foreach ($this->_methods as $method_num => $method_info) {
			foreach ($method_info['app_product_indexes'] as $product_i) {
				$products_methods[$product_i][] = $method_num;
			}
		}
		
		$this->_debug('End of function _getUsableCombinations'); // Space out debug info
		
		for ($product_i = 0; $product_i < $num_products; $product_i++) {
			$product_methods_string = '';
			
			foreach ($products_methods[$product_i] as $product_method) {
				$product_methods_string .=  $product_method . ', ';
			}
			$product_methods_string = substr($product_methods_string, 0, strlen($product_methods_string) - 2);
			
			$this->_debug("Usable Methods for Product Index $product_i... $product_methods_string");
		}
		
		// Build every possible combination of the usable methods, but ensure that each combination
		// provides exactly one shipping method for each product in the cart
		$method_combinations = $this->_getProductMethodCombinations($products_methods);
		
		$this->_debug("\n<br/>Usable methods combinations...");
		$this->_debug(serialize($method_combinations));
		
		if ($method_combinations !== false) {
			// Build the instances for each method within each combination
			$method_combinations = $this->_getMethodCombinationsWithRateInstancesAndMethodInstances($method_combinations);
		}
		
		return $method_combinations;
	}

	/**
	 * Builds every possible combination of the usable methods, with an individual method
	 * combination for each combination of a method's rates and instances. I.e. if method has
	 * several rates, it is effectively split into several methods, one for each rate. In the same
	 * manner, if a method has several shipping timestamps, each of those timestamps is used to
	 * separate the method into several methods, with there being one instance of each for *each*
	 * rate!
	 *
	 * @access  protected
	 * @param   array     $method_combinations   An array of methods which can be combined for all
	 *                                           products previously examined.
	 * @return  array     An array of all possible method combinations for all instances of the
	 *                    methods  within each overall method combination.
	 */
	public function _getMethodCombinationsWithRateInstancesAndMethodInstances($method_combinations)
	{
		$method_and_instance_combinations = array();
		
		$method_comb_i = 0;
		
		foreach ($method_combinations as $method_combination) {
			$num_methods = sizeof($method_combination);
			
			$method_instance_combs = $this->_getRateAndMethodInstanceCombinations($method_combination);
			
			foreach ($method_instance_combs as $method_instance_comb) {
				// Build the id string which identifies this combination
				$id_string = '';
				
				foreach ($method_instance_comb as $method_instance) {
					$id_string .= $method_instance['method'] . '-' . 
						$method_instance['rate_i'] . '-' .
						$method_instance['instance_i'] . '-';
				}
				
				$id_string = substr($id_string, 0, strlen($id_string) - 1);
				
				$method_and_instance_combinations[$id_string] = $method_instance_comb;
			}
		}
		
		$this->_debug("<br/>Usable method, rate and instance combinations...");
		$this->_debug(serialize($method_and_instance_combinations));
		
		return $method_and_instance_combinations;
	}

	/**
	 * Builds every possible combination of the method instances for a method and all following
	 * methods.
	 *
	 * @access  protected
	 * @param   array     $method_nums   An array with the numbers of the methods.
	 * @param   integer   $method_i      The method number currently having its combinations built.
	 * @return  array     An array of all possible method instance combinations the methods.
	 */
	function _getRateAndMethodInstanceCombinations($method_nums, $method_i = 0)
	{
		$method_instance_combs = array();
		
		$num_methods = sizeof($method_nums);
		$method_num = $method_nums[$method_i];
		
		if ($method_i < ($num_methods - 1)) {
			$following_combs = $this->_getRateAndMethodInstanceCombinations($method_nums, $method_i + 1);
			
			$num_rates = sizeof($this->_methods[$method_num]['rates']);
			
			$num_instances = sizeof($this->_methods[$method_num]['instances']);
			
			for ($rate_i = 0; $rate_i < $num_rates; $rate_i++) {
				for ($instance_i = 0; $instance_i < $num_instances; $instance_i++) {
					foreach ($following_combs as $following_comb) {
						$method_instance_comb = array();
						
						$method_instance_comb[] = array(
							'method' => $method_num,
							'rate_i' => $rate_i,
							'instance_i' => $instance_i,
							'timestamp' => isset($this->_methods[$method_num]['instances'][$instance_i]['timestamp']) ? $this->_methods[$method_num]['instances'][$instance_i]['timestamp'] : ''
						);
						
						foreach ($following_comb as $current_method_instance) {
							$method_instance_comb[] = $current_method_instance;
						}
						
						$method_instance_combs[] = $method_instance_comb;
					}
				}
			}
		} else {
			$num_rates = sizeof($this->_methods[$method_num]['rates']);
			
			$num_instances = 0;
			if(isset($this->_methods[$method_num]['instances'])) {
				$num_instances = sizeof($this->_methods[$method_num]['instances']);
			}
			
			for ($rate_i = 0; $rate_i < $num_rates; $rate_i++) {
				for ($instance_i = 0; $instance_i < $num_instances; $instance_i++) {
					$method_instance_combs[] = array(
						array(
							'method' => $method_num,
							'rate_i' => $rate_i,
							'instance_i' => $instance_i,
							'timestamp' => isset($this->_methods[$method_num]['instances'][$instance_i]['timestamp']) ? $this->_methods[$method_num]['instances'][$instance_i]['timestamp'] : ''
						)
					);
				}
			}
		}
		
		return $method_instance_combs;
	}

	/**
	 * Builds every possible combination of the usable methods, ensuring that each combination
	 * provides exactly one shipping method for each product in the cart.
	 *
	 * @access  protected
	 * @param   array     $products_methods      An array of information about what methods each
	 *                                           product is covered by.
	 * @param   array     $method_combinations   An array of methods which can be combined for all
	 *                                           products previously examined.
	 * @param   array     $products_assigned_methods   An array of the product indexes which have
	 *                                                 already been assigned a shipping method.
	 * @param   integer   $product_i             The product index to be examined.
	 * @return  array|boolean   An array of all possible method combinations for the specified
	 *                          product and all products after it.
	 */
	public function _getProductMethodCombinations($products_methods, $method_combinations = array(), $products_assigned_methods = array(), $product_i = 0)
	{
		$num_products = sizeof($this->order['items']);
		
		$additional_method_combinations = array();
		$current_combination_i = 0;
		
		$this->_debug("<br/>Examining methods for Product Index $product_i...");
		
		if ($product_i < $num_products) {
			$combination_possible = false;
			
			if (isset($this->order['items'][$product_i]['free_shipping']) && $this->order['items'][$product_i]['free_shipping'] == true) {
				// Skip free shipping products
				$following_method_combinations = $this->_getProductMethodCombinations($products_methods, $method_combinations, $products_assigned_methods, $product_i + 1);
				
				if ($following_method_combinations !== false) {
					$combination_possible = true;
					$additional_method_combinations = $following_method_combinations;
				}
			} else {
				// Is this product already covered by a shipping method?
				$num_used_methods_product_covered_by = 0;
				
				if (in_array($product_i, $products_assigned_methods)) {
					// Must ensure that product cannot use more than one of the methods already
					// being used
					foreach ($products_methods[$product_i] as $product_method) {
						if (in_array($product_method, $method_combinations)) {
							$num_used_methods_product_covered_by++;
						}
					}
				}
				
				if ($num_used_methods_product_covered_by > 1) {
					// This product is covered by more than one method currently being used
					// Shipping combination therefore isn't valid!
					$combination_possible = false;
				} else {
					// Product not yet covered by any methods or covered only by one, must check all
					// following product combinations with that generated thus far
					$num_curr_prod_methods = sizeof($products_methods[$product_i]);
					
					for ($curr_prod_method_i = 0; $curr_prod_method_i < $num_curr_prod_methods; $curr_prod_method_i++) {
						
						$current_method = $products_methods[$product_i][$curr_prod_method_i];
						
						$new_method_combinations = $method_combinations;
						$new_products_assigned_methods = $products_assigned_methods;
						
						// Is this method already being used?
						$method_being_used = false;
						
						foreach ($method_combinations as $method_combination) {
							if ($method_combination == $current_method) {
								$method_being_used = true;
								break;
							}
						}
						
						$this->_debug("Product Index $product_i -- " . "Product Method Index $curr_prod_method_i -- " . "Method $current_method: " . ($method_being_used ? "already being used." : "not already being used."));
						
						if (!$method_being_used) {
							// Check if this method covers any products which are already covered
							foreach ($this->_methods[$current_method]['app_product_indexes'] as $app_product_index) {
								if (in_array($app_product_index, $products_assigned_methods)) {
									continue;
								}
							}
							
							$new_method_combinations[] = $current_method;
							
							// Mark off the products that this method covers
							foreach ($this->_methods[$current_method]['app_product_indexes'] as $app_product_index) {
								$new_products_assigned_methods[$app_product_index] = $app_product_index;
							}
							
							$this->_debug("<br />Current list of method combinations...");
							$this->_debug(serialize($new_method_combinations));
						}
						
						$following_method_combinations = $this->_getProductMethodCombinations($products_methods, $new_method_combinations, $new_products_assigned_methods, ($product_i + 1));
						
						if ($following_method_combinations === false) {
							// The combination of all previous methods, this method and the
							// following methods isn't valid!
							continue;
						} else {
							// Combination of all previous methods, this method and the following
							// methods are valid so record the combination's details
							$combination_possible = true;
							
							$num_following = sizeof($following_method_combinations);
							
							if ($num_following > 0) {
								foreach ($following_method_combinations as $following_method_combination) {
									$additional_method_combinations[$current_combination_i] = array();
									
									if (!$method_being_used) {
										$additional_method_combinations[$current_combination_i][] = $current_method;
									}
									
									$additional_method_combinations[$current_combination_i] = array_merge($additional_method_combinations[$current_combination_i], $following_method_combination);
									
									$current_combination_i++;
								}
							} else {
								if (!$method_being_used) {
									$additional_method_combinations[$current_combination_i] = array();
									
									$additional_method_combinations[$current_combination_i][] = $current_method;
									
									$current_combination_i++;
								}
							}
						}
					}
				}
			}
			
			if ($combination_possible === false) {
				return false;
			}
		}
		
		return $additional_method_combinations;
	}

	/**
	 * Examines a table of rates to see if any of the limits for the calculation method(s) within
	 * match the weight/price/number of items/shipping rate/number of packages for a method. If so,
	 * the surcharge rate is calculated and returned.
	 *
	 * @access  protected
	 * @param   string    $table_of_rates             The table of rates.
	 * @param   float     $total_weight_of_packages   The total weight of the method's package(s).
	 * @param   float     $products_total_price       The total price of the method's products.
	 * @param   integer   $product_num_items          The number of items in the method.
	 * @param   float     $order_total_price   The total price of all products in the order, not
	 *                                         just the applicable products for a method.
	 * @param   float     $shipping_rate       The calculated shipping rate for the method.
	 * @param   integer   $num_packages        The number of packages being shipped.
	 * @param   boolean   $rate_limits_inc     Whether or not the limits are inclusive.
	 * @return  float|false   The surcharge rate or false if an error occured parsing the table of
	 *                        rates.
	 */
	public function _calcSurcharge($table_of_rates, $total_weight_of_packages, $products_total_price, $products_num_items, $order_total_price, $shipping_rate, $num_packages, $rate_limits_inc)
	{
		$surcharge = 0;
		
		// Get the calculation method for this table of rates
		$pattern = '|^\<([^\>]+)\>|iU';
		
		if (!preg_match($pattern, $table_of_rates, $matches)) {
			// Simple flat rate being used for surcharge
			$surcharge = (float) $table_of_rates;
			
			$this->_debug("Flat rate being used for surcharge: $surcharge", true);
			
			return $surcharge;
		}
		
		$parsed_rate_string_info = $this->_getParsedRateStringInfoFromTableOfRates($table_of_rates, $total_weight_of_packages, $products_total_price, $products_num_items, $order_total_price, $shipping_rate, $num_packages, $rate_limits_inc);
		
		if (!is_array($parsed_rate_string_info)) {
			return $parsed_rate_string_info;
		}
		
		$calc_method = $parsed_rate_string_info['calc_method'];
		
		$calc_method_value = $parsed_rate_string_info['calc_method_value'];
		
		$rate_string = $parsed_rate_string_info['rate_string'];
		
		$min_max = $parsed_rate_string_info['min_max'];
		
		if (is_array($min_max)) {
			// Min and/or max values have been extracted from the rate format, must use updated
			// string for subsequent calculations
			$rate_string = $min_max['value_format'];
		}
		
		// Check if this surcharge is in fact a discount
		$rate_is_discount = false;
		
		if (substr($rate_string, 0, 1) == '-') {
			$rate_is_discount = true;
			
			$this->_debug('Surcharge rate is actually a discount on the overall rate, so will be substracted from the overall rate', true);
			
			// Remove the minus sign from the rate so it can be calculated properly
			$rate_string = substr($rate_string, 1, strlen($rate_string) - 1);
			
			if (is_array($min_max) && substr($min_max['value_format'], 0, 1) == '-') {
				$min_max['value_format'] = substr($min_max['value_format'], 1, strlen($rate_string) - 1);
			}
		}
		
		switch ($calc_method) {
			case "weight":
				$surcharge_rate_info = $this->_getRateForWeight($calc_method_value, $rate_string, $rate_limits_inc, $min_max);
				break;
			case "price":
			case "totalorderprice":
			case "shipping":
				$surcharge_rate_info = $this->_getRateForPrice($calc_method_value, $rate_string, $rate_limits_inc, $min_max);
				break;
			case "numitems":
			case "numpackages":
				$surcharge_rate_info = $this->_getRateForNumItems($calc_method_value, $rate_string, $rate_limits_inc, $min_max);
				break;
		}
		
		$surcharge = $surcharge_rate_info['rate'];
		
		if ($rate_is_discount) {
			$surcharge = $surcharge * -1;
		}
		
		return $surcharge;
	}

	/**
	 * Calculates a rate based on the weight and rate format string passed.
	 *
	 * @access  protected
	 * @param   float     $weight        The weight for which the rate should be calculated.
	 * @param   string    $rate_format   The string defining the rate.
	 * @param   boolean   $limits_inc    Whether any limits for combination rates are inclusive or
	 *                                   not.
	 * @param   array     $min_max       Any minimum/maximum limits which should be applied to
	 *                                   the final rate calculated.
	 * @return  array|boolean   An array containing the rate and any extra info about individual
	 *                          parts of the rate and how they were calculated or false if there was
	 *                          a problem parsing the rate format.
	 */
	public function _getRateForWeight($weight, $rate_format, $limits_inc, $min_max)
	{
		$rate = 0;
		$rates_info = array();
		
		// Check if a combination rate has been specified
		// Example format: (1-2:3.00)(3-*:2.00)
		if (substr($rate_format, 0, 1) == '(') {
			// Get the list of combination rates and their limits
			$combination_rates_info = $this->_parseCalcCombinationValue($rate_format, $weight, $limits_inc);
			
			if ($combination_rates_info === false) {
				// Couldn't parse the rate properly!
				return false;
			}
			
			$rate = $combination_rates_info['value_total'];
			
			$rates_info = $combination_rates_info['values_info'];
			
			// Attribute the calculation method to the rate
			for ($i = 0, $n = sizeof($rates_info); $i < $n; $i++) {
				$rates_info[$i]['calc_method'] = "weight";
			}
		} else if (strpos($rate_format, '[') !== false) {
			// Value is a block rate, based on weight
			$block_value_info = $this->_parseCalcBlockValue($rate_format, $weight);
			
			if ($block_value_info === false) {
				// Couldn't parse the value properly!
				return false;
			}
			
			$rate_band_rate = $block_value_info['value'];
			
			$block_value = $block_value_info['block_value'];
			$num_blocks = $block_value_info['num_blocks'];
			$block_size = $block_value_info['block_size'];
			
			$rates_info[] = array(
				'value_band_total' => $rate_band_rate,
				'individual_value' => $block_value,
				'num_individual_values' => $num_blocks,
				'block_size' => $block_size,
				'applicable_value' => $weight,
				'additional_value' => null,
				'calc_method' => "weight"
			);
			
			$rate = $rate_band_rate;
		} else if (strpos($rate_format, '%') !== false) {
			// Rate is a percentage based on weight
			$percentage_value = $this->_parseCalcPercentageValue($rate_format, $weight);
			
			if ($percentage_value === false) {
				// Couldn't parse the rate properly!
				return false;
			}
			
			$rate_band_rate = $percentage_value['value'];
			$additional_charge = $percentage_value['additional_value'];
			
			$rates_info[] = array(
				'value_band_total' => $rate_band_rate + $additional_charge,
				'individual_value' => ($weight > 0 ? ($rate_band_rate / $weight) : 0),
				'num_individual_values' => $weight,
				'additional_value' => $additional_charge,
				'calc_method' => "weight"
			);
			
			$rate = $rate_band_rate + $additional_charge;
		} else {
			$rate = $rate_format;
			
			$rates_info[] = array(
				'value_band_total' => $rate,
				'individual_value' => null,
				'num_individual_values' => $weight,
				'additional_value' => null,
				'calc_method' => "weight"
			);
		}
		
		if ($min_max != false) {
			// Apply the limit(s) to the rate
			$rate_limited = $this->calcMinMaxValue($rate, $min_max['min'], $min_max['max']);
			
			if ($rate_limited != $rate) {
				$rate = $rate_limited;
				
				$rates_info = array();
				
				$rates_info[] = array(
					'value_band_total' => $rate,
					'individual_value' => null,
					'num_individual_values' => $weight,
					'additional_value' => null,
					'calc_method' => "weight"
				);
			}
		}
		
		$rate_info = array(
			'rate' => $rate,
			'rate_components_info' => $rates_info
		);
		
		return $rate_info;
	}

    /**
	 * Checks if any shipping methods apply for specific products.
	 *\
	 * @access  protected
	 * @return  boolean   Whether or not category selections are used by any methods.
	 */
	public function _anyMethodsUseProductSelections()
	{
        if(DB::table('advshipper_products')->select(['product_id'])->count() > 0) {
            return true;
        }
        return false;
    }
    
    /**
	 * Checks if any shipping methods apply for specific categories.
	 *
	 * @access  protected
	 * @return  boolean   Whether or not category selections are used by any methods.
	 */
	public function _anyMethodsUseCategorySelections()
	{
		if(DB::table('advshipper_categories')->select(['category_id'])->count() > 0) {
            return true;
        }
        return false;
	}

    /**
	 * Checks if any shipping methods apply for specific manufacturers.
	 *
	 * @access  protected
	 * @return  boolean   Whether or not manufacturer selections are used by any methods.
	 */
	public function _anyMethodsUseManufacturerSelections()
	{
		if(DB::table('advshipper_manufacturers')->select(['manufacturer_id'])->count() > 0) {
            return true;
        }
        return false;
    }
    
    /**
	 * Returns any shipping methods acting as a fallover for all products/categories which haven't
	 * been explicity selected for at least one shipping method.
	 *
	 * @access  protected
	 * @return  array     An array of the numbers for any methods which act as a fallover.
	 */
	public function _getMethodsUsingProductFallover()
	{
        $fallover_methods = array();
        $get_fallover_methods_result = DB::table('advshipper_methods')->where('apply', 1)->get();
        foreach($get_fallover_methods_result as $method) {
            $fallover_methods[] = $method->id;
        }
        return $fallover_methods;
    }
    
    /**
	 * Checks the current configuration to see if any shipping methods specifically cover the
	 * specified product/attribute(s) combination. If so, the number for each of these shipping
	 * methods is included in the returned array.
	 *
	 * @access  protected
	 * @param   integer   $product_id           The product's ID.
	 * @param   array     $product_attributes   The attributes for this product.
	 * @return  array     An array containing the numbers of the shipping methods which specifically
	 *                    cover this product/attribute(s) combination.
	 */
	public function _getProductSpecificShippingMethods($product_id)
	{

        $product_specific_methods = array();
        $check_methods_for_product_result = DB::table('advshipper_products')->where('product_id', $product_id)->get();
        foreach($check_methods_for_product_result as $product) {
            $product_specific_methods[] = $product->method_id;
        }
		return $product_specific_methods;
	}
	
	/**
	 * Applies minimum and/or maxiumum limits to a value.
	 *
	 * @access  public
	 * @param   float     $value   The value to which the limit(s) should be applied.
	 * @param   float     $min     The min limit.
	 * @param   float     $max     The max limit.
	 * @return  float     The calculated value.
	 */
	public function calcMinMaxValue($value, $min, $max)
	{
		if (!is_null($min) && $value < $min) {
			return $min;
		} else if (!is_null($max) && $value > $max) {
			return $max;
		}
		
		return $value;
	}

    /**
	 * Checks the current configuration to see if any shipping methods specifically cover categories
	 * which include the specified product. If so, the number for each of these shipping methods is
	 * included in the returned array.
	 *
	 * @access  protected
	 * @param   integer   $product_id   The product's ID.
	 * @return  array     An array containing the numbers of the shipping methods which specifically
	 *                    cover this product.
	 */
	public function _getCategorySpecificShippingMethods($product_id)
	{
		global $db;
		
		$category_specific_methods = array();
		
		// Get all categories this product is in
		$categories_for_product = array();
        $categories_for_product_result = DB::table('product_category')->where('product_id', $product_id)->get();
        foreach($categories_for_product_result as $pc) {
            $categories_for_product[] = $pc->category_id;
        }
		
		if (sizeof($categories_for_product) > 0) {

            $check_methods_for_categories_result = DB::table('advshipper_categories')->whereIn('category_id', $categories_for_product)->get();
            foreach($check_methods_for_categories_result as $cate) {
                $category_specific_methods[] = $cate->method_id;
            }
		}
		
		return $category_specific_methods;
    }
    
    /**
	 * Checks the current configuration to see if any shipping methods specifically cover
	 * manufacturers which include the specified product. If so, the number for each of these
	 * shipping methods is included in the returned array.
	 *
	 * @access  protected
	 * @param   integer   $product_id   The product's ID.
	 * @return  array     An array containing the numbers of the shipping methods which specifically
	 *                    cover this product.
	 */
	public function _getManufacturerSpecificShippingMethods($product_id)
	{
		global $db;
		
		$manufacturer_specific_methods = array();
		
		// Get the manufacturer for this product
		$manufacturer_for_product_result = DB::table('products')->select(['manufacturer_id'])->where('id', $product_id)->first();
		$manufacturer_for_product = $manufacturer_for_product_result->manufacturer_id;
        $check_methods_for_manufacturers_result = DB::table('advshipper_manufacturers')->where('manufacturer_id', $manufacturer_for_product)->get();
        foreach($check_methods_for_manufacturers_result as $ma) {
            $manufacturer_specific_methods[] = $ma->method_id;
        }
		
		return $manufacturer_specific_methods;
    }

    /**
	 * Checks the specified method to see if it has a region defined which matches the customer's
	 * shipping address. If so, the rates information for that region is returned.
	 *
	 * @access  protected
	 * @param   integer   $method_num   The number of the shipping method to be examined.
	 * @return  array|boolean   The region and rates info for the region which matches the
	 *                          customer's address or false if none was identified or an error was
	 *                          encountered (which should be checked for and passed on to the user).
	 */
	public function _getRegionAndRates($method_num)
	{
        
        $shippingAddress = $this->getShippingAddress();
		
		// Get the necessary information about the customer's address
		$dest_country = strtoupper($shippingAddress['country_code']);
		$dest_postcode = strtolower(preg_replace('/\s+/', '', $shippingAddress['postcode']));
        $dest_state = strtolower($shippingAddress['zone_name']);
        $dest_state_code = strtolower($shippingAddress['zone_code']);
		$dest_city = strtolower($shippingAddress['city']);
		
        // Get the store's shipping country code
        $store_country = strtoupper(\Deepplusplus\Setting\Helpers::config('store_country_code'));
		
		
        $method_result = DB::table('advshipper_methods')->where('id', $method_num)->first();
        $method_result->region_info = DB::table('advshipper_regions')->where('method_id', $method_result->id)->get();
        
        foreach($method_result->region_info as $region_info) {
            if ($region_info->definition_method === 2) {

                if ($dest_postcode == '') {
                    // Can't use geolocation when destination postcode is blank (as can happen
                    // with shipping estimator)
                    continue;
                }

                // $geolocation_function_file = DIR_FS_CATALOG. DIR_WS_MODULES . 'shipping/advshipper/geolocation_' . strtolower($store_country) . '.php';
                /**
                 * To do list
                 * We can use the Google Geolocation API to do this.
                 */


            } else {
                // Geolocation not being used, check customer's address against various address
                // ranges
                
                // Check against the customer's country and postcode ///////////////////////////
                $allowed_countries_postcodes = explode(",", preg_replace('/\s+/', '', $region_info->country_postcode));
                
                // Check if destination address matches the country code and any postcode
                // specified
                $num_countries_postcodes = sizeof($allowed_countries_postcodes);
                for ($region_i2 = 0; $region_i2 < $num_countries_postcodes; $region_i2++) {

                    $allowed_country_postcode = explode(':', $allowed_countries_postcodes[$region_i2]);
                    $allowed_country = strtoupper($allowed_country_postcode[0]);
                    
                    if (sizeof($allowed_country_postcode) > 1) {
                        // Check if this postcode (range) is to be disallowed rather than
                        // allowed
                        if (substr($allowed_country_postcode[1], 0, 1) == '!') {
                            $allow_postcode = false;
                            $postcode_range = strtolower(substr($allowed_country_postcode[1], 1, strlen($allowed_country_postcode[1]) - 1));
                        } else {
                            $allow_postcode = true;
                            $postcode_range = strtolower($allowed_country_postcode[1]);
                        }
                    } else {
                        $postcode_range = null;
                        
                        // Check if this country is to be disallowed rather than allowed
                        if (substr($allowed_country, 0, 1) == '!') {
                            $allow_country = false;
                            $allowed_country = strtoupper(
                                substr($allowed_country, 1, strlen($allowed_country) - 1)
                                );
                            if ($allowed_country == '*') {
                                // Configuration error - ignored for minute
                            }
                        } else {
                            $allow_country = true;
                        }
                    }
                    
                    if ($dest_country === $allowed_country || $allowed_country === '*') {
                        // Check postcode if it has been specified for this region definition
                        if (!is_null($postcode_range) && $postcode_range != '*') {
                            // Must check postcode. Call custom method for country (if exists)
                            $postcode_matches_range_method = '_regionMatchesRange' . $dest_country;
                            
                            if (!method_exists($this, $postcode_matches_range_method)) {
                                $this->quotes['error'] = "Method for determining matching postcode range for country.";
                            } else {
                                // (Match not possible if no destination code available, as
                                // may occur when using shipping estimator).
                                if ($dest_postcode == '') {
                                    continue;
                                }
                                
                                $postcode_matches = $this->$postcode_matches_range_method($dest_postcode, $postcode_range);
                                
                                if ($postcode_matches < 0) {
                                    // An error occurred when attempting to check the postcode
                                    // Alert the user to the fact that they've entered an
                                    // invalid postcode.
                                    
                                    // Check if specific error message for their country exists
                                    $this->quotes['error'] = "Customer postcode error " . $dest_postcode;
                                    return false;
                                } else {
                                    if ($allow_postcode && $postcode_matches) {
                                        // Have matched a valid postcode for this region
                                        return $region_info;
                                    } else if (!$allow_postcode && $postcode_matches) {
                                        // Don't bother checking any other postcodes, this
                                        // postcode isn't allowed for this region!
                                        continue;
                                    }
                                }
                            }
                        } else {
                            if ($allow_country)	{
                                // Not checking against postcode and country matches
                                return $region_info;
                            } else {
                                continue;
                            }
                        }
                    }
                }
            }
        }
		
		return false;
	}
    
    /**
	 * Outputs debug information to the browser if debugging is enabled.
	 *
	 * @access  protected
	 * @param   mixed     $message    The debug information to be displayed or a variable to be
	 *                                dumped.
	 * @param   boolean   $extended   Whether or not this information is "extended" debug info.
	 * @return  none
	 */
	public function _debug($message, $extended = false)
	{
        if(\Deepplusplus\Setting\Helpers::config('module_shipping_advancedshipper_debug') == 1) {
            echo $message . "<br/>\n";
        }
    }
    
    /**
	 * Check Austrian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeAT($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Austrian postcodes are 4 digits in length
		if (!preg_match('/(^[0-9][0-9][0-9][0-9]$)/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeAU()
	
	/**
	 * Check Australian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeAU($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Australian postcodes are 4 digits in length in general but some are only 3 digits
		if (!preg_match('/(^[0-9][0-9][0-9][0-9])|(^[8-9][0-9][0-9])|(^[0-2][0-2][0-9])$/',
				$dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeBA()
	
	/**
	 * Check Bosnian and Herzegowinan postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeBA($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Bosnian and Herzegowinan postcodes are 5 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeBE()
	
	/**
	 * Check Belgian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeBE($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Belgian postcodes are 4 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeBG()
	
	/**
	 * Check Bulgarian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeBG($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Bulgarian postcodes are 4 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeBR()
	
	/**
	 * Check Brazilian postal code.
	 * 
	 * @param   string    $dest_postcode    The postal code to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeBR($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Brazilian postal code are 8 digits in length, normally with a hyphen after the 5th digit
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]\-?([0-9]{3})$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Strip any dash from the code so it can be compared numerically
		$dest_postcode = str_replace('-', '', $dest_postcode);
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeCA()
	
	/**
	 * Check Canadian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeCA($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Canadian postcodes must be of the format X9X 9X9 (whitespace should already have been
		// stripped before calling this method)
		if (!preg_match('/^[a-z][0-9][a-z][0-9][a-z][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Has a range of postcodes been specified?
		if (strpos($postcode_range, '-') !== false) {
			// Check against the range of postcodes specified
			// Check if the range is numerical
			if (preg_match('/([a-z])([0-9])[\-]([0-9])/i', $postcode_range,
					$postcode_range_array)) {
				// Numerical range found (X9-9)
				if (substr($dest_postcode, 0, 1) == $postcode_range_array[1]) {
					$range_start = $postcode_range_array[2];
					$range_end = $postcode_range_array[3];
					
					// Get the number to be compared against the specified numerical range
					$dest_postcode_number = $dest_postcode[1];
					
					if ($dest_postcode_number >= $range_start &&
							$dest_postcode_number <= $range_end) {
						// Postcode matches one of the codes covered by this range!
						return true;
					}
				}
			} else if (preg_match('/([a-z][0-9])([a-z])[\-]([a-z])/i', $postcode_range,
					$postcode_range_array)) {
				// Alphabetic range found (X9X-X)
				// Check if part of code before range matches
				if (substr($dest_postcode, 0, 2) == $postcode_range_array[1]) {
					$range_start = $postcode_range_array[2];
					$range_end = $postcode_range_array[3];
					
					// Get the character to be compared against the specified alphabetic range
					$dest_postcode_character = $dest_postcode[2];
					
					// Get the ASCII code for this character
					$dest_postcode_character_ascii = ord($dest_postcode_character);
					
					if ($dest_postcode_character_ascii >= ord($range_start) &&
							$dest_postcode_character_ascii <= ord($range_end)) {
						// Postcode matches one of the codes covered by this range!
						return true;
					}
				}
			} else {
				$this->quotes['error'] = sprintf(MODULE_ADVANCED_SHIPPER_ERROR_POSTCODE_PARSE,
					$postcode_range);
			}
		} else {
			// Check against a single postcode
			if (substr($dest_postcode, 0, strlen($postcode_range)) == $postcode_range) {
				// Have matched the postcode
				return true;
			}
		}
		
		return false;
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeCH()
	
	/**
	 * Check Swiss postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeCH($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Swiss postcodes are 4 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeCZ()
	
	/**
	 * Check Czech Republic postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeCZ($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Czech Republic postcodes must be 5 digits in length (normally having a space in the
		// middle but this should have been stripped before passing the postcode to this method).
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeDE()
	
	/**
	 * Check German postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeDE($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// German postcodes must be 5 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeDK()
	
	/**
	 * Check Danish postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeDK($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Danish postcodes are 4 digits in length in general but some are only 3 digits. When used
		// internationally they are proceeded with DK
		if (!preg_match('/^[dk\-]*([0-9][0-9][0-9][0-9]?)$/', $dest_postcode, $postcode_parts)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Ignore any prefix
		$dest_postcode = $postcode_parts[1];
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeEE()
	
	/**
	 * Check Estonian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeEE($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Estonian postcodes are 5 digits in length, sometimes being proceeded with EE
		if (!preg_match('/^[e\-]*([0-9][0-9][0-9][0-9][0-9])$/', $dest_postcode, $postcode_parts)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Ignore any prefix
		$dest_postcode = $postcode_parts[1];
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeES()
	
	/**
	 * Check Spanish postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeES($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Spanish postcodes must be 5 digits in length, from 01001 to 52080
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode) ||
				$dest_postcode < 01001 || $dest_postcode > 52080) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeFI()
	
	/**
	 * Check Finnish postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeFI($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Finnish postcodes must be 5 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeFR()
	
	/**
	 * Check French postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeFR($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// French postcodes must be 5 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeGB()
	
	/**
	 * Check UK postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeGB($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// UK postcodes must be of the format X9 9XX, X99 9XX, X9X 9XX, XX9 9XX, XX99 9XX, XX9X 9XX
		// or BFPO (whitespace should already have been stripped before calling this method)
		if (!preg_match('/^[a-z][0-9][0-9][a-z][a-z]$/', $dest_postcode) &&
			!preg_match('/^[a-z][0-9][0-9][0-9][a-z][a-z]$/', $dest_postcode) &&
			!preg_match('/^[a-z][0-9][a-z][0-9][a-z][a-z]$/', $dest_postcode) &&
			!preg_match('/^[a-z][a-z][0-9][0-9][a-z][a-z]$/', $dest_postcode) &&
			!preg_match('/^[a-z][a-z][0-9][0-9][0-9][a-z][a-z]$/', $dest_postcode) &&
			!preg_match('/^[a-z][a-z][0-9][a-z][0-9][a-z][a-z]$/', $dest_postcode) &&
			!preg_match('/^bfpo[0-9]{1,4}$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		if (substr($dest_postcode, 0, 4) == 'bfpo') {
			$dest_outbound_code = 'bfpo';
		} else {
			$dest_outbound_code = substr($dest_postcode, 0, strlen($dest_postcode) - 3);
		}
		
		$dest_inbound_code = substr($dest_postcode,
			-1 * (strlen($dest_postcode) - strlen($dest_outbound_code)));
		
		// Has a range of postcodes been specified?
		if (strpos($postcode_range, '-') !== false) {
			// Check against the range of postcodes specified
			// Check if the range is numerical
			if (preg_match('/^([a-z]+)([0-9]+)[\-]([0-9]+)$/', $postcode_range,
					$postcode_range_array)) {
				// Numerical range found (Matches X9 9XX, X99 9XX, XX9 9XX, XX99 9XX or BFPO XXXX)
				
				// Get the beginning alphanumeric part of the customer's postcode
				preg_match('/^([a-z]+)[0-9]+/', $dest_outbound_code, $dest_postcode_array);
				
				if ($dest_outbound_code == 'bfpo' ||
						$dest_postcode_array[1] == $postcode_range_array[1]) {
					$range_start = $postcode_range_array[2];
					$range_end = $postcode_range_array[3];
					
					// Get the significant digit(s) to be compared against the specified range
					// (Make sure that the beginning of the second part of the postcode doesn't
					// get taken as the end of the first! BT2 3JJ shouldn't be mistaken for
					// BT23 3JJ)!
					if ($dest_outbound_code == 'bfpo') {
						$dest_postcode_numerical_part = substr($dest_postcode, 4,
							strlen($dest_postcode) - 4);
					} else if (strlen($dest_postcode) == 5) {
						$dest_postcode_numerical_part = substr($dest_postcode, 1, 1);
					} else if (strlen($dest_postcode) == 6) {
						if (preg_match('/^[a-z][0-9]/i', $dest_postcode)) {
							$dest_postcode_numerical_part = substr($dest_postcode, 1, 2);
						} else {
							$dest_postcode_numerical_part = substr($dest_postcode, 2, 1);
						}
					} else if (strlen($dest_postcode) == 7) {
						$dest_postcode_numerical_part = substr($dest_postcode, 2, 2);
					}
					
					if ($dest_postcode_numerical_part >= $range_start &&
							$dest_postcode_numerical_part <= $range_end) {
						// Postcode matches one of the codes covered by this range!
						return true;
					}
				}
			} else if (preg_match('/^([a-z]+[0-9])([a-z])[\-]([0-9])$/', $postcode_range,
					$postcode_range_array)) {
				// Alphabetic range found (Matches X9X 9XX or XX9X 9XX)
				// Check if part of code before range matches
				if (substr($dest_postcode, 0, strlen($postcode_range_array[1])) ==
						$postcode_range_array[1]) {
					$range_start = $postcode_range_array[2];
					$range_end = $postcode_range_array[3];
					
					// Get the character to be compared against the specified alphabetic range
					$dest_postcode_character = $dest_postcode[strlen($postcode_range_array[1])];
					
					// Get the ASCII code for this character
					$dest_postcode_character_ascii = ord($dest_postcode_character);
					
					if ($dest_postcode_character_ascii >= ord($range_start) &&
							$dest_postcode_character_ascii <= ord($range_end)) {
						// Postcode matches one of the codes covered by this range!
						return true;
					}
				}
			} else if (strpos($postcode_range, '*') !== false) {
				// Range to be matched includes at least part of the inbound code, so the full
				// postcode format should be specified. E.g. BT100**-1** or BT100J*-K*
				$range_start_end_array = explode('-', $postcode_range);
				
				$range_start = $range_start_end_array[0];
				$range_end = $range_start_end_array[1];
				
				// Only the last digit/character before the wildcards is allowed to use a range, so
				// first thing is to check if all of the preceeding characters match
				$range_start_wildcard_start_pos = strpos($range_start, '*');
				
				$range_start_base_postcode =
					substr($range_start, 0, $range_start_wildcard_start_pos - 1);
				
				$dest_base_postcode =
					substr($dest_postcode, 0, $range_start_wildcard_start_pos - 1);
				
				if ($dest_base_postcode == $range_start_base_postcode) {
					// At least the base postcode matches, now need to check if the destination
					// postcode is within the specified range
					$range_start = substr($range_start, $range_start_wildcard_start_pos - 1);
					
					// Check the format of the range, should be something like 3**-4** or a*-c*
					if (!preg_match('/^[0-9a-z]+[\*]+$/', $range_start) ||
							!preg_match('/^[0-9a-z]+[\*]+$/', $range_end) ||
							strlen($range_start) != strlen($range_end)) {
						$this->quotes['error'] =
							sprintf(MODULE_ADVANCED_SHIPPER_ERROR_POSTCODE_PARSE, $postcode_range);
					} else {
						// Range format okay, can check range against destination postcode
						$dest_significant_part =
							substr($dest_postcode, $range_start_wildcard_start_pos - 1);
						
						// Discount parts of postcode (and ranges) covered by the wildcard(s)
						$num_wildcards = 0;
						
						while (substr($range_start, -1) == '*') {
							$num_wildcards++;
							
							$range_start = substr($range_start, 0, strlen($range_start) - 1);
						}
						
						// Final check or format of range, must make sure it is numerical or
						// alphabetic range and not a mixture
						$range_end = substr($range_end, 0, 1);
						
						if ((is_numeric($range_start) && !is_numeric($range_end)) ||
								(!is_numeric($range_start) && is_numeric($range_end))) {
							$this->quotes['error'] =
								sprintf(MODULE_ADVANCED_SHIPPER_ERROR_POSTCODE_PARSE,
								$postcode_range);
						}
						
						// Ignore parts of destination postcode covered by wildcard(s)
						if (strlen($dest_significant_part) - $num_wildcards == 1) {
							$dest_significant_part = substr($dest_significant_part, 0,
								strlen($dest_significant_part) - $num_wildcards);
							
							if (is_numeric($range_start)) {
								if (is_numeric($dest_significant_part) &&
										$dest_significant_part >= $range_start &&
										$dest_significant_part <= $range_end) {
									// Postcode matches one of the codes covered by this range!
									return true;
								}
							} else if (!is_numeric($dest_significant_part)) {
								// Get the ASCII code for this character
								$dest_postcode_character_ascii = ord($dest_significant_part);
								
								if ($dest_postcode_character_ascii >= ord($range_start) &&
										$dest_postcode_character_ascii <= ord($range_end)) {
									// Postcode matches one of the codes covered by this range!
									return true;
								}
							}
						}
					}
				}
			} else {
				$this->quotes['error'] = sprintf(MODULE_ADVANCED_SHIPPER_ERROR_POSTCODE_PARSE,
					$postcode_range);
			}
		} else if (strpos($postcode_range, '*') !== false) {
			// Check against a single outbound part of a postcode as well as part of its inbound
			// part
			if ($dest_outbound_code == 'bfpo' && substr($postcode_range, 0, 4) == 'bfpo') {
				// Range to be matched is in the format BFPOXXXX where X is a digit or a wildcard
				$postcode_range_outbound_part = 'bfpo';
				
				$postcode_range_inbound_part =
					substr($postcode_range, 4, strlen($postcode_range) - 4);
				
				// Build a regular expression allowing digits in place of the wildcards
				$postcode_range_inbound_part_pattern = '/^';
				
				$postcode_range_inbound_part_pattern = str_replace('*', '[0-9]',
					$postcode_range_inbound_part);
				
				$postcode_range_inbound_part_pattern = '$/';
			} else {
				// Range to be matched has an inbound code with a digit and two letters, any of
				// which can use a wildcard
				$postcode_range_outbound_part =
					substr($postcode_range, 0, strlen($postcode_range) - 3);
				
				$postcode_range_inbound_part = substr($postcode_range, -3);
				
				// Build a regular expression to allow digits or letters in place of the wildcards,
				// as appropriate
				$postcode_range_inbound_part_pattern = '/^';
				
				if (substr($postcode_range_inbound_part, 0, 1) == '*') {
					$postcode_range_inbound_part_pattern .= '[0-9]';
				} else {
					$postcode_range_inbound_part_pattern .=
						substr($postcode_range_inbound_part, 0, 1);
				}
				
				$postcode_range_inbound_part_pattern .=
					str_replace('*', '[a-z]', substr($postcode_range_inbound_part, -2));
				
				$postcode_range_inbound_part_pattern .= '$/';
			}
			
			if ($dest_outbound_code == $postcode_range_outbound_part &&
					preg_match($postcode_range_inbound_part_pattern, $dest_inbound_code)) {
				return true;
			}
		} else if (strlen($postcode_range) == strlen($dest_postcode)) {
			// Check against an entire postcode
			if ($dest_postcode == $postcode_range) {
				return true;
			}
		} else {
			// Check against a single outbound part of a postcode
			if ($dest_outbound_code == 'bfpo') {
				if ($dest_postcode == $postcode_range) {
					return true;
				}
			} else if (strlen($dest_outbound_code) == strlen($postcode_range)) {
				if ($dest_outbound_code == $postcode_range) {
					// Have matched the postcode
					return true;
				}
			} else {
				if (strlen($postcode_range) == 1) {
					if (strlen($dest_outbound_code) == 2) {
						// Matched postcode of format X9 9XX
						if (preg_match('/^[a-z][0-9]/i', $dest_outbound_code)) {
							if (substr($dest_outbound_code, 0, 1) == $postcode_range) {
								return true;
							}
						}
					} else if (strlen($dest_outbound_code) == 3) {
						// Matched postcode of format X99 9XX or X9X 9XX
						if (preg_match('/^[a-z][0-9][0-9]/i', $dest_outbound_code)
								|| preg_match('/^[a-z][0-9][a-z]/i', $dest_outbound_code)) {
							if (substr($dest_outbound_code, 0, 1) == $postcode_range) {
								return true;
							}
						}
					}
				} else if (strlen($postcode_range) == 2) {
					if (strlen($dest_outbound_code) == 3) {
						// Matched postcode of format X9X 9XX or XX9 9XX
						if (preg_match('/^[a-z][0-9][a-z]/i', $dest_outbound_code)
								|| preg_match('/^[a-z][a-z][0-9]/i', $dest_outbound_code)) {
							if (substr($dest_outbound_code, 0, 2) == $postcode_range) {
								return true;
							}
						}
					} else if (strlen($dest_outbound_code) == 4) {
						// Matched postcode of format XX99 9XX or XX9X 9XX
						if (substr($dest_outbound_code, 0, 2) == $postcode_range) {
							return true;
						}
					}
				} else if (strlen($postcode_range) == 3) {
					if (strlen($dest_outbound_code) == 4) {
						// Matched postcode of format XX9X 9XX
						if (preg_match('/^[a-z][a-z][0-9][a-z]/i', $dest_outbound_code)) {
							if (substr($dest_outbound_code, 0, 3) == $postcode_range) {
								return true;
							}
						}
					}
				}
			}
		}
		
		return false;
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeGR()
	
	/**
	 * Check Greek postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeGR($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Greek postcodes must be 5 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeHR()
	
	/**
	 * Check Croatian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeHR($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Croatian postcodes must be 5 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeHU()
	
	/**
	 * Check Hungarian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeHU($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Hungarian postcodes must be 4 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeID()
	
	/**
	 * Check Indonesian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeID($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Indonesian postcodes are 5 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeIE()
	
	/**
	 * Check Irish postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeIE($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Irish postcodes are plain text, possibly with a number after them
		
		// Check against a single postcode
		if (substr($dest_postcode, 0, strlen($postcode_range)) == $postcode_range) {
			// Have matched the postcode
			return true;
		}
		
		return false;
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeIN()
	
	/**
	 * Check Indian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeIN($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Indian postcodes must be 6 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeIT()
	
	/**
	 * Check Italian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeIT($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Italian postcodes are 5 digits in length, with an optional two character prefix
		if (!preg_match('/^[a-z]?[a-z]?\-?([0-9][0-9][0-9][0-9][0-9])/', $dest_postcode,
				$postcode_parts)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Ignore any prefix
		$dest_postcode = $postcode_parts[1];
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeLI()
	
	/**
	 * Check Liechtenstein postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeLI($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Liechtenstein postcodes are 4 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeLT()
	
	/**
	 * Check Lithuanian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeLT($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Lithuanian postcodes are 5 digits in length, sometimes being proceeded with LT
		if (!preg_match('/^[lt\-]*([0-9][0-9][0-9][0-9][0-9])$/', $dest_postcode,
				$postcode_parts)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Ignore any prefix
		$dest_postcode = $postcode_parts[1];
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeLV()
	
	/**
	 * Check Latvian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeLV($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Latvian postcodes are 4 digits in length, normally being proceeded with LV
		if (!preg_match('/^[lv\-]*([0-9][0-9][0-9][0-9])$/', $dest_postcode, $postcode_parts)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Ignore any prefix
		$dest_postcode = $postcode_parts[1];
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeMK()
	
	/**
	 * Check Macedonian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeMK($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Macedonian postcodes are 4 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeMY()
	
	/**
	 * Check Malaysian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeMY($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Malaysian postcodes must be 5 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeNL()
	
	/**
	 * Check Dutch postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeNL($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Dutch postcodes are alphanumeric, having 4 digits followed by two letters, sometimes
		// being proceeded with NL
		if (!preg_match('/^[nl\-]*([0-9][0-9][0-9][0-9])[a-z][a-z]$/', $dest_postcode,
				$postcode_parts)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Ignore any prefix and the two letters at the end, only comparing the numerical part of
		// the postcode
		$dest_postcode = $postcode_parts[1];
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeNO()
	
	/**
	 * Check Norwegian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeNO($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Norwegian postcodes are 4 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeNZ()
	
	/**
	 * Check New Zealand postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeNZ($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// New Zealand postcodes are 4 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangePH()
	
	/**
	 * Check Philippines postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangePH($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Philippines postcodes are 4 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangePL()
	
	/**
	 * Check Polish postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangePL($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Polish postcodes are 5 digits in length, with a hyphen after the first two digits
		
		// Remove any hyphen
		$dest_postcode = str_replace('-', '', $dest_postcode);
		
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangePT()
	
	/**
	 * Check Portuguese postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangePT($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Portuguese postcodes must be at least 4 digits in length (can ignore rest of code)
		if (!preg_match('/^([0-9][0-9][0-9][0-9])/', $dest_postcode, $postcode_parts)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Only use the first four digits
		$dest_postcode = $postcode_parts[1];
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeRO()
	
	/**
	 * Check Romanian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeRO($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Romanian postcodes are 6 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeRS()
	
	/**
	 * Check Serbian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeRS($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Serbian postcodes are 5 or 6 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9][0-9]?$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeSE()
	
	/**
	 * Check Swedish postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeSE($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Swedish postcodes are 5 digits in length (in a group of 3 and 2 but with the whitespace
		// stripped they'll just be treated as a single 5 digit number)
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeSI()
	
	/**
	 * Check Slovenian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeSI($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Slovenian postcodes are 4 digits in length When used internationally they can be
		// proceeded with SI
		if (!preg_match('/^[si\-]*([0-9][0-9][0-9][0-9])$/', $dest_postcode, $postcode_parts)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Ignore any prefix
		$dest_postcode = $postcode_parts[1];
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeSK()
	
	/**
	 * Check Slovakian postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeSK($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Slovakian postcodes are 5 digits in length
		if (!preg_match('/^[0-9][0-9][0-9][0-9][0-9]?$/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeSM()
	
	/**
	 * Check San Marino postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeSM($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// San Marino postcodes are 5 digits in length, with an optional three character prefix
		if (!preg_match('/^[a-z]?[a-z]?[a-z]?\-?([0-9][0-9][0-9][0-9][0-9])/', $dest_postcode,
				$postcode_parts)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Ignore any prefix
		$dest_postcode = $postcode_parts[1];
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeUS()
	
	/**
	 * Check US postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeUS($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// US ZIP codes must be at least 5 digits in length (can ignore extended digits)
		if (!preg_match('/^([0-9][0-9][0-9][0-9][0-9])/', $dest_postcode, $postcode_parts)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Only use the first five digits
		$dest_postcode = $postcode_parts[1];
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeVA()
	
	/**
	 * Check Vatican City postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeVA($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// Vatican City postcodes are 5 digits in length, with an optional three character prefix
		if (!preg_match('/^[a-z]?[a-z]?[a-z]?\-?([0-9][0-9][0-9][0-9][0-9])/', $dest_postcode,
				$postcode_parts)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		// Ignore any prefix
		$dest_postcode = $postcode_parts[1];
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _regionMatchesRangeZA()
	
	/**
	 * Check South African postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean|integer   The boolean status of whether or not the postcode was matched or
	 *                            -1 if an error occurred (Customer's postcode is in incorrect
	 *                            format to be tested by this method).
	 */
	public function _regionMatchesRangeZA($dest_postcode, $postcode_range)
	{
		// Check the postcode is in the correct format
		// South African postcodes are 4 digits in length
		if (!preg_match('/(^[0-9][0-9][0-9][0-9]$)/', $dest_postcode)) {
			// Postcode is not in the correct format
			return -1;
		}
		
		return $this->_numericalPostcodeMatchesRange($dest_postcode, $postcode_range);
	}
	
	// }}}
	
	
	// {{{ _numericalPostcodeMatchesRange()
	
	/**
	 * Helper method to check a numerical postcode.
	 * 
	 * @param   string    $dest_postcode    The postcode to be checked (Whitespace should already
	 *                                      have been stripped and should be lowercase).
	 * @param   string    $postcode_range   A postcode or range of postcodes to check against
	 *                                      (should be lowercase).
	 * @return  boolean   The boolean status of whether or not the postcode was matched or not.
	 */
	public function _numericalPostcodeMatchesRange($dest_postcode, $postcode_range)
	{
		// Has a range of postcodes been specified?
		if (strpos($postcode_range, '-') !== false) {
			// Check against the range of postcodes specified
			// Format xxxxx-xxxxx where x = 0-9, with first digit(s) being significant
			if (preg_match('/^([0-9]+)[\-]([0-9]+)/', $postcode_range, $postcode_range_array)) {
				$range_start = $postcode_range_array[1];
				$range_end = $postcode_range_array[2];
				
				if (substr($dest_postcode, 0, strlen($range_start)) >= $range_start
					&& substr($dest_postcode, 0, strlen($range_end)) <= $range_end) {
					// Postcode matches one of the codes covered by this range!
					return true;
				}
			} else {
				$this->quotes['error'] = sprintf(MODULE_ADVANCED_SHIPPER_ERROR_POSTCODE_PARSE,
					$postcode_range);
			}
		} else {
			// Check against a single postcode
			if (substr($dest_postcode, 0, strlen($postcode_range)) == $postcode_range) {
				// Have matched the postcode
				return true;
			}
		}
		
		return false;
	}

	/**
	 * Extracts the source for an element. Takes encapsulated elements of the same type into
	 * consideration so that they form part of the source also.
	 *
	 * @access  protected
	 * @param   string    $source           The source in which to look for the element.
	 * @param   integer   $start_pos        The position within the source to begin looking for the
	 *                                      element.
	 * @param   string    $tag_name         The name of the tag for this element
	 * @param   string    $attributes       An optional attribute string which wmay be used to
	 *                                      identify an element uniquely (rather than simply
	 *                                      searching for the element type)
	 * @param   boolean   $match_brackets   Whether the elements use '<>' or '{}' (true for brackets)
	 * @return  string|boolean   The source of the element or false if not found.
	 */
	public function _extractElement($source, $start_pos, $tag_name, $attributes = '', $match_brackets = false)
	{
		// Build the first test for the start tag
		if (!$match_brackets) {
			$start_tag_with_attributes = '<' . $tag_name . $attributes;
			$start_tag = '<' . $tag_name;
			$end_tag = '</' . $tag_name . '>';
		} else {
			$start_tag_with_attributes = '{' . $tag_name . $attributes;
			$start_tag = '{' . $tag_name;
			$end_tag = '{/' . $tag_name . '}';
		}
		
		$start_tag_pos = strpos($source, $start_tag_with_attributes, $start_pos);
		if ($start_tag_pos === false) {
			// No matching tag found
			return false;
		}
		
		// Find ending tag for this element
		$tag_source = substr($source, $start_tag_pos, (strlen($source) - $start_tag_pos));
		
		$num_open_elements = 1;
		$current_start_pos = $start_tag_pos + 1; // Add 1 to ensure this tag isn't matched again
		
		do {

			$end_tag_pos = strpos($source, $end_tag, $current_start_pos);
			if ($end_tag_pos === false) {
				// Starting tag not closed in source - error, can't extract the element!
				return false;
			} else {
				$num_open_elements--;
			}
			
			// Check if any starting tags for similar elements exist within this element. If they do
			// then closing tag just found may belong to another element.
			do {
				$current_start_pos = strpos($source, $start_tag, $current_start_pos);
				
				if ($current_start_pos !== false && $current_start_pos < $end_tag_pos) {
					// Another element of the same type exists within this element. Must ensure its
					// closing tag is not taken as the closing tag for this element.
					$num_open_elements++;
					$current_start_pos++; // Add 1 to ensure this tag isn't matched again
				} else {
					// No (more) encapsulated elements found
					break;
				}
			} while (1);
			
			if ($num_open_elements == 0) {
				// No open encapsulated tags found so the source found IS the source for the element
				break;
			}
			
			// There are still some open encapsulated tags, need to move past their closing tags to
			// find the closing tag for the element we are interested in
			$current_start_pos = $end_tag_pos + 1;
		} while (1);
		
		return substr($source, $start_tag_pos, ($end_tag_pos - $start_tag_pos) + strlen($end_tag));
	}

	/**
	 * Calculates a timestamp for a day of the week and time in either the forthcoming or past
	 * week.
	 *
	 * @access  protected
	 * @param   integer   $day_of_week   The day of the current week.
	 * @param   integer   $time_of_day   The time of day.
	 * @return  integer   The UNIX timestamp.
	 */
	public function _calcDayOfWeekAndTimeTimestamp($day_of_week, $time_of_day)
	{
		// Get the timestamp for the start of this week
		$current_ts = time();
		
		$current_day = date('w', $current_ts);
		$current_hour = date('G', $current_ts);
		$current_minute = date('i', $current_ts);
		
		$start_of_week_ts = $current_ts - (($current_hour + 1) * 3600) - ($current_minute * 60) -
			($current_day * 24 * 3600);
		
		if (is_string($time_of_day) && preg_match('/^[0-9][0-9]:[0-9][0-9]/', $time_of_day)) {
			$hour = (int) substr($time_of_day, 0, 2);
			$minute = (int) substr($time_of_day, 3, 2);
		} else {
			$hour = 0;
			$minute = 0;
		}
		
		$ts = $start_of_week_ts + ($day_of_week * 24 * 3600) + (($hour + 1) * 3600) + ($minute * 60);
		
		return $ts;
	}

}