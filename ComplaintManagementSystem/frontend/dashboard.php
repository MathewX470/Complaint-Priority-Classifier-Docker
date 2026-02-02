<?php
require_once '../backend/config.php';
require_once '../backend/complaint_api.php';
requireLogin();

$complaintAPI = new ComplaintAPI();
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$result = $complaintAPI->getUserComplaints($_SESSION['user_id'], $page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - Smart Complaint Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .priority-badge {
            font-weight: bold;
            padding: 5px 15px;
        }
        .status-badge {
            padding: 5px 15px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 sidebar p-3">
                <h4 class="mb-4"><i class="bi bi-headset"></i> CMS</h4>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link text-white active" href="dashboard.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#" data-bs-toggle="modal" data-bs-target="#newComplaintModal">
                            <i class="bi bi-plus-circle"></i> New Complaint
                        </a>
                    </li>
                </ul>
                <hr class="text-white">
                <div class="mt-auto">
                    <p class="mb-1"><i class="bi bi-person"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                    <a href="logout.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <h2 class="mb-4">My Complaints</h2>
                
                <div id="alertMessage"></div>
                
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span><i class="bi bi-list-ul"></i> All Complaints</span>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newComplaintModal">
                            <i class="bi bi-plus"></i> Submit New Complaint
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if ($result['success'] && !empty($result['complaints'])): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Complaint</th>
                                        <th>Priority</th>
                                        <th>Status</th>
                                        <th>Submitted</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($result['complaints'] as $complaint): ?>
                                    <tr>
                                        <td>#<?php echo $complaint['complaint_id']; ?></td>
                                        <td><?php echo htmlspecialchars(substr($complaint['complaint_text'], 0, 100)) . '...'; ?></td>
                                        <td>
                                            <span class="badge priority-badge 
                                                <?php 
                                                echo $complaint['priority'] === 'High' ? 'bg-danger' : 
                                                     ($complaint['priority'] === 'Medium' ? 'bg-warning text-dark' : 
                                                     ($complaint['priority'] === 'Low' ? 'bg-info' : 'bg-secondary')); 
                                                ?>">
                                                <?php echo $complaint['priority']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge status-badge 
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
                                            <button class="btn btn-sm btn-outline-primary" onclick="viewDetails(<?php echo $complaint['complaint_id']; ?>)">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($result['pages'] > 1): ?>
                        <nav>
                            <ul class="pagination justify-content-center">
                                <?php for ($i = 1; $i <= $result['pages']; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                        <?php endif; ?>
                        
                        <?php else: ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox" style="font-size: 60px; color: #ccc;"></i>
                            <p class="mt-3">No complaints yet. Submit your first complaint!</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Complaint Modal -->
    <div class="modal fade" id="newComplaintModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit New Complaint</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="complaintForm">
                        <div class="mb-3">
                            <label for="complaintText" class="form-label">Describe your complaint</label>
                            <textarea class="form-control" id="complaintText" rows="6" required></textarea>
                            <div class="form-text">Our AI will automatically classify the priority of your complaint</div>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Submit Complaint
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
        document.getElementById('complaintForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const complaintText = document.getElementById('complaintText').value;
            
            try {
                const response = await fetch('../backend/submit_complaint.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ complaint_text: complaintText })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showAlert('success', 'Complaint submitted successfully! Priority: ' + data.priority);
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showAlert('danger', data.message);
                }
            } catch (error) {
                showAlert('danger', 'Failed to submit complaint. Please try again.');
            }
        });
        
        async function viewDetails(complaintId) {
            try {
                const response = await fetch(`../backend/get_complaint_details.php?id=${complaintId}`);
                const data = await response.json();
                
                if (data.success) {
                    let html = `
                        <div class="mb-3">
                            <h6>Complaint Text:</h6>
                            <p>${data.details.complaint_text}</p>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Priority:</strong> <span class="badge bg-${getPriorityColor(data.details.priority)}">${data.details.priority}</span>
                            </div>
                            <div class="col-md-6">
                                <strong>Status:</strong> <span class="badge bg-${getStatusColor(data.details.status)}">${data.details.status}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <strong>Submitted:</strong> ${new Date(data.details.submitted_at).toLocaleString()}
                        </div>
                        <h6 class="mt-4">Status History:</h6>
                        <ul class="list-group">
                    `;
                    
                    data.history.forEach(item => {
                        html += `
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <span><strong>${item.old_status || 'Initial'}</strong> → <strong>${item.new_status}</strong></span>
                                    <small>${new Date(item.changed_at).toLocaleString()}</small>
                                </div>
                                ${item.notes ? `<div class="mt-1"><small>${item.notes}</small></div>` : ''}
                            </li>
                        `;
                    });
                    
                    html += '</ul>';
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
