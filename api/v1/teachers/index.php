<?php
/**
 * Teachers API Endpoints
 * Handles teacher profile and assignment operations
 */

require_once '../../../controllers/ApiController.php';

class TeachersApiController extends ApiController {
    /**
     * Handle API requests
     */
    public function handleRequest() {
        $id = $_GET['id'] ?? null;

        switch ($this->requestMethod) {
            case 'GET':
                if ($id) {
                    $this->getTeacher($id);
                } else {
                    $this->getTeachers();
                }
                break;
            case 'POST':
                $this->createTeacher();
                break;
            case 'PUT':
                if (!$id) {
                    $this->errorResponse('Teacher ID required', 400);
                }
                $this->updateTeacher($id);
                break;
            case 'DELETE':
                if (!$id) {
                    $this->errorResponse('Teacher ID required', 400);
                }
                $this->deleteTeacher($id);
                break;
            default:
                $this->methodNotAllowed();
        }
    }

    /**
     * Get all teachers with pagination and filtering
     */
    private function getTeachers() {
        $this->requireAuth();

        $department = $this->requestData['department'] ?? null;
        $designation = $this->requestData['designation'] ?? null;
        $search = $this->requestData['search'] ?? null;
        $isActive = $this->requestData['is_active'] ?? '1';

        $query = "SELECT t.*, u.username, u.email as user_email, r.role_name
                  FROM teachers t
                  LEFT JOIN users u ON t.user_id = u.id
                  LEFT JOIN user_roles r ON u.role_id = r.id
                  WHERE 1=1";

        $params = [];

        if ($isActive !== null) {
            $query .= " AND t.is_active = ?";
            $params[] = $isActive;
        }

        if ($department) {
            $query .= " AND t.department = ?";
            $params[] = $department;
        }

        if ($designation) {
            $query .= " AND t.designation = ?";
            $params[] = $designation;
        }

        if ($search) {
            $query .= " AND (t.first_name LIKE ? OR t.last_name LIKE ? OR t.employee_id LIKE ? OR t.email LIKE ? OR CONCAT(t.first_name, ' ', t.last_name) LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
        }

        $query .= " ORDER BY t.first_name, t.last_name";

        $result = $this->getPaginatedResults('teachers', '', $params, 't.first_name, t.last_name');

        // Add additional data for each teacher
        foreach ($result['data'] as &$teacher) {
            $teacher['full_name'] = trim($teacher['first_name'] . ' ' . ($teacher['middle_name'] ?? '') . ' ' . $teacher['last_name']);
            $teacher['age'] = $teacher['dob'] ? (new DateTime())->diff(new DateTime($teacher['dob']))->y : null;

            // Get assigned subjects
            $teacher['assigned_subjects'] = $this->getTeacherSubjects($teacher['id']);
            $teacher['workload'] = count($teacher['assigned_subjects']);

            // Get performance metrics
            $teacher['performance'] = $this->getTeacherPerformance($teacher['user_id']);
        }

        $this->successResponse($result);
    }

    /**
     * Get single teacher
     */
    private function getTeacher($id) {
        $this->requireAuth();

        $teacher = Teacher::withUser($id);

        if (!$teacher) {
            $this->notFound('Teacher');
        }

        // Check permissions
        $this->checkTeacherAccess($teacher);

        $data = $teacher->toArray();
        $data['full_name'] = $teacher->getFullName();
        $data['age'] = $teacher->getAge();

        // Add related data
        $data['assigned_subjects'] = $teacher->getAssignedSubjects();
        $data['workload'] = $teacher->getWorkload();
        $data['performance'] = $teacher->getPerformanceMetrics();

        $this->successResponse($data);
    }

    /**
     * Create new teacher
     */
    private function createTeacher() {
        $this->requireAuth();
        $this->requireRole(['admin']);

        $this->validateRequired([
            'employee_id', 'first_name', 'last_name', 'dob', 'gender',
            'qualification', 'designation', 'department', 'date_of_joining'
        ]);

        // Check for duplicate employee ID
        if (Teacher::employeeIdExists($this->requestData['employee_id'])) {
            $this->errorResponse('Employee ID already exists', 400);
        }

        $teacher = new Teacher($this->requestData);
        $teacher->is_active = $this->requestData['is_active'] ?? true;

        if ($teacher->save()) {
            $this->successResponse($teacher->toArray(), 'Teacher created successfully', 201);
        } else {
            $this->errorResponse('Failed to create teacher', 500);
        }
    }

    /**
     * Update teacher
     */
    private function updateTeacher($id) {
        $this->requireAuth();

        $teacher = Teacher::find($id);
        if (!$teacher) {
            $this->notFound('Teacher');
        }

        $this->checkTeacherAccess($teacher, true);

        // Check for duplicate employee ID (excluding current teacher)
        if (isset($this->requestData['employee_id']) &&
            Teacher::employeeIdExists($this->requestData['employee_id'], $id)) {
            $this->errorResponse('Employee ID already exists', 400);
        }

        $teacher->fill($this->requestData);

        if ($teacher->save()) {
            $this->successResponse($teacher->toArray(), 'Teacher updated successfully');
        } else {
            $this->errorResponse('Failed to update teacher', 500);
        }
    }

    /**
     * Delete teacher (soft delete)
     */
    private function deleteTeacher($id) {
        $this->requireAuth();
        $this->requireRole(['admin']);

        $teacher = Teacher::find($id);
        if (!$teacher) {
            $this->notFound('Teacher');
        }

        $teacher->is_active = false;

        if ($teacher->save()) {
            $this->successResponse(null, 'Teacher deleted successfully');
        } else {
            $this->errorResponse('Failed to delete teacher', 500);
        }
    }

    /**
     * Get teacher's assigned subjects
     */
    private function getTeacherSubjects($teacherId) {
        return $this->db->fetchAll(
            "SELECT cs.*, c.class_name, c.section, s.subject_name, s.subject_code
             FROM class_subjects cs
             LEFT JOIN classes c ON cs.class_id = c.id
             LEFT JOIN subjects s ON cs.subject_id = s.id
             WHERE cs.teacher_id = ?
             ORDER BY c.class_name, c.section, s.subject_name",
            [$teacherId]
        );
    }

    /**
     * Get teacher performance metrics
     */
    private function getTeacherPerformance($userId) {
        $month = date('m');
        $year = date('Y');

        // Get attendance marked by this teacher
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total_sessions FROM attendance WHERE marked_by = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?",
            [$userId, $month, $year]
        );
        $totalSessions = $result['total_sessions'] ?? 0;

        // Get exam results entered by this teacher
        $result = $this->db->fetch(
            "SELECT COUNT(*) as total_results FROM exam_results WHERE entered_by = ?",
            [$userId]
        );
        $totalResults = $result['total_results'] ?? 0;

        return [
            'total_sessions_this_month' => $totalSessions,
            'total_results_entered' => $totalResults
        ];
    }

    /**
     * Check if user has access to teacher data
     */
    private function checkTeacherAccess($teacher, $requireWrite = false) {
        $userRole = $this->user['role_name'];

        switch ($userRole) {
            case 'admin':
                // Admin has full access
                return;

            case 'teacher':
                // Teachers can only access their own data
                if ($teacher->user_id != $this->user['user_id']) {
                    $this->errorResponse('Access denied', 403);
                }
                if ($requireWrite) {
                    $this->errorResponse('Teachers cannot modify their profile data', 403);
                }
                break;

            default:
                $this->errorResponse('Access denied', 403);
        }
    }
}

// Initialize and handle request
$controller = new TeachersApiController();
$controller->handleRequest();