<?php
require_once __DIR__ . '/../includes/simple_crud.php';
simple_crud([
  'table' => 'growth_stages', 'title' => 'Giai đoạn sinh trưởng', 'active' => 'stages', 'slug' => true, 'self' => 'admin/stages.php',
  'fields' => ['name' => ['label' => 'Tên giai đoạn', 'required' => true], 'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
               'sort_order' => ['label' => 'Thứ tự', 'type' => 'number']],
  'list' => ['name' => 'Tên', 'slug' => 'Slug', 'sort_order' => 'Thứ tự'],
  'order' => 'sort_order, name',
]);
