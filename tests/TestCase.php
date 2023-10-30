<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public $ownerUser;
    public $adminUser;

    protected function setUp(): void
    {
        parent::setUp();
    }
}
