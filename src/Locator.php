<?php

namespace MultiServicePack;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class Locator
{
    public const BASE_URL = 'http://ip-api.com';
    public const JSON_URI_URL = '/json';
    protected $client;
    protected $info;
    protected $locale;
    protected $currency;
    protected $language;

    public function __construct($ipAddress)
    {
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
            'timeout'  => 30.0,
            'verify'   => false, // Disabling SSL verification
        ]);

        $this->setData($ipAddress);
    }

    private function setData($ipAddress)
    {
        $this->locateIp($ipAddress);

        $data = $this->info;
        $countryCode = $data['code'];
        $this->setLocale($countryCode);
        $this->setLanguage($countryCode);
        $this->setCurrency($countryCode);

    }

    public function getInfo()
    {
        return $this->info;
    }

    public function getLocale()
    {
        return $this->locale;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getLanguage()
    {
        return $this->language;
    }

    public function getAll()
    {
        $data = (object) [
            'country' => $this->info,
            'locales' => $this->locale,
            'languages' => $this->language,
            'currency' => $this->currency
        ];

        return $data;
    }

    private function getCountry() : array
    {
        // Path to the JSON file
        $jsonFilePath = __DIR__ . '/data/countries.json';

        // Get the JSON content
        $jsonContent = file_get_contents($jsonFilePath);

        // Decode the JSON content into an associative array
        $countryData = json_decode($jsonContent, true);

        return $countryData;

    }
    
    /**
     * Converts a specified amount from one currency to another.
     *
     * @param string $ipAddress IP Address to locate the location. 
     * 
     * Example usage:
     * $ipAddress = "8.8.8.8";
     *
     */
    private function locateIp($ipAddress)
    {
        try {

            $uriurl = self::JSON_URI_URL . "/" . $ipAddress;

            // Send a GET request
            $response = $this->client->request('GET', $uriurl , [
                'headers' => [
                    'Accept' => 'application/json',
                ],
            ]);
        
            // Get the response body
            $body = $response->getBody();
            $data = json_decode($body, true); // Decode the JSON response
        
            // Check if the conversion was successful
            if (isset($data) && $data['status'] == 'success') {

                $country = $data['country'];
                $countryCode = $data['countryCode'];
 
                $this->info = [
                    'name'=> $country,
                    'code'=> $countryCode
                ];
                
            } else {
                return [
                    'error' => 'Error: IP Api result not found in response.'
                ];
            }
        
        } catch (RequestException $e) {
            return [
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Converts a specified amount from one currency to another.
     *
     * @param string $countryCode Pass code country for getting locale info. 
     * 
     * Example usage:
     * $countryCode = "MYS";
     *
     */
    private function setLocale($countryCode)
    {
        try {
            
            $countries = $this->getCountry();
            $locales = null;

            foreach ($countries as $country) {
                if ($country['alpha3'] === $countryCode || $country['alpha2'] === $countryCode) {
                    $locales = $country['locales'];
                }
            }

            $this->locale = $locales;


        } catch (\Throwable $e) {
            return [
                'error' => $e->getMessage()
            ];
        }

    }

    /**
     * Converts a specified amount from one currency to another.
     *
     * @param string $countryCode Pass code country for getting currency info. 
     * 
     * Example usage:
     * $countryCode = "MYS";
     *
     */
    private function setCurrency($countryCode)
    {
        try {
            
            $countries = $this->getCountry();
            $currency = null;

            foreach ($countries as $country) {
                if ($country['alpha3'] === $countryCode || $country['alpha2'] === $countryCode) {
                    $currency = [
                        'name' => $country['currency_name'],
                        'code' => $country['currency']
                    ];
                }
            }

            $this->currency = $currency;


        } catch (\Throwable $e) {
            return [
                'error' => $e->getMessage()
            ];
        }

    }

    /**
     * Converts a specified amount from one currency to another.
     *
     * @param string $countryCode Pass code country for getting language info. 
     * 
     * Example usage:
     * $countryCode = "MYS";
     *
     */
    private function setLanguage($countryCode)
    {
        try {
            
            $countries = $this->getCountry();
            $languages = null;

            foreach ($countries as $country) {
                if ($country['alpha3'] === $countryCode || $country['alpha2'] === $countryCode) {
                    $languages = $country['languages'];
                }
            }

            $this->language = $languages;


        } catch (\Throwable $e) {
            return [
                'error' => $e->getMessage()
            ];
        }

    }

}
