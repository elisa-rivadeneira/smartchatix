<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class EnviarEmailCliente extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $cliente;
    public $mensaje;


public function __construct($cliente, $mensaje)
{
    Log::info("📩 Cliente recibido en EnviarEmailCliente: " . json_encode($cliente));

    $this->cliente = $cliente;
    $this->mensaje = $mensaje;
}

    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS'))
                    ->subject("Mensaje de tu Asesor")
                    ->view('emails.notificacion_cliente')
                    ->with([
                        'nombre' => $this->cliente->nombre,
                        'mensaje' => $this->mensaje
                    ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Enviar Email Cliente',
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
