<?php
/**
 * Exams API Endpoints
 * Handles examination results and schedules
 */

require_once '../../../controllers/ApiController.php';

class ExamsApiController extends ApiController {
    /**
     * Handle API requests
     */
    public function handleRequest() {
        $action = $_GET['action'] ?? 'list';

        switch ($this->requestMethod) {
            case 'GET':
                $this->handleGet($action);
                break;
            case 'POST':
                $this->handlePost($action);
                break;
            case 'PUT':
                $this->handlePut($action);
                break;
            default:
                $this->methodNotAllowed();
        }
    }

    /**
     * Handle GET requests
     */
    private function handleGet($action) {
        switch ($action) {
            case 'list':
                $this->getExams();
                break;
            case 'results':
                $this->getExamResults();
                break;
            case 'schedule':
                $this->getExamSchedule();
                break;
            case 'student':
                $this->getStudentResults();
                break;
            default:
                $this->errorResponse('Invalid action', 400);
        }
    }

    /**
     * Handle POST requests
     */
    private function handlePost($action) {
        switch ($action) {
            case 'create':
                $this->createExam();
                break;
            case 'result':
                $this->enterResult();
                break;
            default:
                $this->errorResponse('Invalid action', 400);
        }
    }

    /**
     * Handle PUT requests
     */
    private function handlePut($action) {
        switch ($action) {
            case 'result':
                $this->updateResult();
                break;
            default:
                $this->errorResponse('Invalid action', 400);
        }
    }

    /**
     * Get exams list
     */
    private function getExams() {
        $this->requireAuth();

        $classId = $this->requestData['class_id'] ?? null;
        $academicYear = $this->requestData['academic_year'] ?? null;
        $examType = $this->requestData['exam_type'] ?? null;
        $isActive = $this->requestData['is_active'] ?? '1';

        $query = "SELECT e.*, c.class_name, c.section,
                         u.username as created_by_name
                  FROM exams e
                  LEFT JOIN classes c ON e.class_id = c.id
                  LEFT JOIN users u ON e.created_by = u.id
                  WHERE 1=1";

        $params = [];

        if ($isActive !== null) {
            $query .= " AND e.is_active = ?";
            $params[] = $isActive;
        }

        if ($classId) {
            $query .= " AND e.class_id = ?";
            $params[] = $classId;
        }

        if ($academicYear) {
            $query .= " AND e.academic_year = ?";
            $params[] = $academicYear;
        }

        if ($examType) {
            $query .= " AND e.exam_type = ?";
            $params[] = $examType;
        }

        $query .= " ORDER BY e.start_date DESC, e.exam_name";

        $result = $this->getPaginatedResults('exams', '', $params, 'e.start_date DESC');

        $this->successResponse($result);
    }

    /**
     * Get exam results
     */
    private function getExamResults() {
        $this->requireAuth();

        $examId = $this->requestData['exam_id'] ?? null;
        $studentId = $this->requestData['student_id'] ?? null;
        $subjectId = $this->requestData['subject_id'] ?? null;

        if (!$examId) {
            $this->errorResponse('Exam ID required', 400);
        }

        // Check access permissions
        $this->checkExamAccess($examId);

        $query = "SELECT er.*, e.exam_name, e.exam_type,
                         s.first_name, s.last_name, s.scholar_number,
                         sub.subject_name, sub.subject_code,
                         c.class_name, c.section,
                         u.username as entered_by_name
                  FROM exam_results er
                  LEFT JOIN exams e ON er.exam_id = e.id
                  LEFT JOIN students s ON er.student_id = s.id
                  LEFT JOIN subjects sub ON er.subject_id = sub.id
                  LEFT JOIN classes c ON s.class_id = c.id
                  LEFT JOIN users u ON er.entered_by = u.id
                  WHERE er.exam_id = ?";

        $params = [$examId];

        if ($studentId) {
            $query .= " AND er.student_id = ?";
            $params[] = $studentId;
        }

        if ($subjectId) {
            $query .= " AND er.subject_id = ?";
            $params[] = $subjectId;
        }

        $query .= " ORDER BY s.first_name, s.last_name, sub.subject_name";

        $results = $this->db->fetchAll($query, $params);

        // Calculate statistics
        $stats = $this->calculateExamStats($results);

        $result = [
            'exam_id' => $examId,
            'results' => $results,
            'statistics' => $stats
        ];

        $this->successResponse($result);
    }

    /**
     * Get exam schedule
     */
    private function getExamSchedule() {
        $this->requireAuth();

        $examId = $this->requestData['exam_id'] ?? null;

        if (!$examId) {
            $this->errorResponse('Exam ID required', 400);
        }

        // Check access permissions
        $this->checkExamAccess($examId);

        $schedule = $this->db->fetchAll(
            "SELECT es.*, e.exam_name, s.subject_name, s.subject_code
             FROM exam_schedules es
             LEFT JOIN exams e ON es.exam_id = e.id
             LEFT JOIN subjects s ON es.subject_id = s.id
             WHERE es.exam_id = ?
             ORDER BY es.exam_date, es.start_time",
            [$examId]
        );

        $this->successResponse([
            'exam_id' => $examId,
            'schedule' => $schedule
        ]);
    }

    /**
     * Get student results
     */
    private function getStudentResults() {
        $this->requireAuth();

        $studentId = $this->requestData['student_id'] ?? null;

        if (!$studentId) {
            $this->errorResponse('Student ID required', 400);
        }

        // Check access permissions
        $this->checkStudentResultsAccess($studentId);

        $results = $this->db->fetchAll(
            "SELECT er.*, e.exam_name, e.exam_type, e.academic_year,
                    s.subject_name, s.subject_code,
                    c.class_name, c.section
             FROM exam_results er
             LEFT JOIN exams e ON er.exam_id = e.id
             LEFT JOIN subjects s ON er.subject_id = s.id
             LEFT JOIN students st ON er.student_id = st.id
             LEFT JOIN classes c ON st.class_id = c.id
             WHERE er.student_id = ?
             ORDER BY e.start_date DESC, s.subject_name",
            [$studentId]
        );

        // Calculate GPA and overall performance
        $performance = $this->calculateStudentPerformance($results);

        $this->successResponse([
            'student_id' => $studentId,
            'results' => $results,
            'performance' => $performance
        ]);
    }

    /**
     * Create new exam
     */
    private function createExam() {
        $this->requireAuth();
        $this->requireRole(['admin', 'teacher']);

        $this->validateRequired([
            'exam_name', 'exam_type', 'class_id', 'academic_year',
            'start_date', 'end_date'
        ]);

        $exam = [
            'exam_name' => $this->requestData['exam_name'],
            'exam_type' => $this->requestData['exam_type'],
            'class_id' => $this->requestData['class_id'],
            'academic_year' => $this->requestData['academic_year'],
            'start_date' => $this->requestData['start_date'],
            'end_date' => $this->requestData['end_date'],
            'created_by' => $this->user['user_id'],
            'is_active' => $this->requestData['is_active'] ?? true,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $examId = $this->db->insert('exams', $exam);

        if ($examId) {
            $this->successResponse([
                'exam_id' => $examId,
                'exam' => $exam
            ], 'Exam created successfully', 201);
        } else {
            $this->errorResponse('Failed to create exam', 500);
        }
    }

    /**
     * Enter exam result
     */
    private function enterResult() {
        $this->requireAuth();
        $this->requireRole(['admin', 'teacher']);

        $this->validateRequired([
            'exam_id', 'student_id', 'subject_id', 'marks_obtained', 'max_marks'
        ]);

        $examId = $this->requestData['exam_id'];
        $studentId = $this->requestData['student_id'];
        $subjectId = $this->requestData['subject_id'];

        // Check if result already exists
        $existing = $this->db->fetch(
            "SELECT id FROM exam_results WHERE exam_id = ? AND student_id = ? AND subject_id = ?",
            [$examId, $studentId, $subjectId]
        );

        if ($existing) {
            $this->errorResponse('Result already exists for this exam, student, and subject', 400);
        }

        // Calculate percentage and grade
        $marksObtained = $this->requestData['marks_obtained'];
        $maxMarks = $this->requestData['max_marks'];
        $percentage = ($marksObtained / $maxMarks) * 100;
        $grade = $this->calculateGrade($percentage);

        $result = [
            'exam_id' => $examId,
            'student_id' => $studentId,
            'subject_id' => $subjectId,
            'marks_obtained' => $marksObtained,
            'max_marks' => $maxMarks,
            'grade' => $grade,
            'percentage' => round($percentage, 2),
            'remarks' => $this->requestData['remarks'] ?? null,
            'entered_by' => $this->user['user_id'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $resultId = $this->db->insert('exam_results', $result);

        if ($resultId) {
            $this->successResponse([
                'result_id' => $resultId,
                'result' => $result
            ], 'Result entered successfully', 201);
        } else {
            $this->errorResponse('Failed to enter result', 500);
        }
    }

    /**
     * Update exam result
     */
    private function updateResult() {
        $this->requireAuth();
        $this->requireRole(['admin', 'teacher']);

        $resultId = $_GET['id'] ?? null;

        if (!$resultId) {
            $this->errorResponse('Result ID required', 400);
        }

        $result = $this->db->fetch("SELECT * FROM exam_results WHERE id = ?", [$resultId]);

        if (!$result) {
            $this->notFound('Exam result');
        }

        $updateData = [];

        if (isset($this->requestData['marks_obtained'])) {
            $updateData['marks_obtained'] = $this->requestData['marks_obtained'];
            $percentage = ($this->requestData['marks_obtained'] / $result['max_marks']) * 100;
            $updateData['percentage'] = round($percentage, 2);
            $updateData['grade'] = $this->calculateGrade($percentage);
        }

        if (isset($this->requestData['remarks'])) {
            $updateData['remarks'] = $this->requestData['remarks'];
        }

        $updateData['updated_at'] = date('Y-m-d H:i:s');

        $success = $this->db->update('exam_results', $updateData, 'id = ?', [$resultId]);

        if ($success) {
            $this->successResponse($updateData, 'Result updated successfully');
        } else {
            $this->errorResponse('Failed to update result', 500);
        }
    }

    /**
     * Calculate grade based on percentage
     */
    private function calculateGrade($percentage) {
        if ($percentage >= 90) return 'A+';
        if ($percentage >= 80) return 'A';
        if ($percentage >= 70) return 'B+';
        if ($percentage >= 60) return 'B';
        if ($percentage >= 50) return 'C+';
        if ($percentage >= 40) return 'C';
        return 'F';
    }

    /**
     * Calculate exam statistics
     */
    private function calculateExamStats($results) {
        if (empty($results)) {
            return [
                'total_students' => 0,
                'average_percentage' => 0,
                'highest_score' => 0,
                'lowest_score' => 0,
                'pass_percentage' => 0
            ];
        }

        $totalStudents = count($results);
        $totalPercentage = 0;
        $highestScore = 0;
        $lowestScore = 100;
        $passed = 0;

        foreach ($results as $result) {
            $percentage = $result['percentage'];
            $totalPercentage += $percentage;
            $highestScore = max($highestScore, $percentage);
            $lowestScore = min($lowestScore, $percentage);

            if ($percentage >= 40) { // Assuming 40% is pass mark
                $passed++;
            }
        }

        return [
            'total_students' => $totalStudents,
            'average_percentage' => round($totalPercentage / $totalStudents, 2),
            'highest_score' => $highestScore,
            'lowest_score' => $lowestScore,
            'pass_percentage' => round(($passed / $totalStudents) * 100, 2)
        ];
    }

    /**
     * Calculate student performance
     */
    private function calculateStudentPerformance($results) {
        if (empty($results)) {
            return [
                'total_exams' => 0,
                'average_percentage' => 0,
                'overall_grade' => 'N/A',
                'subjects_passed' => 0,
                'subjects_failed' => 0
            ];
        }

        $totalExams = count($results);
        $totalPercentage = 0;
        $passed = 0;
        $failed = 0;

        foreach ($results as $result) {
            $percentage = $result['percentage'];
            $totalPercentage += $percentage;

            if ($percentage >= 40) {
                $passed++;
            } else {
                $failed++;
            }
        }

        $averagePercentage = round($totalPercentage / $totalExams, 2);
        $overallGrade = $this->calculateGrade($averagePercentage);

        return [
            'total_exams' => $totalExams,
            'average_percentage' => $averagePercentage,
            'overall_grade' => $overallGrade,
            'subjects_passed' => $passed,
            'subjects_failed' => $failed
        ];
    }

    /**
     * Check access to exam data
     */
    private function checkExamAccess($examId) {
        $userRole = $this->user['role_name'];

        switch ($userRole) {
            case 'admin':
                return; // Full access

            case 'teacher':
                // Teachers can access exams for classes they teach
                $exam = $this->db->fetch("SELECT class_id FROM exams WHERE id = ?", [$examId]);
                if ($exam) {
                    $assigned = $this->db->fetch(
                        "SELECT id FROM class_subjects WHERE class_id = ? AND teacher_id = ?",
                        [$exam['class_id'], $this->user['user_id']]
                    );
                    if (!$assigned) {
                        $this->errorResponse('Access denied to this exam', 403);
                    }
                }
                break;

            default:
                $this->errorResponse('Access denied', 403);
        }
    }

    /**
     * Check access to student results
     */
    private function checkStudentResultsAccess($studentId) {
        $userRole = $this->user['role_name'];

        switch ($userRole) {
            case 'admin':
            case 'teacher':
                return; // Full access for viewing results

            case 'student':
                // Students can only view their own results
                $student = $this->db->fetch("SELECT user_id FROM students WHERE id = ?", [$studentId]);
                if (!$student || $student['user_id'] != $this->user['user_id']) {
                    $this->errorResponse('Access denied', 403);
                }
                break;

            case 'parent':
                // Parents can view their children's results
                $this->errorResponse('Parent access not implemented', 403);
                break;

            default:
                $this->errorResponse('Access denied', 403);
        }
    }
}

// Initialize and handle request
$controller = new ExamsApiController();
$controller->handleRequest();