<?php
require __DIR__ . '/config.php';

// Fetch all users
$stmt = $pdo->query("SELECT id, name, age, status FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en" dir="ltr">
<head>
  <meta charset="utf-8">
  <title>Users Manager</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

  <h1>Users</h1>

  <!-- One-line form -->
  <form id="add-form" class="form-row">
    <label>Name
      <input type="text" name="name" required placeholder="Name">
    </label>
    <label>Age
      <input type="number" name="age" required min="0" max="150" placeholder="Age">
    </label>
    <input type="submit" value="Submit">
    <small id="form-msg" class="muted"></small>
  </form>

  <table id="users-table" class="grid-table">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Age</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($users as $u): ?>
      <tr data-id="<?= (int)$u['id'] ?>">
        <td><?= (int)$u['id'] ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= (int)$u['age'] ?></td>
        <td class="status-cell"><?= (int)$u['status'] ?></td>
        <td><button class="toggel-btn">Toggel</button></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

<script>
// Add a new user (no page reload)
const form = document.getElementById('add-form');
const msg  = document.getElementById('form-msg');
const tbody = document.querySelector('#users-table tbody');

form.addEventListener('submit', async (e) => {
  e.preventDefault();
  msg.textContent = 'Adding...';
  const fd = new FormData(form);
  try {
    const res = await fetch('api/insert.php', { method: 'POST', body: fd });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Insert failed');

    // Append the new row (status starts at 0)
    const tr = document.createElement('tr');
    tr.dataset.id = data.id;
    tr.innerHTML = `
      <td>${data.id}</td>
      <td>${escapeHtml(data.name)}</td>
      <td>${data.age}</td>
      <td class="status-cell">0</td>
      <td><button class="toggel-btn">Toggel</button></td>
    `;
    tbody.appendChild(tr);
    form.reset();
    msg.textContent = 'Added ✔';
  } catch (err) {
    msg.textContent = 'Error: ' + err.message;
  } finally {
    setTimeout(() => msg.textContent = '', 2000);
  }
});

// Toggle status between 0 and 1
document.addEventListener('click', async (e) => {
  if (!e.target.classList.contains('toggel-btn')) return;
  const tr = e.target.closest('tr');
  const id = tr.dataset.id;
  const cell = tr.querySelector('.status-cell');
  const current = Number(cell.textContent.trim());

  try {
    const res = await fetch('api/toggel.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    });
    const data = await res.json();
    if (!res.ok) throw new Error(data.error || 'Toggel failed');

    // Server is the source of truth
    cell.textContent = Number(data.status);
  } catch (err) {
    // Optional: visual bounce if you want
    cell.textContent = current;
    alert('Could not toggel: ' + err.message);
  }
});

// Prevent HTML injection in the Name cell
function escapeHtml(s) {
  const div = document.createElement('div');
  div.textContent = s;
  return div.innerHTML;
}
</script>
</body>
</html>
