<?php

namespace Tests\Unit;

use App\Models\Medicine;
use Carbon\Carbon;
use Tests\TestCase;

class MedicineExpirationTest extends TestCase
{
    public function test_expiration_check_accepts_date_strings(): void
    {
        Carbon::setTestNow('2026-10-15 12:00:00');

        try {
            $medicine = new Medicine(['expiry_date' => '2026-10-14']);

            $this->assertTrue($medicine->isExpired('2026-10-15'));

            $medicine->expiry_date = '2026-10-15';
            $this->assertFalse($medicine->isExpired('2026-10-15'));
        } finally {
            Carbon::setTestNow();
        }
    }
}
