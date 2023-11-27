<?php

namespace App\Mail\AgeCommunicate\Base;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendMailBillingRule extends Mailable
{
    use Queueable, SerializesModels;

    private $viewName;
    private $subjectMail;
    private $name_client;
    private $barcode;


    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($viewName, $subject, $name_client, $barcode, $billetPath = [])
    {
        $this->viewName = $viewName;
        $this->subjectMail = $subject;
        $this->name_client = mb_convert_case($name_client, MB_CASE_TITLE, "UTF-8");
        $this->barcode = $barcode;
        $this->billetPath= $billetPath;
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: $this->subjectMail,
        );
    }

    /**
     * Get the message content definition.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content()
    {
        return new Content(
            view: 'mail.ageCommunicate.base.billingRule.' . $this->viewName,
            with: ['name_client' => $this->name_client, 'barcode' => $this->barcode],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return $this->billetPath;
    }
}
