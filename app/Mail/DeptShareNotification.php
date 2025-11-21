<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeptShareNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $share;

    /**
     * @param  \App\Models\User|object  $user
     * @param  array  $share
     */
    public function __construct($user, array $share)
    {
        $this->user  = $user;
        $this->share = $share;
    }

    public function build()
    {
        $customer = $this->share['customer'] ?? '-';
        $model    = $this->share['model']    ?? '-';
        $docType  = $this->share['doc_type'] ?? '-';
        $category = $this->share['category'] ?? '-';

        $subject  = "[PROMISE] Package Shared to Dept – {$customer}-{$model}-{$docType}-{$category}";

        return $this->subject($subject)
            ->markdown('emails.dept_share'); 
    }
}
