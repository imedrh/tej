<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

if (!in_array($_SESSION['role'], ['admin', 'gestionnaire'])) {
    exit("Accès refusé");
}

// Options pour les menus déroulants
$cle_options = str_split('ABCDEFGHJKLMNPQRSTVWXYZ'); // exclut I et O
$categorie_options = ['A', 'B', 'D', 'N', 'P'];
$tva_options = ['C', 'M', 'N', 'P'];

$entreprises = $pdo->query("SELECT * FROM entreprises")->fetchAll();

// Création
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_entreprise'])) {
    $matricule_complet = str_pad($_POST['matricule'], 7, '0', STR_PAD_LEFT)
                       . strtoupper($_POST['cle'])
                       . strtoupper($_POST['categorie'])
                       . strtoupper($_POST['tva'])
                       . strtoupper($_POST['serie']);

    if (!preg_match('/^[0-9]{7}[ABCDEFGHJKLMNPQRSTVWXYZ][ABDNP][CMNP]000$/', $matricule_complet)) {
        die("❌ Matricule fiscal invalide");
    }

    $stmt = $pdo->prepare("INSERT INTO entreprises (matricule, cle, categorie, tva, serie, raison_sociale, activite, ville, code_postal) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['matricule'], $_POST['cle'], $_POST['categorie'],
        $_POST['tva'], $_POST['serie'],
        $_POST['raison_sociale'], $_POST['activite'],
        $_POST['ville'], $_POST['code_postal'],
    ]);
	$_SESSION['flash'] = "✅ Entreprise ajoutée avec succès.";
    header("Location: entreprises.php");
    exit;
	

}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Entreprises</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<?php if (isset($_SESSION['flash'])): ?>
  <div class="mb-4 bg-green-100 border border-green-400 text-green-800 px-4 py-2 rounded">
    <?= $_SESSION['flash'] ?>
  </div>
  <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gestion des entreprises</h1>
        <a href="dashboard.php" class="text-blue-600 hover:underline">← Retour</a>
    </div>

    <button onclick="document.getElementById('modal-create').classList.remove('hidden')" class="mb-4 bg-green-600 text-white px-4 py-2 rounded">➕ Ajouter une entreprise</button>

    <table class="w-full border text-sm bg-white shadow rounded">
        <thead class="bg-gray-100">
            <tr>
                <th class="border p-2">Matricule complet</th>
                <th class="border p-2">Raison sociale</th>
                <th class="border p-2">Activité</th>
                <th class="border p-2">Ville</th>
                <th class="border p-2">Code postal</th>
                <th class="border p-2">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($entreprises as $e): ?>
                <tr>
                    <td class="border p-2">
                        <?= str_pad($e['matricule'], 7, '0', STR_PAD_LEFT) . $e['cle'] . $e['categorie'] . $e['tva'] . $e['serie'] ?>
                    </td>
                    <td class="border p-2"><?= htmlspecialchars($e['raison_sociale']) ?></td>
                    <td class="border p-2"><?= htmlspecialchars($e['activite']) ?></td>
                    <td class="border p-2"><?= htmlspecialchars($e['ville']) ?></td>
                    <td class="border p-2"><?= htmlspecialchars($e['code_postal']) ?></td>
                    <td class="border p-2">
                        <form method="POST" style="display:inline">
                            <input type="hidden" name="entreprise_id" value="<?= $e['id'] ?>">
                            <input type="hidden" name="delete_entreprise" value="1">
                            <button type="submit" class="text-red-600 hover:underline">Supprimer</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Modal de création amélioré -->
<div id="modal-create" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-white w-full max-w-4xl rounded-lg shadow-lg overflow-hidden">
    <div class="flex justify-between items-center bg-gray-100 px-6 py-4 border-b">
      <h2 class="text-xl font-semibold text-gray-800">Ajouter une entreprise</h2>
      <button onclick="document.getElementById('modal-create').classList.add('hidden')" class="text-gray-500 hover:text-red-600 text-2xl">&times;</button>
    </div>
    
    <form method="POST" id="form-entreprise" class="px-6 py-4 space-y-4">
      <input type="hidden" name="create_entreprise" value="1">

      <!-- Section Matricule -->
      <div>
        <h3 class="text-md font-medium text-gray-700 mb-2">Identité fiscale</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
          <div>
            <label class="text-sm text-gray-700">Matricule</label>
            <input name="matricule" class="form-input w-full" maxlength="7" required>
          </div>
          <div>
            <label class="text-sm text-gray-700">Clé</label>
            <select name="cle" class="form-input w-full" required>
              <option value="">--</option>
              <?php foreach ($cle_options as $option): ?>
                <option value="<?= $option ?>"><?= $option ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="text-sm text-gray-700">Catégorie</label>
            <select name="categorie" class="form-input w-full" required>
              <option value="">--</option>
              <?php foreach ($categorie_options as $option): ?>
                <option value="<?= $option ?>"><?= $option ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="text-sm text-gray-700">TVA</label>
            <select name="tva" class="form-input w-full" required>
              <option value="">--</option>
              <?php foreach ($tva_options as $option): ?>
                <option value="<?= $option ?>"><?= $option ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label class="text-sm text-gray-700">Série</label>
            <input name="serie" value="000" class="form-input w-full" maxlength="3" required>
          </div>
        </div>
      </div>

      <!-- Section Infos générales -->
      <div>
        <h3 class="text-md font-medium text-gray-700 mb-2">Informations générales</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <input name="raison_sociale" class="form-input w-full" placeholder="Raison sociale" required>
          <input name="activite" class="form-input w-full" placeholder="Activité" required>
          <input name="ville" class="form-input w-full" placeholder="Ville">
          <input name="code_postal" class="form-input w-full" placeholder="Code postal">
        </div>
      </div>

      <!-- Actions -->
      <div class="flex justify-end space-x-2 pt-4 border-t">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">✅ Enregistrer</button>
        <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Annuler</button>
      </div>
    </form>
  </div>
</div>
<!-- Modal d'édition -->
<div id="modal-edit" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
  <div class="bg-white w-full max-w-4xl rounded-lg shadow-lg overflow-hidden">
    <div class="flex justify-between items-center bg-gray-100 px-6 py-4 border-b">
      <h2 class="text-xl font-semibold text-gray-800">Modifier l’entreprise</h2>
      <button onclick="closeEditModal()" class="text-gray-500 hover:text-red-600 text-2xl">&times;</button>
    </div>

    <form method="POST" id="form-edit" class="px-6 py-4 space-y-4">
      <input type="hidden" name="update_entreprise" value="1">
      <input type="hidden" name="entreprise_id" id="edit-id">

      <!-- Section matricule -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <input name="matricule" id="edit-matricule" class="form-input" required>
        <select name="cle" id="edit-cle" class="form-input" required>
          <option value="">--</option>
          <?php foreach ($cle_options as $option): ?>
            <option value="<?= $option ?>"><?= $option ?></option>
          <?php endforeach; ?>
        </select>
        <select name="categorie" id="edit-categorie" class="form-input" required>
          <option value="">--</option>
          <?php foreach ($categorie_options as $option): ?>
            <option value="<?= $option ?>"><?= $option ?></option>
          <?php endforeach; ?>
        </select>
        <select name="tva" id="edit-tva" class="form-input" required>
          <option value="">--</option>
          <?php foreach ($tva_options as $option): ?>
            <option value="<?= $option ?>"><?= $option ?></option>
          <?php endforeach; ?>
        </select>
        <input name="serie" id="edit-serie" class="form-input" required>
      </div>

      <!-- Raison sociale et infos -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <input name="raison_sociale" id="edit-raison_sociale" class="form-input" placeholder="Raison sociale">
        <input name="activite" id="edit-activite" class="form-input" placeholder="Activité">
        <input name="ville" id="edit-ville" class="form-input" placeholder="Ville">
        <input name="code_postal" id="edit-code_postal" class="form-input" placeholder="Code postal">
      </div>

      <div class="flex justify-end space-x-2 pt-4 border-t">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">💾 Enregistrer</button>
        <button type="button" onclick="closeEditModal()" class="bg-gray-400 text-white px-4 py-2 rounded hover:bg-gray-500">Annuler</button>
      </div>
    </form>
  </div>
</div>


    <script>
    document.getElementById("form-entreprise").addEventListener("submit", function (e) {
  const m = document.querySelector("input[name='matricule']").value.padStart(7, '0');
  const cle = document.querySelector("select[name='cle']").value.toUpperCase();
  const cat = document.querySelector("select[name='categorie']").value.toUpperCase();
  const tva = document.querySelector("select[name='tva']").value.toUpperCase();
  const serie = document.querySelector("input[name='serie']").value;
  const full = m + cle + cat + tva + serie;
  const regex = /^[0-9]{7}[ABCDEFGHJKLMNPQRSTVWXYZ][ABDNP][CMNP]000$/;
  if (!regex.test(full)) {
    e.preventDefault();
    alert("❌ Matricule fiscal invalide !");
  }
});
function openEditModal(entreprise) {
  document.getElementById('edit-id').value = entreprise.id;
  document.getElementById('edit-matricule').value = entreprise.matricule;
  document.getElementById('edit-cle').value = entreprise.cle;
  document.getElementById('edit-categorie').value = entreprise.categorie;
  document.getElementById('edit-tva').value = entreprise.tva;
  document.getElementById('edit-serie').value = entreprise.serie;
  document.getElementById('edit-raison_sociale').value = entreprise.raison_sociale;
  document.getElementById('edit-activite').value = entreprise.activite;
  document.getElementById('edit-ville').value = entreprise.ville;
  document.getElementById('edit-code_postal').value = entreprise.code_postal;

  document.getElementById('modal-edit').classList.remove('hidden');
}

function closeEditModal() {
  document.getElementById('modal-edit').classList.add('hidden');
}

    </script>
	

</body>
</html>
