<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Emailwk extends Mailable
{
    use Queueable, SerializesModels;

    public $dados;

    /**
     * Create a new message instance.
     */
    public function __construct($dados)
    {
        $this->dados = $dados;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Emailwk',
        );
    }


    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'), 'Workflex') // ✅ Correto!
        ->subject('Seu Assunto')
        ->view('emails.meu_email')
        ->with('dados', $this->dados);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
        );
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
