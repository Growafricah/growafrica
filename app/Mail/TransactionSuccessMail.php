<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class TransactionSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;  // Pass the order data to the mailable

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $customerMail= $this->view('emails.transaction_success')
                    ->subject('Order Confirmation')
                    ->with([

                        'order' => $this->order,
                        'orderItems' => $this->order->orderItems
                    ]);

        return  $customerMail;

    }
}
