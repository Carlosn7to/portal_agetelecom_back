<?php

namespace App\Mail\AgeCommunicate\Base\SCPC;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendSCPC extends Mailable
{

    private $data = [
        'nameClient' => '',
        'cpf' => '',
        'cnpj' => '',
        'addressClient' => '',
        'contractClient' => '',
        'financialNature' => '',
        'valueDebit' => '',
        'dateDebit' => '',
    ];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($clientName, $cpf, $debits, $dateActual)
    {
        $this->data['nameClient'] = $clientName;
        $this->data['cpf'] = $cpf;
        $this->data['cnpj'] = '40.085.642/0001-55';
        $this->data['address'] = 'ST SIA TRECHO 17 VIA IA 4 LT 1080';
        $this->data['debits'] = $debits;
        $this->data['dateActual'] = $dateActual;

    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: '[Age Telecom] - Comunicado Importante',
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
            view: 'mail.ageCommunicate.base.scpc.scpc',
            with: ['data' => $this->data],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }
}

