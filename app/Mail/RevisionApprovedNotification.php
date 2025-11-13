<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RevisionApprovedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $approval;

    public function __construct($user, array $approval)
    {
        $this->user     = $user;
        $this->approval = $approval;
    }

    public function build()
    {
        return $this->subject('PROMISE - Revision Approved ' . ($this->approval['part_no'] ?? ''))
                    ->view('emails.approved_notif');
    }
}
