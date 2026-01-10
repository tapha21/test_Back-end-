<?php
// public/setup.php - Exécuter UNE FOIS pour créer tables
require dirname(__DIR__).'/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;

$dotenv = new Dotenv();
$dotenv->loadEnv(dirname(__DIR__).'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], false);
$kernel->boot();

$entityManager = $kernel->getContainer()->get('doctrine')->getManager();

try {
    // Créer DB + Tables
    $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
    $classes = $entityManager->getMetadataFactory()->getAllMetadata();
    $schemaTool->createSchema($classes);
    
    echo "✅ Tables créées avec succès !";
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
?>
