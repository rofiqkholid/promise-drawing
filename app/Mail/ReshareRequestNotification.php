<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReshareRequestNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $supplierName;
    public $packageName;
    public $reason;
    public $requestUrl;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($supplierName, $packageName, $reason, $requestUrl)
    {
        $this->supplierName = $supplierName;
        $this->packageName = $packageName;
        $this->reason = $reason;
        $this->requestUrl = $requestUrl;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $p = is_array($this->packageName) ? $this->packageName : [];
        $subjectSegments = array_filter([
            $p['customer'] ?? null,
            $p['model'] ?? null,
            $p['part_no'] ?? null,
            $p['doc_type'] ?? null,
            $p['category'] ?? null,
            $p['code_part_group'] ?? null,
            $p['ecn_no'] ?? null
        ], fn($v) => $v !== null && $v !== '-' && $v !== '');
        
        $subjectName = empty($subjectSegments) ? (is_string($this->packageName) ? $this->packageName : '-') : implode(' - ', $subjectSegments);

        return $this->subject("[PROMISE] New Re-share Request from {$this->supplierName}: {$subjectName}")
            ->markdown('emails.reshare_request');
    }
}
