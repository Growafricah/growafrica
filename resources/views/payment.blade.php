<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment Form</title>
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f4f4f4;
    }
    .container {
        max-width: 500px;
        margin: auto;
        padding: 20px;
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    h2 {
        text-align: center;
    }
    .form-group {
        margin-bottom: 20px;
    }
    label {
        display: block;
        font-weight: bold;
    }
    input[type="text"],
    input[type="email"],
    select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 16px;
    }
    .btn {
        display: block;
        width: 100%;
        padding: 10px;
        background-color: #4caf50;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 16px;
        transition: background-color 0.3s;
    }
    .btn:hover {
        background-color: #45a049;
    }
</style>
</head>
<body>

<div class="container">
    <h2>Payment Form</h2>
    <form id="paymentForm">

        <div class="form-group">
            <label for="address">first-name:</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="delivery_fee">Delivery Fee:</label>
            <input type="text" id="delivery_fee" name="delivery_fee" required>
        </div>
        <div class="form-group">
            <label for="total">Sub Total:</label>
            <input type="text" id="sub_total" name="sub_total" required>
        </div>
        <div class="form-group">
            <label for="total">Total:</label>
            <input type="text" id="total" name="total" required>
        </div>
        <button type="submit" class="btn" onclick="payWithPaystack()">Submit Payment</button>
    </form>

    <script src="https://js.paystack.co/v1/inline.js"></script>
</div>

</body>

{{-- <script>


    const paymentForm = document.getElementById('paymentForm');

    let delivery_fee = document.getElementById("delivery_fee").value;

    let address = document.getElementById("address").value;

    let sub_total = document.getElementById("sub_total").value;

    let total= document.getElementById("total").value;

    paymentForm.addEventListener("submit", payWithPaystack, false);




    function payWithPaystack(e) {

        e.preventDefault();


        let handler = PaystackPop.setup({



          key: 'pk_test_58043fcdc746c1d60622e808c7e1cd57dd810aa5', // Replace with your public key

          firstname: document.getElementById("name").value,

          email: document.getElementById("email").value,

          amount: document.getElementById("total").value * 100,

          ref: 'VRN'+Math.floor((Math.random() * 1000000000) + 1), // generates a pseudo-unique reference. Please replace with a reference you generated. Or remove the line entirely so our API will generate one for you

          // label: "Optional string that replaces customer email"

          onClose: function(){

            alert('Window closed.');

          },

          callback: function(response){

            let message = 'Payment complete! Reference: ' + response.reference;

            // alert(message);

            window.location.href = "http://127.0.0.1:8000/api/processpay?ref="+response.reference+"&address="+address+"&sub_total="+sub_total+"&delivery_fee="+delivery_fee+"&total="+total;

          }

        });


        handler.openIframe();

    }


</script> --}}

<script>
    const paymentForm = document.getElementById('paymentForm');

    paymentForm.addEventListener("submit", payWithPaystack, false);

    function payWithPaystack(e) {
        e.preventDefault();

        let address = document.getElementById("address").value;
        let delivery_fee = document.getElementById("delivery_fee").value;
        let sub_total = document.getElementById("sub_total").value;
        let total = document.getElementById("total").value;

        let handler = PaystackPop.setup({
            key: 'pk_test_58043fcdc746c1d60622e808c7e1cd57dd810aa5', // Replace with your public key
            firstname: document.getElementById("name").value,
            email: document.getElementById("email").value,
            amount: total * 100,
            ref: 'GRA' + Math.floor((Math.random() * 1000000000) + 1), // generates a pseudo-unique reference

            onClose: function() {
                alert('Window closed.');
            },

            callback: function(response) {
                let message = 'Payment complete! Reference: ' + response.reference;

                // Redirect to the specified URL with query parameters
                window.location.href = `http://127.0.0.1:8000/api/processpay?ref=${response.reference}&address=${encodeURIComponent(address)}&sub_total=${encodeURIComponent(sub_total)}&delivery_fee=${encodeURIComponent(delivery_fee)}&total=${encodeURIComponent(total)}`;
            }
        });

        handler.openIframe();
    }
</script>
</html>
