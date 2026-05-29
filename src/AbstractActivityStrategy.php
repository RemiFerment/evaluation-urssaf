<?php

namespace Urssaf;

abstract class AbstractActivityStrategy
{
    public function buildReport(float $caHt, string $taxSystem): string
    {
        $cotisations = $caHt * $this->cotisationRate();
        $aide = $this->specificSubsidy($caHt);

        $output = "";

        if ($taxSystem === 'ps') {
            // Prélèvement à la source
            $revenuImposable = $caHt * (1 - $this->abatementRate());
            $caTtc = $caHt - $cotisations + $aide;

            $output .= "CA HT mensuel:               " . number_format($caHt, 2, ',', ' ') . " EUROS\n";
            $output .= "Aide spécifique:               " . number_format($aide, 2, ',', ' ') . " EUROS\n";
            $output .= "Cotisations sociales:          " . number_format($cotisations, 2, ',', ' ') . " EUROS\n";
            $output .= "Revenu imposable:              " . number_format($revenuImposable, 2, ',', ' ') . " EUROS\n";
            $output .= "CA TTC mensuel:              " . number_format($caTtc, 2, ',', ' ') . " EUROS\n";
        } else {
            // Versement fiscal libératoire
            $impot = $caHt * $this->taxDischargePayment();
            $caTtc = $caHt - $cotisations - $impot + $aide;
            $output .= "CA HT mensuel:                  " . number_format($caHt, 2, ',', ' ') . " EUROS\n";
            $output .= "Aide spécifique:                    " . number_format($aide, 2, ',', ' ') . " EUROS\n";
            $output .= "Cotisations sociales:             " . number_format($cotisations, 2, ',', ' ') . " EUROS\n";
            $output .= "Montant de l'impôt à prélever:     " . number_format($impot, 2, ',', ' ') . " EUROS\n";
            $output .= "CA TTC mensuel:                 " . number_format($caTtc, 2, ',', ' ') . " EUROS\n";
        }

        return $output;
    }

    abstract protected function cotisationRate(): float;
    abstract protected function taxDischargePayment(): float;
    abstract protected function abatementRate(): float;
    abstract protected function specificSubsidy(float $caHt): float;
}
