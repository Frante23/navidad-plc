<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NuevaClaveOrgMail extends Mailable
{
    use Queueable, SerializesModels;

    public $org;
    public $clave;

    public function __construct($org, $clave)
    {
        $this->org = $org;
        $this->clave = $clave;
    }

    public function build()
    {
        return $this->subject('Nueva clave de acceso - Programa Navidad')
            ->view('emails.nueva-clave');
    }
}
