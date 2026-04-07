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
        // kalau controller sudah kirim 'package_data', pakai langsung
        $packageData = $this->approval['package_data'] ?? null;

        // fallback: susun sendiri dari field2 yang ada
        if (!$packageData) {
            $customer   = $this->approval['customer']    ?? '';
            $model      = $this->approval['model']       ?? '';
            $partNo     = $this->approval['part_no']     ?? '';
            $docType    = $this->approval['doc_type']    ?? '';
            $category   = $this->approval['category']    ?? '';
            $partGroup  = $this->approval['part_group']  ?? '';
            $ecnNo      = $this->approval['ecn_no']      ?? '';

            $segments = array_filter([
                $customer,
                $model,
                $partNo,
                $docType,
                $category,
                $partGroup,
                $ecnNo,
            ], fn ($v) => $v !== null && $v !== '');

            $packageData = implode(' - ', $segments);
        }

        $subject = "[PROMISE] Revision Approved – {$packageData}";

        return $this->subject($subject)
                    ->markdown('emails.approved_notif');
    }
}
