<?php
/**
 * Price Parrot PHP API
 * https://priceparrot.io
 * 
 * PHP Version 8.5.
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

use JsonException;
use RuntimeException;
use InvalidArgumentException;

final class PriceParrotClient { 
    private string $endpoint_url =      'https://api.parrotprice.com/';
    
    public function __construct(
        private readonly string $endpoint_apikey,
        private readonly string $endpoint_secret,
    ) {}


    public function setEndpoint(string $url) : void {
        $url = trim($url);
        if ($url === '') {
            throw new InvalidArgumentException('Endpoint URL cannot be empty.');
        }

        $this->endpoint_url = rtrim($url, '/') . '/';
    }

    public function Call(string $url, ?array $post=null, string $method='GET'){
        //Combine API key and secret
        $auth = $this->endpoint_apikey . ' ' . $this->endpoint_secret;
        
        //Set options for endpoint call
        $options = ['headers' => ['Authorization' => $auth]];

        if(!empty($post)){
            $options['post'] => $post;
            if($method != 'PUT' && $method != 'POST' && $method != 'DELETE'){
                $method = 'POST';
            }
        }
        
        if($method != 'GET'){
            $options['method'] = $method;
        }
        
        //Fetch respons
        try {
            $raw = PriceParrotConnect::FetchURL($this->endpoint_url . $url, $options);
        } catch (\Exception $e) {
            throw new RuntimeException('Request failed: ' . $e->getMessage(), 0, $e);
        }
        
        //Return JSON or throw exception
        try {
            return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $preview = is_string($raw) ? substr($raw, 0, 500) : '';
            throw new RuntimeException('Invalid JSON response. First 500 chars: ' . $preview, 0, $e);
        }
    }
    
    /* Product */
    
    /* Fetch a single product with your unique Product ID */
    public function FetchProduct(string $productid){
        return $this->Call('product/'.$productid, [], 'GET');
    }
    
    /* Update a single product with your unique SKU
     * Parameters:
     *  data: [
     *      name <string>
     *      sku <string, optional>
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
    public function UpdateProduct(array $data, ?string $productid=null){
        return $this->Call('product/'.(!empty($productid) ? $productid : 'new'), $data, 'PUT');
    }
    
    /* Update a single product with your unique SKU
     * Parameters:
     *  data: [[
     *      productid <string>
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
    public function DeleteProduct(string $productid){
        return $this->Call('product/'.$productid, [], 'DELETE');
    }
    
    /* End Product */
    
    
    /* Competitor's */
    
    /* Add or edit a competitor */
    public function AddCompetitor(string $domain, array $post=[]){
        return $this->Call('competitor/'.urlencode($domain), $post, 'PUT');
    }
    
    /* Delete a competitor */
    public function RemoveCompetitor(string $domain){
        return $this->Call('competitor/'.urlencode($domain), [], 'DELETE');
    }

    public function PauseCompetitor(string $domain, bool $toggle){
        return $this->Call('competitor/'.urlencode($domain), ['pause' => $toggle], 'POST');
    }
    
    /* End competitor's */
    
    
    
    /* Product competitor URL's */
    
    /* Fetch all competitor URL's with your unique SKU */
    public function FetchProductMatches(string $productid){
        return $this->Call('productmatch/'.$productid, [], 'GET');
    }
    
    /* Add a new competitor URL with your unique SKU and the URL of the competitor */
    public function AddProductMatch(string $productid,  string $url, ?array $settings=null){
        return $this->Call('productmatch/'.$productid, ['url' => $url, 'settings' => $settings], 'PUT');
    }
    
    /* Delete a competitor URL with your unique SKU and the URL or product-ID of the competitor */
    public function DeleteProductMatch(string $productid, string $match){
        return $this->Call('productmatch/'.$productid, ['url' => $match], 'DELETE');
    }
    
    /* Report a competitor URL with your unique SKU */
    public function ReportProductMatch(string $productid, string $url, int $reason, string $comment){
        return $this->Call('productmatch/report/'.$productid, ['url' => $url, 'reason' => $reason, 'comment' => $comment], 'PUT');
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


    /* Alerts */

    public function DeleteAlert(string $ruleid){
        return $this->Call('alert/'.$ruleid, [], 'DELETE');
    }

    public function UpdateAlert(string $ruleid, array $data){
        return $this->Call('alert/'.$ruleid, $data, 'PUT');
    }

    /* End Alerts */

    /* Search Discovery */

    public function StartSearchDiscovery(string $productid, array $data=[]){
        return $this->Call('searchdiscovery/'.$productid, $data, 'POST');
    }

    public function FetchSearchDiscovery(string $productid){
        return $this->Call('searchdiscovery/'.$productid, [], 'GET');
    }

    public function SearchDiscoveryAdd(string $productid, array $data=[]){
        return $this->Call('searchdiscovery/'.$productid, $data, 'PUT');
    }

    public function SearchDiscoveryRemove(string $productid, array $data=[]){
        return $this->Call('searchdiscovery/'.$productid, $data, 'DELETE');
    }

    /* End Search Discovery */


    /* Summaries */
    
    public function FetchProductSummary(){
        return $this->Call('summary');
    }
    
    /* End Summaries */
    
    
    /* Products Lists */
    
    public function FetchAllProducts(int $offset=0, int $amount=20){
        return $this->Call('products/list/'.$offset.'/'.$amount);
    }
    
    public function SearchAllProducts(string $search, int $offset=0, int $amount=20){
        return $this->Call('products/search/'.urlencode($search).'/'.$offset.'/'.$amount);
    }
    
    /* End Products Lists */
}
?>
