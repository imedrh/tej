<?php
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$nom = $_SESSION['nom'] ?? '';
$role = $_SESSION['role'] ?? 'visualiseur';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Tableau de bord</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

  <!-- En-tête -->
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-700">Bienvenue, <?= htmlspecialchars($nom) ?> (<?= $role ?>)</h1>
    <a href="/auth/logout.php" class="text-red-600 hover:underline">Déconnexion</a>
  </div>

  <!-- Navigation filtrée -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

    <!-- Accessible à tous les rôles -->
    <a href="entreprises.php" class="block p-4 bg-white rounded shadow hover:bg-blue-50">
      🏢 Gestion des entreprises
    </a>

    <a href="/etablissements/index.php" class="block p-4 bg-white rounded shadow hover:bg-blue-50">
      🏬 Gestion des établissements
    </a>

    <a href="/exercices/index.php" class="block p-4 bg-white rounded shadow hover:bg-blue-50">
      📅 Gestion des exercices
    </a>

    <a href="/certificats/index.php" class="block p-4 bg-white rounded shadow hover:bg-blue-50">
      🧾 Certificats de retenue
    </a>

    <a href="/operations/index.php" class="block p-4 bg-white rounded shadow hover:bg-blue-50">
      💼 Opérations liées
    </a>

    <a href="/imports/xml_upload.php" class="block p-4 bg-white rounded shadow hover:bg-blue-50">
      📤 Import XML et validation
    </a>

    <a href="/historique/index.php" class="block p-4 bg-white rounded shadow hover:bg-blue-50">
      🕘 Historique des modifications
    </a>

    <!-- Visible uniquement pour les admins -->
    <?php if ($role === 'admin'): ?>
      <a href="auth/users.php" class="block p-4 bg-white rounded shadow hover:bg-blue-50 border border-blue-500">
        👥 Gestion des utilisateurs (admin)
      </a>
    <?php endif; ?>

  </div>

</body>
</html>

