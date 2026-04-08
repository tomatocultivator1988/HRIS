<?php
$response = file_get_contents('http://localhost/HRIS/login');
echo "First 500 chars:\n";
echo substr($response, 0, 500);
echo "\n\nContains 'login': " . (stripos($response, 'login') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'password': " . (stripos($response, 'password') !== false ? 'YES' : 'NO') . "\n";
echo "Contains 'CFAS': " . (stripos($response, 'CFAS') !== false ? 'YES' : 'NO') . "\n";
