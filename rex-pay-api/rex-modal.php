<?php

define("REXPAY_MAKE_PAYMENT", "/api/pgs/payment/v2/createPayment");

class RexApi{

    private $rexpay_access_token;
    // private $rexpay_refresh_token;
    private $api_base_url = "https://pgs-sandbox.globalaccelerex.com";
    public $api_live_url = "https://pgs.globalaccelerex.com";
    public $api_test_url = "https://pgs-sandbox.globalaccelerex.com";
    public $payment_method;
    public $payment_page_mode;
    public $handler;
    public $email;
    public $reference;
    public $currency;
    public $cb_url;
    public $userID;
    public $bankCode;
    public $amount;
    public $payment_status;
    public $payment_link;
    public $requeryCount = 0;
    protected $price;

    public function __construct($clientId, $clientSecret, $testmode){
        $this->set_base_url($testmode);
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->rexpay_api_auth = (string)base64_encode($this->clientId . ":" . $this->clientSecret);
        $this->set_access_token($this->get_access_token());
    }

    public function set_handler($handler){

        $this->handler = $handler;

    }

    public function get_api_auth(){

        return $this->rexpay_api_auth;

    }

    public function get_payment_status(){

        return $this->payment_status;

    }

    public function set_payment_status($status){

        $this->payment_status = $status;

    }

    public function set_amount($amount){

        $this->amount = $amount;

    }

    public function set_payment_page_mode($mode){
        $this->payment_page_mode = $mode;
    }

    public function get_payment_page_mode(){
        return $this->payment_page_mode;
    }
    public function set_email($email)
    {

        $this->email = $email;

    }

    public function set_currency($currency){

        $this->currency = $currency;

    }

    public function set_reference($reference){
        $this->reference = $reference;
    }

    public function get_reference(){
        return $this->reference;
    }

    public function set_userID($userID){
        $this->userID = $userID;
    }

    public function set_bankCode($bankCode){
        $this->bankCode = $bankCode;
    }

    public function set_callbackUrl($cb_url){
        $this->cb_url = $cb_url;
    }

    public function set_payment_link($pay_link){
        $this->payment_link = $pay_link;
    }

    public function get_payment_link(){
        return $this->payment_link;
    }

    public function get_baseurl(){
        return $this->api_base_url;
    }


    public function set_base_url($env_mode){
        ($env_mode) ? $this->api_base_url = $this->api_test_url : $this->api_base_url = $this->api_live_url;
    }

    public function set_access_token($token){
        $this->rexpay_access_token = $token;
    }

    public function get_token(){
        return $this->rexpay_access_token;
    }

    public function get_access_token(){
        $token_response = $this->get_rexpay_call($this->api_base_url . '/api/pgs/clients/v1/get/' . $this->clientId . '/token?email=admin');
        // echo "<pre>";
        // print_r($token_response);
        // echo "</pre>";
        if (isset($token_response['secretKey'])) {
            return $token_response['secretKey'];
        }
    }


    public function set_payment_method($selected_method){
        switch ($selected_method) {
            case 'card':
                $this->payment_method = "card";
                break;
            case 'ussd':
                $this->payment_method = "ussd";
                break;
            case 'bank':
                $this->payment_method = "bank";
                break;
            default:
                $this->payment_method = "default";
                break;
        }

        return $this->payment_method;

    }


    public function sort_payload($payload){
        $sorted_payload = ksort($payload);
        return $sorted_payload;
    }

    public function event_handler($handler)
    {
        $this->handler = $handler;
    }


    public function initiate_payment(){

        $metadata = (object)[
            'email' => $this->email
        ];
        $payload = array(
            "reference" => $this->reference,
            "userId" => $this->userID,
            "amount" => $this->amount,
            "currency" => $this->currency,
            "callbackUrl" => $this->cb_url,
            "paymentChannel" => strtoupper($this->payment_method),
            "callbackMode" => false,
            "metadata" => $metadata
        );

        if (isset($this->handler)) {
            $this->handler->onInit($payload);
        }

        switch ($this->payment_method) {
            case 'card':
                $this->card_payment();
                break;

            default:
                $this->normal_payment();
                break;
        }

    }

    public function normal_payment(){

        $metadata = (object)[
            'email' => $this->email
        ];

        $rexpay_plugin_payload = array(
            "reference" => (string)$this->reference,
            "userId" => (string)$this->userID,
            "amount" => (int)$this->amount,
            "currency" => (string)$this->currency,
            "paymentChannel" => strtoupper($this->payment_method),
            "metadata" => $metadata
        );

        $page_mode = $this->get_payment_page_mode();

        if ($page_mode == 'redirect') {
            $rexpay_plugin_payload["callbackUrl"] = (string)$this->cb_url;
            $rexpay_plugin_payload["callbackMode"] = (bool)false;
        }

        $rexpay_response = $this->post_rexpay_call($this->api_base_url . REXPAY_MAKE_PAYMENT, $rexpay_plugin_payload);


        if (isset($rexpay_response["responseMessage"])) {
            $this->set_payment_link($_SERVER['HTTP_REFERER']);
        }

        if (isset($rexpay_response["status"])) {
            if ($rexpay_response["status"] === "CREATED") {

                $this->set_payment_link($rexpay_response["paymentUrl"]);

            } else {

                $this->set_payment_link($_SERVER['HTTP_REFERER']);
            }
        }

    }

    public function card_payment(){
        $metadata = (object)[
            'email' => $this->email
        ];


        $rexpay_plugin_payload = array(
            "reference" => (string)$this->reference,
            "userId" => (string)$this->userID,
            "amount" => (int)$this->amount,
            "currency" => (string)$this->currency,
            "paymentChannel" => (string)strtoupper($this->payment_method),
            "metadata" => $metadata
        );

        $page_mode = $this->get_payment_page_mode();

        if ($page_mode == 'redirect') {
            $rexpay_plugin_payload["callbackUrl"] = (string)$this->cb_url;
            $rexpay_plugin_payload["callbackMode"] = (bool)false;
        }

        $rexpay_response = $this->post_rexpay_call($this->api_base_url . REXPAY_MAKE_PAYMENT, $rexpay_plugin_payload);
        // echo "<pre>";
        // echo "base_url :". $this->api_base_url. "</br>";
        // print_r($rexpay_plugin_payload);

        // print_r($rexpay_response);
        // echo "</pre>";

        if (isset($rexpay_response["responseMessage"])) {
            $this->set_payment_link($_SERVER['HTTP_REFERER']);
        }

        if (isset($rexpay_response["status"])) {
            if ($rexpay_response["status"] === "ONGOING") {

                $this->set_payment_link($rexpay_response["paymentUrl"]);
                // // return "<script> window.location='".$rexpay_response["paymentUrl"]."'</script>";
                // header('Location: '.$rexpay_response["paymentUrl"]);

            } else {

                $this->set_payment_link($_SERVER['HTTP_REFERER']);
            }
        }


    }

    public function ussd_payment(){

        $metadata = (object)[
            'email' => $this->email
        ];

        $rexpay_plugin_payload = array(
            "reference" => $this->reference,
            "userId" => $this->userID,
            "amount" => $this->amount,
            "currency" => $this->currency,
            "callbackUrl" => $this->cb_url,
            "bankCode" => $this->bankCode,
            "paymentChannel" => strtoupper($this->payment_method),
            "metadata" => $metadata
        );

        $rexpay_response = $this->post_rexpay_call($this->api_base_url . REXPAY_MAKE_PAYMENT, $rexpay_plugin_payload);
        if ($rexpay_response["status"] === "Created") {
            echo "<script> window.location='" . $payload["paymentUrl"] . "'</script>";
        } else {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }


    }

    public function account_payment(){
        $metadata = (object)[
            'email' => $this->email
        ];

        $rexpay_plugin_payload = array(
            "reference" => $this->reference,
            "userId" => $this->userID,
            "amount" => $this->amount,
            "currency" => $this->currency,
            "callbackUrl" => $this->cb_url,
            "bankCode" => $this->bankCode,
            "paymentChannel" => strtoupper($this->payment_method),
            "callbackMode" => false,
            "metadata" => $metadata
        );

        $rexpay_response = $this->post_rexpay_call($this->api_base_url . REXPAY_MAKE_PAYMENT, $rexpay_plugin_payload);
        if ($rexpay_response["status"] === "Created") {
            echo "<script> window.location='" . $payload["paymentUrl"] . "'</script>";
        } else {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        }

    }


    function requery_transaction($reference, $auth = "YWJyYWhhbTo="){
        $this->reference = $reference;

        $this->requeryCount++;
        if (isset($this->handler)) {
            $this->handler->onRequery($this->reference);
        }


        // Make `POST` request and handle response with unirest
        $response = $this->get_rexpay_call_norm($this->api_base_url . '/api/pgs/payment/v1/getPaymentDetails/' . $this->reference, $auth);
        // echo "<pre>";
        // print_r($response);
        // echo "</pre>";
        // exit();
        //check the status is success
        if ($response['status'] === "SUCCESS") {
            // Handle successful
            $this->set_payment_status($response['status']);
            if (isset($this->handler)) {
                $this->handler->onSuccessful($response);
            }
        } elseif ($response['status'] === "FAILED") {
            // Handle Failure
            $this->set_payment_status($response['status']);
            if (isset($this->handler)) {
                $this->handler->onFailure($response);
            }
        } else {

            // I will requery again here. Just incase we have some devs that cannot setup a queue for requery. I don't like this.
            if ($this->requeryCount > 4) {
                // Now you have to setup a queue by force. We couldn't get a status in 5 requeries.
                if (isset($this->handler)) {
                    $this->handler->onTimeout($this->reference, $response);
                }
            } else {
                sleep(3);
                $this->requery_transaction($this->reference);
            }
        }
        return $this;
    }

    public function post_rexpay_call($endpoint, $data){

        $json_encoded_data = json_encode($data, JSON_UNESCAPED_SLASHES);
        $request = wp_remote_post($endpoint, array(
            'headers' => ['Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $this->rexpay_api_auth,
                'X-AUTH-TOKEN' => $this->rexpay_access_token],
            'body' => $json_encoded_data
        ));

        if (is_array($request) && !is_wp_error($request)) {

            $result = json_decode($request['body'], true);

            return $result;
        }

        if (is_wp_error($request)) {
            $error_message = $request->get_error_message();
            return "Something went wrong: $error_message";
        }

    }

    public function get_rexpay_call($endpoint){

        $args = array(
            // 'header' => array('Authorization' => 'Basic '.$this->rexpay_api_auth),
            'headers' => array('Authorization' => 'Basic ' . $this->rexpay_api_auth),
        );

        $request = wp_remote_get($endpoint, $args);

        if (is_array($request) && !is_wp_error($request && wp_remote_retrieve_response_code($request) == 200)) {

            $result = json_decode($request['body'], true);

            return $result;
        }

        if (is_wp_error($request)) {
            $error_message = $request->get_error_message();
            return "Something went wrong: $error_message";
        }
    }

    public function get_rexpay_call_norm($endpoint, $auth){
        echo $this->rexpay_access_token;
        $args = array(
            // 'header' => array('Authorization' => 'Basic '.$this->rexpay_api_auth),
            'headers' => array('Content-Type' => 'application/json',
                'Authorization' => 'Basic ' . $auth,
                'X-AUTH-TOKEN' => $this->rexpay_access_token
            ),
        );

        $request = wp_remote_get($endpoint, $args);

        if (is_array($request) && !is_wp_error($request && wp_remote_retrieve_response_code($request) == 200)) {

            $result = json_decode($request['body'], true);

            return $result;
        }

        if (is_wp_error($request)) {
            $error_message = $request->get_error_message();
            return "Something went wrong: $error_message";
        }
    }

    public function delete_rexpay_call($endpoint){

        $response = wp_remote_request($endpoint,
            array(
                'method' => 'DELETE',
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $this->learnworld_access_token, 'Lw-Client' => $this->clientId]
            )
        );

        // $body = wp_remote_retrieve_body($response);
        // echo $body;

        if (is_array($response) && !is_wp_error($response)) {

            $result = json_decode($response['body'], true);

            return $result;
        }

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            return "Something went wrong: $error_message";
        }

    }

    public function do_nothing(){
        //do nothing for now
    }

}

?>