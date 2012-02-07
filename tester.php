<?php

// Simple but ghetto test driver for exchangerator class
// for WMF Coding Project
// By Duane O'Brien

include "exchangerator.php";

$excr = new Exchangerator();

if ($excr->getOneRate('JPY') != '10.013125') {
    echo '<pre> FAIL on Init - Unexpected JPY rate : 10.0131 expect, ' . $excr->getOneRate('JPY') . '</pre><br />';
} else {
    echo '<pre> pass on Init - JPY rate : ' . $excr->getOneRate('JPY') . '</pre><br />';    
}

$excr->updateRates();

if ($excr->getOneRate('JPY') != '0.013125') {
    echo '<pre> FAIL First Load - Unexpected JPY rate : 0.013125 expect, ' . $excr->getOneRate('JPY') . '</pre><br />';
} else {
    echo '<pre> pass on first load - JPY rate : ' . $excr->getOneRate('JPY') . '</pre><br />';    
}

$excr->setRateURL('http://duaneobrien.com/work/resume/simple_currency.xml');

$excr->updateRates();

if ($excr->getOneRate('JPY') != '10') {
    echo '<pre> FAIL Second Load - Unexpected JPY rate : 10.0131 expect, ' . $excr->getOneRate('JPY') . '</pre><br />';
} else {
    echo '<pre> pass on second load - JPY rate : ' . $excr->getOneRate('JPY') . '</pre><br />';    
}

if ($excr->convert("JPY 73") != "USD 730") {
    echo '<pre> FAIL CONVERT - Unexpected rate : USD 730 expect, ' . $excr->convert('JPY 73') . '</pre><br />';
} else {
    echo '<pre> pass convert - JPY rate 73 : ' . $excr->convert('JPY 73') . '</pre><br />';    
}

if ($excr->convert("JAX 73") != "JAX 73") {
    echo '<pre> FAIL CONVERT - Unexpected rate : JAX 73 expect, ' . $excr->convert("JAX 73") . '</pre><br />';
} else {
    echo '<pre> pass convert - JAX rate 73 : ' . $excr->convert('JAX 73') . '</pre><br />';    
}

if ($excr->convert(array("JPY 73", "bgn 12", "czK 64")) != array("USD 730", "USD 60", "USD 128")) {
    echo '<pre> FAIL CONVERT - Unexpected rate : "USD 730", "BGN 60", "CZK 128" expect, ' . join(", ", $excr->convert(array("JPY 73", "bgn 12", "czK 64"))) . '</pre><br />';
} else {
    echo '<pre> pass convert - "JPY 73.024", "bgn .012", "czK 6415.23", "USD 1000", "KRN 15.6" to - ' . join(", ", $excr->convert(array("JPY 73.024", "bgn .012", "czK 6415.23", "USD 1000", "KRN 15.6"))) . '</pre><br />';
}

if ($excr->getOneRate("FIBBLE") != "0") {
    echo '<pre> FAIL GET RATE - Unexpected rate : 0 expect, ' . $excr->getOneRate("FIBBLE") . '</pre><br />';
} else {
    echo '<pre> pass get rate - FIBBLE rate : ' . $excr->getOneRate("FIBBLE") . '</pre><br />';    
}

if ($excr->getOneRate("JPY") != "10") {
    echo '<pre> FAIL GET RATE - Unexpected rate : 10 expect, ' . $excr->getOneRate("JPY") . '</pre><br />';
} else {
    echo '<pre> pass get rate - JPY rate : ' . $excr->getOneRate("JPY") . '</pre><br />';    
}

if (!$excr->setConvertTo("USD")) {
    echo '<pre> FAIL SETTING CONVERT TO USD</pre><br />';
} else {
    echo '<pre> pass set convert to USD</pre><br />';    
}

if ($excr->setConvertTo("KPL")) {
    echo '<pre> FAIL SET CONVERT TO KPL</pre><br />';
} else {
    echo '<pre> pass would not set convert to KPL</pre><br />';    
}

$excr->setRateURL('http://duaneobrien.com/work/resume/big_currency.xml');

$excr->updateRates();

if ($excr->getOneRate('JPY') != '10.013125') {
    echo '<pre> FAIL Third Load - Unexpected JPY rate : 10.0131 expect, ' . $excr->getOneRate('JPY') . '</pre><br />';
} else {
    echo '<pre> pass on third load - JPY rate : ' . $excr->getOneRate('JPY') . '</pre><br />';    
}

if (!$excr->getRateURL()) {
    echo '<pre> FAIL GETTING RATE URL : ' . $excr->getRateURL() . '</pre><br />';
} else {
    echo '<pre> pass on get rate url : ' . $excr->getRateURL() . '</pre><br />';    
}

echo '<pre> rate table dump: ' . print_r($excr->dumpRateTable(), true) . '</pre><br />';    

class ExchangeratorTest extends Exchangerator {
    function addBogusRate($currency, $rate) {
        $this->setOneRate($currency, $rate);
    }
}

$excrt = new ExchangeratorTest();

$before = $excrt->dumpRateTable();

$excrt->addBogusRate('XKCD', '12');

$after = $excrt->dumpRateTable();

if ($before == $after) {
    echo '<pre> FAILED ADDING A RATE : ' . print_r($excrt->dumpRateTable(), true) . '</pre><br />';        
} else {
    echo '<pre> pass adding a rate : ' . print_r($excrt->dumpRateTable(), true) . '</pre><br />';    
}

$excrt->reloadRates();

$end = $excrt->dumpRateTable();


if ($before != $end) {
    echo '<pre> FAILED RELOADING RATES: ' . print_r($excrt->dumpRateTable(), true) . '</pre><br />';            
} else {
    echo '<pre> pass reloading rates: ' . print_r($excrt->dumpRateTable(), true) . '</pre><br />';            
}

?>