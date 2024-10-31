<?php 

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

interface rexpayEventHandlerInterface{
    /**
     * This is called when the a transaction is initialized
     * @param object $initializationData This is the initial transaction data as passed
     * */
    function onInit($initializationData);
    
    /**
     * This is called only when a transaction is successful
     * @param object $transactionData This is the transaction data as returned from the Rex-Pay payment gateway
     * */
    function onSuccessful($transactionData);
    
    /**
     * This is called only when a transaction failed
     * @param object $transactionData This is the transaction data as returned from the Rex-Pay payment gateway
     * */
    function onFailure($transactionData);
    
    /**
     * This is called when a transaction is requeryed from the payment gateway
     * @param string $transactionReference This is the transaction reference as returned from the Rex-Pay payment gateway
     * */
    function onRequery($transactionReference);
    
    /**
     * This is called a transaction requery returns with an error
     * @param string $requeryResponse This is the error response gotten from the Rex-Pay payment gateway requery call
     * */
    function onRequeryError($requeryResponse);
    
    /**
     * This is called when a transaction is canceled by the user
     * @param string $transactionReference This is the transaction reference as returned from the Rex-Pay payment gateway
     * */
    function onCancel($transactionReference);
    
    /**
     * This is called when a transaction doesn't return with a success or a failure response.
     * @param string $transactionReference This is the transaction reference as returned from the Rex-Pay payment gateway
     * @data object $data This is the data returned from the requery call.
     * */
    function onTimeout($transactionReference,$data);
}
?>