<?php
require('inc/links.php');
require('inc/essentials.php');
adminLogin();
require('inc/db_config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviews Management</title>
    <?php require('inc/links.php'); ?>
    <link rel="stylesheet" href="css/common.css">
</head>
<body class="bg-light">
    <?php require('inc/header.php'); ?>

    <div class="col-lg-10 ms-auto p-4 overflow-hidden">
        <h2 class="mb-4">Reviews Management</h2>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="reviews-table">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Room</th>
                                <th>User</th>
                                <th>Rating</th>
                                <th>Comment</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Filled by JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <?php require('inc/scripts.php'); ?>
    <script>
    document.addEventListener('DOMContentLoaded', function(){
        fetchReviews();
    });

    function fetchReviews(){
        fetch('scripts/reviews_admin.php?action=list')
          .then(r => r.json())
          .then(data => renderTable(data.reviews || []))
          .catch(() => {});
    }

    function renderTable(rows){
        const tb = document.querySelector('#reviews-table tbody');
        if(!tb) return;
        if(rows.length === 0){
            tb.innerHTML = `<tr><td colspan="8" class="text-center text-muted">No reviews found</td></tr>`;
            return;
        }
        tb.innerHTML = rows.map(r => {
            const badge = r.status === 'approved' ? 'bg-success' : (r.status==='pending'?'bg-warning':'bg-danger');
            return `
            <tr>
                <td>${r.id}</td>
                <td>${r.room_name || ('#'+r.room_id)}</td>
                <td>${r.user_name || ('#'+r.user_id)}</td>
                <td>${r.rating}★</td>
                <td>${(r.comment||'').replace(/</g,'&lt;')}</td>
                <td><span class="badge ${badge}">${r.status}</span></td>
                <td>${r.created_at}</td>
                <td>
                    <button class="btn btn-sm btn-success me-1" onclick="changeStatus(${r.id}, 'approved')">Approve</button>
                    <button class="btn btn-sm btn-secondary me-1" onclick="changeStatus(${r.id}, 'pending')">Pending</button>
                    <button class="btn btn-sm btn-danger" onclick="changeStatus(${r.id}, 'rejected')">Reject</button>
                </td>
            </tr>`;
        }).join('');
    }

    function changeStatus(id, status){
        fetch('scripts/reviews_admin.php', {
            method: 'POST',
            headers: { 'Content-Type':'application/json' },
            body: JSON.stringify({ action:'update_status', id, status })
        })
        .then(r => r.json())
        .then(data => {
            if(data && data.success){ fetchReviews(); }
            else alert('Failed: ' + (data.message || ''));
        });
    }
    </script>
</body>
</html>