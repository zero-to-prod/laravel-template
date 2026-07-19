<?php

namespace Tests\Feature;

use App\Routes\Web;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HomeTest extends TestCase
{
    #[Test]
    public function home_ok(): void
    {
        $this->get(Web::home->value)->assertOk();
    }
}
