<?php
require('../inc/essentials.php');
require('../inc/db_config.php');
adminLogin();

// Get system statistics
if (isset($_POST['get_system_stats'])) {
    $stats = array();
    
    // Count active sessions (simplified - count from users table where last_activity is recent)
    $q = "SELECT COUNT(*) as count FROM user_cred WHERE datentime > DATE_SUB(NOW(), INTERVAL 30 MINUTE)";
    $res = mysqli_query($con, $q);
    $row = mysqli_fetch_assoc($res);
    $stats['active_sessions'] = $row['count'];
    
    // Check last backup (from a hypothetical backups table or file)
    $backup_file = '../backups/last_backup.txt';
    if (file_exists($backup_file)) {
        $stats['last_backup'] = date('Y-m-d H:i:s', filemtime($backup_file));
    } else {
        $stats['last_backup'] = 'Never';
    }
    
    echo json_encode($stats);
    exit;
}

// Get database statistics
if (isset($_POST['get_db_stats'])) {
    $tables = ['user_cred', 'rooms', 'room_features', 'room_facilities', 'user_queries', 'settings'];
    $html = '';
    
    foreach ($tables as $table) {
        $q = "SELECT COUNT(*) as count FROM $table";
        $res = mysqli_query($con, $q);
        if ($res) {
            $row = mysqli_fetch_assoc($res);
            $count = $row['count'];
            $html .= "<div class='d-flex justify-content-between'>";
            $html .= "<span>" . ucfirst(str_replace('_', ' ', $table)) . ":</span>";
            $html .= "<span class='fw-bold'>$count</span>";
            $html .= "</div>";
        } else {
            // Skip tables that don't exist
            continue;
        }
    }
    
    echo $html;
    exit;
}

// Create database backup
if (isset($_POST['create_backup'])) {
    try {
        $backup_dir = '../backups';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        $backup_file = $backup_dir . '/backup_' . date('Y-m-d_H-i-s') . '.sql';
        
        // Get database credentials
        $host = 'localhost';
        $username = 'root';
        $password = '';
        $database = 'hbwebsite';
        
        // Create mysqldump command
        $command = "mysqldump --host=$host --user=$username --password=$password $database > $backup_file";
        
        // Execute backup (Note: This is a simplified version)
        // In production, you might want to use PHP's mysqli or PDO to export data
        $tables = ['user_cred', 'admin_cred', 'rooms', 'room_features', 'room_facilities', 'user_queries', 'carousel', 'contact_details', 'settings'];
        
        $backup_content = "-- Database Backup Created: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach ($tables as $table) {
            // Get table structure
            $q = "SHOW CREATE TABLE $table";
            $res = mysqli_query($con, $q);
            if ($res && $row = mysqli_fetch_assoc($res)) {
                $backup_content .= "-- Table structure for $table\n";
                $backup_content .= "DROP TABLE IF EXISTS `$table`;\n";
                $backup_content .= $row['Create Table'] . ";\n\n";
                
                // Get table data
                $q = "SELECT * FROM $table";
                $res = mysqli_query($con, $q);
                if ($res && mysqli_num_rows($res) > 0) {
                    $backup_content .= "-- Data for table $table\n";
                    while ($row = mysqli_fetch_assoc($res)) {
                        $values = array();
                        foreach ($row as $value) {
                            $values[] = "'" . mysqli_real_escape_string($con, $value) . "'";
                        }
                        $backup_content .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                    }
                    $backup_content .= "\n";
                }
            }
        }
        
        file_put_contents($backup_file, $backup_content);
        file_put_contents('../backups/last_backup.txt', date('Y-m-d H:i:s'));
        
        echo "success";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    exit;
}

// Clear cache
if (isset($_POST['clear_cache'])) {
    try {
        // Clear PHP opcache if available
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Clear any custom cache files
        $cache_dir = '../cache';
        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
        
        echo 1;
    } catch (Exception $e) {
        echo 0;
    }
    exit;
}

// Clear all sessions
if (isset($_POST['clear_sessions'])) {
    try {
        // Clear session files
        $session_path = session_save_path();
        if (empty($session_path)) {
            $session_path = sys_get_temp_dir();
        }
        
        $files = glob($session_path . '/sess_*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        echo 1;
    } catch (Exception $e) {
        echo 0;
    }
    exit;
}

// Get system logs
if (isset($_POST['get_logs'])) {
    $log_content = '';
    
    // Check PHP error log
    $php_log = ini_get('error_log');
    if ($php_log && file_exists($php_log)) {
        $log_content .= "=== PHP Error Log ===\n";
        $log_content .= tail($php_log, 50);
        $log_content .= "\n\n";
    }
    
    // Check Apache error log (common locations)
    $apache_logs = [
        'C:/xampp/apache/logs/error.log',
        '/var/log/apache2/error.log',
        '/var/log/httpd/error_log'
    ];
    
    foreach ($apache_logs as $log_file) {
        if (file_exists($log_file)) {
            $log_content .= "=== Apache Error Log ===\n";
            $log_content .= tail($log_file, 30);
            break;
        }
    }
    
    if (empty($log_content)) {
        $log_content = "No log files found or accessible.";
    }
    
    echo $log_content;
    exit;
}

// Download logs
if (isset($_GET['download_logs'])) {
    $log_content = '';
    
    // Collect all available logs
    $php_log = ini_get('error_log');
    if ($php_log && file_exists($php_log)) {
        $log_content .= "=== PHP Error Log ===\n";
        $log_content .= file_get_contents($php_log);
        $log_content .= "\n\n";
    }
    
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="system_logs_' . date('Y-m-d_H-i-s') . '.txt"');
    echo $log_content;
    exit;
}

// Check maintenance mode
if (isset($_POST['check_maintenance'])) {
    $maintenance_file = '../maintenance.txt';
    echo file_exists($maintenance_file) ? '1' : '0';
    exit;
}

// Toggle maintenance mode
if (isset($_POST['toggle_maintenance'])) {
    $maintenance_file = '../maintenance.txt';
    $mode = $_POST['mode'];
    
    if ($mode == '1') {
        // Enable maintenance
        file_put_contents($maintenance_file, date('Y-m-d H:i:s'));
        echo 1;
    } else {
        // Disable maintenance
        if (file_exists($maintenance_file)) {
            unlink($maintenance_file);
        }
        echo 1;
    }
    exit;
}

// Emergency shutdown
if (isset($_POST['emergency_shutdown'])) {
    try {
        $results = array();
        
        // 1. Create backup
        $backup_dir = '../backups';
        if (!is_dir($backup_dir)) {
            mkdir($backup_dir, 0755, true);
        }
        
        $backup_file = $backup_dir . '/emergency_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $backup_content = "-- Emergency Backup Created: " . date('Y-m-d H:i:s') . "\n\n";
        
        $tables = ['user_cred', 'admin_cred', 'rooms', 'room_features', 'room_facilities', 'user_queries', 'carousel'];
        foreach ($tables as $table) {
            $q = "SELECT * FROM $table";
            $res = mysqli_query($con, $q);
            if ($res && mysqli_num_rows($res) > 0) {
                $backup_content .= "-- Data for table $table\n";
                while ($row = mysqli_fetch_assoc($res)) {
                    $values = array();
                    foreach ($row as $value) {
                        $values[] = "'" . mysqli_real_escape_string($con, $value) . "'";
                    }
                    $backup_content .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
                }
                $backup_content .= "\n";
            }
        }
        
        file_put_contents($backup_file, $backup_content);
        $results[] = "Backup created";
        
        // 2. Clear sessions
        $session_path = session_save_path();
        if (empty($session_path)) {
            $session_path = sys_get_temp_dir();
        }
        
        $files = glob($session_path . '/sess_*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        $results[] = "Sessions cleared";
        
        // 3. Enable maintenance mode
        $maintenance_file = '../maintenance.txt';
        file_put_contents($maintenance_file, 'Emergency shutdown: ' . date('Y-m-d H:i:s'));
        $results[] = "Maintenance mode enabled";
        
        echo "success: " . implode(", ", $results);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    exit;
}

// Helper function to get last N lines of a file
function tail($file, $lines = 50) {
    $handle = fopen($file, "r");
    if (!$handle) return '';
    
    $linecounter = $lines;
    $pos = -2;
    $beginning = false;
    $text = array();
    
    while ($linecounter > 0) {
        $t = " ";
        while ($t != "\n") {
            if (fseek($handle, $pos, SEEK_END) == -1) {
                $beginning = true;
                break;
            }
            $t = fgetc($handle);
            $pos--;
        }
        $linecounter--;
        if ($beginning) {
            rewind($handle);
        }
        $text[$lines - $linecounter - 1] = fgets($handle);
        if ($beginning) break;
    }
    fclose($handle);
    return implode("", array_reverse($text));
}
?>