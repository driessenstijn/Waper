<?php
/**
 * Waper
 * 
 * Waper returns the image found to be the logo of a certain website
 * 
 * @author Stijn Driessen <driessen.stijn@gmail.com>
 * @license MIT
 * 
 */

namespace Waper;

/**
 * Class Waper
 * 
 * This class returns the logo of the website that you provide inside the construct
 * 
 * @category HourPendulum
 * @package HourPendulum\Waper
 */

class Waper
{
    /*
    * website to be used
    */
    private $website;
	
    /*
    * @param $url url of the website we need to fetch data from
    */
    public function __construct($website) 
    {
        $this->website = $website;
    }
	
    /*
    * @return $website
    */
    public function getWebsite() 
    {
        return $this->website;
    }

    /*
     * @param $url url to get the domain from
     * @return $domain
    */
    public function getDomain($url)       
    {
        preg_match("/^(?:https?:\/\/)?(?:[^@\n]+@)?(?:www\.)?([^:\/\n]+)/im", $url, $result);
        return $result[0];
    }

    /*
    * @param $url url of the website to fetch data from
    * @return $array mixed result
    */
    public function fetchContent($url) 
    {
        $result = array();
        // We need to get the content of the provided url
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; .NET CLR 1.1.4322)');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result['data'] = curl_exec($ch);
        $result['httpCode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result['url'] = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        // we must check the httpCode if we received the content in a valid way
        if ($result['httpCode'] < 200 || $result['httpCode'] >= 300) {
            return false;
        }		

        return $result;
    }

    /*
    * @param $url url of the website
    * @param $file filename of the file
    */
    public function getFullUrl ($url, $file) 
    {
        if(substr($file, 0, 4) == 'http') {
                return $file;
        }
        if (substr($file, 0, 2) == '//') {
                return substr($file, 2);
        }
        $domain = $this->getDomain($url);
        if(substr($file, 0, 1) == '/') {
                return $domain.$file;
        }
        return $domain.'/'.$file;
    }

    /*
     * @param $url url of the website to be fetched for any logo
     * @return $string logoUrl
     */
    public function fetch() 
    {

        $data = $this->fetchContent($this->getWebsite());

        // putting html to dom elements to ensure to get all images
        // Please note that preg match is not working to find all images due to variety of HTML that can be obtained
        $dom = new \DOMDocument();
        @$dom->loadHTML($data['data']);

        // we need to loop through all images until we find one with name logo
        foreach ($dom->getElementsByTagName('img') as $image) {
            if (preg_match('/.*(logo).*/', $image->getAttribute('src'), $result)) {
                // means we have found a logo item in the images
                return $this->getFullUrl($data['url'], $result[0]);
            }
        }

        // if we get here, we didn't find an image and need to take it from CSS
        foreach ($dom->getElementsByTagName('link') as $css) {
            if ('stylesheet' == strtolower($css->getAttribute('rel')) ||
                    preg_match('/\.css/', strtolower($css->getAttribute('href')))) 
            {
                $fileName = $this->getFullUrl($data['url'], $css->getAttribute('href'));
                $content = $this->fetchContent($fileName);		
                if (preg_match_all('/(https?)+\S+logo+.*?(jpe?g|gif|png)/', $content['data'], $results)) {
                    return $this->getFullUrl($data['url'], $results[0][0]);
                }
            }
        }

        // if we still haven't found our logo, we must use the almighty Google
        $searchKeywords = $this->getwebsite().'+logo';
        $data = $this->fetchContent('https://www.google.com/search?q='.$searchKeywords.'&tbm=isch');
        $dom = new \DomDocument();
        @$dom->loadHTML($data['data']);
        foreach ($dom->getElementsByTagName('img') as $image) {
                        return $image->getAttribute('src');
        }
        // if we really found nothing
        return false;
    }
}