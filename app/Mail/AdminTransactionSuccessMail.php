<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminTransactionSuccessMail extends Mailable
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

        $adminMail = $this->view('emails.admin_notification')
                            ->subject('ADMIN ORDER NOTIFICATION')
                            ->with([
                                'order' => $this->order,
                                'orderItems' => $this->order->orderItems
                            ]);


        return $adminMail;


    }
}
