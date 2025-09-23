<?php
require('inc/essentials.php');
require('inc/db_config.php');
adminLogin();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - System Control</title>
    <?php require('inc/links.php'); ?>
</head>

<body class="bg-light">

    <?php require('inc/header.php'); ?>

    <!-- Main Content -->
    <div class="col-lg-10 ms-auto p-4 overflow-hidden">
        <h3 class="mb-4">System Control</h3>

        <!-- System Status Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-success">System Status</h5>
                        <h4 class="card-text text-success">
                            <i class="bi bi-check-circle-fill"></i> Online
                        </h4>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-info">Server Uptime</h5>
                        <h6 class="card-text" id="server-uptime">Loading...</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-warning">Last Backup</h5>
                        <h6 class="card-text" id="last-backup">Never</h6>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title text-primary">Active Sessions</h5>
                        <h4 class="card-text" id="active-sessions">0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Management -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-database"></i> Database Management</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Database Backup</h6>
                        <p class="text-muted">Create a backup of the entire database</p>
                        <button class="btn btn-success" onclick="createBackup()">
                            <i class="bi bi-download"></i> Create Backup
                        </button>
                    </div>
                    <div class="col-md-6">
                        <h6>Database Statistics</h6>
                        <div id="db-stats">
                            <small class="text-muted">Loading database statistics...</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Maintenance -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-tools"></i> System Maintenance</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Clear Cache</h6>
                        <p class="text-muted">Clear system cache and temporary files</p>
                        <button class="btn btn-warning" onclick="clearCache()">
                            <i class="bi bi-trash"></i> Clear Cache
                        </button>
                    </div>
                    <div class="col-md-4">
                        <h6>Clear Sessions</h6>
                        <p class="text-muted">Clear all active user sessions</p>
                        <button class="btn btn-warning" onclick="clearSessions()">
                            <i class="bi bi-person-x"></i> Clear Sessions
                        </button>
                    </div>
                    <div class="col-md-4">
                        <h6>System Logs</h6>
                        <p class="text-muted">View system error logs</p>
                        <button class="btn btn-info" onclick="viewLogs()">
                            <i class="bi bi-file-text"></i> View Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Emergency Controls -->
        <div class="card border-0 shadow-sm border-danger">
            <div class="card-header bg-danger text-white">
                <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Emergency Controls</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Maintenance Mode</h6>
                        <p class="text-muted">Put the website in maintenance mode</p>
                        <button class="btn btn-warning" onclick="toggleMaintenance()">
                            <i class="bi bi-tools"></i> <span id="maintenance-btn-text">Enable Maintenance</span>
                        </button>
                    </div>
                    <div class="col-md-6">
                        <h6>Emergency Shutdown</h6>
                        <p class="text-muted">Shutdown the system (requires backup)</p>
                        <button class="btn btn-danger" onclick="emergencyShutdown()">
                            <i class="bi bi-power"></i> Emergency Shutdown
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Progress Modal -->
    <div class="modal fade" id="backup-modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Creating Backup</h5>
                </div>
                <div class="modal-body text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p>Please wait while we create a backup of your database...</p>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%" id="backup-progress"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- System Logs Modal -->
    <div class="modal fade" id="logs-modal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">System Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <pre id="logs-content" style="max-height: 400px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 5px;">
Loading logs...
                    </pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="downloadLogs()">Download Logs</button>
                </div>
            </div>
        </div>
    </div>

    <?php require('inc/scripts.php'); ?>
    <script src="scripts/system_control.js"></script>

</body>

</html>