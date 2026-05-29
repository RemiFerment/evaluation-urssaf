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

    public function clear(): void
    {
        $this->pdo->exec("DELETE FROM contractor");
        $this->pdo->exec("DELETE FROM sqlite_sequence WHERE name = 'contractor'");
    }
    private function initializeDatabase()
    {
        $sql = <<< SQL
        CREATE TABLE IF NOT EXISTS contractor (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            fullName TEXT NOT NULL,
            siret TEXT NOT NULL UNIQUE,
            activity TEXT NOT NULL,
            taxSystem TEXT NOT NULL,
            createdAt TEXT DEFAULT CURRENT_DATE
        );
        SQL;
        $this->pdo->exec($sql);
    }

    public function getAllContractors(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM contractor");
        return $stmt->fetchAll(\PDO::FETCH_OBJ);
    }

    public function getContractorById(int $id): Contractor
    {
        $sql = <<< SQL
        SELECT 
            fullName, siret, activity, taxSystem
        FROM contractor
        WHERE id = :id
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':id', $id, \PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_OBJ);
        return $result ? new Contractor($result->fullName, $result->siret, $result->activity, $result->taxSystem) : throw new \Exception("Contractor with ID $id not found.");
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

    public function addContractor(string $fullName, string $siret, string $activityRegime, string $taxRegime): int
    {
        $this->checkSiretConfirmity($siret);
        $sql = <<< SQL
        INSERT INTO contractor (fullName, siret, activity, taxSystem)
        VALUES (:fullName, :siret, :activity, :taxSystem)
        SQL;
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':fullName', $fullName);
        $stmt->bindParam(':siret', $siret);
        $stmt->bindParam(':activity', $activityRegime);
        $stmt->bindParam(':taxSystem', $taxRegime);
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
        INSERT INTO contractor (fullName, siret, activity, taxSystem)
        VALUES 
        ('John Incubator Jones', '18812369758410', 'bic-vente', 'ps'),
        ('Luigi Vercotti', '18823697384617', 'bnc', 'vfl'),
        ('Emily Brontë', '33512369768412', 'bic', 'vfl'),
        ('Ellie Williams', '11112245668417', 'bnc', 'vfl')
        SQL;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute();
    }
}
