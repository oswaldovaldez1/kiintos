<?php
return array(
    // set your paypal credential
    'client_id' => 'AU8IfXsl28xX_Q6KSSJn0HhpeTzCKMtBzOcO_UC6gnOMCPG_773sp5hDItw_kPF4D86fCndyeY30QHGv',
    'secret' => 'EKx4XNTOnGiVgC7UnvQ_kNyVD_UlhhKyGVgaJBpf5KXLxnDbux1inBX5LCQkESgmPZhm1-zKcQuursKE',

    /**
     * SDK configuration 
     */
    'settings' => array(
        /**
         * Available option 'sandbox' or 'live'
         */
        'mode' => 'live',

        /**
         * Specify the max request time in seconds
         */
        'http.ConnectionTimeOut' => 30,

        /**
         * Whether want to log to a file
         */
        'log.LogEnabled' => true,

        /**
         * Specify the file that want to write on
         */
        'log.FileName' => storage_path() . '/logs/paypal.log',

        /**
         * Available option 'FINE', 'INFO', 'WARN' or 'ERROR'
         *
         * Logging is most verbose in the 'FINE' level and decreases as you
         * proceed towards ERROR
         */
        'log.LogLevel' => 'FINE'
    ),
);