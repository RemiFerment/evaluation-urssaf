<?php

namespace Urssaf;
/*
Ici on choisit une classe abstraite pour centraliser l'implémentation de la génération du rapport, commune à tous les régimes. On fait un mélange entre le pattern *Strategy* et *Template Method*
*/

abstract class AbstractActivityStrategy
{ //À implémenter...
    //Retourne le rapport (qui sera affiché sur la sortie standard)
    public function buildReport(float $caHt, string $taxSystem): string
    {
        return "Rapport d'activité:\n" .
            "Chiffre d'affaires HT: {$caHt} €\n" .
            "Cotisations sociales: " . ($caHt * $this->cotisationRate()) . " €\n" .
            "Indemnités de frais d'exploitation: " . $this->specificSubsidy($caHt) . " €\n" .
            "Régime fiscal: {$taxSystem}\n" .
            "Prélèvement fiscal: " . ($taxSystem === 'vfl' ? ($caHt * $this->taxDischargePayment()) : ($caHt * $this->abatementRate())) . " €\n";
    }
    //Retourne le taux de cotisation social
    abstract protected function cotisationRate(): float;
    //Retourne le taux de prélèvement dans le cadre du régime fiscal "Versement fiscal libératoire" (vfl)
    abstract protected function taxDischargePayment(): float;
    //Retourne le taux d'abattement fiscal dans le cadre du régime fiscal "Prélèvement à la source" (ps)
    abstract protected function abatementRate(): float;

    //De la logique métier propre à chaque régime d'activité
    //Calcul des indemnités de frais d'exploitation
    abstract protected function specificSubsidy(float $caHt): float;
}
