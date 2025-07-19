<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
redirectIfNotLoggedIn();

if ($_SESSION['role'] !== 'admin') exit("Accès refusé");

$users = $pdo->query("SELECT * FROM utilisateurs")->fetchAll();

// Traitement ajout utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    if ($nom && $email && $password && in_array($role, ['admin', 'gestionnaire', 'visualiseur'])) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nom, $email, $hash, $role]);
        header("Location: users.php");
        exit;
    }
}

// Traitement modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $stmt = $pdo->prepare("UPDATE utilisateurs SET nom=?, email=?, role=? WHERE id=?");
    $stmt->execute([$_POST['nom'], $_POST['email'], $_POST['role'], $_POST['user_id']]);
    header("Location: users.php");
    exit;
}

// Traitement suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $stmt = $pdo->prepare("DELETE FROM utilisateurs WHERE id = ?");
    $stmt->execute([$_POST['user_id']]);
    header("Location: users.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Utilisateurs</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="p-6 bg-gray-100">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Gestion des utilisateurs</h1>
    <a href="../dashboard.php" class="text-blue-600 hover:underline">← Retour au tableau de bord</a>
  </div>

  <!-- Bouton Ajouter -->
  <button onclick="openModal('add-user')" class="bg-green-600 text-white px-4 py-2 rounded mb-4">➕ Ajouter un utilisateur</button>

  <!-- Tableau -->
  <table class="w-full border table-auto text-sm bg-white shadow rounded">
    <thead class="bg-gray-100">
      <tr>
        <th class="border p-2">Nom</th>
        <th class="border p-2">Email</th>
        <th class="border p-2">Rôle</th>
        <th class="border p-2">Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr>
        <td class="border p-2"><?= htmlspecialchars($u['nom']) ?></td>
        <td class="border p-2"><?= htmlspecialchars($u['email']) ?></td>
        <td class="border p-2"><?= htmlspecialchars($u['role']) ?></td>
        <td class="border p-2">
          <button onclick="openModal('edit-<?= $u['id'] ?>')" class="text-blue-600 hover:underline">Modifier</button>
          <button onclick="openModal('delete-<?= $u['id'] ?>')" class="text-red-600 hover:underline ml-2">Supprimer</button>
        </td>
      </tr>

      <!-- Modal Modifier -->
      <div id="modal-edit-<?= $u['id'] ?>" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
          <h3 class="text-lg font-semibold mb-4">Modifier l'utilisateur</h3>
          <form method="POST">
            <input type="hidden" name="update_user" value="1">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <input type="text" name="nom" value="<?= htmlspecialchars($u['nom']) ?>" required class="w-full border p-2 rounded mb-2">
            <input type="email" name="email" value="<?= htmlspecialchars($u['email']) ?>" required class="w-full border p-2 rounded mb-2">
            <select name="role" class="w-full border p-2 rounded mb-4" required>
              <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
              <option value="gestionnaire" <?= $u['role'] === 'gestionnaire' ? 'selected' : '' ?>>Gestionnaire</option>
              <option value="visualiseur" <?= $u['role'] === 'visualiseur' ? 'selected' : '' ?>>Visualiseur</option>
            </select>
            <div class="flex justify-end gap-2">
              <button type="button" onclick="closeModal('modal-edit-<?= $u['id'] ?>')" class="px-4 py-2 bg-gray-300 rounded">Annuler</button>
              <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Enregistrer</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal Supprimer -->
      <div id="modal-delete-<?= $u['id'] ?>" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50 flex items-center justify-center">
        <div class="bg-white p-6 rounded shadow-md w-full max-w-sm text-center">
          <h3 class="text-lg font-bold mb-4">Supprimer l'utilisateur ?</h3>
          <p class="mb-4 text-gray-700"><?= htmlspecialchars($u['nom']) ?> (<?= htmlspecialchars($u['email']) ?>)</p>
          <form method="POST" class="flex justify-center gap-4">
            <input type="hidden" name="delete_user" value="1">
            <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
            <button type="button" onclick="closeModal('modal-delete-<?= $u['id'] ?>')" class="px-4 py-2 bg-gray-300 rounded">Annuler</button>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded">Supprimer</button>
          </form>
        </div>
      </div>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- Modal Ajouter -->
  <div id="modal-add-user" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50 flex items-center justify-center">
    <div class="bg-white p-6 rounded shadow-md w-full max-w-md">
      <h3 class="text-lg font-semibold mb-4">Ajouter un nouvel utilisateur</h3>
      <form method="POST">
        <input type="hidden" name="create_user" value="1">
        <input type="text" name="nom" placeholder="Nom complet" required class="w-full border p-2 rounded mb-2">
        <input type="email" name="email" placeholder="Email" required class="w-full border p-2 rounded mb-2">
        <input type="password" name="password" placeholder="Mot de passe" required class="w-full border p-2 rounded mb-2">
        <select name="role" class="w-full border p-2 rounded mb-4" required>
          <option value="">-- Sélectionner un rôle --</option>
          <option value="admin">Admin</option>
          <option value="gestionnaire">Gestionnaire</option>
          <option value="visualiseur">Visualiseur</option>
        </select>
        <div class="flex justify-end gap-2">
          <button type="button" onclick="closeModal('modal-add-user')" class="px-4 py-2 bg-gray-300 rounded">Annuler</button>
          <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Créer</button>
        </div>
      </form>
    </div>
  </div>

  <!-- JS pour les modales -->
  <script>
    function openModal(id) {
      document.getElementById('modal-' + id).classList.remove('hidden');
    }
    function closeModal(id) {
      document.getElementById(id).classList.add('hidden');
    }
  </script>
</body>
</html>
