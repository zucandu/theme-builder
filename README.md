## Getting Started with the Theme Builder for Zucandu

<p>To create a new theme for Zucandu, you can install this package on your local. You will have to know a little about Vanilla JS, Vuejs. It's easy because we have all temporary data so you just need to change your HTML/CSS. You can keep all javascript code from <script> tag in Vue components if you don't know much about JS or Vue.</p>

<p>In this package, we have made it simple so that you can create a new theme. All data are .json files and you can see them in <code>/storage/app/public/data/</code>. When you complete the theme, you must create the package with the following structure. You can upload your theme to zucandu.com site and earn money.</p>
<div>_theme_name</div>
<div>____assets (your images)</div>
<div>____templates</div>
<div>_______storefront (where you work on)</div>
<div>__________plugins</div>
<div>__________scss</div>
<div>__________templates</div>
<div>__________...(Vue component .vue)</div>

## Setup Local Server and Install the Theme Builder

<p>Download the package and extract the package on your destination folder.</p>

<ol>
    <li><code>npm install</code></li>
    <li><code>php composer.phar update --no-scripts</code></li>
    <li>Change <code>.env.example</code> to <code>.env</code></li>    
    <li><code>php artisan storage:link</code></li>
    <li><code>php artisan key:generate</code></li>
    <li><a href="https://drive.google.com/file/d/1sSg-VmwHWjkf9QUCcEem2ApNMgABh3kB/view?usp=sharing" target="_blank">Download the data.zip</a> and extract it in <code>/storage/app/public</code>. This zip file includes .json data and images for the example theme.</li>
    <li>Open 2 CLI and run: <code>npm run watch</code> and <code>php artisan serve</code></li>
    <li>Open: <a href="http://localhost:8000" target="_blank">http://localhost:8000</a> and enjoy!</li>
</ol>

## How to Work

<p>All of theme files will be located at <code>/resources/js/components/themes/default/</code>. When you complete, you can pack your theme with above structure and upload your theme to https://zucandu.com</p>

## Some notes:

<p>- This package comes with everything needed to create a new Zucandu theme. You just need to know HTML CSS or SCSS. Kinda easy! Also, if you want to learn about Vuejs, this is a good package to start with.</p>
<p>- When you customize the layout on the product listing, the filter feature will not apply when you selected the filter option because all of data is temporary from <code>/storage/app/public/data/</code> however it should work when the URL looks like this:</p>

<p><code>http://localhost:8000/category/appliances?flt=m:2|m:3|a:1-2|r:4|p:100-120</code></p>

<p>- You can easy to change your data from <code>/storage/app/public/data/</code>. Keep all of fields and only change the data if it's necessary.</p>
