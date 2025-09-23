<?php
require('inc/essentials.php');
adminLogin();


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Dashboard</title>
    <?php require('inc/links.php'); ?>
    <style>
        /* Modern Admin Panel Styling */
        .admin-dashboard {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .main-content {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            margin: 20px;
            padding: 30px;
        }
        
        .dashboard-title {
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            font-size: 2.5rem;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%);
            pointer-events: none;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(102, 126, 234, 0.4);
        }
        
        .stat-card .card-body {
            position: relative;
            z-index: 2;
            padding: 25px;
        }
        
        .stat-card .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .stat-card .card-text {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-top: 30px;
        }
        
        .table-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            margin: 0;
            font-weight: 600;
        }
        
        .table {
            margin: 0;
        }
        
        .table thead th {
            background: linear-gradient(135deg, #f8f9ff, #e3e7ff);
            color: #667eea;
            font-weight: 600;
            border: none;
            padding: 15px;
        }
        
        .table tbody tr {
            transition: all 0.3s ease;
        }
        
        .table tbody tr:hover {
            background: linear-gradient(135deg, #f8f9ff, #e3e7ff);
            transform: scale(1.02);
        }
        
        .table tbody td {
            padding: 15px;
            border-color: #e3e7ff;
        }
        
        .badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .badge.bg-success {
            background: linear-gradient(135deg, #4facfe, #00f2fe) !important;
        }
        
        .badge.bg-warning {
            background: linear-gradient(135deg, #fa709a, #fee140) !important;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .main-content {
                margin: 10px;
                padding: 20px;
                border-radius: 15px;
            }
            
            .dashboard-title {
                font-size: 2rem;
            }
            
            .stat-card .card-text {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body class="admin-dashboard">
    <!-- Top Navbar -->
    <?php require('inc/header.php'); ?>
    
    <!-- Main Content -->
    <div class="col-lg-10" id="main-content">
        <div class="main-content">
            <h1 class="dashboard-title text-center">Dashboard Overview</h1>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-5">
                <div class="col-lg-4 col-md-6">
                    <div class="card stat-card text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Bookings</h5>
                            <p class="card-text">128</p>
                            <small class="opacity-75">+12% from last month</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card stat-card text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Active Guests</h5>
                            <p class="card-text">53</p>
                            <small class="opacity-75">Currently checked in</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="card stat-card text-white">
                        <div class="card-body text-center">
                            <h5 class="card-title">Revenue</h5>
                            <p class="card-text">₱7,450</p>
                            <small class="opacity-75">This week</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Bookings Table -->
            <div class="table-container">
                <h4 class="table-header">Recent Bookings</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Check-In</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>1</strong></td>
                                <td>Jane Smith</td>
                                <td>Ocean View Suite</td>
                                <td>2025-08-20</td>
                                <td><span class="badge bg-success">Confirmed</span></td>
                            </tr>
                            <tr>
                                <td><strong>2</strong></td>
                                <td>Mark Lee</td>
                                <td>Deluxe Room</td>
                                <td>2025-08-21</td>
                                <td><span class="badge bg-warning">Pending</span></td>
                            </tr>
                            <tr>
                                <td><strong>3</strong></td>
                                <td>Sarah Johnson</td>
                                <td>Family Suite</td>
                                <td>2025-08-22</td>
                                <td><span class="badge bg-success">Confirmed</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <!-- Custom Hover Style -->
    <style>
        .hover-link:hover {
            background-color: #0d6efd;
            color: #fff !important;
            transition: 0.3s;
        }
    </style>

    <!-- Bootstrap CSS & JS -->
<?php require('inc/scripts.php'); ?>
</body>

</html>