<?php
require_once __DIR__ . '/../includes/simple_crud.php';
simple_crud([
  'table' => 'symptoms', 'title' => 'Triệu chứng', 'active' => 'symptoms', 'slug' => false, 'self' => 'admin/symptoms.php', 'unique_name' => true,
  'fields' => ['name' => ['label' => 'Tên triệu chứng', 'required' => true],
               'body_part' => ['label' => 'Bộ phận biểu hiện', 'type' => 'select',
                  'options' => ['leaf' => 'Lá', 'stem' => 'Thân', 'flower' => 'Hoa', 'fruit' => 'Quả', 'root' => 'Rễ', 'whole_plant' => 'Toàn cây']],
               'description' => ['label' => 'Mô tả', 'type' => 'textarea']],
  'list' => ['name' => 'Tên', 'body_part' => 'Bộ phận'],
  'order' => "FIELD(body_part,'leaf','stem','flower','fruit','root','whole_plant'), name",
]);
