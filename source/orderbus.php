<?php

class OrderBus extends Thread
{
    public function __construct()
    {
        $this->shutdown = false;
    }
    
    /**
     * The thread's run() method that runs in parallel.
     * 
     * @link http://www.php.net/manual/en/thread.run.php
     */
    public function run()
    {
        while(!$this->shutdown) {

            echo "Checking order bus...\n";
            sleep(1);
        }
    }        
}
