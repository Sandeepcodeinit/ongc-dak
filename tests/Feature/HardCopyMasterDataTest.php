<?php

namespace Tests\Feature;

use Tests\TestCase;

class HardCopyMasterDataTest extends TestCase
{
    public function test_master_models_are_available(): void
    {
        $this->assertTrue(class_exists(\App\Models\Committee::class));
        $this->assertTrue(class_exists(\App\Models\ScheduleVii::class));
        $this->assertTrue(class_exists(\App\Models\MpList::class));
    }
}
