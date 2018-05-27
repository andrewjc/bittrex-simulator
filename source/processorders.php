<?php
/*
    Checks for outstanding orders in the queue

    Processes:
        - Is the order value likely to sell?
            - eg above or below the last price or current bid?

        - Subtract cost from user's current balance
        - Set closed date on order
*/

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/controller.php';
use \MongoDB\Client;

$shutdown = false;

echo "Starting order bus...\n";
$orderBus = new OrderBus();
$orderBus->run();

echo "Shutting down...";


class OrderBus 
{
    public function __construct()
    {
        $this->shutdown = false;
        $this->bittrex = new Controllers();
    }
    
    /**
     * The thread's run() method that runs in parallel.
     * 
     * @link http://www.php.net/manual/en/thread.run.php
     */
    public function run()
    {

        while(!$this->shutdown) {

            $curPrice = $this->bittrex->getCurrentTicker();

            $this->checkBuyOrders($curPrice);
            $this->checkSellOrders($curPrice);


            sleep(0.5);
        }
    }        

    private function checkBuyOrders($curPrice) {
        $ordersCollection = (new MongoDB\Client)->bittrex->orders;
        $balanceClient = (new MongoDB\Client)->bittrex->balance;
        $orderQuery  = [
            'Closed' => array('$eq' => null),
            'Limit' => array('$lte' => $curPrice),
            'OrderType' => array('$eq' => 'LIMIT_BUY')
        ];

        $matches = $ordersCollection->find($orderQuery);

        foreach($matches as $match) {
            $item = $match;
            $item['Price'] = $curPrice * $item['Quantity'];
            $item['PricePerUnit'] = $curPrice;
            $item['CommissionPaid'] = (double)$item['Price'] * (0.25/100);
            $item['Closed']= gmDate("Y-m-d\TH:i:s"); 


            $result = $ordersCollection->updateOne(
                array('OrderUuid' => $item['OrderUuid']),
                array('$set' => array(
                    'Price' => (double)$curPrice * (double)$item['Quantity'],
                    'PricePerUnit' => $curPrice,
                    'CommissionPaid' => ((double)$curPrice * (double)$item['Quantity']) * (double)(0.25/100),
                    'Closed' => gmDate("Y-m-d\TH:i:s")
                ))
            );

            $newAmount = $bittrex->getAccountBalance('BTC');
            $newAmount -= ((double)$curPrice * (double)$item['Quantity']) . (((double)$curPrice * (double)$item['Quantity']) * (double)(0.25/100));
            $bittrex->setCurrencyBalance('BTC', $newAmount);

            echo "Committing order... " . $item['OrderUuid'] . "\n";    
        }
    }

    private function checkSellOrders($curPrice) {
        $collection = (new MongoDB\Client)->bittrex->orders;
        $orderQuery  = [
            'Closed' => array('$eq' => null),
            'Limit' => array('$gte' => $curPrice),
            'OrderType' => array('$eq' => 'LIMIT_SELL')
        ];

        $matches = $collection->find($orderQuery);

        foreach($matches as $match) {
            $item = $match;
            $item['Price'] = $curPrice * $item['Quantity'];
            $item['PricePerUnit'] = $curPrice;
            $item['CommissionPaid'] = (double)$item['Price'] * (0.25/100);
            $item['Closed']= gmDate("Y-m-d\TH:i:s"); 


            $result = $collection->updateOne(
                array('OrderUuid' => $item['OrderUuid']),
                array('$set' => array(
                    'Price' => (double)$curPrice * (double)$item['Quantity'],
                    'PricePerUnit' => $curPrice,
                    'CommissionPaid' => ((double)$curPrice * (double)$item['Quantity']) * (double)(0.25/100),
                    'Closed' => gmDate("Y-m-d\TH:i:s")
                ))
                );

            echo "Committing order... " . $item['OrderUuid'] . "\n";    
        }
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
