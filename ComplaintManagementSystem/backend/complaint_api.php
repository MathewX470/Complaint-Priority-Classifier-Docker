<?php
/**
 * Complaint Management API
 * Smart Complaint Management System
 */

require_once 'config.php';

class ComplaintAPI {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Submit a new complaint
     */
    public function submitComplaint($userId, $complaintText) {
        try {
            // Validate complaint text
            if (empty(trim($complaintText))) {
                return ['success' => false, 'message' => 'Complaint text is required'];
            }
            
            // Call ML model to predict priority
            $priority = $this->predictPriority($complaintText);
            
            // Insert complaint
            $stmt = $this->db->prepare(
                "INSERT INTO complaints (user_id, complaint_text, priority, status) 
                 VALUES (?, ?, ?, 'Registered')"
            );
            $stmt->execute([$userId, $complaintText, $priority['priority']]);
            $complaintId = $this->db->lastInsertId();
            
            // Log ML prediction
            $this->logMLPrediction($complaintId, $priority['priority'], $priority['confidence']);
            
            return [
                'success' => true,
                'message' => 'Complaint submitted successfully',
                'complaint_id' => $complaintId,
                'priority' => $priority['priority']
            ];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Failed to submit complaint: ' . $e->getMessage()];
        }
    }
    
    /**
     * Predict complaint priority using ML model
     */
    private function predictPriority($complaintText) {
        try {
            $ch = curl_init(ML_API_URL);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['complaint_text' => $complaintText]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, ML_API_TIMEOUT);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode === 200 && $response) {
                $result = json_decode($response, true);
                return [
                    'priority' => $result['priority'] ?? 'Low',
                    'confidence' => $result['confidence'] ?? 0.0
                ];
            }
            
        } catch(Exception $e) {
            error_log("ML prediction failed: " . $e->getMessage());
        }
        
        // Default to Low priority if ML fails
        return ['priority' => 'Low', 'confidence' => 0.0];
    }
    
    /**
     * Log ML prediction
     */
    private function logMLPrediction($complaintId, $priority, $confidence) {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO ml_predictions_log (complaint_id, predicted_priority, confidence_score, model_version) 
                 VALUES (?, ?, ?, 'v1.0')"
            );
            $stmt->execute([$complaintId, $priority, $confidence]);
        } catch(PDOException $e) {
            error_log("ML logging failed: " . $e->getMessage());
        }
    }
    
    /**
     * Get user complaints
     */
    public function getUserComplaints($userId, $page = 1) {
        try {
            $offset = ($page - 1) * RECORDS_PER_PAGE;
            
            $stmt = $this->db->prepare(
                "SELECT complaint_id, complaint_text, priority, status, submitted_at, updated_at, resolved_at
                 FROM complaints 
                 WHERE user_id = ? 
                 ORDER BY submitted_at DESC 
                 LIMIT ? OFFSET ?"
            );
            $stmt->execute([$userId, RECORDS_PER_PAGE, $offset]);
            $complaints = $stmt->fetchAll();
            
            // Get total count
            $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM complaints WHERE user_id = ?");
            $stmt->execute([$userId]);
            $total = $stmt->fetch()['total'];
            
            return [
                'success' => true,
                'complaints' => $complaints,
                'total' => $total,
                'page' => $page,
                'pages' => ceil($total / RECORDS_PER_PAGE)
            ];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Failed to fetch complaints: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get all complaints (Admin)
     */
    public function getAllComplaints($filters = [], $page = 1) {
        try {
            $offset = ($page - 1) * RECORDS_PER_PAGE;
            $whereConditions = [];
            $params = [];
            
            // Apply filters
            if (!empty($filters['priority'])) {
                $whereConditions[] = "c.priority = ?";
                $params[] = $filters['priority'];
            }
            if (!empty($filters['status'])) {
                $whereConditions[] = "c.status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['search'])) {
                $whereConditions[] = "(c.complaint_text LIKE ? OR u.full_name LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%{$filters['search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $query = "SELECT c.complaint_id, c.complaint_text, c.priority, c.status, c.submitted_at, 
                             c.updated_at, c.resolved_at, u.full_name, u.email
                      FROM complaints c
                      JOIN users u ON c.user_id = u.user_id
                      $whereClause
                      ORDER BY 
                        CASE c.priority 
                          WHEN 'High' THEN 1 
                          WHEN 'Medium' THEN 2 
                          WHEN 'Low' THEN 3 
                          WHEN 'Other' THEN 4 
                        END,
                        c.submitted_at DESC
                      LIMIT ? OFFSET ?";
            
            $params[] = RECORDS_PER_PAGE;
            $params[] = $offset;
            
            $stmt = $this->db->prepare($query);
            $stmt->execute($params);
            $complaints = $stmt->fetchAll();
            
            // Get total count
            $countQuery = "SELECT COUNT(*) as total 
                          FROM complaints c
                          JOIN users u ON c.user_id = u.user_id
                          $whereClause";
            $stmt = $this->db->prepare($countQuery);
            $stmt->execute(array_slice($params, 0, -2));
            $total = $stmt->fetch()['total'];
            
            return [
                'success' => true,
                'complaints' => $complaints,
                'total' => $total,
                'page' => $page,
                'pages' => ceil($total / RECORDS_PER_PAGE)
            ];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Failed to fetch complaints: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update complaint status (Admin)
     */
    public function updateComplaintStatus($complaintId, $newStatus, $adminId, $notes = null) {
        try {
            // Use stored procedure
            $stmt = $this->db->prepare("CALL UpdateComplaintStatus(?, ?, ?, ?)");
            $stmt->execute([$complaintId, $newStatus, $adminId, $notes]);
            
            return [
                'success' => true,
                'message' => 'Complaint status updated successfully'
            ];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Failed to update status: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get complaint details with lifecycle
     */
    public function getComplaintDetails($complaintId) {
        try {
            $stmt = $this->db->prepare("CALL GetComplaintLifecycle(?)");
            $stmt->execute([$complaintId]);
            
            $details = $stmt->fetch();
            $stmt->nextRowset();
            $history = $stmt->fetchAll();
            
            return [
                'success' => true,
                'details' => $details,
                'history' => $history
            ];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Failed to fetch details: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get statistics (Admin Dashboard)
     */
    public function getStatistics() {
        try {
            // Overall stats
            $stmt = $this->db->query(
                "SELECT 
                    COUNT(*) as total_complaints,
                    SUM(CASE WHEN status = 'Registered' THEN 1 ELSE 0 END) as registered,
                    SUM(CASE WHEN status = 'Under Review' THEN 1 ELSE 0 END) as under_review,
                    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
                    SUM(CASE WHEN status = 'Resolved' THEN 1 ELSE 0 END) as resolved,
                    SUM(CASE WHEN priority = 'High' THEN 1 ELSE 0 END) as high_priority,
                    SUM(CASE WHEN priority = 'Medium' THEN 1 ELSE 0 END) as medium_priority,
                    SUM(CASE WHEN priority = 'Low' THEN 1 ELSE 0 END) as low_priority,
                    SUM(CASE WHEN priority = 'Other' THEN 1 ELSE 0 END) as other_priority
                 FROM complaints"
            );
            $overallStats = $stmt->fetch();
            
            // Priority stats
            $stmt = $this->db->query("SELECT * FROM complaint_stats_by_priority");
            $priorityStats = $stmt->fetchAll();
            
            // Recent complaints
            $stmt = $this->db->query("SELECT * FROM recent_activity LIMIT 10");
            $recentComplaints = $stmt->fetchAll();
            
            return [
                'success' => true,
                'overall' => $overallStats,
                'by_priority' => $priorityStats,
                'recent' => $recentComplaints
            ];
            
        } catch(PDOException $e) {
            return ['success' => false, 'message' => 'Failed to fetch statistics: ' . $e->getMessage()];
        }
    }
}
?>
