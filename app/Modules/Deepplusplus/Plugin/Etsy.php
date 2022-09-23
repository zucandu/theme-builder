<?php

class Etsy {

    /**
     * Hook into source code
     */
    public $availableHooks = ['get_access_token'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // If Etsy configs exists, disable installation button
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Process the subscription data from order completed
     * @param Object $data paramenter from external websites
     * @param String $listener where the event/listener called
     * @return Boolean true/false
     * 
     */
    public function responsehandler($data, $listener)
    {
        switch($listener) {
            case 'get_access_token':
                // return $this->getAccessToken();
            break;
        }
    }

    /**
     * Get access token from Etsy
     */
    public function getAccessToken()
    {
        // Redirect to Etsy
		if(!isset($_GET['code']) && empty($_GET['code'])) {
			
			$length = 43;
			$codeVerifier = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
			session()->put('code_challenge', $codeVerifier);
            session()->save();
			
			$data = [
				'client_id' => 'mst4rkoq1cwdjhnj17p2ao94',
				'response_type' => 'code',
				'redirect_uri' => 'https://deepplus.plus/authentication/etsy',
				'scope' => 'listings_w transactions_r transactions_w',
				'state' => csrf_token(),
				'code_challenge' => 'find-this-func->'/* $this->PKCECodeChallenge($codeVerifier) */,
				'code_challenge_method' => 'S256'
			];
		
			$queryStr = http_build_query($data);
			$loginUrl = "https://www.etsy.com/oauth/connect?{$queryStr}";
			header("Location: " . $loginUrl);
		} else {
			
			$response = \Http::post('https://api.etsy.com/v3/public/oauth/token', [
				'client_id' => 'mst4rkoq1cwdjhnj17p2ao94',
				'grant_type' => 'authorization_code',
				'redirect_uri' => 'https://deepplus.plus/authentication/etsy',
				'code' => $_GET['code'],
				'code_verifier' => session()->get('code_challenge')
			]);
			
			$responseJson = $response->json();
			
			file_put_contents(storage_path('app/public/etsy.json'), json_encode($responseJson));
			
			return ['message' => 'Your access token has been stored.', 'data' => $responseJson];
		}
    }

    /**
     * 
     */
    public function createDraftListing()
    {
        /**
         * TESTING
         */
		$shopId = "28057089";
		
		// Get Etsy json content
		$path = "public/etsy.json";
		$etsyJson = json_decode(\Storage::get($path), true); 
		$accessToken = $etsyJson['access_token'];
		$refreshToken = $etsyJson['refresh_token'];
		$expiresIn = $etsyJson['expires_in'];
		
		// Get last modified
		$lastModified = Carbon::parse(\Storage::lastModified($path))->addSeconds($expiresIn)->timestamp;
		$now = Carbon::now()->timestamp;
		
		// Refresh the access token if expired
		if($now > $lastModified) {
			$refresh = \Http::post('https://api.etsy.com/v3/public/oauth/token', [
				'client_id' => 'mst4rkoq1cwdjhnj17p2ao94',
				'grant_type' => 'refresh_token',
				'refresh_token' => $refreshToken,
			]);
			$responseJson = $refresh->json();
			file_put_contents(storage_path('app/public/etsy.json'), json_encode($responseJson));
			
			$accessToken = $responseJson['access_token'];
		}
		
        $response = \Http::withHeaders([
			'Content-Type' => 'application/json',
			'x-api-key' => 'mst4rkoq1cwdjhnj17p2ao94',
			'Authorization' => "Bearer {$accessToken}"
		])->post("https://openapi.etsy.com/v3/application/shops/{$shopId}/listings", [
			'quantity' => 2,
			'title' => 'createDraftListing Creates a physical draft listing product in a shop on the Etsy channel.',
			'description' => 'The positive non-zero number of products available for purchase in the listing. Note: The listing quantity is the sum of available offering quantities. You can request the quantities for individual offerings from the ListingInventory resource using the getListingInventory endpoint.',
			'price' => 15.66,
			'who_made' => 'i_did',
			'when_made' => 'made_to_order',
			'taxonomy_id' => 1,
			'shipping_profile_id' => 131680082016,
			'item_weight' => 1.2,
			'item_length' => 16,
			'item_height' => 8,
			'item_width' => 12,
			'item_weight_unit' => 'lb',
			'item_dimensions_unit' => 'in'
		]);
		
		/* var_dump($response->json());
		die('aaaaaaaa'); */
    }

}