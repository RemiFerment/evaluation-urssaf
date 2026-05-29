<?php

namespace Urssaf;

interface Contractor
{
    public function __construct(string $fullName, string $siret, string $activity, string $taxSystem);
    public function buildReport(float $caHt): string;
    public function cotisationRate(): float;
    public function taxDischargePayment(): float;
    public function abatementRate(): float;
    public function specificSubsidy(float $caHt): float;
}
