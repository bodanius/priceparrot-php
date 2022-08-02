<?php
/**
 * Price Parrot PHP API
 * https://priceparrot.io
 * 
 * PHP Version 7.1.
 *
 * @see       https://github.com/priceparrot/priceparrot-php
 *
 * @author    Price Parrot <support@priceparrot.io>
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note      This program is distributed in the hope that it will be useful - WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.
 */


namespace PriceParrot;

class PriceParrotClient { 
    private string $endpoint_url =      'https://api.parrotprice.com/';
    private string $endpoint_apikey =   '';
    private string $endpoint_secret =   '';
    
    function __construct(string $apikey, string $secret) {
        $this->endpoint_apikey = $apikey;
        $this->endpoint_secret = $secret;
    }
    
    private function Call(string $url, ?array $post=null, string $method='GET'){
        //Combine API key and secret
        $auth = $this->endpoint_apikey . ' ' . $this->endpoint_secret;
        
        //Set options for endpoint call
        $options = ['post' => $post, 'headers' => ['Authorization' => $auth]];
        if($method != 'GET'){
            $options['method'] = $method;
        }
        
        //Fetch response
        $req = PriceParrotConnect::FetchURL($this->endpoint_url . $url, $options);
        
        //Return JSON or throw exception
        return json_decode($req, true, 512, JSON_THROW_ON_ERROR);
    }
    
    /* Product */
    
    /* Fetch a single product with your unique SKU */
    public function FetchProduct(string $sku){
        return $this->Call('product/'.$sku, [], 'GET');
    }
    
    /* Update a single product with your unique SKU
     * Parameters:
     *  data: [
     *      name <string>
     *      sku <string, optional>, set to update the current items SKU
     *      brand <string, optional>
     *      tag <string, optional>
     *      url <string, optional>
     *      mpn <string, optional>
     *      ean <string, optional>
     *      costs <decimal, optional>
     *      price <decimal, optional>
     *      instock <boolean, optional>
     *      maxprice <decimal, optional>
     *      active <boolean, optional>
     *      matches <array, optional>
     * ]
     * sku: <string>, empty for new items
    */
    public function UpdateProduct(array $data, ?string $sku=null){
        return $this->Call('product/'.($sku != null && strlen($sku) > 0 ? $sku : 'new'), $data, 'PUT');
    }
    
    /* Update a single product with your unique SKU
     * Parameters:
     *  data: [[
     *      name <string>
     *      sku <string>
     *      brand <string, optional>
     *      tag <string, optional>
     *      url <string, optional>
     *      mpn <string, optional>
     *      ean <string, optional>
     *      costs <decimal, optional>
     *      price <decimal, optional>
     *      instock <boolean, optional>
     *      maxprice <decimal, optional>
     *      active <boolean, optional>
     *      matches <array, optional>
     * ]]
    */
    public function ImportProducts(array $data){
        return $this->Call('products/import', $data, 'PUT');
    }
    
    /* Delete a single product and all its matches with your unique SKU */
    public function DeleteProduct(string $sku){
        return $this->Call('product/'.$sku, [], 'DELETE');
    }
    
    /* End Product */
    
    
    /* Competitor's */
    
    /* Add or edit a competitor */
    public function AddCompetitor(string $domain, array $post=[]){
        return $this->Call('competitor/'.$domain, $post, 'PUT');
    }
    
    /* Delete a competitor */
    public function RemoveCompetitor(string $domain){
        return $this->Call('competitor/'.$domain, [], 'DELETE');
    }
    
    /* End competitor's */
    
    
    
    /* Product competitor URL's */
    
    /* Fetch all competitor URL's with your unique SKU */
    public function FetchProductMatches(string $sku){
        return $this->Call('productmatch/'.$sku, [], 'GET');
    }
    
    /* Add a new competitor URL with your unique SKU and the URL of the competitor */
    public function AddProductMatch(string $sku,  string $url){
        return $this->Call('productmatch/'.$sku, ['url' => $url], 'PUT');
    }
    
    /* Delete a competitor URL with your unique SKU and the URL or product-ID of the competitor */
    public function DeleteProductMatch(string $sku, string $match){
        return $this->Call('productmatch/'.$sku, ['url' => $match], 'DELETE');
    }
    
    /* Report a competitor URL with your unique SKU */
    public function ReportProductMatch(string $sku, string $url, int $reason, string $comment){
        return $this->Call('productmatch/report/'.$sku, ['url' => $url, 'reason' => $reason, 'comment' => $comment], 'PUT');
    }
    
    /* Search for a known product match (alpha feature) */
    public function SearchProduct($search){
        return $this->Call('productmatch/search', ['search' => $search]);
    }
    
    /* End Product competitor URL's */
    
    
    /* Settings */
    
    /* Fetch all store settings */
    public function FetchSettings(){
        return $this->Call('settings');
    }
    
    /* Update store settings 
     * data: [
     *     name <string, optional>
     *     website <string, optional>
     *     website_sync <boolean, optional>
     *     website_syncadd <boolean, optional>
     *     currency <string, optional>
     *     country <string, optional>
     *     convert_currency <boolean, optional>
     *     convert_inclvat <boolean, optional>
     *     liveupdate_enabled <boolean, optional>
     *     liveupdate_webhook <string, optional>
     *     liveupdate_interval <int, optional>
     * ]
    */
    public function UpdateSettings(array $data){
        return $this->Call('settings', $data, 'PUT');
    }
    
    /* End Settings */
    
    
    /* Dynamic pricing rules */
    
    public function DeleteDynamicRule(string $ruleid){
        return $this->Call('rule/'.$ruleid, [], 'DELETE');
    }
    
    public function UpdateDynamicRule(string $ruleid, array $data){
        return $this->Call('rule/'.$ruleid, $data, 'PUT');
    }
    
    public function UpdateDynamicRules(array $rules){
        return $this->Call('rules', ['rules' => $rules], 'PUT');
    }
    
    /* End Dynamic pricing rules */
    
    
    /* Summaries */
    
    public function FetchProductSummary(){
        return $this->Call('summary');
    }
    
    /* End Summaries */
    
    
    /* Products Lists */
    
    public function FetchAllProducts(int $offset=0, int $amount=20){
        return $this->Call('/products/list/'.$offset.'/'.$amount);
    }
    
    public function SearchAllProducts(string $search, int $offset=0, int $amount=20){
        return $this->Call('/products/search/'.urlencode($search).'/'.$offset.'/'.$amount);
    }
    
    /* End Products Lists */
}
?>