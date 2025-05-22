<?php
require 'connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inventory Management</title>
  <link rel="stylesheet" href="admin-inventory-style.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
</head>
<body>

  <button id="back-btn">← Back</button>

  <div class="container animate__animated animate__fadeIn">
    <h1 class="animate__animated animate__fadeInDown">Inventory Management</h1>

    <!-- Filters -->
    <div class="filters card animate__animated animate__fadeInUp">
      <select id="filter-category">
        <option value="">All Categories</option>
      </select>
      <input type="text" id="search-box" placeholder="Search by name">
      <button class="orange-btn" id="show-add-form">+ Add Ingredient</button>
    </div>

    <!-- Add Category Form -->
    <div class="card animate__animated" id="add-category-form" hidden>
      <h2 class="section-title">Add New Category</h2>
      <div class="form-group">
        <label>Category Name</label>
        <input type="text" id="new-category-name" placeholder="e.g. Meat, Vegetables">
      </div>
      <button class="blue-btn" id="add-category-save">Save Category</button>
      <button class="delete-btn" id="add-category-cancel">Cancel</button>
    </div>

    <!-- Button to show Add Category Form -->
    <div class="filters card animate__animated animate__fadeInUp" style="margin-top: 20px;">
      <button class="orange-btn" id="show-add-category-form">+ Add Category</button>
    </div>

    <!-- Add Ingredient Form -->
    <div class="card animate__animated" id="add-form" hidden>
      <h2 class="section-title">Add New Ingredient</h2>
      <div class="form-group">
        <label>Name</label>
        <input type="text" id="new-name" placeholder="e.g. Chicken Breast">
      </div>
      <div class="form-group">
        <label>Category</label>
        <select id="new-category"></select>
      </div>
      <div class="form-group">
        <label>Unit</label>
        <input type="text" id="new-unit" placeholder="e.g. kg, pcs">
      </div>
      <div class="form-group">
        <label>Starting Quantity</label>
        <input type="number" step="0.01" id="new-quantity" placeholder="e.g. 10">
      </div>
      <div class="form-group">
        <label>Reorder Level</label>
        <input type="number" step="0.01" id="new-reorder" placeholder="e.g. 5">
      </div>
      <div class="form-group">
        <label>Cost per Unit</label>
        <input type="number" step="0.01" id="new-cost" placeholder="e.g. 120.00">
      </div>
      <button class="blue-btn" id="add-save">Save Ingredient</button>
      <button class="delete-btn" id="add-cancel">Cancel</button>
    </div>

    <!-- Inventory Table -->
    <div class="card animate__animated animate__fadeInUp">
      <table id="inventory-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Category</th>
            <th>Unit</th>
            <th>Quantity</th>
            <th>Reorder Level</th>
            <th>Cost/Unit</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <script src="admin-inventory.js"></script>
</body>
</html>
