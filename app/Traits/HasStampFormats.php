<?php

namespace App\Traits;

use Carbon\Carbon;

trait HasStampFormats
{
    /**
     * Format a date for Stamp (e.g., Feb.13ᵗʰ 2026)
     * 
     * @param mixed $date
     * @return string|null
     */
    public function formatStampDate($date): ?string
    {
        if (!$date) return null;
        
        $cB = Carbon::parse($date);
        $day = $cB->day;
        
        if (in_array($day % 100, [11, 12, 13], true)) {
            $suffixRaw = 'th';
        } else {
            $last = $day % 10;
            $suffixRaw = match ($last) {
                1 => 'st',
                2 => 'nd',
                3 => 'rd',
                default => 'th'
            };
        }
        
        $superscripts = ['st' => 'ˢᵗ', 'nd' => 'ⁿᵈ', 'rd' => 'ʳᵈ', 'th' => 'ᵗʰ'];
        return $cB->format('M') . '.' . $day . $superscripts[$suffixRaw] . ' ' . $cB->format('Y');
    }
}
