let bookingsData = [];

document.addEventListener('DOMContentLoaded', function() {
    loadBookings();
    setupEventListeners();
    setMinDates();
});

function setupEventListeners() {
    // Filter event listeners
    document.getElementById('statusFilter').addEventListener('change', loadBookings);
    document.getElementById('roomFilter').addEventListener('change', loadBookings);
    document.getElementById('dateFilter').addEventListener('change', loadBookings);

    // Create booking form
    document.getElementById('createBookingBtn').addEventListener('click', createBooking);
    document.getElementById('updateBookingBtn').addEventListener('click', updateBooking);

    // Date validation for create form
    const createForm = document.getElementById('createBookingForm');
    const checkinInput = createForm.querySelector('input[name="checkin_date"]');
    const checkoutInput = createForm.querySelector('input[name="checkout_date"]');
    
    checkinInput.addEventListener('change', function() {
        validateBookingDates('create');
        // Set minimum checkout date to day after checkin
        if (this.value) {
            const nextDay = new Date(this.value);
            nextDay.setDate(nextDay.getDate() + 1);
            checkoutInput.min = nextDay.toISOString().split('T')[0];
        }
    });
    
    checkoutInput.addEventListener('change', function() {
        validateBookingDates('create');
    });

    // Room selection change
    createForm.querySelector('select[name="room_id"]').addEventListener('change', function() {
        validateBookingDates('create');
    });
}

function setMinDates() {
    const today = new Date().toISOString().split('T')[0];
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];

    // Set minimum dates for create form
    const createForm = document.getElementById('createBookingForm');
    createForm.querySelector('input[name="checkin_date"]').min = today;
    createForm.querySelector('input[name="checkout_date"]').min = tomorrowStr;
}

async function loadBookings() {
    try {
        const filters = {
            action: 'get_bookings',
            status_filter: document.getElementById('statusFilter').value,
            room_filter: document.getElementById('roomFilter').value,
            date_filter: document.getElementById('dateFilter').value
        };

        const response = await fetch('ajax/booking_management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(filters)
        });

        const data = await response.json();
        
        if (data.success) {
            bookingsData = data.bookings;
            displayBookings(data.bookings);
        } else {
            showAlert('error', 'Failed to load bookings: ' + data.message);
        }
    } catch (error) {
        console.error('Error loading bookings:', error);
        showAlert('error', 'Error loading bookings. Please try again.');
    }
}

function displayBookings(bookings) {
    const tbody = document.getElementById('bookingsTableBody');
    if (!bookings || bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="12" class="text-center">No bookings found</td></tr>';
        return;
    }
    tbody.innerHTML = bookings.map(b => {
        const checkinDate = new Date(b.check_in).toLocaleDateString();
        const checkoutDate = new Date(b.check_out).toLocaleDateString();
        const createdDate = b.created_at ? new Date(b.created_at).toLocaleDateString() : '';
        const nights = Math.ceil((new Date(b.check_out) - new Date(b.check_in)) / (1000 * 60 * 60 * 24));
        const payment = (b.payment_status || 'pending').toUpperCase();
        const paymentCls = ({pending:'secondary',paid:'success',refunded:'warning'})[b.payment_status] || 'secondary';
        const refund = (b.refund_status || 'none').toUpperCase();
        const refundCls = ({none:'secondary',requested:'info',approved:'success',rejected:'danger'})[b.refund_status] || 'secondary';
        const statusBadge = `<span class="badge bg-${getStatusColor(b.booking_status)}">${b.booking_status}</span>`;
        const paymentBadge = `<span class="badge bg-${paymentCls}">${payment}</span>`;
        const refundBadge = `<span class="badge bg-${refundCls}">${refund}</span>`;
        return `
            <tr>
                <td>#${b.id}</td>
                <td>${b.guest_name || ''}</td>
                <td>${b.room_name || 'N/A'}</td>
                <td>${checkinDate}</td>
                <td>${checkoutDate}</td>
                <td>${nights}</td>
                <td>$${parseFloat(b.total_amount || 0).toFixed(2)}</td>
                <td>${statusBadge}</td>
                <td>${paymentBadge}</td>
                <td>${refundBadge}</td>
                <td>${createdDate}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-success" onclick="markPaid(${b.id})" title="Mark Paid"><i class="bi bi-cash-coin"></i></button>
                        <button class="btn btn-outline-warning" onclick="markRefunded(${b.id})" title="Mark Refunded"><i class="bi bi-arrow-counterclockwise"></i></button>
                        <button class="btn btn-outline-primary" onclick="approveRefund(${b.id})" title="Approve Refund"><i class="bi bi-check2-circle"></i></button>
                        <button class="btn btn-outline-danger" onclick="rejectRefund(${b.id})" title="Reject Refund"><i class="bi bi-x-circle"></i></button>
                        <button class="btn btn-outline-secondary" onclick="editBooking(${b.id})" title="Edit"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-outline-dark" onclick="deleteBooking(${b.id})" title="Delete"><i class="bi bi-trash"></i></button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function getStatusColor(status) {
    const colors = {
        'confirmed': 'success',
        'pending': 'warning',
        'cancelled': 'danger',
        'completed': 'info'
    };
    return colors[status] || 'secondary';
}

async function validateBookingDates(formType) {
    const form = document.getElementById(formType + 'BookingForm');
    const roomId = form.querySelector('select[name="room_id"]').value;
    const checkinDate = form.querySelector('input[name="checkin_date"]').value;
    const checkoutDate = form.querySelector('input[name="checkout_date"]').value;
    const messageDiv = document.getElementById(formType + 'BookingValidationMessage');

    // Clear previous messages
    messageDiv.className = 'alert d-none';
    messageDiv.textContent = '';

    if (!roomId || !checkinDate || !checkoutDate) {
        return;
    }

    try {
        const requestData = {
            action: 'validate_dates',
            room_id: roomId,
            checkin_date: checkinDate,
            checkout_date: checkoutDate
        };

        // If editing, exclude current booking from conflict check
        if (formType === 'edit') {
            requestData.exclude_booking_id = form.querySelector('input[name="booking_id"]').value;
        }

        const response = await fetch('ajax/booking_management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestData)
        });

        const data = await response.json();
        
        if (data.success) {
            messageDiv.className = 'alert alert-success';
            messageDiv.textContent = 'Dates are available for booking!';
        } else {
            messageDiv.className = 'alert alert-danger';
            let message = data.message;
            if (data.conflicting_bookings && data.conflicting_bookings.length > 0) {
                message += ' Conflicting dates: ';
                message += data.conflicting_bookings.map(conflict => 
                    `${new Date(conflict.check_in).toLocaleDateString()} - ${new Date(conflict.check_out).toLocaleDateString()}`
                ).join(', ');
            }
            messageDiv.textContent = message;
        }
    } catch (error) {
        console.error('Error validating dates:', error);
        messageDiv.className = 'alert alert-warning';
        messageDiv.textContent = 'Unable to validate dates. Please check manually.';
    }
}

async function createBooking() {
    const form = document.getElementById('createBookingForm');
    const formData = new FormData(form);
    const bookingData = {
        action: 'create_booking'
    };

    // Convert FormData to object
    for (let [key, value] of formData.entries()) {
        bookingData[key] = value;
    }

    // Validate required fields
    const requiredFields = ['guest_name', 'guest_email', 'room_id', 'checkin_date', 'checkout_date'];
    for (let field of requiredFields) {
        if (!bookingData[field]) {
            showAlert('error', `Please fill in the ${field.replace('_', ' ')} field.`);
            return;
        }
    }

    // Validate dates
    if (new Date(bookingData.checkin_date) >= new Date(bookingData.checkout_date)) {
        showAlert('error', 'Check-out date must be after check-in date.');
        return;
    }

    try {
        const response = await fetch('ajax/booking_management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(bookingData)
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert('success', 'Booking created successfully!');
            bootstrap.Modal.getInstance(document.getElementById('createBookingModal')).hide();
            form.reset();
            setMinDates();
            loadBookings();
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error creating booking:', error);
        showAlert('error', 'Error creating booking. Please try again.');
    }
}

function editBooking(bookingId) {
    const booking = bookingsData.find(b => b.id == bookingId);
    if (!booking) {
        showAlert('error', 'Booking not found.');
        return;
    }

    const form = document.getElementById('editBookingForm');
    
    // Populate form fields
    form.querySelector('input[name="booking_id"]').value = booking.id;
    form.querySelector('input[name="guest_name"]').value = booking.guest_name;
    form.querySelector('input[name="guest_email"]').value = booking.guest_email;
    form.querySelector('input[name="guest_phone"]').value = booking.guest_phone || '';
    form.querySelector('select[name="status"]').value = booking.booking_status;
    form.querySelector('input[name="total_amount"]').value = booking.total_amount || '';
    form.querySelector('textarea[name="special_requests"]').value = booking.special_requests || '';

    // Clear validation message
    document.getElementById('editBookingValidationMessage').className = 'alert d-none';

    // Show modal
    new bootstrap.Modal(document.getElementById('editBookingModal')).show();
}

async function updateBooking() {
    const form = document.getElementById('editBookingForm');
    const formData = new FormData(form);
    const bookingData = {
        action: 'update_booking'
    };

    // Convert FormData to object
    for (let [key, value] of formData.entries()) {
        bookingData[key] = value;
    }

    try {
        const response = await fetch('ajax/booking_management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(bookingData)
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert('success', 'Booking updated successfully!');
            bootstrap.Modal.getInstance(document.getElementById('editBookingModal')).hide();
            loadBookings();
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error updating booking:', error);
        showAlert('error', 'Error updating booking. Please try again.');
    }
}

async function deleteBooking(bookingId) {
    if (!confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
        return;
    }

    try {
        const response = await fetch('ajax/booking_management.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_booking',
                booking_id: bookingId
            })
        });

        const data = await response.json();
        
        if (data.success) {
            showAlert('success', 'Booking deleted successfully!');
            loadBookings();
        } else {
            showAlert('error', data.message);
        }
    } catch (error) {
        console.error('Error deleting booking:', error);
        showAlert('error', 'Error deleting booking. Please try again.');
    }
}

function showAlert(type, message) {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    // Add to page
    document.body.appendChild(alertDiv);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}

// Reset create form when modal is closed
document.getElementById('createBookingModal').addEventListener('hidden.bs.modal', function() {
    document.getElementById('createBookingForm').reset();
    document.getElementById('bookingValidationMessage').className = 'alert d-none';
    setMinDates();
});

// Render a single booking row in the admin table
function renderBookingRow(b) {
  const paymentBadge = (() => {
    const map = {
      pending: 'secondary',
      paid: 'success',
      refunded: 'warning',
    };
    const label = (b.payment_status || 'pending').toUpperCase();
    const cls = map[b.payment_status] || 'secondary';
    return `<span class="badge bg-${cls}">${label}</span>`;
  })();
  const refundBadge = (() => {
    const status = b.refund_status || 'none';
    const map = { none: 'secondary', requested: 'info', approved: 'success', rejected: 'danger' };
    const cls = map[status] || 'secondary';
    return `<span class="badge bg-${cls}">${status.toUpperCase()}</span>`;
  })();

  const actions = `
    <div class="btn-group btn-group-sm" role="group">
      <button class="btn btn-outline-success" onclick="markPaid(${b.id})" title="Mark Paid"><i class="bi bi-cash-coin"></i></button>
      <button class="btn btn-outline-warning" onclick="markRefunded(${b.id})" title="Mark Refunded"><i class="bi bi-arrow-counterclockwise"></i></button>
      <button class="btn btn-outline-primary" onclick="approveRefund(${b.id})" title="Approve Refund"><i class="bi bi-check2-circle"></i></button>
      <button class="btn btn-outline-danger" onclick="rejectRefund(${b.id})" title="Reject Refund"><i class="bi bi-x-circle"></i></button>
      <button class="btn btn-outline-secondary" onclick="openEditModal(${b.id})" title="Edit"><i class="bi bi-pencil"></i></button>
      <button class="btn btn-outline-dark" onclick="deleteBooking(${b.id})" title="Delete"><i class="bi bi-trash"></i></button>
    </div>
  `;

  return `
    <tr>
      <td>${b.id}</td>
      <td>${escapeHtml(b.guest_name || '')}</td>
      <td>${escapeHtml(b.room_name || '')}</td>
      <td>${b.checkin_date}</td>
      <td>${b.checkout_date}</td>
      <td>${b.nights || ''}</td>
      <td>${formatCurrency(b.total_amount || 0)}</td>
      <td>${statusBadge(b.status)}</td>
      <td>${paymentBadge}</td>
      <td>${refundBadge}</td>
      <td>${b.created_at || ''}</td>
      <td>${actions}</td>
    </tr>
  `;
}

// Wire actions
async function updatePaymentStatus(booking_id, new_status) {
  const res = await fetch('ajax/booking_management.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'update_payment_status', booking_id, new_status }),
  });
  const data = await res.json();
  if (data.success) {
    loadBookings();
  } else {
    showAlert('error', data.message || 'Failed to update payment status');
  }
}
function markPaid(id) { updatePaymentStatus(id, 'paid'); }
function markRefunded(id) { updatePaymentStatus(id, 'refunded'); }

async function approveRefund(booking_id) {
  const res = await fetch('ajax/booking_management.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'approve_refund', booking_id }),
  });
  const data = await res.json();
  if (data.success) {
    loadBookings();
  } else {
    showAlert('error', data.message || 'Failed to approve refund');
  }
}
async function rejectRefund(booking_id) {
  const res = await fetch('ajax/booking_management.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'reject_refund', booking_id }),
  });
  const data = await res.json();
  if (data.success) {
    loadBookings();
  } else {
    showAlert('error', data.message || 'Failed to reject refund');
  }
}