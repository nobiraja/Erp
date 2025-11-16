<?php
/**
 * Exams Controller
 * Handles exam management operations
 */

class ExamsController extends BaseController {

    /**
     * Display exams dashboard
     */
    public function index() {
        try {
            $page = $this->input('page', 1);
            $perPage = $this->input('per_page', 10);
            $search = $this->input('search', '');
            $classId = $this->input('class_id', '');
            $examType = $this->input('exam_type', '');
            $academicYear = $this->input('academic_year', date('Y') . '-' . (date('Y') + 1));

            $offset = ($page - 1) * $perPage;

            // Get filtered exams
            $exams = $this->getFilteredExams($search, $classId, $examType, $academicYear, $offset, $perPage);
            $totalRecords = $this->getFilteredExamsCount($search, $classId, $examType, $academicYear);

            // Get classes for filter dropdown
            $classes = ClassModel::allWithTeachers();

            // Get exam statistics
            $stats = $this->getExamStats();

            $data = [
                'title' => 'Exams & Results Management',
                'exams' => $exams,
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalRecords,
                'search' => $search,
                'class_id' => $classId,
                'exam_type' => $examType,
                'academic_year' => $academicYear,
                'classes' => $classes,
                'stats' => $stats,
                'total_pages' => ceil($totalRecords / $perPage)
            ];

            echo $this->view('admin.exams.index', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show add exam form
     */
    public function add() {
        try {
            $classes = ClassModel::allWithTeachers();

            $data = [
                'title' => 'Create New Exam',
                'classes' => $classes,
                'exam_types' => ['mid-term', 'final', 'unit-test', 'custom']
            ];

            echo $this->view('admin.exams.add', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Store new exam
     */
    public function store() {
        try {
            $data = [
                'exam_name' => $this->input('exam_name'),
                'exam_type' => $this->input('exam_type'),
                'class_id' => $this->input('class_id'),
                'academic_year' => $this->input('academic_year'),
                'start_date' => $this->input('start_date'),
                'end_date' => $this->input('end_date'),
                'created_by' => $this->getUserId(),
                'is_active' => $this->input('is_active', true)
            ];

            // Validate required fields
            $required = ['exam_name', 'exam_type', 'class_id', 'academic_year', 'start_date', 'end_date'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->error("{$field} is required");
                    return;
                }
            }

            // Validate dates
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                $this->error('End date cannot be before start date');
                return;
            }

            $exam = Exam::create($data);

            if ($exam) {
                $this->success(['id' => $exam->id], 'Exam created successfully');
            } else {
                $this->error('Failed to create exam');
            }
        } catch (Exception $e) {
            $this->error('An error occurred while creating the exam');
        }
    }

    /**
     * Show edit exam form
     */
    public function edit($id) {
        try {
            $exam = Exam::find($id);

            if (!$exam) {
                $this->flash('error', 'Exam not found');
                $this->redirect('/admin/exams');
                return;
            }

            $classes = ClassModel::allWithTeachers();

            $data = [
                'title' => 'Edit Exam',
                'exam' => $exam,
                'classes' => $classes,
                'exam_types' => ['mid-term', 'final', 'unit-test', 'custom']
            ];

            echo $this->view('admin.exams.edit', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Update exam
     */
    public function update($id) {
        try {
            $exam = Exam::find($id);

            if (!$exam) {
                $this->error('Exam not found');
                return;
            }

            $data = [
                'exam_name' => $this->input('exam_name'),
                'exam_type' => $this->input('exam_type'),
                'class_id' => $this->input('class_id'),
                'academic_year' => $this->input('academic_year'),
                'start_date' => $this->input('start_date'),
                'end_date' => $this->input('end_date'),
                'is_active' => $this->input('is_active', false)
            ];

            // Validate required fields
            $required = ['exam_name', 'exam_type', 'class_id', 'academic_year', 'start_date', 'end_date'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->error("{$field} is required");
                    return;
                }
            }

            // Validate dates
            if (strtotime($data['end_date']) < strtotime($data['start_date'])) {
                $this->error('End date cannot be before start date');
                return;
            }

            if ($exam->update($data)) {
                $this->success([], 'Exam updated successfully');
            } else {
                $this->error('Failed to update exam');
            }
        } catch (Exception $e) {
            $this->error('An error occurred while updating the exam');
        }
    }

    /**
     * Delete exam
     */
    public function destroy($id) {
        try {
            $exam = Exam::find($id);

            if (!$exam) {
                if ($this->isAjax()) {
                    $this->error('Exam not found');
                } else {
                    $this->flash('error', 'Exam not found');
                    $this->redirect('/admin/exams');
                }
                return;
            }

            if ($exam->delete()) {
                if ($this->isAjax()) {
                    $this->success([], 'Exam deleted successfully');
                } else {
                    $this->flash('success', 'Exam deleted successfully');
                    $this->redirect('/admin/exams');
                }
            } else {
                $message = 'Failed to delete exam';
                if ($this->isAjax()) {
                    $this->error($message);
                } else {
                    $this->flash('error', $message);
                    $this->back();
                }
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show exam results page
     */
    public function results($examId = null) {
        try {
            $examId = $examId ?: $this->input('exam_id');

            if (!$examId) {
                $this->flash('error', 'Exam ID is required');
                $this->redirect('/admin/exams');
                return;
            }

            $exam = Exam::find($examId);
            if (!$exam) {
                $this->flash('error', 'Exam not found');
                $this->redirect('/admin/exams');
                return;
            }

            $students = $exam->getStudents();
            $results = $exam->getResults();

            // Group results by student
            $resultsByStudent = [];
            foreach ($results as $result) {
                $studentId = $result['student_id'];
                if (!isset($resultsByStudent[$studentId])) {
                    $resultsByStudent[$studentId] = [
                        'student' => [
                            'id' => $result['student_id'],
                            'scholar_number' => $result['scholar_number'],
                            'name' => $result['first_name'] . ' ' . ($result['middle_name'] ? $result['middle_name'] . ' ' : '') . $result['last_name']
                        ],
                        'subjects' => []
                    ];
                }
                $resultsByStudent[$studentId]['subjects'][$result['subject_id']] = [
                    'subject_name' => $result['subject_name'],
                    'marks_obtained' => $result['marks_obtained'],
                    'max_marks' => $result['max_marks']
                ];
            }

            $data = [
                'title' => 'Exam Results - ' . $exam->exam_name,
                'exam' => $exam,
                'students' => $students,
                'results_by_student' => $resultsByStudent
            ];

            echo $this->view('admin.exams.results', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Save exam results
     */
    public function saveResults() {
        try {
            $examId = $this->input('exam_id');
            $results = $this->input('results', []);

            if (!$examId || empty($results)) {
                $this->error('Invalid data provided');
                return;
            }

            $exam = Exam::find($examId);
            if (!$exam) {
                $this->error('Exam not found');
                return;
            }

            $userId = $this->getUserId();
            $saved = 0;
            $errors = [];

            foreach ($results as $studentId => $studentResults) {
                foreach ($studentResults as $subjectId => $result) {
                    $marks = floatval($result['marks_obtained']);
                    $maxMarks = floatval($result['max_marks']);

                    if ($marks < 0 || $marks > $maxMarks) {
                        $errors[] = "Invalid marks for student {$studentId}, subject {$subjectId}";
                        continue;
                    }

                    // Check if result already exists
                    $existing = $this->db->fetch(
                        "SELECT id FROM exam_results WHERE exam_id = ? AND student_id = ? AND subject_id = ?",
                        [$examId, $studentId, $subjectId]
                    );

                    $resultData = [
                        'exam_id' => $examId,
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'marks_obtained' => $marks,
                        'max_marks' => $maxMarks,
                        'percentage' => round(($marks / $maxMarks) * 100, 2),
                        'entered_by' => $userId
                    ];

                    if ($existing) {
                        // Update existing
                        $this->db->update('exam_results', $resultData, ['id' => $existing['id']]);
                    } else {
                        // Create new
                        $this->db->insert('exam_results', $resultData);
                    }
                    $saved++;
                }
            }

            if ($saved > 0) {
                $this->success(['saved' => $saved], "Results saved successfully for {$saved} entries");
            } else {
                $this->error('Failed to save results', $errors);
            }
        } catch (Exception $e) {
            $this->error('An error occurred while saving results');
        }
    }

    /**
     * Show subject schedule page
     */
    public function schedule($examId = null) {
        try {
            $examId = $examId ?: $this->input('exam_id');

            if (!$examId) {
                $this->flash('error', 'Exam ID is required');
                $this->redirect('/admin/exams');
                return;
            }

            $exam = Exam::find($examId);
            if (!$exam) {
                $this->flash('error', 'Exam not found');
                $this->redirect('/admin/exams');
                return;
            }

            // Get subjects for this class
            $subjects = ClassSubjectModel::getByClass($exam->class_id);
            $schedule = $exam->getSubjectsWithSchedule();

            $data = [
                'title' => 'Subject Schedule - ' . $exam->exam_name,
                'exam' => $exam,
                'subjects' => $subjects,
                'schedule' => $schedule
            ];

            echo $this->view('admin.exams.schedule', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Save subject schedule
     */
    public function saveSchedule() {
        try {
            $examId = $this->input('exam_id');
            $schedule = $this->input('schedule', []);

            if (!$examId) {
                $this->error('Exam ID is required');
                return;
            }

            $exam = Exam::find($examId);
            if (!$exam) {
                $this->error('Exam not found');
                return;
            }

            // First, delete existing schedule
            $this->db->delete('exam_subjects', ['exam_id' => $examId]);

            $saved = 0;
            foreach ($schedule as $item) {
                if (!empty($item['subject_id']) && !empty($item['exam_date'])) {
                    $scheduleData = [
                        'exam_id' => $examId,
                        'subject_id' => $item['subject_id'],
                        'exam_date' => $item['exam_date'],
                        'day' => $item['day'] ?? '',
                        'start_time' => $item['start_time'] ?? '',
                        'end_time' => $item['end_time'] ?? ''
                    ];

                    $this->db->insert('exam_subjects', $scheduleData);
                    $saved++;
                }
            }

            $this->success(['saved' => $saved], "Schedule saved successfully for {$saved} subjects");
        } catch (Exception $e) {
            $this->error('An error occurred while saving schedule');
        }
    }

    /**
     * Generate admit cards
     */
    public function admitCards() {
        try {
            $examId = $this->input('exam_id');
            $studentIds = $this->input('student_ids', []);
            $format = $this->input('format', 'individual'); // individual or bulk

            if (!$examId) {
                $this->flash('error', 'Exam ID is required');
                $this->redirect('/admin/exams');
                return;
            }

            $exam = Exam::find($examId);
            if (!$exam) {
                $this->flash('error', 'Exam not found');
                $this->redirect('/admin/exams');
                return;
            }

            $students = empty($studentIds) ? $exam->getStudents() : $this->getStudentsByIds($studentIds);
            $schedule = $exam->getSubjectsWithSchedule();

            if ($format === 'bulk') {
                $this->generateBulkAdmitCards($exam, $students, $schedule);
            } else {
                $this->generateIndividualAdmitCards($exam, $students, $schedule);
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Generate marksheets
     */
    public function marksheets() {
        try {
            $examId = $this->input('exam_id');
            $studentIds = $this->input('student_ids', []);
            $format = $this->input('format', 'individual'); // individual or bulk

            if (!$examId) {
                $this->flash('error', 'Exam ID is required');
                $this->redirect('/admin/exams');
                return;
            }

            $exam = Exam::find($examId);
            if (!$exam) {
                $this->flash('error', 'Exam not found');
                $this->redirect('/admin/exams');
                return;
            }

            $results = $exam->calculateGradesAndRanks();

            if ($format === 'bulk') {
                $this->generateBulkMarksheets($exam, $results);
            } else {
                $this->generateIndividualMarksheets($exam, $results);
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Get students by IDs
     */
    private function getStudentsByIds($studentIds) {
        if (empty($studentIds)) return [];

        $placeholders = str_repeat('?,', count($studentIds) - 1) . '?';
        $sql = "SELECT s.*, c.class_name, c.section
                FROM students s
                LEFT JOIN classes c ON s.class_id = c.id
                WHERE s.id IN ({$placeholders}) AND s.is_active = 1
                ORDER BY s.first_name, s.last_name";

        return $this->db->fetchAll($sql, $studentIds);
    }

    /**
     * Generate bulk admit cards PDF
     */
    private function generateBulkAdmitCards($exam, $students, $schedule) {
        require_once 'libraries/tcpdf/tcpdf.php';

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('School Management System');
        $pdf->SetAuthor('School Admin');
        $pdf->SetTitle('Admit Cards - ' . $exam->exam_name);

        // Set margins
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 10);

        $cardsPerPage = 2;
        $cardCount = 0;

        foreach ($students as $student) {
            if ($cardCount % $cardsPerPage == 0) {
                $pdf->AddPage();
            }

            $this->addAdmitCardToPDF($pdf, $exam, $student, $schedule, $cardCount % $cardsPerPage);
            $cardCount++;
        }

        $pdf->Output('admit_cards_' . $exam->exam_name . '.pdf', 'D');
        exit;
    }

    /**
     * Generate individual admit cards PDF
     */
    private function generateIndividualAdmitCards($exam, $students, $schedule) {
        require_once 'libraries/tcpdf/tcpdf.php';

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('School Management System');
        $pdf->SetAuthor('School Admin');
        $pdf->SetTitle('Admit Cards - ' . $exam->exam_name);

        foreach ($students as $student) {
            $pdf->AddPage();
            $this->addAdmitCardToPDF($pdf, $exam, $student, $schedule, 0);
        }

        $pdf->Output('admit_cards_' . $exam->exam_name . '.pdf', 'D');
        exit;
    }

    /**
     * Add admit card to PDF
     */
    private function addAdmitCardToPDF($pdf, $exam, $student, $schedule, $position = 0) {
        $yOffset = $position * 140;

        // School Header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetXY(10, 10 + $yOffset);
        $pdf->Cell(0, 10, 'SCHOOL MANAGEMENT SYSTEM', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetXY(10, 20 + $yOffset);
        $pdf->Cell(0, 8, 'ADMIT CARD', 0, 1, 'C');

        // Exam Details
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, 35 + $yOffset);
        $pdf->Cell(50, 6, 'Exam Name:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $exam->exam_name, 0, 1);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, 41 + $yOffset);
        $pdf->Cell(50, 6, 'Class:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $student['class_name'] . ' ' . $student['section'], 0, 1);

        // Student Details
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, 50 + $yOffset);
        $pdf->Cell(50, 6, 'Scholar Number:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $student['scholar_number'], 0, 1);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, 56 + $yOffset);
        $pdf->Cell(50, 6, 'Student Name:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name'], 0, 1);

        // Subject Schedule Table
        $pdf->SetXY(10, 70 + $yOffset);
        $pdf->SetFont('helvetica', 'B', 8);

        // Table headers
        $pdf->Cell(40, 6, 'Subject', 1, 0, 'C');
        $pdf->Cell(25, 6, 'Date', 1, 0, 'C');
        $pdf->Cell(15, 6, 'Day', 1, 0, 'C');
        $pdf->Cell(20, 6, 'Start Time', 1, 0, 'C');
        $pdf->Cell(20, 6, 'End Time', 1, 1, 'C');

        // Table data
        $pdf->SetFont('helvetica', '', 8);
        foreach ($schedule as $item) {
            $pdf->Cell(40, 6, $item['subject_name'], 1, 0);
            $pdf->Cell(25, 6, date('d/m/Y', strtotime($item['exam_date'])), 1, 0, 'C');
            $pdf->Cell(15, 6, $item['day'], 1, 0, 'C');
            $pdf->Cell(20, 6, $item['start_time'], 1, 0, 'C');
            $pdf->Cell(20, 6, $item['end_time'], 1, 1, 'C');
        }

        // Signature areas
        $pdf->SetXY(10, 120 + $yOffset);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(60, 6, 'Principal Signature: ___________________', 0, 0);
        $pdf->Cell(60, 6, 'Exam Controller: ___________________', 0, 0);
        $pdf->Cell(60, 6, 'School Seal', 0, 1);
    }

    /**
     * Generate bulk marksheets PDF
     */
    private function generateBulkMarksheets($exam, $results) {
        require_once 'libraries/tcpdf/tcpdf.php';

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('School Management System');
        $pdf->SetAuthor('School Admin');
        $pdf->SetTitle('Marksheets - ' . $exam->exam_name);

        foreach ($results as $result) {
            $pdf->AddPage();
            $this->addMarksheetToPDF($pdf, $exam, $result);
        }

        $pdf->Output('marksheets_' . $exam->exam_name . '.pdf', 'D');
        exit;
    }

    /**
     * Generate individual marksheets PDF
     */
    private function generateIndividualMarksheets($exam, $results) {
        require_once 'libraries/tcpdf/tcpdf.php';

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('School Management System');
        $pdf->SetAuthor('School Admin');
        $pdf->SetTitle('Marksheets - ' . $exam->exam_name);

        foreach ($results as $result) {
            $pdf->AddPage();
            $this->addMarksheetToPDF($pdf, $exam, $result);
        }

        $pdf->Output('marksheets_' . $exam->exam_name . '.pdf', 'D');
        exit;
    }

    /**
     * Add marksheet to PDF
     */
    private function addMarksheetToPDF($pdf, $exam, $result) {
        // School Header
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetXY(10, 10);
        $pdf->Cell(0, 10, 'SCHOOL MANAGEMENT SYSTEM', 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetXY(10, 20);
        $pdf->Cell(0, 8, 'MARKSHEET', 0, 1, 'C');

        // Exam and Student Details
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, 35);
        $pdf->Cell(50, 6, 'Exam Name:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $exam->exam_name, 0, 1);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, 41);
        $pdf->Cell(50, 6, 'Scholar Number:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $result['scholar_number'], 0, 1);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, 47);
        $pdf->Cell(50, 6, 'Student Name:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $result['first_name'] . ' ' . ($result['middle_name'] ? $result['middle_name'] . ' ' : '') . $result['last_name'], 0, 1);

        // Marks Table
        $pdf->SetXY(10, 60);
        $pdf->SetFont('helvetica', 'B', 8);

        // Table headers
        $pdf->Cell(60, 6, 'Subject', 1, 0, 'C');
        $pdf->Cell(25, 6, 'Marks Obtained', 1, 0, 'C');
        $pdf->Cell(25, 6, 'Max Marks', 1, 0, 'C');
        $pdf->Cell(20, 6, 'Grade', 1, 1, 'C');

        // Parse marks and subjects
        $subjects = explode(',', $result['subjects']);
        $marks = explode(',', $result['marks']);
        $maxMarks = explode(',', $result['max_marks']);

        $pdf->SetFont('helvetica', '', 8);
        for ($i = 0; $i < count($subjects); $i++) {
            $grade = $this->calculateGrade(($marks[$i] / $maxMarks[$i]) * 100);
            $pdf->Cell(60, 6, $subjects[$i], 1, 0);
            $pdf->Cell(25, 6, $marks[$i], 1, 0, 'C');
            $pdf->Cell(25, 6, $maxMarks[$i], 1, 0, 'C');
            $pdf->Cell(20, 6, $grade, 1, 1, 'C');
        }

        // Total and Summary
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, 100);
        $pdf->Cell(50, 6, 'Total Marks:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $result['total_marks'] . ' / ' . $result['total_max_marks'], 0, 1);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, 106);
        $pdf->Cell(50, 6, 'Percentage:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $result['percentage'] . '%', 0, 1);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, 112);
        $pdf->Cell(50, 6, 'Grade:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $result['grade'], 0, 1);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetXY(10, 118);
        $pdf->Cell(50, 6, 'Rank:', 0, 0);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 6, $result['rank'], 0, 1);

        // Signature areas
        $pdf->SetXY(10, 140);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(60, 6, 'Principal Signature: ___________________', 0, 0);
        $pdf->Cell(60, 6, 'Class Teacher: ___________________', 0, 0);
        $pdf->Cell(60, 6, 'Exam Controller: ___________________', 0, 1);
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
        if ($percentage >= 33) return 'D';
        return 'F';
    }

    // Helper methods

    private function getFilteredExams($search, $classId, $examType, $academicYear, $offset, $limit) {
        $instance = new Exam();

        $whereClause = "1=1";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND e.exam_name LIKE ?";
            $params[] = "%{$search}%";
        }

        if (!empty($classId)) {
            $whereClause .= " AND e.class_id = ?";
            $params[] = $classId;
        }

        if (!empty($examType)) {
            $whereClause .= " AND e.exam_type = ?";
            $params[] = $examType;
        }

        if (!empty($academicYear)) {
            $whereClause .= " AND e.academic_year = ?";
            $params[] = $academicYear;
        }

        $sql = "SELECT e.*, c.class_name, c.section, u.username as created_by_name
                FROM exams e
                LEFT JOIN classes c ON e.class_id = c.id
                LEFT JOIN users u ON e.created_by = u.id
                WHERE {$whereClause}
                ORDER BY e.start_date DESC
                LIMIT {$limit} OFFSET {$offset}";

        return $instance->db->fetchAll($sql, $params);
    }

    private function getFilteredExamsCount($search, $classId, $examType, $academicYear) {
        $instance = new Exam();

        $whereClause = "1=1";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND e.exam_name LIKE ?";
            $params[] = "%{$search}%";
        }

        if (!empty($classId)) {
            $whereClause .= " AND e.class_id = ?";
            $params[] = $classId;
        }

        if (!empty($examType)) {
            $whereClause .= " AND e.exam_type = ?";
            $params[] = $examType;
        }

        if (!empty($academicYear)) {
            $whereClause .= " AND e.academic_year = ?";
            $params[] = $academicYear;
        }

        $sql = "SELECT COUNT(*) as count FROM exams e WHERE {$whereClause}";

        $result = $instance->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }

    private function getExamStats() {
        $instance = new Exam();

        $sql = "SELECT
                    COUNT(*) as total_exams,
                    COUNT(CASE WHEN is_active = 1 THEN 1 END) as active_exams,
                    COUNT(DISTINCT class_id) as classes_with_exams,
                    COUNT(DISTINCT CASE WHEN start_date >= CURDATE() THEN id END) as upcoming_exams
                FROM exams";

        return $instance->db->fetch($sql);
    }
}