<?php
/**
 * Students API Endpoints
 * Handles CRUD operations for students
 */

require_once '../../../controllers/ApiController.php';

class StudentsApiController extends ApiController {
    /**
     * Handle API requests
     */
    public function handleRequest() {
        $id = $_GET['id'] ?? null;

        switch ($this->requestMethod) {
            case 'GET':
                if ($id) {
                    $this->getStudent($id);
                } else {
                    $this->getStudents();
                }
                break;
            case 'POST':
                $this->createStudent();
                break;
            case 'PUT':
                if (!$id) {
                    $this->errorResponse('Student ID required', 400);
                }
                $this->updateStudent($id);
                break;
            case 'DELETE':
                if (!$id) {
                    $this->errorResponse('Student ID required', 400);
                }
                $this->deleteStudent($id);
                break;
            default:
                $this->methodNotAllowed();
        }
    }

    /**
     * Get all students with pagination and filtering
     */
    private function getStudents() {
        $this->requireAuth();

        $classId = $this->requestData['class_id'] ?? null;
        $section = $this->requestData['section'] ?? null;
        $search = $this->requestData['search'] ?? null;
        $isActive = $this->requestData['is_active'] ?? '1';

        $query = "SELECT s.*, c.class_name, c.section as class_section, c.academic_year
                  FROM students s
                  LEFT JOIN classes c ON s.class_id = c.id
                  WHERE 1=1";

        $params = [];

        if ($isActive !== null) {
            $query .= " AND s.is_active = ?";
            $params[] = $isActive;
        }

        if ($classId) {
            $query .= " AND s.class_id = ?";
            $params[] = $classId;
        }

        if ($section) {
            $query .= " AND s.section = ?";
            $params[] = $section;
        }

        if ($search) {
            $query .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.scholar_number LIKE ? OR s.admission_number LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?)";
            $searchParam = "%{$search}%";
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
        }

        $query .= " ORDER BY s.first_name, s.last_name";

        $result = $this->getPaginatedResults('students', '', $params, 's.first_name, s.last_name');

        // Add additional data for each student
        foreach ($result['data'] as &$student) {
            $student['full_name'] = trim($student['first_name'] . ' ' . ($student['middle_name'] ?? '') . ' ' . $student['last_name']);
            $student['age'] = $student['dob'] ? (new DateTime())->diff(new DateTime($student['dob']))->y : null;

            // Get attendance percentage (current month)
            $attendance = $this->getStudentAttendance($student['id']);
            $student['attendance_percentage'] = $attendance['percentage'];

            // Get fees status
            $fees = $this->getStudentFeesStatus($student['id']);
            $student['fees_status'] = $fees;
        }

        $this->successResponse($result);
    }

    /**
     * Get single student
     */
    private function getStudent($id) {
        $this->requireAuth();

        $student = Student::withClass($id);

        if (!$student) {
            $this->notFound('Student');
        }

        // Check permissions - students can only view their own data, teachers can view their class students, etc.
        $this->checkStudentAccess($student);

        $data = $student->toArray();
        $data['full_name'] = $student->getFullName();
        $data['age'] = $student->getAge();

        // Add related data
        $data['attendance_percentage'] = $student->getAttendancePercentage();
        $data['fees_status'] = $student->getFeesStatus();

        $this->successResponse($data);
    }

    /**
     * Create new student
     */
    private function createStudent() {
        $this->requireAuth();
        $this->requireRole(['admin', 'teacher']);

        $this->validateRequired([
            'scholar_number', 'admission_number', 'first_name', 'last_name',
            'class_id', 'section', 'dob', 'gender'
        ]);

        // Check for duplicate scholar/admission numbers
        if (Student::scholarNumberExists($this->requestData['scholar_number'])) {
            $this->errorResponse('Scholar number already exists', 400);
        }

        if (Student::admissionNumberExists($this->requestData['admission_number'])) {
            $this->errorResponse('Admission number already exists', 400);
        }

        $student = new Student($this->requestData);
        $student->admission_date = $this->requestData['admission_date'] ?? date('Y-m-d');
        $student->is_active = $this->requestData['is_active'] ?? true;

        if ($student->save()) {
            $this->successResponse($student->toArray(), 'Student created successfully', 201);
        } else {
            $this->errorResponse('Failed to create student', 500);
        }
    }

    /**
     * Update student
     */
    private function updateStudent($id) {
        $this->requireAuth();

        $student = Student::find($id);
        if (!$student) {
            $this->notFound('Student');
        }

        $this->checkStudentAccess($student, true);

        // Check for duplicate scholar/admission numbers (excluding current student)
        if (isset($this->requestData['scholar_number']) &&
            Student::scholarNumberExists($this->requestData['scholar_number'], $id)) {
            $this->errorResponse('Scholar number already exists', 400);
        }

        if (isset($this->requestData['admission_number']) &&
            Student::admissionNumberExists($this->requestData['admission_number'], $id)) {
            $this->errorResponse('Admission number already exists', 400);
        }

        $student->fill($this->requestData);

        if ($student->save()) {
            $this->successResponse($student->toArray(), 'Student updated successfully');
        } else {
            $this->errorResponse('Failed to update student', 500);
        }
    }

    /**
     * Delete student (soft delete by setting is_active = false)
     */
    private function deleteStudent($id) {
        $this->requireAuth();
        $this->requireRole(['admin']);

        $student = Student::find($id);
        if (!$student) {
            $this->notFound('Student');
        }

        $student->is_active = false;

        if ($student->save()) {
            $this->successResponse(null, 'Student deleted successfully');
        } else {
            $this->errorResponse('Failed to delete student', 500);
        }
    }

    /**
     * Check if user has access to student data
     */
    private function checkStudentAccess($student, $requireWrite = false) {
        $userRole = $this->user['role_name'];

        switch ($userRole) {
            case 'admin':
                // Admin has full access
                return;

            case 'teacher':
                // Teachers can access students in their assigned classes
                if ($requireWrite) {
                    $this->errorResponse('Teachers cannot modify student data', 403);
                }
                // Check if teacher is assigned to student's class
                $assignedSubjects = $this->db->fetchAll(
                    "SELECT cs.* FROM class_subjects cs
                     JOIN teachers t ON cs.teacher_id = t.id
                     WHERE t.user_id = ? AND cs.class_id = ?",
                    [$this->user['user_id'], $student->class_id]
                );
                if (empty($assignedSubjects)) {
                    $this->errorResponse('Access denied to this student', 403);
                }
                break;

            case 'student':
                // Students can only access their own data
                if ($student->user_id != $this->user['user_id']) {
                    $this->errorResponse('Access denied', 403);
                }
                if ($requireWrite) {
                    $this->errorResponse('Students cannot modify their data', 403);
                }
                break;

            case 'parent':
                // Parents can access their children's data
                // This would require a parent-child relationship table
                $this->errorResponse('Parent access not implemented yet', 403);
                break;

            default:
                $this->errorResponse('Access denied', 403);
        }
    }

    /**
     * Get student attendance data
     */
    private function getStudentAttendance($studentId) {
        $month = date('m');
        $year = date('Y');

        $result = $this->db->fetch(
            "SELECT COUNT(*) as total_days,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
             FROM attendance
             WHERE student_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?",
            [$studentId, $month, $year]
        );

        $total = $result['total_days'] ?? 0;
        $present = $result['present_days'] ?? 0;

        return [
            'total_days' => $total,
            'present_days' => $present,
            'percentage' => $total > 0 ? round(($present / $total) * 100, 2) : 0
        ];
    }

    /**
     * Get student fees status
     */
    private function getStudentFeesStatus($studentId) {
        $result = $this->db->fetch(
            "SELECT SUM(amount) as total_fees,
                    SUM(CASE WHEN is_paid = 1 THEN amount ELSE 0 END) as paid_fees
             FROM fees
             WHERE student_id = ?",
            [$studentId]
        );

        $total = $result['total_fees'] ?? 0;
        $paid = $result['paid_fees'] ?? 0;
        $pending = $total - $paid;

        return [
            'total' => $total,
            'paid' => $paid,
            'pending' => $pending,
            'percentage' => $total > 0 ? round(($paid / $total) * 100, 2) : 0
        ];
    }
}

// Initialize and handle request
$controller = new StudentsApiController();
$controller->handleRequest();