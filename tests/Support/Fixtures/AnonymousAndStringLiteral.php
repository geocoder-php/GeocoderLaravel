<?php

namespace Geocoder\Laravel\Tests\Support\Fixtures\Tricky;

class Real
{
    public function makeAnonymous(): object
    {
        return new class {
        };
    }

    public function literalThatLooksLikeAClass(): string
    {
        return "class FakeFromString {}";
    }
}
