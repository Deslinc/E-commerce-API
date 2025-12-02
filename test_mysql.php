<?php
echo "Testing MySQL Connection for MySQL80 Service...\n";
echo "==============================================\n\n";

// Common configurations to try
$configs = [
    // Default MySQL 8.0 installations often use these
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => ''],
    ['host' => 'localhost', 'port' => 3306, 'user' => 'root', 'pass' => ''],
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => 'root'],
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => 'password'],
    // MySQL 8.0 sometimes uses auth_socket on Windows
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => 'Admin@123'],
    ['host' => '127.0.0.1', 'port' => 3306, 'user' => 'root', 'pass' => 'Password123'],
];

$connected = false;

foreach ($configs as $config) {
    $dsn = "mysql:host={$config['host']};port={$config['port']}";
    
    echo "Trying: {$config['user']}@{$config['host']}:{$config['port']} ";
    echo "Password: " . (empty($config['pass']) ? '(empty)' : '***') . "\n";
    
    try {
        $pdo = new PDO($dsn, $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Get version and details
        $version = $pdo->query('SELECT VERSION()')->fetchColumn();
        
        echo "? SUCCESS!\n";
        echo "   MySQL Version: $version\n";
        
        // List databases
        echo "   Available Databases:\n";
        $stmt = $pdo->query('SHOW DATABASES');
        while ($db = $stmt->fetch(PDO::FETCH_COLUMN)) {
            echo "   - $db\n";
        }
        
        // Check authentication method
        $auth = $pdo->query("SELECT user, host, plugin FROM mysql.user WHERE user = 'root'")->fetchAll(PDO::FETCH_ASSOC);
        echo "\n   Authentication Methods for root:\n";
        foreach ($auth as $row) {
            echo "   - {$row['user']}@{$row['host']}: {$row['plugin']}\n";
        }
        
        $connected = true;
        break;
        
    } catch (Exception $e) {
        echo "   ? Failed: " . $e->getMessage() . "\n\n";
    }
}

if (!$connected) {
    echo "\n??  All connections failed. You may need to:\n";
    echo "   1. Reset MySQL password\n";
    echo "   2. Check if MySQL 8.0 uses caching_sha2_password plugin\n";
    echo "   3. Try connecting via MySQL Workbench or command line first\n";
}
