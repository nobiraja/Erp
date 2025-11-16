<?php
/**
 * Attendance Controller
 * Handles attendance management operations
 */

class AttendanceController extends BaseController {

    /**
     * Display attendance dashboard
     */
    public function index() {
        try {
            $page = $this->input('page', 1);
            $perPage = $this->input('per_page', 10);
            $search = $this->input('search', '');
            $classId = $this->input('class_id', '');
            $date = $this->input('date', date('Y-m-d'));
            $status = $this->input('status', '');

            $offset = ($page - 1) * $perPage;

            // Get attendance records with filters
            $attendanceRecords = $this->getFilteredAttendance($search, $classId, $date, $status, $offset, $perPage);
            $totalRecords = $this->getFilteredAttendanceCount($search, $classId, $date, $status);

            // Get classes for filter dropdown
            $classes = ClassModel::allWithTeachers();

            // Get today's attendance stats
            $todayStats = Attendance::getDashboardStats();

            $data = [
                'title' => 'Attendance Management',
                'attendance_records' => $attendanceRecords,
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalRecords,
                'search' => $search,
                'class_id' => $classId,
                'date' => $date,
                'status' => $status,
                'classes' => $classes,
                'today_stats' => $todayStats,
                'total_pages' => ceil($totalRecords / $perPage)
            ];

            echo $this->view('admin.attendance.index', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show attendance marking form for a class
     */
    public function mark() {
        try {
            $classId = $this->input('class_id');
            $date = $this->input('date', date('Y-m-d'));
            $subjectId = $this->input('subject_id');

            if (!$classId) {
                $this->flash('error', 'Class ID is required');
                $this->redirect('/admin/attendance');
                return;
            }

            $class = ClassModel::find($classId);
            if (!$class) {
                $this->flash('error', 'Class not found');
                $this->redirect('/admin/attendance');
                return;
            }

            // Get students in the class
            $students = $class->students();

            // Get existing attendance for this date/class/subject
            $existingAttendance = Attendance::getByDateAndClass($date, $classId, $subjectId);
            $attendanceMap = [];
            foreach ($existingAttendance as $att) {
                $attendanceMap[$att->student_id] = $att;
            }

            // Get subjects for this class (optional)
            $subjects = ClassSubjectModel::getByClass($classId);

            $data = [
                'title' => 'Mark Attendance - ' . $class->class_name . ' ' . $class->section,
                'class' => $class,
                'students' => $students,
                'date' => $date,
                'subject_id' => $subjectId,
                'subjects' => $subjects,
                'existing_attendance' => $attendanceMap
            ];

            echo $this->view('admin.attendance.mark', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Save attendance records
     */
    public function save() {
        try {
            $classId = $this->input('class_id');
            $date = $this->input('date');
            $subjectId = $this->input('subject_id');
            $attendanceData = $this->input('attendance', []);

            if (!$classId || !$date || empty($attendanceData)) {
                $this->error('Invalid attendance data');
                return;
            }

            $userId = $this->getUserId();
            $saved = 0;
            $errors = [];

            foreach ($attendanceData as $studentId => $status) {
                if (!in_array($status, ['present', 'absent', 'late'])) {
                    $errors[] = "Invalid status for student {$studentId}";
                    continue;
                }

                // Check if attendance already exists
                if (Attendance::attendanceExists($studentId, $date, $subjectId)) {
                    // Update existing
                    $existing = Attendance::where('student_id', $studentId)
                                        ->where('attendance_date', $date)
                                        ->where('subject_id', $subjectId)
                                        ->first();

                    if ($existing) {
                        $existing->status = $status;
                        $existing->marked_by = $userId;
                        $existing->save();
                        $saved++;
                    }
                } else {
                    // Create new
                    $attendance = Attendance::create([
                        'student_id' => $studentId,
                        'class_id' => $classId,
                        'subject_id' => $subjectId,
                        'attendance_date' => $date,
                        'status' => $status,
                        'marked_by' => $userId
                    ]);

                    if ($attendance) {
                        $saved++;
                    }
                }
            }

            if ($saved > 0) {
                $this->success([
                    'saved' => $saved,
                    'errors' => $errors
                ], "Attendance saved successfully for {$saved} students");
            } else {
                $this->error('Failed to save attendance', $errors);
            }
        } catch (Exception $e) {
            $this->error('An error occurred while saving attendance');
        }
    }

    /**
     * Bulk upload attendance from CSV/Excel
     */
    public function bulkUpload() {
        try {
            $file = $this->file('attendance_file');

            if (!$file) {
                $this->flash('error', 'No file uploaded');
                $this->back();
                return;
            }

            // Handle file upload
            $uploadResult = $this->handleUpload('attendance_file', 'uploads/attendance', ['csv', 'xlsx', 'xls'], 5 * 1024 * 1024); // 5MB limit

            if (!$uploadResult['success']) {
                $this->flash('error', $uploadResult['error']);
                $this->back();
                return;
            }

            $filePath = $uploadResult['path'];

            // Process the file
            $result = $this->processAttendanceFile($filePath);

            // Clean up uploaded file
            unlink($filePath);

            if ($result['success']) {
                $this->flash('success', "Successfully processed {$result['processed']} attendance records");
            } else {
                $this->flash('error', 'Failed to process attendance file: ' . $result['error']);
            }

            $this->redirect('/admin/attendance');
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show attendance reports
     */
    public function reports() {
        try {
            $classId = $this->input('class_id');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $studentId = $this->input('student_id');

            $classes = ClassModel::allWithTeachers();
            $reportData = null;

            if ($classId && $startDate && $endDate) {
                if ($studentId) {
                    // Individual student report
                    $reportData = Attendance::getStudentAttendance($studentId, $startDate, $endDate);
                    $student = Student::find($studentId);
                } else {
                    // Class summary report
                    $reportData = Attendance::getClassAttendanceSummary($classId, $startDate, $endDate);
                }
            }

            $data = [
                'title' => 'Attendance Reports',
                'classes' => $classes,
                'class_id' => $classId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'student_id' => $studentId,
                'report_data' => $reportData,
                'student' => isset($student) ? $student : null
            ];

            echo $this->view('admin.attendance.reports', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show attendance analytics with charts
     */
    public function analytics() {
        try {
            $classId = $this->input('class_id');
            $year = $this->input('year', date('Y'));

            $classes = ClassModel::allWithTeachers();

            // Get monthly trends data
            $monthlyTrends = Attendance::getMonthlyTrends($classId, $year);

            // Get current month attendance summary
            $currentMonth = date('Y-m');
            $currentMonthData = Attendance::getClassAttendanceSummary($classId, $currentMonth . '-01', $currentMonth . '-31');

            $data = [
                'title' => 'Attendance Analytics',
                'classes' => $classes,
                'class_id' => $classId,
                'year' => $year,
                'monthly_trends' => $monthlyTrends,
                'current_month_data' => $currentMonthData
            ];

            echo $this->view('admin.attendance.analytics', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Get students for a class (AJAX)
     */
    public function getStudents() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
            return;
        }

        try {
            $classId = $this->input('class_id');

            if (!$classId) {
                $this->error('Class ID is required');
                return;
            }

            $class = ClassModel::find($classId);
            if (!$class) {
                $this->error('Class not found');
                return;
            }

            $students = $class->students();

            $data = array_map(function($student) {
                return [
                    'id' => $student->id,
                    'scholar_number' => $student->scholar_number,
                    'name' => $student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name,
                    'roll_number' => $student->id // Using ID as roll number for now
                ];
            }, $students);

            $this->success($data);
        } catch (Exception $e) {
            $this->error('Failed to load students');
        }
    }

    /**
     * Export attendance report
     */
    public function export() {
        try {
            $classId = $this->input('class_id');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $format = $this->input('format', 'csv');

            if (!$classId || !$startDate || !$endDate) {
                $this->flash('error', 'Class, start date, and end date are required');
                $this->back();
                return;
            }

            $class = ClassModel::find($classId);
            if (!$class) {
                $this->flash('error', 'Class not found');
                $this->back();
                return;
            }

            $reportData = Attendance::getClassAttendanceSummary($classId, $startDate, $endDate);

            if ($format === 'csv') {
                $this->exportCSV($reportData, $class, $startDate, $endDate);
            } elseif ($format === 'excel') {
                $this->exportExcel($reportData, $class, $startDate, $endDate);
            } else {
                $this->flash('error', 'Invalid export format');
                $this->back();
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Delete attendance record
     */
    public function destroy($id) {
        try {
            $attendance = Attendance::find($id);

            if (!$attendance) {
                if ($this->isAjax()) {
                    $this->error('Attendance record not found');
                } else {
                    $this->flash('error', 'Attendance record not found');
                    $this->redirect('/admin/attendance');
                }
                return;
            }

            if ($attendance->delete()) {
                if ($this->isAjax()) {
                    $this->success([], 'Attendance record deleted successfully');
                } else {
                    $this->flash('success', 'Attendance record deleted successfully');
                    $this->redirect('/admin/attendance');
                }
            } else {
                $message = 'Failed to delete attendance record';
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

    // Helper methods

    private function getFilteredAttendance($search, $classId, $date, $status, $offset, $limit) {
        $instance = new Attendance();

        $whereClause = "1=1";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.scholar_number LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($classId)) {
            $whereClause .= " AND a.class_id = ?";
            $params[] = $classId;
        }

        if (!empty($date)) {
            $whereClause .= " AND a.attendance_date = ?";
            $params[] = $date;
        }

        if (!empty($status)) {
            $whereClause .= " AND a.status = ?";
            $params[] = $status;
        }

        $sql = "SELECT a.*, s.first_name, s.middle_name, s.last_name, s.scholar_number,
                       c.class_name, c.section, sub.subject_name, u.username as marked_by_name
                FROM attendance a
                LEFT JOIN students s ON a.student_id = s.id
                LEFT JOIN classes c ON a.class_id = c.id
                LEFT JOIN subjects sub ON a.subject_id = sub.id
                LEFT JOIN users u ON a.marked_by = u.id
                WHERE {$whereClause}
                ORDER BY a.attendance_date DESC, s.first_name ASC
                LIMIT {$limit} OFFSET {$offset}";

        return $instance->db->fetchAll($sql, $params);
    }

    private function getFilteredAttendanceCount($search, $classId, $date, $status) {
        $instance = new Attendance();

        $whereClause = "1=1";
        $params = [];

        if (!empty($search)) {
            $whereClause .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.scholar_number LIKE ?)";
            $searchTerm = "%{$search}%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
        }

        if (!empty($classId)) {
            $whereClause .= " AND a.class_id = ?";
            $params[] = $classId;
        }

        if (!empty($date)) {
            $whereClause .= " AND a.attendance_date = ?";
            $params[] = $date;
        }

        if (!empty($status)) {
            $whereClause .= " AND a.status = ?";
            $params[] = $status;
        }

        $sql = "SELECT COUNT(*) as count
                FROM attendance a
                LEFT JOIN students s ON a.student_id = s.id
                WHERE {$whereClause}";

        $result = $instance->db->fetch($sql, $params);
        return $result['count'] ?? 0;
    }

    private function processAttendanceFile($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        if ($extension === 'csv') {
            return $this->processCSV($filePath);
        } elseif (in_array($extension, ['xlsx', 'xls'])) {
            return $this->processExcel($filePath);
        }

        return ['success' => false, 'error' => 'Unsupported file format'];
    }

    private function processCSV($filePath) {
        $records = [];
        $userId = $this->getUserId();

        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle); // Skip header

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 4) {
                    $records[] = [
                        'student_id' => $data[0],
                        'class_id' => $data[1],
                        'attendance_date' => $data[2],
                        'status' => $data[3],
                        'subject_id' => $data[4] ?? null,
                        'marked_by' => $userId
                    ];
                }
            }
            fclose($handle);
        }

        if (!empty($records)) {
            $saved = Attendance::bulkInsert($records);
            return ['success' => true, 'processed' => $saved];
        }

        return ['success' => false, 'error' => 'No valid records found'];
    }

    private function processExcel($filePath) {
        // For now, return not implemented
        // In a real implementation, you'd use a library like PhpSpreadsheet
        return ['success' => false, 'error' => 'Excel processing not implemented'];
    }

    private function exportCSV($data, $class, $startDate, $endDate) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendance_report_' . $class->class_name . '_' . $startDate . '_to_' . $endDate . '.csv"');

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['Scholar Number', 'Student Name', 'Total Days', 'Present', 'Absent', 'Late', 'Attendance %']);

        // Data
        foreach ($data as $row) {
            $student = Student::find($row['student_id']);
            $studentName = $student ? ($student->first_name . ' ' . $student->last_name) : 'Unknown';

            fputcsv($output, [
                $student ? $student->scholar_number : '',
                $studentName,
                $row['total_days'],
                $row['present_days'],
                $row['absent_days'],
                $row['late_days'],
                number_format($row['attendance_percentage'], 2) . '%'
            ]);
        }

        fclose($output);
        exit;
    }

    private function exportExcel($data, $class, $startDate, $endDate) {
        // For now, use CSV export
        // In a real implementation, you'd use a library like PhpSpreadsheet
        $this->exportCSV($data, $class, $startDate, $endDate);
    }

    /**
     * Download CSV template for bulk upload
     */
    public function downloadTemplate() {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="attendance_template.csv"');

        $output = fopen('php://output', 'w');

        // Header
        fputcsv($output, ['student_id', 'class_id', 'attendance_date', 'status', 'subject_id']);

        // Sample data
        fputcsv($output, ['1', '1', '2024-01-15', 'present', '']);
        fputcsv($output, ['2', '1', '2024-01-15', 'absent', '']);
        fputcsv($output, ['3', '1', '2024-01-15', 'late', '2']);

        fclose($output);
        exit;
    }
}