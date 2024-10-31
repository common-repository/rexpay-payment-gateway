var form = jQuery('#rexpay-payment-button'),
btn = jQuery('#rexpay-payment-button-2'),
rex_iframe = jQuery('#TB_window'),
close_btn = jQuery('#TB_closeWindowButton');
// modal_overlay = jQuery('#TB_overlay');

// rex_iframe.attr("src", rexpay_payment_args.payment_url);

setInterval(() => {

    jQuery('#TB_overlay').off('click');

}, 1000);

if(rexpay_payment_args.payment_page_mode == 'inline'){
    jQuery('body').on('thickbox:removed', function(jQuery) {
    //CONFIRM
    const rex_verify_url = rexpay_payment_args.baseurl+'/api/pgs/payment/v1/getPaymentDetails/'+rexpay_payment_args.reference;
    // axios.get(rex_verify_url, { headers: {
    //     'Content-Type': 'application/json',
    //     'Authorization': 'Basic '+rexpay_payment_args.rex_auth,
    //     'X-AUTH-TOKEN': rexpay_payment_args.token
    // }}).then(response => (response.data.status !== 'CREATED')? rexredirectTo(): console.log('ongoing payment'));
      let fetchData = {

        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Basic '+rexpay_payment_args.rex_auth,
          'X-AUTH-TOKEN': rexpay_payment_args.token
        }

      }
    fetch(rex_verify_url, fetchData)
.then(function( response ) {
  console.log(response.json());
  (response.json().status !== 'CREATED')? rexredirectTo(): console.log('ongoing payment')
})
    
    });
}


if (form) {

  form.on('click', function (evt) {
    evt.preventDefault();    

  });

}

let rexredirectTo = () => {
  jQuery('body').html('<center><img src="'+ rexpay_payment_args.image_loading_gif +'" /><b><p> Confirming Payment....</p></b></center>')
  location.href = rexpay_payment_args.callbackurl
}

function checkStatus1(){
        rexredirectTo();

    // console.log('your payment status :'+ paymentstatus)
 
        // if(paymentstatus == "FAILED"){
        //     redirectTo();
    
        // }
    
        // if(paymentstatus == "SUCCESS"){
        //     redirectTo();
    
        // }
    
        // if(paymentstatus == "ONGOING"){
        //     console.log('querying transaction');       
        // }

}



