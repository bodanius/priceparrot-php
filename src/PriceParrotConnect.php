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

class PriceParrotConnect{	
    public static function FetchURL(string $url, array $options = []){
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Price Parrot Github API');
        curl_setopt($ch, CURLOPT_ENCODING, '');

        //Send POST data
        if(!empty($options['post'])){
            $data_string = json_encode($options['post'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);  
            
            $options['headers']['Content-Type'] = 'application/json';
            $options['headers']['Content-Length'] = strlen($data_string);
                                                 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        }
        
        //Add headers
        if(!empty($options['headers'])){
            $headerarray = [];
            foreach($options['headers'] as $key => $val){
                $headerarray[] = $key . ': ' . $val;
            }
            
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerarray);
        }
        
        //Set method
        if(isset($options['method'])){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['method']); 
        }

        //Fetch data
        return curl_exec($ch);
    }
}
?>
