<?php

namespace Urssaf;

class Database
{
    private static Database $instance;
    private \PDO $pdo;

    private function __construct()
    {
        $this->pdo = new \PDO('sqlite:' . __DIR__ . '/database.sqlite');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->initializeDatabase();
    }

    public static function getInstance(): Database
    {
        if (!isset(self::$instance)) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    private function initializeDatabase()
    {
        $sql = <<< SQL
        CREATE TABLE IF NOT EXISTS contractor (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            siret TEXT NOT NULL UNIQUE,
            activity TEXT NOT NULL,
            tax_system TEXT NOT NULL,
            created_at TEXT DEFAULT CURRENT_DATE
        );
        SQL;
        $this->pdo->exec($sql);
    }

    public function getAllContractors(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM contractor");
        return $stmt->fetchAll(\PDO::FETCH_CLASS, Contractor::class);
    }

    public function getContractorById(int $id): Contractor
    {
        $sql = <<< SQL
        SELECT *
        FROM contractor
        WHERE id = :id
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchObject(Contractor::class);
        return $result ?? throw new \Exception("Contractor with ID $id not found.");
    }

    private function checkSiretConfirmity(string $siret): void
    {
        if (mb_strlen($siret) !== 14 || !ctype_digit($siret)) {
            throw new \Exception("Le SIRET doit être une chaîne de 14 chiffres.");
        }

        $sql = <<< SQL
        SELECT COUNT(*)
        FROM contractor
        WHERE siret = :siret
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':siret' => $siret]);
        if ($stmt->fetchColumn() > 0) {
            throw new \Exception("Le SIRET '$siret' existe déjà.");
        }
    }

    public function addSelfEmployed(string $fullName, string $siret, string $activityRegime, string $taxRegime): int
    {
        $this->checkSiretConfirmity($siret);
        $sql = <<< SQL
        INSERT INTO self_employed (full_name, siret, activity_regime, tax_regime)
        VALUES (:full_name, :siret, :activity_regime, :tax_regime)
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':full_name', $fullName);
        $stmt->bindParam(':siret', $siret);
        $stmt->bindParam(':activity_regime', $activityRegime);
        $stmt->bindParam(':tax_regime', $taxRegime);
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function Dataset(): bool
    {
        //| Prénom | Nom             | SIRET          | Régime d'activité | Régime fiscal                | CA HT mensuel (EUROS) |
        // | :----- | :-------------- | :------------- | :---------------- | :--------------------------- | :-------------------- |
        // | John   | Incubator Jones | 18812369758410 | BIC(Vente)        | Prélèvement à la source      | 3235                  |
        // | Luigi  | Vercotti        | 18823697384617 | BNC               | Versement fiscal libératoire | 2205                  |
        // | Emily  | Brontë          | 33512369768412 | BIC               | Versement fiscal libératoire | 4050                  |
        // | Ellie  | Williams        | 11112245668417 | BNC               | Versement fiscal libératoire | 0                     |
        $sql = <<< SQL
        INSERT INTO self_employed (full_name, siret, activity_regime, tax_regime)
        VALUES 
        ('John Incubator Jones', '18812369758410', 'BIC(Vente)', 'Prélèvement à la source'),
        ('Luigi Vercotti', '18823697384617', 'BNC', 'Versement fiscal libératoire'),
        ('Emily Brontë', '33512369768412', 'BIC', 'Versement fiscal libératoire'),
        ('Ellie Williams', '11112245668417', 'BNC', 'Versement fiscal libératoire')
        SQL;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }
}
