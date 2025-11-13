<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ShareNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $username;
    public $packageId;
    public $emailSubject; 
    public $shareToNames; 
    public $files;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    // Constructor sekarang menerima 4 argumen
    public function __construct($username, $packageId, $subject, $shareTo,  $files)
    {
        $this->username = $username;
        $this->packageId = $packageId;
        $this->emailSubject = $subject;
        $this->shareToNames = $shareTo;
        $this->files = $files;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('New Document Shared: ' . $this->emailSubject)
            ->markdown('emails.share_notification', [
                'username' => $this->username,
                'packageId' => $this->packageId,
                'emailSubject' => $this->emailSubject, 
                'shareToNames' => $this->shareToNames, 
                'files' => $this->files,
            ]);
    }
}