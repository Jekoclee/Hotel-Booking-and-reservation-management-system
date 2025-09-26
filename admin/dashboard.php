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
            margin: 20px auto;
            max-width: 1400px;
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
            background: linear-gradient(135deg, #3a86ff, #8338ec);
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            transition: transform .2s ease, box-shadow .2s ease;
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
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.2);
        }
        
        .stat-card .card-body {
            position: relative;
            z-index: 2;
            padding: 25px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 140px;
        }
        
        .stat-card .card-title {
            letter-spacing: .5px;
            text-transform: uppercase;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .stat-card .card-text {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: .25rem;
        }
        
        .metric-value { font-size: 2.25rem; }
        .metric-subtitle { display: block; }
        .stat-icon {
            font-size: 2rem;
            display: inline-block;
            margin-bottom: .5rem;
            color: #fff;
            text-shadow: 0 1px 2px rgba(0,0,0,0.25);
        }
        .stat-card .progress {
            background: rgba(255,255,255,0.25);
            border-radius: 8px;
        }
        .stat-card .progress-bar {
            transition: width .6s ease;
            box-shadow: 0 2px 6px rgba(0,0,0,0.2);
        }
        .progress-thin { height: 10px; }
        
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
    <div class="col-lg-10 mx-auto" id="main-content">
        <div class="main-content">
            <h1 class="dashboard-title text-center">Dashboard Overview</h1>
            
            <!-- Stats Cards -->
            <div class="row g-4 mb-5 align-items-stretch">
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card stat-card text-white h-100 flex-fill">
                        <div class="card-body text-center">
                            <i class="bi bi-person-check stat-icon"></i>
                            <h5 class="card-title">Arrivals Today</h5>
                            <p class="card-text metric-value"><span id="arrivalsToday">0</span></p>
                            <small class="opacity-75 metric-subtitle">Guests checking in today</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card stat-card text-white h-100 flex-fill">
                        <div class="card-body text-center">
                            <i class="bi bi-box-arrow-right stat-icon"></i>
                            <h5 class="card-title">Departures Today</h5>
                            <p class="card-text metric-value"><span id="departuresToday">0</span></p>
                            <small class="opacity-75 metric-subtitle">Guests checking out today</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card stat-card text-white h-100 flex-fill">
                        <div class="card-body text-center">
                            <i class="bi bi-graph-up stat-icon"></i>
                            <h5 class="card-title">Occupancy</h5>
                            <p class="card-text metric-value"><span id="occupancyPct">0%</span></p>
                            <div class="mt-2 w-100" style="max-width: 420px;">
                                <div class="d-flex justify-content-between">
                                    <small>Occupied</small>
                                    <small id="occupancyPctSmall">0%</small>
                                </div>
                                <div class="progress progress-thin">
                                    <div class="progress-bar bg-success" id="occupancyBar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card stat-card text-white h-100 flex-fill">
                        <div class="card-body text-center">
                            <i class="bi bi-cash-coin stat-icon"></i>
                            <h5 class="card-title">ADR</h5>
                            <p class="card-text metric-value"><span id="adrValue">₱0.00</span></p>
                            <small class="opacity-75 metric-subtitle">Average Daily Rate</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 d-flex">
                    <div class="card stat-card text-white h-100 flex-fill">
                        <div class="card-body text-center">
                            <i class="bi bi-wallet2 stat-icon"></i>
                            <h5 class="card-title">RevPAR</h5>
                            <p class="card-text metric-value"><span id="revparValue">₱0.00</span></p>
                            <small class="opacity-75 metric-subtitle">Revenue per Available Room</small>
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

            <!-- Top Rooms (last 30 days) -->
            <div class="table-container">
                <h4 class="table-header">Top Rooms (last 30 days)</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Room</th>
                                <th>Bookings</th>
                            </tr>
                        </thead>
                        <tbody id="topRoomsBody">
                            <tr><td colspan="3" class="text-center">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Booking Source Distribution (last 30 days) -->
            <div class="table-container">
                <h4 class="table-header">Booking Sources (last 30 days)</h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Source</th>
                                <th>Bookings</th>
                            </tr>
                        </thead>
                        <tbody id="sourceDistBody">
                            <tr><td colspan="2" class="text-center">Loading...</td></tr>
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
<script>
// Fetch and render dashboard metrics
async function loadDashboardMetrics() {
    try {
        const res = await fetch('ajax/booking_management.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ action: 'get_dashboard_metrics' })
        });
        const json = await res.json();
        if (!json.success) throw new Error('Failed to load metrics');
        const d = json.data;
        const arrivals = Number(d.arrivals_today || 0);
        const departures = Number(d.departures_today || 0);
        const occ = Number(d.occupancy_pct || 0);
        const adr = Number(d.adr || 0);
        const revpar = Number(d.revpar || 0);

        document.getElementById('arrivalsToday').textContent = arrivals;
        document.getElementById('departuresToday').textContent = departures;
        document.getElementById('occupancyPct').textContent = occ + '%';
        const occSmallEl = document.getElementById('occupancyPctSmall');
        if (occSmallEl) occSmallEl.textContent = occ + '%';
        const bar = document.getElementById('occupancyBar');
        if (bar) {
            bar.style.width = occ + '%';
            bar.setAttribute('aria-valuenow', occ);
        }
        document.getElementById('adrValue').textContent = '₱' + adr.toFixed(2);
        document.getElementById('revparValue').textContent = '₱' + revpar.toFixed(2);
        // Top rooms
        const topBody = document.getElementById('topRoomsBody');
        topBody.innerHTML = (d.top_rooms && d.top_rooms.length)
            ? d.top_rooms.map((r, idx) => `<tr><td>${idx+1}</td><td>${r.room_name || '-'}</td><td>${r.bookings_count || 0}</td></tr>`).join('')
            : '<tr><td colspan="3" class="text-center">No data</td></tr>';
        // Source distribution
        const srcBody = document.getElementById('sourceDistBody');
        srcBody.innerHTML = (d.source_distribution && d.source_distribution.length)
            ? d.source_distribution.map((s) => `<tr><td>${s.source || '-'}</td><td>${s.cnt || 0}</td></tr>`).join('')
            : '<tr><td colspan="2" class="text-center">No data</td></tr>';
    } catch (err) {
        console.error('Dashboard metrics error:', err);
    }
}

document.addEventListener('DOMContentLoaded', loadDashboardMetrics);
</script>
</body>

</html>