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
    // Build a clean, informative subject
    $customer  = $this->approval['customer']  ?? '-';
    $model     = $this->approval['model']     ?? '-';
    $docType   = $this->approval['doc_type']  ?? '-';
    $category  = $this->approval['category']  ?? '-';
    $revNo     = $this->approval['revision_no'] ?? '-';
    $partNo    = $this->approval['part_no']   ?? '';

    $subject = "[PROMISE] Revision Approved – {$customer}-{$model}-{$docType}-{$category} (Rev-{$revNo})"
             . ($partNo ? " – {$partNo}" : "");

    return $this->subject($subject)
                ->markdown('emails.approved_notif'); // ⬅️ ini yang penting
}

}
