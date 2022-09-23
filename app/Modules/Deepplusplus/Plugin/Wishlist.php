<?php

class Wishlist {

    /**
     * Add product into wishlist
     * @param Array $params a product array
     * @return Boolean true/false
     */
    public function add($params)
    {
        $id = $this->defaultWishlist(auth()->user()->id);
        if($id === 0) {
            $id = DB::table('wishlists')->insertGetId([
                'name' => 'WLID #: ' . auth()->user()->id, 
                'customer_id' => auth()->user()->id,
                'created_at' => DB::raw('now()'),
            ]);
        }

        DB::table('wishlist_products')->updateOrInsert(
            ['wishlist_id' => $id, 'product_id' => $params['id']],
            [
                'wl_qty' => 1, 
                'created_at' => DB::raw('now()'),
                'updated_at' => DB::raw('now()')
            ]
        );

        return true;
    }

    /**
     * 
     */
    public function list()
    {
        return response()->json(['products' => $this->wlGetProducts()]);
    }

    /**
     * Get default wishlist
     * @param Integer $customerId
     * @return Integer wishlist id
     */
    public function defaultWishlist($customerId)
    {
        $wishlist = DB::table('wishlists')->select('id')->where(['customer_id' => $customerId])->first();
        return $wishlist ? $wishlist->id : 0;
    }

    /**
     * get all of product ids from wishlists
     * @param Int $customerId
     * @return Array $productIds
     */
    public function wlGetProducts()
    {
        $productIds = DB::table('wishlist_products')->whereIn('wishlist_id', DB::table('wishlists')->where('customer_id', auth()->user()->id)->pluck('id')->toArray())->select('product_id')->pluck('product_id');
        $products = DB::table('products')->whereIn('id', $productIds)->get();
        $products->map(function($product) {
            
            $product->images = DB::table('product_images')->select('src')->where('product_id', $product->id)->get();

            // Get a few values from parent product if this is a child product
            $parentProduct = DB::table('products')->where('id', DB::table('products')->where('id', $product->id)->select('parent_id')->value('parent_id'))->first();

            // Get parent product images if child product empty
            if($parentProduct) {
                
                if($product->images->count() === 0) {
                    $product->images = DB::table('product_images')->where('product_id', $parentProduct->id)->get();

                    // If image is not available, get no-image
                    if($product->images->count() === 0) {
                        $product->images = [['src' => 'no-image.png']];
                    }
                }

                // Reviews
                $product->total_reviews = $parentProduct->total_reviews;
                $product->rating = $parentProduct->rating;
            }

            $product->translations = DB::table('product_translations')->select('name', 'slug', 'locale')->where('product_id', $product->id)->get();
        });
        return $products;
    }

}