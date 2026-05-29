<?php

namespace Urssaf;

use Urssaf\Contractor;
use Urssaf\Database;

/**
 * Couche d'abstraction sur l'origine des données.
 */
class ContractorRepository
{
    //Injection de dépendance dans le constructeur de l'instance PDO (accès a la base de données)
    public function __construct(private Database $db)
    {
        $this->db = Database::getInstance();
    }

    /**
     * Retourne l'identifiant généré par la base pour le nouveau record
     * @throws \Exception Si l'insertion en base de données échoue
     * @return int
     */
    public function save(string $fullName, string $siret, string $activity, string $taxSystem): int
    {
        try {
            return $this->db->addContractor($fullName, $siret, $activity, $taxSystem);
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de l'ajout de l'auto-entreprise: " . $e->getMessage());
        }
    }

    /**
     * @return Contractor|null
     */
    public function find(int $id): ?Contractor
    {
        try {
            return $this->db->getContractorById($id);
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la recherche de l'auto-entreprise: " . $e->getMessage());
        }
    }

    /**
     * @return array<Contractor>
     */
    public function findAll(): array
    {
        try {
            return $this->db->getAllContractors();
        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de la récupération des auto-entreprises: " . $e->getMessage());
        }
    }
}
