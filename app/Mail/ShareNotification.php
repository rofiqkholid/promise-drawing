<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShareNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $username;
    public $packageId;

    public function __construct($username, $packageId)
    {
        $this->username = $username;
        $this->packageId = $packageId;
    }

    public function build()
    {
        return $this->subject('Dokumen Baru Dibagikan Kepada Anda')
            ->markdown('emails.share_notification', [
                'username' => $this->username,
                'packageId' => $this->packageId,
            ]);
    }
}
