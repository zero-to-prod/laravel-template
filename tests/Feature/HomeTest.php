<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomeTest extends TestCase
{
    #[Test]
    public function home_ok(): void
    {
        $this->get(web()->home)->assertOk();
    }
}
