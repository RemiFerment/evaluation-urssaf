<?php

namespace Urssaf;

class BicStrategy extends AbstractActivityStrategy
{
    protected function cotisationRate(): float
    {
        return 0.128;
    }

    protected function taxDischargePayment(): float
    {
        return 0.017;
    }

    protected function abatementRate(): float
    {
        return 0.5;
    }

    protected function specificSubsidy(float $caHt): float
    {
        return 0;
    }
}
