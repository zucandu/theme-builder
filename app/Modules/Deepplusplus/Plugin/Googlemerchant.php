<?php

class Googlemerchant {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $feeds = \Storage::allFiles("public/feeds/google");
        $feedUrls = [];
        foreach($feeds as $f) {
            if(strpos($f, '.xml') !== false) {
                $feedUrls[] = \Deepplusplus\Setting\Helpers::config('store_url') . \Storage::url($f);
            }
        }
        return $feedUrls;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($params)
    {

        // Params
        $locale = $params['language'];
        $currency = $params['currency']['code'];

        // XML
        $dom = new DOMDocument('1.0', 'utf-8');
        $rss = $dom->createElement('rss');
        $rss->setAttribute('version', '2.0');
        $rss->setAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
        $channel = $dom->createElement('channel');
        $title = $dom->createElement('title');
        $title->appendChild($dom->createCDATASection(\Deepplusplus\Setting\Helpers::config('store_name')));
        $link = $dom->createElement('link', \Deepplusplus\Setting\Helpers::config('store_url'));
        $channel_description = $dom->createElement('description', 'Google Merchant Product Feeder - Deepplusplus');
        $channel->appendChild($title);
        $channel->appendChild($link);
        $channel->appendChild($channel_description);

        switch($params['type']) {
            case 'product':
                
                $products = \DB::table('products')->select(
                    'products.*', 'product_translations.name', 'product_translations.slug', 'product_translations.summary', 
                    'product_translations.description', 'product_translations.locale'
                )->join(
                    'product_translations', 'products.id', '=', 'product_translations.product_id'
                )->where([
                    ['products.status', 1],
                    ['product_translations.locale', $locale]
                ])->get();

                foreach($products as $product) {

                    // Get this from category/database
                    $googleProductCategory = 'Animals & Pet Supplies > Live Animals';

                    $item = $dom->createElement('item');
                    $productName = $dom->createElement('title');
                    $productName->appendChild($dom->createCDATASection($product->name));
                    $item->appendChild($productName);

                    $id = $dom->createElement('g:id');
                    $id->appendChild($dom->createCDATASection($product->id));
                    $item->appendChild($id);

                    // Get tax rate
                    $taxRate = \Deepplusplus\Setting\Helpers::taxRate($product->tax_class_id);

                    // Price with tax
                    $price = \Deepplusplus\Setting\Helpers::priceWithTax($product->price, $taxRate);

                    // modify price to match defined currency
                    $price = number_format($price*$params['currency']['rate'], $params['currency']['decimal_digits']);

                    $item->appendChild($dom->createElement('g:price', "{$price} {$currency}"));

                    // Sale price
                    if((float)$product->sale_price > 0) {

                        // Sale price with tax
                        $salePrice = \Deepplusplus\Setting\Helpers::priceWithTax($product->sale_price, $taxRate);

                        // modify price to match defined currency
                        $salePrice = number_format($salePrice*$params['currency']['rate'], $params['currency']['decimal_digits']);
                        
                        $item->appendChild($dom->createElement('g:sale_price', "{$salePrice} {$currency}"));

                        // Sale date
                        $fromStringToTime = strtotime($product->sale_date_from);
                        $toStrToTime = strtotime($product->sale_date_to);
                        if(($fromStringToTime > 0) && ($toStrToTime > 0) && ($toStrToTime > $fromStringToTime)) {
                            $item->appendChild($dom->createElement('g:sale_price_effective_date', gmdate("Y-m-d\TH:i:s\Z", $fromStringToTime) . '/' . gmdate("Y-m-d\TH:i:s\Z", $toStrToTime)));
                        }

                    }

                    // Google tax rate
                    if((float)$taxRate > 0) {
                        $tax = $dom->createElement('g:tax');
                        $tax->appendChild($dom->createElement('g:country', \Deepplusplus\Setting\Helpers::config('store_country_code')));
                        $tax->appendChild($dom->createElement('g:region', \Deepplusplus\Setting\Helpers::config('store_zone_code')));
                        $tax->appendChild($dom->createElement('g:rate', (float)$taxRate));
                        $item->appendChild($tax);
                    }

                    // Inventory
                    if ($product->quantity > 0) {
                        $item->appendChild($dom->createElement('g:availability', 'in stock'));
                    } else {
                        $item->appendChild($dom->createElement('g:availability', 'out of stock'));
                    }

                    if((float)$product->weight > 0) {
                        $item->appendChild($dom->createElement('g:shipping_weight', $product->weight . ' ' . $params['weight_unit']));
                    }

                    $shipping = $dom->createElement('g:shipping');
                    if (!empty($params['shipping']['country'])) {
                        $shipping->appendChild($dom->createElement('g:country', $params['shipping']['country']));
                    }
                    if (!empty($params['shipping']['region'])) {
                        $shipping->appendChild($dom->createElement('g:region', $params['shipping']['region']));
                    }
                    if (!empty($params['shipping']['service'])) {
                        $shipping->appendChild($dom->createElement('g:service', $params['shipping']['service']));
                    }
                    $shipping->appendChild($dom->createElement('g:price', number_format($params['shipping']['cost'], 2) . ' ' . $currency));
                    $item->appendChild($shipping);

                    // Manufacturer
                    if ((int)$product->manufacturer_id > 0) {
                        $query = \DB::table('manufacturers')->select('name')->where('id', (int)$product->manufacturer_id)->first();
                        $manufacturer = $dom->createElement('g:brand');
                        $manufacturer->appendChild($dom->createCDATASection($query->name));
                        $item->appendChild($manufacturer);
                    }

                    // Condition
                    $item->appendChild($dom->createElement('g:condition', 'new'));

                    // Get product type
                    $categories = \DB::table('categories')->select(
                        'categories.*', 'category_translations.name'
                    )->join(
                        'category_translations', 'categories.id', '=', 'category_translations.category_id'
                    )->where([
                        ['categories.status', 1],
                        ['categories.type', 'c'],
                        ['category_translations.locale', $locale]
                    ])->get();

                    $categories = json_decode(json_encode($categories), true);

                    $productCategory = \DB::table('product_category')->select('category_id')->where('product_id', $product->id)->first();
                    
                    if($productCategory) {

                        // Get product type
                        $productType = $dom->createElement('g:product_type');
                        $productType->appendChild($dom->createCDATASection(str_replace('^', ' &gt; ', htmlspecialchars(\Deepplusplus\Setting\Helpers::categoryFullPath($productCategory->category_id, $categories)))));
                        $item->appendChild($productType);

                        // Google product category
                        $query = \DB::table('categories')->select('google_product_category')->where('id', $productCategory->category_id)->first();
                        if($query->google_product_category) {
                            //$item->appendChild($dom->createElement('g:google_product_category', htmlspecialchars($query->google_product_category)));
                            $googleProductCategory = $dom->createElement('g:google_product_category');
                            $googleProductCategory->appendChild($dom->createCDATASection(htmlspecialchars($query->google_product_category)));
                            $item->appendChild($googleProductCategory);
                        }
                    }

                    // Get images
                    $images = \DB::Table('product_images')->where('product_id', $product->id)->get();
                    foreach($images as $key => $img) {
                        if($key === 0) {
                            $item->appendChild($dom->createElement('g:image_link', \Deepplusplus\Setting\Helpers::config('store_url') . '/images/' . $img->src));
                        } else {
                            $item->appendChild($dom->createElement('g:additional_image_link', \Deepplusplus\Setting\Helpers::config('store_url') . '/images/' . $img->src));
                        }
                    }

                    // Link
                    $item->appendChild($dom->createElement('link', \Deepplusplus\Setting\Helpers::urlBySlug('product', $product->slug)));

                    // identifier_exists: Use for products that don’t have a GTIN, MPN, or brand.
                    if($params['identifier'] === 'no') {
                        $item->appendChild($dom->createElement('g:identifier_exists', $params['identifier']));
                    } else {

                        // MPN
                        $mpnCode = $product->mpn ? $product->mpn : $product->sku;
                        if (!empty($mpnCode)) {
                            $mpn = $dom->createElement('g:mpn');
                            $mpn->appendChild($dom->createCDATASection($mpnCode));
                            $item->appendChild($mpn);
                        }

                        // UPC
                        if (!empty($product->gtin)) {
                            $upc = $dom->createElement('g:upc');
                            $upc->appendChild($dom->createCDATASection($product->gtin));
                            $item->appendChild($upc);
                        }

                    }

                    // Attributes
                    $attributes = \DB::Table('product_attributes')->select(\DB::raw('attribute_option_translations.name as option'), \DB::raw('attribute_option_value_translations.name as value'))->join(
                        'attribute_options', 'product_attributes.attribute_option_id', '=', 'attribute_options.id'
                    )->join(
                        'attribute_option_translations', 'attribute_options.id', '=', 'attribute_option_translations.attribute_option_id'
                    )->join(
                        'attribute_option_values', 'product_attributes.attribute_option_value_id', '=', 'attribute_option_values.id'
                    )->join(
                        'attribute_option_value_translations', 'attribute_option_values.id', '=', 'attribute_option_value_translations.attribute_option_value_id'
                    )->where([
                        ['product_attributes.product_id', $product->id],
                        ['attribute_option_translations.locale', $locale],
                        ['attribute_option_value_translations.locale', $locale],
                    ])->get();
                    
                    // Item group
                    if((int)$product->parent_id > 0) {
                        
                        // item group id
                        $item->appendChild($dom->createElement('item_group_id', (int)$product->parent_id));

                        // handle with option/value element
                        foreach($attributes as $at) {
                            $attr = $dom->createElement('g:' . preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($at->option)));
                            $attr->appendChild($dom->createCDATASection($at->value));
                            $item->appendChild($attr);
                        }
                        
                    }

                    $channel->appendChild($item);

                }

                $rss->appendChild($channel);
                $dom->appendChild($rss);
                $dom->formatOutput = true;
                
                // Save file
                $fileName = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(\Deepplusplus\Setting\Helpers::config('store_name')));
                $dom->save(\Storage::path("public/feeds/google/{$fileName}-{$locale}-{$currency}.xml"));

            break;
        }

        return $this->index();
    }

}