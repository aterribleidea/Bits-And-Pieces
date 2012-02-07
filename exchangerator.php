<?php

// Basic currency conversion class per spec
// for WMF Coding Project
// By Duane O'Brien

class Exchangerator {
    // DB vars
    private $dbstr = 'mysql:host=serve.mammon.org;dbname=cake13series';
    private $dbun = 'cake13un';
    private $dbpw = 'cake13pw';
    private $dbh;

    // RATE vars
    private $rateURL = 'http://duaneobrien.com/work/resume/currency.xml';

    // CLASS vars
    private $convertTo = 'USD';
    private $rates = array();
    
    function __construct() {
        try {
            // Open a DB connection, save it to the class
            $this->dbh = new PDO($this->dbstr, $this->dbun, $this->dbpw);
            
            // call a method to pull the rates back out and into the class
            $this->reloadRates();
            
        } catch (Exception $e) {
            // Do something graceful with this error
            // Dying isn't graceful, but let's do ie.
            die("Could not open a database connection : " . $e->getMessage());
        }
        return;
    }
    
    function setRateURL ($url) {
        // Call this with a URL to change where the rates are coming from
        // Needs data validation
        $this->rateURL = $url;
        return;
    }

    function getRateURL () {
        // Return the current Rate URL
        return $this->rateURL;
    }

    function updateRates () {
        // Go out to the Rate URL, get the rates, push them into the DB and update this class
        try {
            $xml = file_get_contents($this->rateURL);
            $rates = new SimpleXMLIterator($xml);
        } catch (Exception $e) {
            // More ungraceful dying, but we shouldn't continue here
            die("Error getting or parsing rates : " . $e->getMessage());
        }

        $sql = $this->dbh->prepare("INSERT INTO exchange_rates VALUES (:currency, :rate) ON DUPLICATE KEY UPDATE rate=:rate");
        
        foreach($rates as $conversion) {
            $sql->bindParam(':currency', $conversion->currency);
            $sql->bindParam(':rate', $conversion->rate);
            if ($sql->execute()) {
                // Update the rate in the class
                $this->setOneRate($conversion->currency, $conversion->rate);
            } else {
                // Raise some error here if it doesn't go in smoothly
                // Probably silent or logged elsewhere
            }
        }
        
        // May want to return a count of updated rows
        return;
    }
    
    function reloadRates () {
        // Throw away the rates we have (if any) and get fresh ones from the DB
        $this->rates = array();
        $sql = $this->dbh->prepare("SELECT currency, rate FROM exchange_rates WHERE 1");
        $sql->execute();
        while ($row = $sql->fetch()) {
            $this->setOneRate($row['currency'], $row['rate']);
        }
    }
    
    protected function setOneRate ($currency, $rate) {
        // Take a code and a rate, and set them in the class
        // This needs some data validation
        $this->rates[(string) strtoupper($currency)] = (string) $rate;
        return;
    }

    function getOneRate ($currency) {
        // Return the conversion rate for one currency code
        // Return 0 for unknown currencies
        if ($this->rates[strtoupper($currency)]) {
            return $this->rates[strtoupper($currency)];            
        } else {
            return 0;
        }
    }
    
    function dumpRateTable () {
        // So you can see all the rates the class has, if you want.
        return $this->rates;
    }
    
    function setConvertTo ($code) {
        // Use this to change what you're converting to
        // This assumes that you're getting rates that contain an entry for the
        // Currency you're converting to, which may not be valid.  But it's a
        // start.
        $code = strtoupper($code);
        if ($this->rates[$code]) {
            $this->convertTo = $code;
            return true;
        } else {
            return false;
        }
    }
    
    function convert ($conversions) {
        // Pass in a string or an array of strings for conversion
        // returns non-strings and non-arrays unchanged
        if (is_string($conversions)) {
            return $this->handleOneConversion($conversions);
        } else if (is_array($conversions)) {
            $results = array();
            foreach ($conversions as $oneconversion) {
                $results[] = $this->handleOneConversion($oneconversion);
            }
            return $results;
        } else {
            // Just send it back for now
            return $conversions;
        }
    }
    
    protected function handleOneConversion ($conversion) {
        // Perform a single currency conversion
        list($currency, $amount) = split(" ", $conversion);
        $currency = strtoupper($currency);
        if ($this->rates[$currency] && is_numeric($amount) ) {
            // Go ahead and convert it
            return $this->convertTo . " " . ($amount * (float) $this->rates[$currency]);
        } else {
            // Return it as is
            // This might be the wrong thing to do, especially $this->getOneRate returns 0
            // Those seem inconsistent
            return $conversion;
        }
    }
}
