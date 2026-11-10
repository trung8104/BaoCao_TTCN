<?php
require_once __DIR__ . '/../includes/simple_crud.php';
simple_crud([
  'table' => 'categories', 'title' => 'Chuyên mục', 'active' => 'categories', 'slug' => true, 'self' => 'admin/categories.php',
  'fields' => ['name' => ['label' => 'Tên chuyên mục', 'required' => true], 'description' => ['label' => 'Mô tả', 'type' => 'textarea'],
               'sort_order' => ['label' => 'Thứ tự hiển thị', 'type' => 'number']],
  'list' => ['name' => 'Tên', 'slug' => 'Slug', 'sort_order' => 'Thứ tự'],
  'order' => 'sort_order, name', 'in_use_msg' => 'Không xóa được: vẫn còn bài viết thuộc chuyên mục này.',
]);
