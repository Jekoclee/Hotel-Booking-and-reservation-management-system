let maintenanceMode = false;

// Load system info on page load
window.onload = function() {
    getSystemStats();
    getDbStats();
    checkMaintenanceMode();
    updateServerUptime();
    
    // Update uptime every minute
    setInterval(updateServerUptime, 60000);
}

function getSystemStats() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/system_control.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status == 200) {
            let stats = JSON.parse(this.responseText);
            document.getElementById('active-sessions').textContent = stats.active_sessions;
            document.getElementById('last-backup').textContent = stats.last_backup || 'Never';
        }
    }
    
    xhr.send('get_system_stats=1');
}

function getDbStats() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/system_control.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status == 200) {
            document.getElementById('db-stats').innerHTML = this.responseText;
        }
    }
    
    xhr.send('get_db_stats=1');
}

function updateServerUptime() {
    // Simple uptime calculation (this is a basic implementation)
    let startTime = localStorage.getItem('serverStartTime');
    if (!startTime) {
        startTime = Date.now();
        localStorage.setItem('serverStartTime', startTime);
    }
    
    let uptime = Date.now() - parseInt(startTime);
    let days = Math.floor(uptime / (1000 * 60 * 60 * 24));
    let hours = Math.floor((uptime % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    let minutes = Math.floor((uptime % (1000 * 60 * 60)) / (1000 * 60));
    
    document.getElementById('server-uptime').textContent = `${days}d ${hours}h ${minutes}m`;
}

function createBackup() {
    if (confirm('Are you sure you want to create a database backup? This may take a few minutes.')) {
        let modal = new bootstrap.Modal(document.getElementById('backup-modal'));
        modal.show();
        
        let progress = 0;
        let progressBar = document.getElementById('backup-progress');
        
        // Simulate progress
        let progressInterval = setInterval(() => {
            progress += Math.random() * 20;
            if (progress > 90) progress = 90;
            progressBar.style.width = progress + '%';
        }, 500);
        
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/system_control.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            clearInterval(progressInterval);
            progressBar.style.width = '100%';
            
            setTimeout(() => {
                modal.hide();
                if (this.responseText.includes('success')) {
                    alert('Database backup created successfully!');
                    getSystemStats(); // Refresh stats
                } else {
                    alert('Failed to create backup: ' + this.responseText);
                }
            }, 1000);
        }
        
        xhr.send('create_backup=1');
    }
}

function clearCache() {
    if (confirm('Are you sure you want to clear the system cache?')) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/system_control.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (this.status == 200) {
                if (this.responseText == 1) {
                    alert('Cache cleared successfully!');
                } else {
                    alert('Failed to clear cache!');
                }
            }
        }
        
        xhr.send('clear_cache=1');
    }
}

function clearSessions() {
    if (confirm('Are you sure you want to clear all active user sessions? This will log out all users.')) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/system_control.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (this.status == 200) {
                if (this.responseText == 1) {
                    alert('All sessions cleared successfully!');
                    getSystemStats(); // Refresh stats
                } else {
                    alert('Failed to clear sessions!');
                }
            }
        }
        
        xhr.send('clear_sessions=1');
    }
}

function viewLogs() {
    let modal = new bootstrap.Modal(document.getElementById('logs-modal'));
    modal.show();
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/system_control.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status == 200) {
            document.getElementById('logs-content').textContent = this.responseText;
        }
    }
    
    xhr.send('get_logs=1');
}

function downloadLogs() {
    window.open('ajax/system_control.php?download_logs=1', '_blank');
}

function checkMaintenanceMode() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/system_control.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status == 200) {
            maintenanceMode = (this.responseText == '1');
            document.getElementById('maintenance-btn-text').textContent = 
                maintenanceMode ? 'Disable Maintenance' : 'Enable Maintenance';
        }
    }
    
    xhr.send('check_maintenance=1');
}

function toggleMaintenance() {
    let action = maintenanceMode ? 'disable' : 'enable';
    if (confirm(`Are you sure you want to ${action} maintenance mode?`)) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/system_control.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (this.status == 200) {
                if (this.responseText == 1) {
                    maintenanceMode = !maintenanceMode;
                    document.getElementById('maintenance-btn-text').textContent = 
                        maintenanceMode ? 'Disable Maintenance' : 'Enable Maintenance';
                    alert(`Maintenance mode ${maintenanceMode ? 'enabled' : 'disabled'} successfully!`);
                } else {
                    alert('Failed to toggle maintenance mode!');
                }
            }
        }
        
        xhr.send('toggle_maintenance=1&mode=' + (maintenanceMode ? '0' : '1'));
    }
}

function emergencyShutdown() {
    if (confirm('⚠️ EMERGENCY SHUTDOWN ⚠️\n\nThis will:\n1. Create a database backup\n2. Clear all sessions\n3. Put the site in maintenance mode\n4. Log out all users\n\nAre you absolutely sure?')) {
        if (confirm('This action cannot be undone easily. Type "SHUTDOWN" to confirm:') && 
            prompt('Type "SHUTDOWN" to confirm:') === 'SHUTDOWN') {
            
            let xhr = new XMLHttpRequest();
            xhr.open("POST", "ajax/system_control.php", true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            
            xhr.onload = function() {
                if (this.status == 200) {
                    if (this.responseText.includes('success')) {
                        alert('Emergency shutdown completed successfully!\n\nThe system is now in maintenance mode.');
                        window.location.reload();
                    } else {
                        alert('Emergency shutdown failed: ' + this.responseText);
                    }
                }
            }
            
            xhr.send('emergency_shutdown=1');
        }
    }
}