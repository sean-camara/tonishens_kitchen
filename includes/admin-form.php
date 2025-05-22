<div id="userFormModal" class="modal">
  <div class="modal-content">
    <span class="closeBtn">&times;</span>
    <form action="includes/admin-action.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" id="user_id">

      <label>First Name:</label>
      <input type="text" name="fname" id="fname" required>

      <label>Last Name:</label>
      <input type="text" name="lname" id="lname" required>

      <label>Email:</label>
      <input type="email" name="email" id="email" required>

      <label>Password:</label>
      <input type="password" name="password" id="password" required>

      <label>Upload Profile Picture:</label>
      <input type="file" name="profile_pic" id="profile_pic" required>

      <input type="hidden" name="role" value="admin">

      <button type="submit" name="save_admin">Save</button>
    </form>
  </div>
</div>
