<?php

namespace Urssaf;

class Contractor
{
    public function __construct(
        public string $fullName,
        public string $siret,
        public string $activity,
        public string $taxSystem
    ) {}
}
