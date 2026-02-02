<?php
require_once '../backend/config.php';
require_once '../backend/complaint_api.php';
requireLogin();
requireAdmin();

$complaintAPI = new ComplaintAPI();
$stats = $complaintAPI->getStatistics();

// Get filters
$filters = [
    'priority' => $_GET['priority'] ?? '',
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['search'] ?? ''
];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$complaints = $complaintAPI->getAllComplaints($filters, $page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Complaint Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .stat-card {
            border-left: 4px solid;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-3">
                <h4 class="mb-4"><i class="bi bi-shield-check"></i> Admin</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="admin_dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#complaintsSection">
                            <i class="bi bi-list-task"></i> Manage Complaints
                        </a>
                    </li>
                </ul>
                <hr class="text-white">
                <div class="mt-auto">
                    <p class="mb-1"><i class="bi bi-person-badge"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4">Admin Dashboard</h2>
                
                <div id="alertMessage"></div>
                
                <!-- Statistics Cards -->
                <?php if ($stats['success']): ?>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stat-card" style="border-left-color: #667eea;">
                            <div class="card-body">
                                <h6 class="text-muted">Total Complaints</h6>
                                <h2><?php echo $stats['overall']['total_complaints']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card" style="border-left-color: #dc3545;">
                            <div class="card-body">
                                <h6 class="text-muted">High Priority</h6>
                                <h2><?php echo $stats['overall']['high_priority']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card" style="border-left-color: #ffc107;">
                            <div class="card-body">
                                <h6 class="text-muted">Under Review</h6>
                                <h2><?php echo $stats['overall']['under_review']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stat-card" style="border-left-color: #28a745;">
                            <div class="card-body">
                                <h6 class="text-muted">Resolved</h6>
                                <h2><?php echo $stats['overall']['resolved']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Complaints Management -->
                <div class="card" id="complaintsSection">
                    <div class="card-header">
                        <h5><i class="bi bi-list-task"></i> Complaint Management</h5>
                    </div>
                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" class="row g-3 mb-3">
                            <div class="col-md-3">
                                <select name="priority" class="form-select">
                                    <option value="">All Priorities</option>
                                    <option value="High" <?php echo $filters['priority'] === 'High' ? 'selected' : ''; ?>>High</option>
                                    <option value="Medium" <?php echo $filters['priority'] === 'Medium' ? 'selected' : ''; ?>>Medium</option>
                                    <option value="Low" <?php echo $filters['priority'] === 'Low' ? 'selected' : ''; ?>>Low</option>
                                    <option value="Other" <?php echo $filters['priority'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="Registered" <?php echo $filters['status'] === 'Registered' ? 'selected' : ''; ?>>Registered</option>
                                    <option value="Under Review" <?php echo $filters['status'] === 'Under Review' ? 'selected' : ''; ?>>Under Review</option>
                                    <option value="In Progress" <?php echo $filters['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Resolved" <?php echo $filters['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="search" class="form-control" placeholder="Search..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-funnel"></i> Filter
                                </button>
                            </div>
                        </form>
                        
                        <?php if ($complaints['success'] && !empty($complaints['complaints'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Complaint</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($complaints['complaints'] as $complaint): ?>
                                    <tr>
                                        <td>#<?php echo $complaint['complaint_id']; ?></td>
                                        <td>
                                            <div><?php echo htmlspecialchars($complaint['full_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($complaint['email']); ?></small>
                                        </td>
                                        <td><?php echo htmlspecialchars(substr($complaint['complaint_text'], 0, 80)) . '...'; ?></td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                echo $complaint['priority'] === 'High' ? 'bg-danger' : 
                                                     ($complaint['priority'] === 'Medium' ? 'bg-warning text-dark' : 
                                                     ($complaint['priority'] === 'Low' ? 'bg-info' : 'bg-secondary')); 
                                                ?>">
                                                <?php echo $complaint['priority']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge 
                                                <?php 
                                                echo $complaint['status'] === 'Resolved' ? 'bg-success' : 
                                                     ($complaint['status'] === 'In Progress' ? 'bg-primary' : 
                                                     ($complaint['status'] === 'Under Review' ? 'bg-warning text-dark' : 'bg-secondary')); 
                                                ?>">
                                                <?php echo $complaint['status']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M d, Y', strtotime($complaint['submitted_at'])); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary" onclick="viewComplaintDetails(<?php echo $complaint['complaint_id']; ?>)">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success" onclick="updateStatus(<?php echo $complaint['complaint_id']; ?>, '<?php echo $complaint['status']; ?>')">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($complaints['pages'] > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $complaints['pages']; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&priority=<?php echo $filters['priority']; ?>&status=<?php echo $filters['status']; ?>&search=<?php echo $filters['search']; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                        
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 60px; color: #ccc;"></i>
                            <p class="mt-3">No complaints found matching your filters.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal fade" id="updateStatusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Complaint Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="statusForm">
                        <input type="hidden" id="complaintId">
                        <div class="mb-3">
                            <label for="newStatus" class="form-label">New Status</label>
                            <select class="form-select" id="newStatus" required>
                                <option value="Registered">Registered</option>
                                <option value="Under Review">Under Review</option>
                                <option value="In Progress">In Progress</option>
                                <option value="Resolved">Resolved</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="adminNotes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control" id="adminNotes" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Update Status
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- View Details Modal -->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Complaint Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <!-- Content loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateStatus(complaintId, currentStatus) {
            document.getElementById('complaintId').value = complaintId;
            document.getElementById('newStatus').value = currentStatus;
            new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
        }
        
        document.getElementById('statusForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                complaint_id: document.getElementById('complaintId').value,
                new_status: document.getElementById('newStatus').value,
                notes: document.getElementById('adminNotes').value
            };
            
            try {
                const response = await fetch('../backend/update_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', 'Status updated successfully!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    showAlert('danger', data.message);
                }
            } catch (error) {
                showAlert('danger', 'Failed to update status. Please try again.');
            }
        });
        
        async function viewComplaintDetails(complaintId) {
            try {
                const response = await fetch(`../backend/get_complaint_details.php?id=${complaintId}`);
                const data = await response.json();
                
                if (data.success) {
                    let html = `
                        <div class="mb-3">
                            <h6>User Information:</h6>
                            <p><strong>Name:</strong> ${data.details.user_name}<br>
                               <strong>Email:</strong> ${data.details.user_email}</p>
                        </div>
                        <div class="mb-3">
                            <h6>Complaint Text:</h6>
                            <p>${data.details.complaint_text}</p>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <strong>Priority:</strong> <span class="badge bg-${getPriorityColor(data.details.priority)}">${data.details.priority}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Status:</strong> <span class="badge bg-${getStatusColor(data.details.status)}">${data.details.status}</span>
                            </div>
                            <div class="col-md-4">
                                <strong>Time Elapsed:</strong> ${data.details.hours_elapsed} hours
                            </div>
                        </div>
                        <h6 class="mt-4">Status History:</h6>
                        <div class="timeline">
                    `;
                    
                    data.history.forEach(item => {
                        html += `
                            <div class="alert alert-light">
                                <div class="d-flex justify-content-between">
                                    <span><strong>${item.old_status || 'Initial'}</strong> → <strong>${item.new_status}</strong></span>
                                    <small>${new Date(item.changed_at).toLocaleString()}</small>
                                </div>
                                ${item.changed_by_name ? `<div><small>By: ${item.changed_by_name}</small></div>` : ''}
                                ${item.notes ? `<div class="mt-2">${item.notes}</div>` : ''}
                            </div>
                        `;
                    });
                    
                    html += '</div>';
                    document.getElementById('detailsContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('detailsModal')).show();
                }
            } catch (error) {
                showAlert('danger', 'Failed to load details.');
            }
        }
        
        function getPriorityColor(priority) {
            const colors = { High: 'danger', Medium: 'warning', Low: 'info', Other: 'secondary' };
            return colors[priority] || 'secondary';
        }
        
        function getStatusColor(status) {
            const colors = { 
                Resolved: 'success', 
                'In Progress': 'primary', 
                'Under Review': 'warning', 
                Registered: 'secondary' 
            };
            return colors[status] || 'secondary';
        }
        
        function showAlert(type, message) {
            const alertDiv = document.getElementById('alertMessage');
            alertDiv.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>`;
        }
    </script>
</body>
</html>
