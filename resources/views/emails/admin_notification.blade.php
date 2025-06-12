<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width" />
    <title>Order Receipt</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet"/>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Inter", sans-serif;
        }

        .ctr {
            background: #fbfaff;
            padding: 24px;
        }

        .email {
            max-width: 640px;
            margin: auto;
        }

        .email p {
            color: #333;
        }

        .email .header {
            padding: 24px;
            background-color: #000;
            color: #fff;
        }

        .email .body {
            padding: 24px;
            background-color: #fff;
        }

        .email .body p {
            margin-bottom: 16px;
            color: #333;
        }

        .email .footer {
            padding: 32px;
            background-color: #f7f9fc;
        }

        .email .footer p {
            font-size: 14px;
        }

        .email .footer .unsub a {
            color: #333;
        }

        .email table {
            width: 100%;
            border-collapse: collapse;
        }

        .email table td, .email table th {
            border: 1px solid #ddd;
            padding: 8px;
        }

        .email table th {
            background-color: #f4f4f4;
        }

        @media screen and (min-width: 640px) {
            .ctr {
                padding: 68px;
            }

            .email .header {
                padding: 24px 32px;
            }

            .email .body {
                padding: 32px;
            }
        }
    </style>
</head>
<body>
    <div class="ctr">
        <div class="email">
            <div class="header">
                <img
                src="https://growafrica.shop/assets/logo-CAIRzSpJ.svg"
                  alt="GrowAfrica logo"
                width="117"
              />
                <h1>Order Receipt</h1>
            </div>

            <div class="body">
                <p> {{ $order->user->last_name}} {{ $order->user->first_name}},</p>
                <p>Placed an order, details are contained below;</p>

                <h2>Order Details</h2>
                <p>Order ID: {{ $order->id }}</p>
                <p>Transaction ID: {{ $order->txn_id }}</p>
                <p>Address: {{ $order->address }}</p>
                <p>Items Count: {{ $order->items_count }}</p>
                <p>Delivery Fee: &#x20A6;{{ number_format($order->delivery_fee, 2) }}</p>
                <p>Sub Total: &#x20A6;{{ number_format($order->sub_total, 2) }}</p>
                <p>Total Amount: ${{ number_format($order->total_amount, 2) }}</p>

                <h2>Order Items</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Merchant</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orderItems as $item)
                            <tr>
                                <td>{{ $item->seller->business_name}}</td>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>&#x20A6;{{ number_format($item->unit_price, 2) }}</td>
                                <td>&#x20A6;{{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="footer">
                <p class="unsub">
                    This email was sent to {{ $order->user->email }}.
                    You are receiving this email because you are an admin and there was a successful order placement.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
