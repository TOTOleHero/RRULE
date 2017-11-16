<?php

class Locale
{
   static function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE)
   {
      $output = NULL;
      if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
         $ip = $_SERVER["REMOTE_ADDR"];
         if ($deep_detect) {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
               $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (isset($_SERVER['HTTP_CLIENT_IP']) && filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
               $ip = $_SERVER['HTTP_CLIENT_IP'];
         }
      }
      $purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
      $support    = array("country", "countrycode", "state", "region", "city", "location", "address");
      $continents = array(
         "AF" => "Africa",
         "AN" => "Antarctica",
         "AS" => "Asia",
         "EU" => "Europe",
         "OC" => "Australia (Oceania)",
         "NA" => "North America",
         "SA" => "South America"
      );
      if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
         $ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
         if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
            switch ($purpose) {
               case "location":
                  $output = array(
                     "city"           => @$ipdat->geoplugin_city,
                     "state"          => @$ipdat->geoplugin_regionName,
                     "country"        => @$ipdat->geoplugin_countryName,
                     "country_code"   => @$ipdat->geoplugin_countryCode,
                     "continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
                     "continent_code" => @$ipdat->geoplugin_continentCode
                  );
                  break;
               case "address":
                  $address = array($ipdat->geoplugin_countryName);
                  if (@strlen($ipdat->geoplugin_regionName) >= 1)
                     $address[] = $ipdat->geoplugin_regionName;
                  if (@strlen($ipdat->geoplugin_city) >= 1)
                     $address[] = $ipdat->geoplugin_city;
                  $output = implode(", ", array_reverse($address));
                  break;
               case "city":
                  $output = @$ipdat->geoplugin_city;
                  break;
               case "state":
                  $output = @$ipdat->geoplugin_regionName;
                  break;
               case "region":
                  $output = @$ipdat->geoplugin_regionName;
                  break;
               case "country":
                  $output = @$ipdat->geoplugin_countryName;
                  break;
               case "countrycode":
                  $output = @$ipdat->geoplugin_countryCode;
                  break;
            }
         }
      }
      return $output;
   }

   // returns: India
   static function GetCountry()
   {
      return Locale::ip_info("Visitor", "Country");
   }
   
   // returns: IN
   static function GetCountryCode()
   {
      return Locale::ip_info("Visitor", "Country Code");
   }
   
   // returns: Andhra Pradesh
   static function GetProvinceOrState()
   {
      return Locale::ip_info("Visitor", "State");
   }
   
   // returns: Proddatur
   static function GetCity()
   {
      return Locale::ip_info("Visitor", "City");
   }
   
   // returns: Proddatur, Andhra Pradesh, India
   static function GetAddress()
   {
      return Locale::ip_info("Visitor", "Address");
   }
   
   // returns: Array ( [city] => Proddatur [state] => Andhra Pradesh [country] => India [country_code] => IN [continent] => Asia [continent_code] => AS )
   static function GetLocation()
   {
      return Locale::ip_info("Visitor", "Location");
   }
};

?>
