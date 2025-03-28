<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;


class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $data; 
    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data; 
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Email',
        );
    }

    /**
     * Get the message content definition.
     */

     public function build()
    {
        return $this->subject('Assunto do E-mail')
                    ->view('otpcode')->with('data', $this->data);; // Nome da view (caminho relativo a resources/views)
    }
        
    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
