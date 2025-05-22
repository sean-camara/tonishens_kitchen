<?php
require_once __DIR__ . '/../connect.php';

// Fetch all admins
$query  = "SELECT id, fname, lname, email, role, profile_pic FROM users_admin ORDER BY id DESC";
$result = mysqli_query($conn, $query);
?>

<table>
  <thead>
    <tr>
      <th>Profile</th>
      <th>Full Name</th>
      <th>Email</th>
      <th>Role</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
    <tr>
      <td>
        <?php if (!empty($row['profile_pic'])): ?>
          <img src="data:image/jpeg;base64,<?= base64_encode($row['profile_pic']) ?>" class="profile-pic" alt="Avatar" />
        <?php else: ?>
          <img src="images/default-avatar.png" class="profile-pic" alt="Avatar" />
        <?php endif; ?>
      </td>
      <td><?= htmlspecialchars($row['fname'] . ' ' . $row['lname']) ?></td>
      <td><?= htmlspecialchars($row['email']) ?></td>
      <td><?= htmlspecialchars($row['role']) ?></td>
      <td>
        <button
          class="editBtn"
          data-id="<?= $row['id'] ?>"
          data-fname="<?= htmlspecialchars($row['fname']) ?>"
          data-lname="<?= htmlspecialchars($row['lname']) ?>"
          data-email="<?= htmlspecialchars($row['email']) ?>"
        >Edit</button>

        <form method="POST" action="includes/admin-action.php" style="display:inline;">
          <input type="hidden" name="id" value="<?= $row['id'] ?>">
          <button
            type="submit"
            name="delete_admin"
            onclick="return confirm('Delete this admin?')"
          >Delete</button>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </tbody>
</table>
