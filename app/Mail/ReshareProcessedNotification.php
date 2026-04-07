<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReshareProcessedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $supplierName;
    public $packageName;
    public $status;
    public $expiredAt;
    public $appUrl;
    public $rejectReason;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($supplierName, $packageName, $status, $expiredAt = null, $appUrl = null, $rejectReason = null)
    {
        $this->supplierName = $supplierName;
        $this->packageName = $packageName;
        $this->status = $status;
        $this->expiredAt = $expiredAt;
        $this->appUrl = $appUrl;
        $this->rejectReason = $rejectReason;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $statusText = $this->status === 'approved' ? 'Approved' : 'Rejected';
        
        $p = is_array($this->packageName) ? $this->packageName : [];
        $subjectSegments = array_filter([
            $p['model'] ?? null,
            $p['part_no'] ?? null,
            $p['doc_type'] ?? null,
            $p['category'] ?? null,
            $p['code_part_group'] ?? null,
            $p['ecn_no'] ?? null
        ], fn($v) => $v !== null && $v !== '-' && $v !== '');
        
        $subjectName = empty($subjectSegments) ? (is_string($this->packageName) ? $this->packageName : '-') : implode(' - ', $subjectSegments);

        return $this->subject("[PROMISE] Re-share Request {$statusText}: {$subjectName}")
            ->markdown('emails.reshare_processed');
    }
}
