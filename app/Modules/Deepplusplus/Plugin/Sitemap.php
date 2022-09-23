<?php

class Sitemap {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sitemaps = \Storage::allFiles("public/sitemaps");
        $sitemapUrls = [];
        foreach($sitemaps as $sm) {
            $sitemapUrls[] = url(\Storage::url($sm));
        }
        return $sitemapUrls;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        // Create sitemap folder is missed
        $sitemapDic = storage_path('app/public/sitemaps');
        if(is_dir($sitemapDic) === false) {
            mkdir($sitemapDic);
        }

        // Categories
        $sitemapCategories = \App::make("sitemap");
        $categories = \DB::table('categories')
                        ->join('category_translations', 'categories.id', '=', 'category_translations.category_id')
                        ->where('hidden', 0)
                        ->orderBy('categories.created_at', 'desc')->get();
        foreach ($categories as $cat)
        {
            $sitemapCategories->add(url('/category/' . $cat->slug), $cat->updated_at, 1, 'daily');
        }
        $sitemapCategories->store('xml','storage/sitemaps/sitemap-categories');

        // Manufacturers
        $sitemapManufacturers = \App::make("sitemap");
        $manufacturers = \DB::table('manufacturers')
                            ->join('manufacturer_translations', 'manufacturers.id', '=', 'manufacturer_translations.manufacturer_id')
                            ->orderBy('manufacturers.created_at', 'desc')
                            ->get();
        foreach ($manufacturers as $m)
        {
            $sitemapManufacturers->add(url('/manufacturer/' . $m->slug), $m->updated_at, 1, 'daily');
        }
        $sitemapManufacturers->store('xml','storage/sitemaps/sitemap-manufacturers');

        // Posts
        $sitemapPosts = \App::make("sitemap");
        $posts = \DB::table('posts')
                            ->join('post_translations', 'posts.id', '=', 'post_translations.post_id')
                            ->orderBy('posts.created_at', 'desc')
                            ->get();
        foreach ($posts as $item)
        {
            $sitemapPosts->add(url('/post/' . $item->slug), $item->updated_at, 1, 'daily');
        }
        $sitemapPosts->store('xml','storage/sitemaps/sitemap-posts');

        // Static pages
        $sitemapPosts = \App::make("sitemap");
        $posts = \DB::table('meta_tags')
                            ->join('meta_tag_translations', 'meta_tags.id', '=', 'meta_tag_translations.meta_tag_id')
                            ->orderBy('meta_tags.created_at', 'desc')
                            ->get();
        foreach ($posts as $item)
        {
            $sitemapPosts->add(url($item->path), $item->updated_at, 1, 'daily');
        }
        $sitemapPosts->store('xml','storage/sitemaps/sitemap-static-pages');

        // create sitemap
        $sitemapProducts = \App::make("sitemap");

        // add items
        $products = \DB::table('product_translations')->orderBy('created_at', 'desc')->get();
        foreach ($products as $product)
        {
            $sitemapProducts->add(url('/product/' . $product->slug), $product->updated_at, 1, 'daily');
        }

        // create file sitemap-posts.xml in your public folder (format, filename)
        $sitemapProducts->store('xml','storage/sitemaps/sitemap-products');

        // create sitemap index
        $sitemap = \App::make("sitemap");

        // add sitemaps (loc, lastmod (optional))
        $sitemap->addSitemap(url('storage/sitemaps/sitemap-products.xml'));
        $sitemap->addSitemap(url('storage/sitemaps/sitemap-categories.xml'));
        $sitemap->addSitemap(url('storage/sitemaps/sitemap-manufacturers.xml'));
        $sitemap->addSitemap(url('storage/sitemaps/sitemap-posts.xml'));
        $sitemap->addSitemap(url('storage/sitemaps/sitemap-static-pages.xml'));

        // create file sitemap.xml in your public folder (format, filename)
        $sitemap->store('sitemapindex','storage/sitemaps/sitemap');

        $sitemapUrls = $this->index();
        
        return $sitemapUrls;

    }

}