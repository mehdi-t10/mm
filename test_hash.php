<?php
$hash = '$2y$10$8jvc3RiY3uLQJvtWGn2qXerFLA3NP7kg8Iio.HtUZPvIrKbg4RiwS';
$password = 'az2026';

echo "Testing password_verify:\n";
echo "Hash: " . $hash . "\n";
echo "Password: " . $password . "\n";
echo "Result: " . (password_verify($password, $hash) ? "MATCH" : "NO MATCH") . "\n";

// Also test what the correct hash should be for az2026
$correct_hash = password_hash('az2026', PASSWORD_DEFAULT);
echo "\nCorrect hash for az2026: " . $correct_hash . "\n";
echo "Does correct hash match? " . (password_verify('az2026', $correct_hash) ? "YES" : "NO") . "\n";
?>

