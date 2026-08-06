<?php

use Tests\TestCase;

pest()->extend(TestCase::class)->in('Behavior', 'Feature');
pest()->tia()->locally();
