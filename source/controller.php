<?php

/*
    Rest API - Bittrex Simulator
*/

use \Jacwright\RestServer\RestException;
use \MongoDB\Client;
class Controllers
{

    /**
     * @url GET /api/v1.1/account/getbalance
     */
    public function getAccountBalance($query) {
        $collection = (new MongoDB\Client)->bittrex->balance;
        
        $tickerQuery = ['query' => $query['currency']]; 
        $doc = $collection->findOne($tickerQuery);

        $retObject = new stdClass;
        $retObject->success = true;
        $retObject->message = "";
        $retObject->result = new stdClass;

        if($doc == null) {

        }
        else {
            $retObject->result->Currency = $collection->Currency;
            $retObject->result->Balance = $collection->Balance;
            $retObject->result->Available = $collection->Available;
            $retObject->result->Pending = $collection->Pending;
            $retObject->result->CryptoAddress = $collection->CryptoAddress;
        }

        return $retObject;
    }

    /**
     * @url GET /api/v1.1/account/setbalance
     */
    public function setCurrencyBalance($currency, $amount) {
        $collection = (new MongoDB\Client)->bittrex->balance;

        $collection->drop(array('Currency' => $currency));
        $balance = new stdClass;
        $balance->Currency = $currency;
        $balance->Balance = $amount;
        $balance->Available = $amount;
        $balance->Pending = 0;
        $balance->CryptoAddress = "123-123-123-123-123";
        $collection->insertOne($balance);
    }

    /**
     * @url GET /api/v1.1/public/getticker
     */
    public function getTicker($query) {
        $collection = (new MongoDB\Client)->bittrex->ticker;

        $maxRecordCount = 3045857;
        $idx = (time() % $maxRecordCount);
        
        $tickerQuery = ['idx' => $idx];
        $doc = $collection->findOne($tickerQuery);

        $retObject = new stdClass;
        $retObject->success = true;
        $retObject->message = "";
        $retObject->result = new stdClass;
        $amount = $doc->close;
        $rndBidAmountLess = $this->float_rand(0.2, 1.0, 2);

        $retObject->result->Bid = (float)$amount-(float)$rndBidAmountLess;
        $retObject->result->Ask = $amount;
        $retObject->result->Last = $amount;
        return $retObject;
    }

    /**
     * @url GET /api/v1.1/account/getorderhistory
     */
    public function getOrderHistory($query) {
        $collection = (new MongoDB\Client)->bittrex->orders;

        $retObject = new stdClass;
        $retObject->success = true;
        $retObject->message = "";

        $orderQuery  = ['Closed' => array('$ne' => null)];
        $doc = $collection->find($orderQuery);

        $retObject->result = array();
        foreach($doc as $src) {
            $item = new stdclass;
            $item->Uuid = $src['Uuid'];
            $item->OrderUuid = $src['OrderUuid'];
            $item->Exchange = $src['Exchange'];
            $item->OrderType = $src['OrderType'];
            $item->Quantity = $src['Quantity'];
            $item->QuantityRemaining = $src['QuantityRemaining'];
            $item->Limit = $src['Limit'];
            $item->CommissionPaid = $src['CommissionPaid'];
            $item->Price = $src['Price'];
            $item->PricePerUnit = $src['PricePerUnit'];
            $item->Opened = $src['Opened'];
            $item->Closed= $src['Closed'];
            $item->CancelInitiated = $src['CancelInitiated'];
            $item->ImmediateOrCancel = $src['ImmediateOrCancel'];
            $item->IsConditional = $src['IsConditional'];
            $item->Condition = $src['Condition'];
            $item->ConditionTarget = $src['ConditionTarget'];
            $retObject->result[] = $item;
        }
      
        return $retObject;
    }

    /**
     * @url GET /api/v1.1/market/getopenorders
     */
    public function getOpenOrders($query) {
        $collection = (new MongoDB\Client)->bittrex->orders;

        $retObject = new stdClass;
        $retObject->success = true;
        $retObject->message = "";

        $orderQuery  = ['Closed' => array('$eq' => null)];
        $doc = $collection->find($orderQuery);

        $retObject->result = array();
        foreach($doc as $src) {
            $item = new stdclass;
            $item->Uuid = $src['Uuid'];
            $item->OrderUuid = $src['OrderUuid'];
            $item->Exchange = $src['Exchange'];
            $item->OrderType = $src['OrderType'];
            $item->Quantity = $src['Quantity'];
            $item->QuantityRemaining = $src['QuantityRemaining'];
            $item->Limit = $src['Limit'];
            $item->CommissionPaid = $src['CommissionPaid'];
            $item->Price = $src['Price'];
            $item->PricePerUnit = $src['PricePerUnit'];
            $item->Opened = $src['Opened'];
            $item->Closed= $src['Closed'];
            $item->CancelInitiated = $src['CancelInitiated'];
            $item->ImmediateOrCancel = $src['ImmediateOrCancel'];
            $item->IsConditional = $src['IsConditional'];
            $item->Condition = $src['Condition'];
            $item->ConditionTarget = $src['ConditionTarget'];
            $retObject->result[] = $item;
        }
      
        return $retObject;
    }

    /**
     * @url GET /api/v1.1/market/buylimit
     */
    public function buyLimit($query) {
        $collection = (new MongoDB\Client)->bittrex->orders;

        $retObject = new stdClass;
        $retObject->success = true;
        $retObject->message = "";

        $item = null;
        $item['Uuid'] = null;
        $item['OrderUuid'] = $this->uuid();
        $item['Exchange'] = $query['market'];
        $item['OrderType'] = "LIMIT_BUY";
        $item['Quantity'] = (double)$query['quantity'];
        $item['QuantityRemaining'] = (double)$query['quantity'];
        $item['Limit'] = (double)$query['rate'];
        $item['CommissionPaid'] = (double)0.0;
        $item['Price'] = (double)0.00000000;
        $item['PricePerUnit'] = null;
        $item['Opened'] = gmDate("Y-m-d\TH:i:s"); 
        $item['Closed']= null;
        $item['CancelInitiated'] = false;
        $item['ImmediateOrCancel'] = false;
        $item['IsConditional'] = false;
        $item['Condition'] = "NONE";
        $item['ConditionTarget'] = null;
        
        $insertId = $collection->insertOne($item);

        $retObject->result = new stdClass;
        $retObject->result->uuid = $item['OrderUuid'];
      
        return $retObject;
    }

    /**
     * @url DELETE /internal/orders/erase
     */
    public function eraseOrders($query) {
        $collection = (new MongoDB\Client)->bittrex->orders;

        $tickerQuery = [];
        $doc = $collection->deleteMany($tickerQuery);

        $num = $doc->getDeletedCount();

        $retObject = new stdClass;
        $retObject->success = true;
        $retObject->message = "Deleted $num results";
        $retObject->result = array();
      
        return $retObject;
    }


    /**
     * Throws an error
     * 
     * @url GET /error
     */
    public function throwError() {
        throw new RestException(401, "Empty password not allowed");
    }

    function float_rand($min, $max, $round=0){
        $randomfloat = $min + mt_rand() / mt_getrandmax() * ($max - $min);
        if($round>0)
            $randomfloat = round($randomfloat,$round);
    
        return $randomfloat;
    }

    function uuid($serverID=1)
    {
        $t=explode(" ",microtime());
        return sprintf( '%04x-%08s-%08s-%04s-%04x%04x',
            $serverID,
            $this->clientIPToHex(),
            substr("00000000".dechex($t[1]),-8),   // get 8HEX of unixtime
            substr("0000".dechex(round($t[0]*65536)),-4), // get 4HEX of microtime
            mt_rand(0,0xffff), mt_rand(0,0xffff));
    }

    function clientIPToHex($ip="") {
        $hex="";
        if($ip=="") $ip=getEnv("REMOTE_ADDR");
        $part=explode('.', $ip);
        for ($i=0; $i<=count($part)-1; $i++) {
            $hex.=substr("0".dechex($part[$i]),-2);
        }
        return $hex;
    }
}
