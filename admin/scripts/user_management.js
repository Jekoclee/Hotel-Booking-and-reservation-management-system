let users_data = document.getElementById('users-data');
let edit_user_form = document.getElementById('edit-user-form');

// Load users on page load
window.onload = function() {
    get_users();
    get_user_stats();
}

function get_users(search = '', status = '', verified = '') {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/user_management.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status == 200) {
            users_data.innerHTML = this.responseText;
        }
    }
    
    xhr.send('get_users=1&search=' + search + '&status=' + status + '&verified=' + verified);
}

function get_user_stats() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/user_management.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status == 200) {
            let stats = JSON.parse(this.responseText);
            document.getElementById('total-users').textContent = stats.total;
            document.getElementById('active-users').textContent = stats.active;
            document.getElementById('unverified-users').textContent = stats.unverified;
            document.getElementById('banned-count').textContent = stats.banned;
        }
    }
    
    xhr.send('get_user_stats=1');
}

function searchUsers() {
    let search = document.getElementById('search-user').value;
    let status = document.getElementById('status-filter').value;
    let verified = document.getElementById('verified-filter').value;
    
    get_users(search, status, verified);
}

function edit_user(id) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/user_management.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (this.status == 200) {
            try {
                let user = JSON.parse(this.responseText);
                if (!user || typeof user !== 'object') {
                    alert('error', 'Failed to load user data: ' + this.responseText);
                    return;
                }
                
                document.getElementById('edit-user-id').value = user.id || '';
                document.getElementById('edit-user-name').value = user.name || '';
                document.getElementById('edit-user-email').value = user.email || '';
                document.getElementById('edit-user-phone').value = user.phonenum || '';
                document.getElementById('edit-user-address').value = user.address || '';
                document.getElementById('edit-user-dob').value = user.dob || '';
                document.getElementById('edit-user-status').value = (user.status != null ? user.status : 1);
                document.getElementById('edit-user-verified').value = (user.is_verified != null ? user.is_verified : 0);
                
                let modalEl = document.getElementById('edit-user-modal');
                let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.show();
            } catch (e) {
                alert('error', 'Invalid response while loading user: ' + e.message + ' | ' + this.responseText);
            }
        } else {
            alert('error', 'Server error (' + this.status + ') while loading user');
        }
    }
    
    xhr.send('get_user=1&id=' + id);
}

function toggle_status(id, status) {
    if (confirm('Are you sure you want to ' + (status == 1 ? 'activate' : 'deactivate') + ' this user?')) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/user_management.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (this.status == 200) {
                if (this.responseText == 1) {
                    alert('success', 'User status updated successfully!');
                    get_users();
                    get_user_stats();
                } else {
                    alert('error', 'Failed to update user status!');
                }
            } else {
                alert('error', 'Server error (' + this.status + ') while updating status');
            }
        }
        
        xhr.send('toggle_status=1&id=' + id + '&status=' + status);
    }
}

function delete_user(id) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone!')) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/user_management.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (this.status == 200) {
                if (this.responseText == 1) {
                    alert('success', 'User deleted successfully!');
                    get_users();
                    get_user_stats();
                } else {
                    alert('error', 'Failed to delete user!');
                }
            } else {
                alert('error', 'Server error (' + this.status + ') while deleting user');
            }
        }
        
        xhr.send('delete_user=1&id=' + id);
    }
}

function verify_user(id) {
    if (confirm('Are you sure you want to verify this user?')) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/user_management.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (this.status == 200) {
                if (this.responseText == 1) {
                    alert('success', 'User verified successfully!');
                    get_users();
                    get_user_stats();
                } else {
                    alert('error', 'Failed to verify user!');
                }
            } else {
                alert('error', 'Server error (' + this.status + ') while verifying user');
            }
        }
        
        xhr.send('verify_user=1&id=' + id);
    }
}

function toggle_ban(id, banned) {
    let action = banned == 1 ? 'ban' : 'unban';
    if (confirm('Are you sure you want to ' + action + ' this user?')) {
        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/user_management.php", true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        
        xhr.onload = function() {
            if (this.status == 200) {
                if (this.responseText == 1) {
                    alert('success', 'User ' + action + 'ned successfully!');
                    get_users();
                    get_user_stats();
                } else {
                    alert('error', 'Failed to ' + action + ' user!');
                }
            } else {
                alert('error', 'Server error (' + this.status + ') while trying to ' + action + ' user');
            }
        }
        
        xhr.send('toggle_ban=1&id=' + id + '&banned=' + banned);
    }
}

// Handle edit user form submission
edit_user_form.addEventListener('submit', function(e) {
    e.preventDefault();
    
    let data = new FormData();
    data.append('update_user', '1');
    data.append('id', document.getElementById('edit-user-id').value);
    data.append('name', document.getElementById('edit-user-name').value);
    data.append('email', document.getElementById('edit-user-email').value);
    data.append('phone', document.getElementById('edit-user-phone').value);
    data.append('address', document.getElementById('edit-user-address').value);
    data.append('dob', document.getElementById('edit-user-dob').value);
    data.append('status', document.getElementById('edit-user-status').value);
    data.append('verified', document.getElementById('edit-user-verified').value);
    
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/user_management.php", true);
    
    xhr.onload = function() {
        if (this.status == 200) {
            if (this.responseText == 1) {
                alert('success', 'User updated successfully!');
                let modalEl = document.getElementById('edit-user-modal');
                let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
                get_users();
                get_user_stats();
            } else {
                alert('error', 'Failed to update user! ' + this.responseText);
            }
        } else {
            alert('error', 'Server error (' + this.status + ') while updating user');
        }
    }
    
    xhr.send(data);
});

// Search on Enter key
document.getElementById('search-user').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        searchUsers();
    }
});