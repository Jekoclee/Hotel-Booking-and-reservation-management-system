<?php
// My Bookings page showing user's bookings and availability validation links
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require('admin/inc/db_config.php');
require('admin/inc/essentials.php');

if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    redirect('index.php');
}

$user_id = (int)($_SESSION['uId'] ?? 0);

// Fetch bookings for this user
$bookings = [];
if ($user_id > 0) {
    $stmt = $con->prepare("SELECT b.*, r.name as room_name, r.price as room_price FROM bookings b LEFT JOIN rooms r ON b.room_id = r.id WHERE b.user_id = ? ORDER BY b.created_at DESC");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) { $bookings[] = $row; }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Bookings - Leisure Coast Resort</title>
    <?php require('inc/links.php'); ?>
    <style>
        .hero { background: linear-gradient(135deg, rgba(40,167,69,0.9), rgba(23,162,184,0.9)); min-height: 30vh; display:flex; align-items:center; margin-top: 76px; }
        .card-ghost { background: #fff; border-radius: 16px; box-shadow: 0 15px 40px rgba(0,0,0,0.08); border: none; }
        .status-pill { padding: 6px 10px; border-radius: 16px; font-size: 12px; text-transform: capitalize; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-completed { background: #d1ecf1; color: #0c5460; }
        .badge { font-weight: 600; }
    </style>
</head>
<body class="bg-light">
    <?php require('inc/header.php'); ?>

    <section class="hero text-white">
        <div class="container">
            <h1 class="fw-bold">My Bookings</h1>
            <p class="mb-0 text-white-50">Review and manage your reservations</p>
        </div>
    </section>

    <div class="container my-5">
        <?php if (empty($bookings)): ?>
            <div class="alert alert-info">You have no bookings yet. Explore our rooms and make your first reservation!</div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($bookings as $bk): 
                    $nights = (new DateTime($bk['check_in']))->diff(new DateTime($bk['check_out']))->days;
                    $payment_status = $bk['payment_status'] ?? 'pending';
                    $refund_status = $bk['refund_status'] ?? 'none';
                    $eligible_refund = (
                        ($payment_status === 'paid') &&
                        (in_array(($bk['booking_status'] ?? ''), ['confirmed','pending'], true)) &&
                        (!in_array($refund_status, ['requested','approved'], true)) &&
                        (new DateTime($bk['check_in']) > new DateTime('today'))
                    );
                ?>
                <div class="col-md-6">
                    <div class="card-ghost p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="fw-bold mb-0"><?= htmlspecialchars($bk['room_name'] ?? 'Room #'.$bk['room_id']) ?></h5>
                            <span class="status-pill status-<?= htmlspecialchars($bk['booking_status']) ?>">
                                <?= htmlspecialchars($bk['booking_status']) ?>
                            </span>
                        </div>
                        <div class="small text-muted mb-2">Booking ID: <?= htmlspecialchars($bk['id']) ?></div>
                        <div class="row g-2 mb-2">
                            <div class="col-6"><strong>Check-in:</strong> <?= date('M j, Y', strtotime($bk['check_in'])) ?></div>
                            <div class="col-6"><strong>Check-out:</strong> <?= date('M j, Y', strtotime($bk['check_out'])) ?></div>
                            <div class="col-6"><strong>Nights:</strong> <?= $nights ?></div>
                            <div class="col-6"><strong>Guests:</strong> <?= (int)$bk['adults'] ?> Adults<?= ((int)$bk['children']>0? ', '.(int)$bk['children'].' Children':'') ?></div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="fw-bold">₱<?= number_format((float)$bk['total_amount'], 2) ?></div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-<?= $payment_status==='paid'?'success':($payment_status==='refunded'?'warning':'secondary') ?>">
                                    Payment: <?= htmlspecialchars(strtoupper($payment_status)) ?>
                                </span>
                                <span class="badge bg-<?= $refund_status==='approved'?'success':($refund_status==='requested'?'info':($refund_status==='rejected'?'danger':'secondary')) ?>">
                                    Refund: <?= htmlspecialchars(strtoupper($refund_status)) ?>
                                </span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div></div>
                            <div class="d-flex gap-2">
                                <a class="btn btn-outline-primary btn-sm" href="booking_confirmation.php?room_id=<?= (int)$bk['room_id'] ?>&check_in=<?= urlencode($bk['check_in']) ?>&check_out=<?= urlencode($bk['check_out']) ?>&adults=<?= (int)$bk['adults'] ?>&children=<?= (int)$bk['children'] ?>">
                                    View
                                </a>
                                <?php if ($payment_status === 'pending'): ?>
                                    <a class="btn btn-success btn-sm" href="booking_confirmation.php?room_id=<?= (int)$bk['room_id'] ?>&check_in=<?= urlencode($bk['check_in']) ?>&check_out=<?= urlencode($bk['check_out']) ?>&adults=<?= (int)$bk['adults'] ?>&children=<?= (int)$bk['children'] ?>">
                                        Pay Now
                                    </a>
                                <?php endif; ?>
                                <?php if ($eligible_refund): ?>
                                    <button class="btn btn-outline-danger btn-sm" onclick="openRefundModal(<?= (int)$bk['id'] ?>)">Request Refund</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Refund Modal -->
    <div class="modal fade" id="refundModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Request Refund</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="refundBookingId" />
            <div class="mb-3">
              <label for="refundReason" class="form-label">Reason for refund</label>
              <textarea class="form-control" id="refundReason" rows="4" placeholder="Please provide a brief reason"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger" onclick="submitRefund()">Submit Request</button>
          </div>
        </div>
      </div>
    </div>

    <?php require('inc/footer.php'); ?>
    <script>
      let refundModal;
      document.addEventListener('DOMContentLoaded', function() {
        const modalEl = document.getElementById('refundModal');
        refundModal = new bootstrap.Modal(modalEl);
      });

      function openRefundModal(bookingId) {
        document.getElementById('refundBookingId').value = bookingId;
        document.getElementById('refundReason').value = '';
        refundModal.show();
      }

      async function submitRefund() {
        const booking_id = parseInt(document.getElementById('refundBookingId').value, 10);
        const reason = document.getElementById('refundReason').value.trim();
        if (!booking_id) { alert('Invalid booking'); return; }
        if (reason.length < 5) { alert('Please provide a brief reason (at least 5 characters).'); return; }

        try {
          const res = await fetch('ajax/refunds.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'request_refund', booking_id, reason })
          });
          const data = await res.json();
          if (data.success) {
            refundModal.hide();
            alert(data.message || 'Refund request submitted');
            window.location.reload();
          } else {
            alert(data.message || 'Failed to submit refund request');
          }
        } catch (e) {
          alert('Network error while submitting refund request');
        }
      }
    </script>
</body>
</html>