<?php
// Sao chép file này thành config.php rồi sửa thông tin. KHÔNG commit config.php lên Git.
return [
    'db_host' => 'localhost',
    'db_name' => 'tomato_guide',
    'db_user' => 'root',
    'db_pass' => '',
    'base_url' => '/tomato-guide',   // đổi theo thư mục htdocs của bạn
    'upload_dir' => __DIR__ . '/../uploads/',
    'upload_max_bytes' => 2 * 1024 * 1024,
];
