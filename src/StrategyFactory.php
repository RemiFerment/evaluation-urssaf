<?php

namespace Urssaf;

use Urssaf\AbstractActivityStrategy;
use Urssaf\BicVenteStrategy;
use Urssaf\BicStrategy;
use Urssaf\BncStrategy;

class StrategyFactory
{
    public static function make(string $activityType): AbstractActivityStrategy
    {
        return match ($activityType) {
            'bic-vente' => new BicVenteStrategy(),
            'bic'       => new BicStrategy(),
            'bnc'       => new BncStrategy(),
            default     => throw new \Exception("Régime d'activité inconnu : {$activityType}"),
        };
    }
}
