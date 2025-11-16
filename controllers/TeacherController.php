<?php
/**
 * Teacher Controller
 * Handles teacher portal operations
 */

class TeacherController extends BaseController {

    /**
     * Get current teacher from session
     */
    private function getCurrentTeacher() {
        $userId = $this->session->get('user_id');
        if (!$userId) {
            $this->redirect('/login');
        }

        $teacher = Teacher::where('user_id', $userId)->first();
        if (!$teacher) {
            $this->flash('error', 'Teacher profile not found');
            $this->redirect('/login');
        }

        return $teacher;
    }

    /**
     * Teacher dashboard
     */
    public function dashboard() {
        try {
            $teacher = $this->getCurrentTeacher();

            // Get assigned classes and subjects
            $assignedSubjects = $teacher->getAssignedSubjects();

            // Get today's attendance tasks
            $today = date('Y-m-d');
            $todayAttendanceTasks = $this->getTodayAttendanceTasks($teacher->id);

            // Get upcoming exams
            $upcomingExams = $this->getUpcomingExams($teacher->id);

            // Get class performance statistics
            $performanceStats = $this->getClassPerformanceStats($teacher->id);

            // Get recent announcements
            $recentAnnouncements = $this->getRecentAnnouncements();

            // Get attendance summary for current month
            $attendanceSummary = $this->getAttendanceSummary($teacher->id);

            // Get exam results pending entry
            $pendingResults = $this->getPendingExamResults($teacher->id);

            $data = [
                'title' => 'Teacher Dashboard',
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects,
                'todayAttendanceTasks' => $todayAttendanceTasks,
                'upcomingExams' => $upcomingExams,
                'performanceStats' => $performanceStats,
                'recentAnnouncements' => $recentAnnouncements,
                'attendanceSummary' => $attendanceSummary,
                'pendingResults' => $pendingResults,
                'currentDate' => $today
            ];

            echo $this->view('teacher.dashboard.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading dashboard: ' . $e->getMessage());
            echo $this->view('teacher.dashboard.index', [
                'title' => 'Teacher Dashboard',
                'teacher' => null,
                'assignedSubjects' => [],
                'todayAttendanceTasks' => [],
                'upcomingExams' => [],
                'performanceStats' => [],
                'recentAnnouncements' => [],
                'attendanceSummary' => [],
                'pendingResults' => [],
                'currentDate' => date('Y-m-d')
            ]);
        }
    }

    /**
     * Get today's attendance tasks for teacher
     */
    private function getTodayAttendanceTasks($teacherId) {
        $today = date('Y-m-d');

        $query = "SELECT cs.*, c.class_name, c.section, s.subject_name,
                         COUNT(CASE WHEN a.status IS NULL THEN 1 END) as unmarked_students
                  FROM class_subjects cs
                  LEFT JOIN classes c ON cs.class_id = c.id
                  LEFT JOIN subjects s ON cs.subject_id = s.id
                  LEFT JOIN attendance a ON cs.class_id = a.class_id
                                         AND cs.subject_id = a.subject_id
                                         AND a.attendance_date = ?
                  WHERE cs.teacher_id = ?
                  GROUP BY cs.id, c.class_name, c.section, s.subject_name
                  HAVING unmarked_students > 0
                  ORDER BY c.class_name, c.section";

        return $this->db->fetchAll($query, [$today, $teacherId]);
    }

    /**
     * Get upcoming exams for teacher
     */
    private function getUpcomingExams($teacherId) {
        $today = date('Y-m-d');

        $query = "SELECT e.*, c.class_name, c.section,
                         COUNT(er.id) as results_entered,
                         (SELECT COUNT(*) FROM students WHERE class_id = e.class_id) as total_students
                  FROM exams e
                  LEFT JOIN classes c ON e.class_id = c.id
                  LEFT JOIN class_subjects cs ON e.class_id = cs.class_id AND cs.teacher_id = ?
                  LEFT JOIN exam_results er ON e.id = er.exam_id
                  WHERE e.end_date >= ? AND cs.teacher_id IS NOT NULL
                  GROUP BY e.id
                  ORDER BY e.start_date ASC
                  LIMIT 5";

        return $this->db->fetchAll($query, [$teacherId, $today]);
    }

    /**
     * Get class performance statistics
     */
    private function getClassPerformanceStats($teacherId) {
        $currentMonth = date('m');
        $currentYear = date('Y');

        $query = "SELECT c.class_name, c.section,
                         AVG(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100 as attendance_percentage,
                         COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.student_id END) as present_students,
                         COUNT(DISTINCT s.id) as total_students
                  FROM class_subjects cs
                  LEFT JOIN classes c ON cs.class_id = c.id
                  LEFT JOIN students s ON c.id = s.class_id
                  LEFT JOIN attendance a ON s.id = a.student_id
                                         AND MONTH(a.attendance_date) = ?
                                         AND YEAR(a.attendance_date) = ?
                  WHERE cs.teacher_id = ?
                  GROUP BY c.id, c.class_name, c.section
                  ORDER BY c.class_name, c.section";

        $results = $this->db->fetchAll($query, [$currentMonth, $currentYear, $teacherId]);

        // Calculate performance metrics
        foreach ($results as &$result) {
            $result['attendance_percentage'] = round($result['attendance_percentage'], 1);
        }

        return $results;
    }

    /**
     * Get recent announcements
     */
    private function getRecentAnnouncements() {
        $query = "SELECT * FROM events
                  WHERE is_active = 1 AND event_date >= CURDATE()
                  ORDER BY event_date ASC
                  LIMIT 5";

        return $this->db->fetchAll($query);
    }

    /**
     * Get attendance summary for current month
     */
    private function getAttendanceSummary($teacherId) {
        $currentMonth = date('m');
        $currentYear = date('Y');

        $query = "SELECT
                    COUNT(CASE WHEN a.status = 'present' THEN 1 END) as total_present,
                    COUNT(CASE WHEN a.status = 'absent' THEN 1 END) as total_absent,
                    COUNT(CASE WHEN a.status = 'late' THEN 1 END) as total_late,
                    COUNT(a.id) as total_sessions
                  FROM attendance a
                  WHERE a.marked_by = (SELECT user_id FROM teachers WHERE id = ?)
                  AND MONTH(a.attendance_date) = ?
                  AND YEAR(a.attendance_date) = ?";

        $result = $this->db->fetch($query, [$teacherId, $currentMonth, $currentYear]);

        return [
            'total_present' => $result['total_present'] ?? 0,
            'total_absent' => $result['total_absent'] ?? 0,
            'total_late' => $result['total_late'] ?? 0,
            'total_sessions' => $result['total_sessions'] ?? 0,
            'attendance_rate' => $result['total_sessions'] > 0 ?
                round(($result['total_present'] / $result['total_sessions']) * 100, 1) : 0
        ];
    }

    /**
     * Get pending exam results for entry
     */
    private function getPendingExamResults($teacherId) {
        $query = "SELECT e.exam_name, e.start_date, e.end_date,
                         c.class_name, c.section, s.subject_name,
                         COUNT(DISTINCT st.id) as total_students,
                         COUNT(DISTINCT er.student_id) as results_entered
                  FROM exams e
                  LEFT JOIN classes c ON e.class_id = c.id
                  LEFT JOIN class_subjects cs ON e.class_id = cs.class_id
                  LEFT JOIN subjects s ON cs.subject_id = s.id
                  LEFT JOIN students st ON e.class_id = st.class_id
                  LEFT JOIN exam_results er ON e.id = er.exam_id
                                             AND cs.subject_id = er.subject_id
                                             AND er.entered_by = (SELECT user_id FROM teachers WHERE id = ?)
                  WHERE cs.teacher_id = ? AND e.end_date >= CURDATE()
                  GROUP BY e.id, cs.subject_id
                  HAVING results_entered < total_students
                  ORDER BY e.end_date ASC
                  LIMIT 5";

        return $this->db->fetchAll($query, [$teacherId, $teacherId]);
    }

    /**
     * Get dashboard data via AJAX
     */
    public function getDashboardData() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $teacher = $this->getCurrentTeacher();

            $data = [
                'todayAttendanceTasks' => $this->getTodayAttendanceTasks($teacher->id),
                'upcomingExams' => $this->getUpcomingExams($teacher->id),
                'performanceStats' => $this->getClassPerformanceStats($teacher->id),
                'attendanceSummary' => $this->getAttendanceSummary($teacher->id),
                'pendingResults' => $this->getPendingExamResults($teacher->id),
                'recentAnnouncements' => $this->getRecentAnnouncements()
            ];

            $this->json($data);

        } catch (Exception $e) {
            $this->error('Error loading dashboard data: ' . $e->getMessage());
        }
    }

    /**
     * Classes management page
     */
    public function classes() {
        try {
            $teacher = $this->getCurrentTeacher();
            $assignedSubjects = $teacher->getAssignedSubjects();

            $data = [
                'title' => 'My Classes',
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects
            ];

            echo $this->view('teacher.classes.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading classes: ' . $e->getMessage());
            echo $this->view('teacher.classes.index', [
                'title' => 'My Classes',
                'teacher' => null,
                'assignedSubjects' => []
            ]);
        }
    }

    /**
     * Attendance management page
     */
    public function attendance() {
        try {
            $teacher = $this->getCurrentTeacher();
            $assignedSubjects = $teacher->getAssignedSubjects();

            $data = [
                'title' => 'Attendance Management',
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects
            ];

            echo $this->view('teacher.attendance.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading attendance: ' . $e->getMessage());
            echo $this->view('teacher.attendance.index', [
                'title' => 'Attendance Management',
                'teacher' => null,
                'assignedSubjects' => []
            ]);
        }
    }

    /**
     * Exams management page
     */
    public function exams() {
        try {
            $teacher = $this->getCurrentTeacher();
            $assignedSubjects = $teacher->getAssignedSubjects();

            $data = [
                'title' => 'Exam Management',
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects
            ];

            echo $this->view('teacher.exams.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading exams: ' . $e->getMessage());
            echo $this->view('teacher.exams.index', [
                'title' => 'Exam Management',
                'teacher' => null,
                'assignedSubjects' => []
            ]);
        }
    }

    /**
     * Mark attendance for a specific class and subject
     */
    public function markAttendance() {
        try {
            $teacher = $this->getCurrentTeacher();
            $classId = $this->input('class_id');
            $subjectId = $this->input('subject_id');
            $date = $this->input('date', date('Y-m-d'));

            if (!$classId || !$subjectId) {
                $this->flash('error', 'Class and subject are required');
                $this->redirect('/teacher/attendance');
                return;
            }

            // Verify teacher is assigned to this class/subject
            $assignedSubjects = $teacher->getAssignedSubjects();
            $isAssigned = false;
            foreach ($assignedSubjects as $subject) {
                if ($subject['class_id'] == $classId && $subject['subject_id'] == $subjectId) {
                    $isAssigned = true;
                    break;
                }
            }

            if (!$isAssigned) {
                $this->flash('error', 'You are not assigned to this class/subject');
                $this->redirect('/teacher/attendance');
                return;
            }

            $class = ClassModel::find($classId);
            $subject = SubjectModel::find($subjectId);
            if (!$class || !$subject) {
                $this->flash('error', 'Class or subject not found');
                $this->redirect('/teacher/attendance');
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

            $data = [
                'title' => 'Mark Attendance - ' . $class->class_name . ' ' . $class->section . ' - ' . $subject->subject_name,
                'teacher' => $teacher,
                'class' => $class,
                'subject' => $subject,
                'students' => $students,
                'date' => $date,
                'existing_attendance' => $attendanceMap
            ];

            echo $this->view('teacher.attendance.mark', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading attendance form: ' . $e->getMessage());
            $this->redirect('/teacher/attendance');
        }
    }

    /**
     * Save attendance records
     */
    public function saveAttendance() {
        try {
            $teacher = $this->getCurrentTeacher();
            $classId = $this->input('class_id');
            $subjectId = $this->input('subject_id');
            $date = $this->input('date');
            $attendanceData = $this->input('attendance', []);

            if (!$classId || !$subjectId || !$date || empty($attendanceData)) {
                $this->error('Invalid attendance data');
                return;
            }

            // Verify teacher is assigned to this class/subject
            $assignedSubjects = $teacher->getAssignedSubjects();
            $isAssigned = false;
            foreach ($assignedSubjects as $subject) {
                if ($subject['class_id'] == $classId && $subject['subject_id'] == $subjectId) {
                    $isAssigned = true;
                    break;
                }
            }

            if (!$isAssigned) {
                $this->error('You are not assigned to this class/subject');
                return;
            }

            $userId = $teacher->user_id;
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
     * View attendance reports
     */
    public function attendanceReports() {
        try {
            $teacher = $this->getCurrentTeacher();
            $classId = $this->input('class_id');
            $subjectId = $this->input('subject_id');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');

            $assignedSubjects = $teacher->getAssignedSubjects();
            $reportData = null;

            if ($classId && $subjectId && $startDate && $endDate) {
                // Verify teacher is assigned to this class/subject
                $isAssigned = false;
                foreach ($assignedSubjects as $subject) {
                    if ($subject['class_id'] == $classId && $subject['subject_id'] == $subjectId) {
                        $isAssigned = true;
                        break;
                    }
                }

                if ($isAssigned) {
                    $reportData = $this->getAttendanceReportData($classId, $subjectId, $startDate, $endDate);
                }
            }

            $data = [
                'title' => 'Attendance Reports',
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects,
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'report_data' => $reportData
            ];

            echo $this->view('teacher.attendance.reports', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading reports: ' . $e->getMessage());
            echo $this->view('teacher.attendance.reports', [
                'title' => 'Attendance Reports',
                'teacher' => null,
                'assignedSubjects' => [],
                'class_id' => null,
                'subject_id' => null,
                'start_date' => null,
                'end_date' => null,
                'report_data' => null
            ]);
        }
    }

    /**
     * View attendance history
     */
    public function attendanceHistory() {
        try {
            $teacher = $this->getCurrentTeacher();
            $classId = $this->input('class_id');
            $subjectId = $this->input('subject_id');
            $month = $this->input('month', date('m'));
            $year = $this->input('year', date('Y'));

            $assignedSubjects = $teacher->getAssignedSubjects();
            $historyData = null;

            if ($classId && $subjectId) {
                // Verify teacher is assigned to this class/subject
                $isAssigned = false;
                foreach ($assignedSubjects as $subject) {
                    if ($subject['class_id'] == $classId && $subject['subject_id'] == $subjectId) {
                        $isAssigned = true;
                        break;
                    }
                }

                if ($isAssigned) {
                    $historyData = $this->getAttendanceHistoryData($classId, $subjectId, $month, $year);
                }
            }

            $data = [
                'title' => 'Attendance History',
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects,
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'month' => $month,
                'year' => $year,
                'history_data' => $historyData
            ];

            echo $this->view('teacher.attendance.history', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading history: ' . $e->getMessage());
            echo $this->view('teacher.attendance.history', [
                'title' => 'Attendance History',
                'teacher' => null,
                'assignedSubjects' => [],
                'class_id' => null,
                'subject_id' => null,
                'month' => date('m'),
                'year' => date('Y'),
                'history_data' => null
            ]);
        }
    }

    /**
     * Get attendance report data
     */
    private function getAttendanceReportData($classId, $subjectId, $startDate, $endDate) {
        $instance = new Attendance();

        $sql = "SELECT
                    s.id as student_id,
                    s.scholar_number,
                    CONCAT(s.first_name, ' ', COALESCE(s.middle_name, ''), ' ', s.last_name) as student_name,
                    COUNT(a.id) as total_days,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_days,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days,
                    ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 2) as attendance_percentage
                FROM students s
                LEFT JOIN attendance a ON s.id = a.student_id
                    AND a.class_id = ?
                    AND a.subject_id = ?
                    AND a.attendance_date BETWEEN ? AND ?
                WHERE s.class_id = ?
                GROUP BY s.id, s.scholar_number, s.first_name, s.middle_name, s.last_name
                ORDER BY s.scholar_number";

        return $instance->db->fetchAll($sql, [$classId, $subjectId, $startDate, $endDate, $classId]);
    }

    /**
     * Get attendance history data
     */
    private function getAttendanceHistoryData($classId, $subjectId, $month, $year) {
        $instance = new Attendance();

        $sql = "SELECT
                    a.attendance_date,
                    COUNT(a.id) as total_students,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_count,
                    ROUND((SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 2) as attendance_rate
                FROM attendance a
                WHERE a.class_id = ?
                    AND a.subject_id = ?
                    AND MONTH(a.attendance_date) = ?
                    AND YEAR(a.attendance_date) = ?
                GROUP BY a.attendance_date
                ORDER BY a.attendance_date";

        return $instance->db->fetchAll($sql, [$classId, $subjectId, $month, $year]);
    }

    /**
     * Get class data including student count, subject count, and attendance rate
     */
    private function getClassData($classId) {
        // Get student count
        $studentCount = $this->db->fetch("SELECT COUNT(*) as count FROM students WHERE class_id = ? AND is_active = 1", [$classId])['count'];

        // Get subject count for this class
        $subjectCount = $this->db->fetch("SELECT COUNT(*) as count FROM class_subjects WHERE class_id = ?", [$classId])['count'];

        // Get attendance rate for current month
        $currentMonth = date('m');
        $currentYear = date('Y');
        $attendanceData = $this->db->fetch(
            "SELECT AVG(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100 as attendance_rate
             FROM attendance a
             JOIN students s ON a.student_id = s.id
             WHERE s.class_id = ? AND MONTH(a.attendance_date) = ? AND YEAR(a.attendance_date) = ?",
            [$classId, $currentMonth, $currentYear]
        );

        return [
            'student_count' => $studentCount,
            'subject_count' => $subjectCount,
            'attendance_rate' => $attendanceData['attendance_rate'] ?? 0
        ];
    }

    /**
     * Get CSS class for attendance rate display
     */
    private function getAttendanceClass($rate) {
        if ($rate >= 85) return 'performance-good';
        if ($rate >= 70) return 'performance-average';
        return 'performance-poor';
    }

    /**
     * Get Bootstrap badge class for attendance rate
     */
    private function getAttendanceBadgeClass($rate) {
        if ($rate >= 85) return 'bg-success';
        if ($rate >= 70) return 'bg-warning';
        return 'bg-danger';
    }

    /**
     * Get student attendance rate for current month
     */
    private function getStudentAttendanceRate($studentId) {
        $currentMonth = date('m');
        $currentYear = date('Y');

        $result = $this->db->fetch(
            "SELECT COUNT(*) as total_sessions,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_sessions
             FROM attendance
             WHERE student_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?",
            [$studentId, $currentMonth, $currentYear]
        );

        if ($result['total_sessions'] > 0) {
            return ($result['present_sessions'] / $result['total_sessions']) * 100;
        }

        return 0;
    }

    /**
     * Get student performance data
     */
    private function getStudentPerformance($studentId) {
        // Get average marks from exam results
        $result = $this->db->fetch(
            "SELECT AVG(marks_obtained / max_marks * 100) as average_marks,
                    COUNT(*) as total_exams
             FROM exam_results
             WHERE student_id = ?",
            [$studentId]
        );

        return [
            'average_marks' => $result['average_marks'] ?? 0,
            'total_exams' => $result['total_exams'] ?? 0
        ];
    }

    /**
     * Get subject count for a class
     */
    private function getSubjectCount($classId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM class_subjects WHERE class_id = ?",
            [$classId]
        );

        return $result['count'] ?? 0;
    }

    /**
     * Get Bootstrap progress bar class for performance
     */
    private function getPerformanceBarClass($marks) {
        if ($marks >= 85) return 'bg-success';
        if ($marks >= 70) return 'bg-warning';
        return 'bg-danger';
    }

    /**
     * Generate sample timetable data (placeholder for real timetable system)
     */
    private function generateSampleTimetable($assignedSubjects) {
        // This is a placeholder. In a real implementation, you'd have a proper timetable table
        // For now, distribute subjects across the week randomly

        $timetable = [];
        $subjects = $assignedSubjects;
        $periodsPerDay = 7; // 8 time slots - 1 lunch break

        // Initialize empty timetable
        for ($day = 1; $day <= 6; $day++) {
            $timetable[$day] = array_fill(0, 8, null);
        }

        // Distribute subjects across the week
        $subjectIndex = 0;
        for ($day = 1; $day <= 6; $day++) {
            $periodsAssigned = 0;
            for ($period = 0; $period < 8; $period++) {
                // Skip lunch break (period 4 = 13:00-14:00)
                if ($period == 4) continue;

                if ($periodsAssigned < 5 && $subjectIndex < count($subjects)) { // Max 5 periods per day
                    $timetable[$day][$period] = $subjects[$subjectIndex];
                    $subjectIndex = ($subjectIndex + 1) % count($subjects);
                    $periodsAssigned++;
                }
            }
        }

        return $timetable;
    }

    /**
     * Get student count for a specific subject in a class
     */
    private function getSubjectStudentCount($classId, $subjectId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM students WHERE class_id = ?",
            [$classId]
        );

        return $result['count'] ?? 0;
    }

    /**
     * Get attendance rate for a specific subject
     */
    private function getSubjectAttendanceRate($classId, $subjectId) {
        $currentMonth = date('m');
        $currentYear = date('Y');

        $result = $this->db->fetch(
            "SELECT COUNT(*) as total_sessions,
                    SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present_sessions
             FROM attendance a
             WHERE a.class_id = ? AND a.subject_id = ? AND MONTH(a.attendance_date) = ? AND YEAR(a.attendance_date) = ?",
            [$classId, $subjectId, $currentMonth, $currentYear]
        );

        if ($result['total_sessions'] > 0) {
            return round(($result['present_sessions'] / $result['total_sessions']) * 100, 1);
        }

        return 0;
    }

    /**
     * Get performance average for a specific subject
     */
    private function getSubjectPerformance($classId, $subjectId) {
        $result = $this->db->fetch(
            "SELECT AVG(er.marks_obtained / er.max_marks * 100) as average_marks
             FROM exam_results er
             JOIN exams e ON er.exam_id = e.id
             WHERE e.class_id = ? AND er.subject_id = ?",
            [$classId, $subjectId]
        );

        return round($result['average_marks'] ?? 0, 1);
    }

    /**
     * Subjects management page
     */
    public function subjects() {
        try {
            $teacher = $this->getCurrentTeacher();
            $assignedSubjects = $teacher->getAssignedSubjects();

            $data = [
                'title' => 'My Subjects',
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects
            ];

            echo $this->view('teacher.classes.subjects', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading subjects: ' . $e->getMessage());
            echo $this->view('teacher.classes.subjects', [
                'title' => 'My Subjects',
                'teacher' => null,
                'assignedSubjects' => []
            ]);
        }
    }

    /**
     * Students list page for a specific class
     */
    public function students() {
        try {
            $teacher = $this->getCurrentTeacher();
            $classId = $this->input('class_id');

            if (!$classId) {
                $this->flash('error', 'Class ID is required');
                $this->redirect('/teacher/classes');
                return;
            }

            // Verify teacher is assigned to this class
            $assignedSubjects = $teacher->getAssignedSubjects();
            $isAssigned = false;
            foreach ($assignedSubjects as $subject) {
                if ($subject['class_id'] == $classId) {
                    $isAssigned = true;
                    break;
                }
            }

            if (!$isAssigned) {
                $this->flash('error', 'You are not assigned to this class');
                $this->redirect('/teacher/classes');
                return;
            }

            $class = ClassModel::find($classId);
            if (!$class) {
                $this->flash('error', 'Class not found');
                $this->redirect('/teacher/classes');
                return;
            }

            // Get students in the class
            $students = $class->students();

            $data = [
                'title' => 'Students - ' . $class->class_name . ' ' . $class->section,
                'teacher' => $teacher,
                'class' => $class,
                'students' => $students
            ];

            echo $this->view('teacher.classes.students', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading students: ' . $e->getMessage());
            $this->redirect('/teacher/classes');
        }
    }

    /**
     * Timetable view for a specific class
     */
    public function timetable() {
        try {
            $teacher = $this->getCurrentTeacher();
            $classId = $this->input('class_id');

            if (!$classId) {
                $this->flash('error', 'Class ID is required');
                $this->redirect('/teacher/classes');
                return;
            }

            // Verify teacher is assigned to this class
            $assignedSubjects = $teacher->getAssignedSubjects();
            $isAssigned = false;
            foreach ($assignedSubjects as $subject) {
                if ($subject['class_id'] == $classId) {
                    $isAssigned = true;
                    break;
                }
            }

            if (!$isAssigned) {
                $this->flash('error', 'You are not assigned to this class');
                $this->redirect('/teacher/classes');
                return;
            }

            $class = ClassModel::find($classId);
            if (!$class) {
                $this->flash('error', 'Class not found');
                $this->redirect('/teacher/classes');
                return;
            }

            // Get timetable data (this would need a timetable table in the database)
            $timetable = $this->getClassTimetable($classId, $teacher->id);

            $data = [
                'title' => 'Timetable - ' . $class->class_name . ' ' . $class->section,
                'teacher' => $teacher,
                'class' => $class,
                'timetable' => $timetable
            ];

            echo $this->view('teacher.classes.timetable', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading timetable: ' . $e->getMessage());
            $this->redirect('/teacher/classes');
        }
    }

    /**
     * Study materials and assignments page
     */
    public function materials() {
        try {
            $teacher = $this->getCurrentTeacher();
            $classId = $this->input('class_id');

            if (!$classId) {
                $this->flash('error', 'Class ID is required');
                $this->redirect('/teacher/classes');
                return;
            }

            // Verify teacher is assigned to this class
            $assignedSubjects = $teacher->getAssignedSubjects();
            $isAssigned = false;
            foreach ($assignedSubjects as $subject) {
                if ($subject['class_id'] == $classId) {
                    $isAssigned = true;
                    break;
                }
            }

            if (!$isAssigned) {
                $this->flash('error', 'You are not assigned to this class');
                $this->redirect('/teacher/classes');
                return;
            }

            $class = ClassModel::find($classId);
            if (!$class) {
                $this->flash('error', 'Class not found');
                $this->redirect('/teacher/classes');
                return;
            }

            // Get uploaded materials (this would need a materials table)
            $materials = $this->getClassMaterials($classId, $teacher->id);

            $data = [
                'title' => 'Study Materials - ' . $class->class_name . ' ' . $class->section,
                'teacher' => $teacher,
                'class' => $class,
                'materials' => $materials,
                'assignedSubjects' => array_filter($assignedSubjects, function($s) use ($classId) {
                    return $s['class_id'] == $classId;
                })
            ];

            echo $this->view('teacher.classes.materials', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading materials: ' . $e->getMessage());
            $this->redirect('/teacher/classes');
        }
    }

    /**
     * Get class timetable (placeholder - would need timetable table)
     */
    private function getClassTimetable($classId, $teacherId) {
        // This is a placeholder. In a real implementation, you'd have a timetable table
        // For now, return assigned subjects as a simple schedule
        $query = "SELECT cs.*, c.class_name, c.section, s.subject_name
                  FROM class_subjects cs
                  LEFT JOIN classes c ON cs.class_id = c.id
                  LEFT JOIN subjects s ON cs.subject_id = s.id
                  WHERE cs.class_id = ? AND cs.teacher_id = ?
                  ORDER BY s.subject_name";

        return $this->db->fetchAll($query, [$classId, $teacherId]);
    }

    /**
     * Get class materials (placeholder - would need materials table)
     */
    private function getClassMaterials($classId, $teacherId) {
        // This is a placeholder. In a real implementation, you'd have a materials/assignments table
        // For now, return empty array
        return [];
    }

    /**
     * Upload study material or assignment
     */
    public function uploadMaterial() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $teacher = $this->getCurrentTeacher();
            $classId = $this->input('class_id');
            $subjectId = $this->input('subject_id');
            $title = $this->input('title');
            $description = $this->input('description');
            $materialType = $this->input('material_type');
            $dueDate = $this->input('due_date');
            $maxMarks = $this->input('max_marks');
            $file = $_FILES['file'] ?? null;

            if (!$classId || !$title || !$file) {
                $this->error('Missing required fields');
                return;
            }

            // Verify teacher is assigned to this class
            $assignedSubjects = $teacher->getAssignedSubjects();
            $isAssigned = false;
            foreach ($assignedSubjects as $subject) {
                if ($subject['class_id'] == $classId) {
                    $isAssigned = true;
                    break;
                }
            }

            if (!$isAssigned) {
                $this->error('You are not assigned to this class');
                return;
            }

            // Handle file upload
            $uploadPath = $this->handleFileUpload($file);
            if (!$uploadPath) {
                $this->error('File upload failed');
                return;
            }

            // Save material record (placeholder - would need materials table in real implementation)
            $materialData = [
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'teacher_id' => $teacher->id,
                'title' => $title,
                'description' => $description,
                'material_type' => $materialType,
                'file_path' => $uploadPath,
                'file_name' => $file['name'],
                'file_size' => $file['size'],
                'due_date' => $dueDate,
                'max_marks' => $maxMarks,
                'uploaded_by' => $teacher->user_id,
                'created_at' => date('Y-m-d H:i:s')
            ];

            // In a real implementation, you'd save to a materials table
            // $materialId = $this->db->insert('materials', $materialData);

            $this->success([
                'message' => 'Material uploaded successfully',
                'material' => $materialData
            ], 'Material uploaded successfully');

        } catch (Exception $e) {
            $this->error('Upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Get student details via AJAX
     */
    public function getStudentDetails() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $teacher = $this->getCurrentTeacher();
            $studentId = $this->input('student_id');

            if (!$studentId) {
                $this->error('Student ID is required');
                return;
            }

            // Get student details
            $student = Student::find($studentId);
            if (!$student) {
                $this->error('Student not found');
                return;
            }

            // Verify teacher is assigned to student's class
            $assignedSubjects = $teacher->getAssignedSubjects();
            $isAssigned = false;
            foreach ($assignedSubjects as $subject) {
                if ($subject['class_id'] == $student->class_id) {
                    $isAssigned = true;
                    break;
                }
            }

            if (!$isAssigned) {
                $this->error('You are not assigned to this student\'s class');
                return;
            }

            // Get attendance data
            $attendanceData = $this->getStudentAttendanceData($studentId);

            // Get performance data
            $performanceData = $this->getStudentPerformanceData($studentId);

            $data = [
                'student' => [
                    'id' => $student->id,
                    'scholar_number' => $student->scholar_number,
                    'name' => $student->first_name . ' ' . ($student->middle_name ?? '') . ' ' . $student->last_name,
                    'class' => $student->class_name . ' ' . $student->section,
                    'email' => $student->email,
                    'mobile' => $student->mobile,
                    'photo_path' => $student->photo_path
                ],
                'attendance' => $attendanceData,
                'performance' => $performanceData
            ];

            $this->json($data);

        } catch (Exception $e) {
            $this->error('Failed to load student details: ' . $e->getMessage());
        }
    }

    /**
     * Get student attendance data
     */
    private function getStudentAttendanceData($studentId) {
        $currentMonth = date('m');
        $currentYear = date('Y');

        $result = $this->db->fetch(
            "SELECT COUNT(*) as total_sessions,
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late_count
             FROM attendance
             WHERE student_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?",
            [$studentId, $currentMonth, $currentYear]
        );

        $total = $result['total_sessions'] ?? 0;
        $present = $result['present_count'] ?? 0;

        return [
            'total_sessions' => $total,
            'present_count' => $present,
            'absent_count' => $result['absent_count'] ?? 0,
            'late_count' => $result['late_count'] ?? 0,
            'attendance_rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0
        ];
    }

    /**
     * Get student exam performance data
     */
    private function getStudentExamPerformanceData($studentId) {
        $query = "SELECT er.*, e.exam_name, e.exam_type, s.subject_name,
                         c.class_name, c.section
                  FROM exam_results er
                  LEFT JOIN exams e ON er.exam_id = e.id
                  LEFT JOIN subjects s ON er.subject_id = s.id
                  LEFT JOIN classes c ON e.class_id = c.id
                  WHERE er.student_id = ?
                  ORDER BY e.start_date DESC, s.subject_name";

        return $this->db->fetchAll($query, [$studentId]);
    }

    /**
     * Handle file upload (placeholder)
     */
    private function handleFileUpload($file) {
        // Placeholder file upload handling
        // In real implementation, validate file type, size, and save to uploads directory
        return 'uploads/materials/' . time() . '_' . $file['name'];
    }

    /**
     * Marks entry page
     */
    public function marks() {
        try {
            $teacher = $this->getCurrentTeacher();
            $examId = $this->input('exam_id');
            $subjectId = $this->input('subject_id');

            $assignedSubjects = $teacher->getAssignedSubjects();
            $exams = $this->getTeacherExams($teacher->id);

            $examData = null;
            $students = [];
            $existingMarks = [];

            if ($examId && $subjectId) {
                // Verify teacher is assigned to this exam/subject
                $isAssigned = false;
                foreach ($assignedSubjects as $subject) {
                    if ($subject['subject_id'] == $subjectId) {
                        $isAssigned = true;
                        break;
                    }
                }

                if ($isAssigned) {
                    $exam = Exam::find($examId);
                    if ($exam) {
                        $examData = $exam;
                        $students = $exam->getStudents();
                        $existingMarks = $this->getExistingMarks($examId, $subjectId);
                    }
                }
            }

            $data = [
                'title' => 'Marks Entry',
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects,
                'exams' => $exams,
                'exam_id' => $examId,
                'subject_id' => $subjectId,
                'exam_data' => $examData,
                'students' => $students,
                'existing_marks' => $existingMarks
            ];

            echo $this->view('teacher.exams.marks', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading marks entry: ' . $e->getMessage());
            echo $this->view('teacher.exams.marks', [
                'title' => 'Marks Entry',
                'teacher' => null,
                'assignedSubjects' => [],
                'exams' => [],
                'exam_id' => null,
                'subject_id' => null,
                'exam_data' => null,
                'students' => [],
                'existing_marks' => []
            ]);
        }
    }

    /**
     * Save marks via AJAX
     */
    public function saveMarks() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $teacher = $this->getCurrentTeacher();
            $examId = $this->input('exam_id');
            $subjectId = $this->input('subject_id');
            $marksData = $this->input('marks', []);

            if (!$examId || !$subjectId || empty($marksData)) {
                $this->error('Invalid marks data');
                return;
            }

            // Verify teacher is assigned to this subject
            $assignedSubjects = $teacher->getAssignedSubjects();
            $isAssigned = false;
            foreach ($assignedSubjects as $subject) {
                if ($subject['subject_id'] == $subjectId) {
                    $isAssigned = true;
                    break;
                }
            }

            if (!$isAssigned) {
                $this->error('You are not assigned to this subject');
                return;
            }

            $saved = 0;
            $errors = [];

            foreach ($marksData as $studentId => $marks) {
                $marksObtained = floatval($marks['obtained'] ?? 0);
                $maxMarks = floatval($marks['max'] ?? 0);

                if ($marksObtained < 0 || $marksObtained > $maxMarks) {
                    $errors[] = "Invalid marks for student {$studentId}";
                    continue;
                }

                $percentage = $maxMarks > 0 ? ($marksObtained / $maxMarks) * 100 : 0;
                $grade = $this->calculateGrade($percentage);

                // Check if result already exists
                $existing = $this->db->fetch(
                    "SELECT id FROM exam_results WHERE exam_id = ? AND student_id = ? AND subject_id = ?",
                    [$examId, $studentId, $subjectId]
                );

                if ($existing) {
                    // Update existing
                    $this->db->update('exam_results', [
                        'marks_obtained' => $marksObtained,
                        'max_marks' => $maxMarks,
                        'percentage' => $percentage,
                        'grade' => $grade,
                        'entered_by' => $teacher->user_id,
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'id = ?', [$existing['id']]);
                } else {
                    // Insert new
                    $this->db->insert('exam_results', [
                        'exam_id' => $examId,
                        'student_id' => $studentId,
                        'subject_id' => $subjectId,
                        'marks_obtained' => $marksObtained,
                        'max_marks' => $maxMarks,
                        'percentage' => $percentage,
                        'grade' => $grade,
                        'entered_by' => $teacher->user_id
                    ]);
                }

                $saved++;
            }

            if ($saved > 0) {
                $this->success([
                    'saved' => $saved,
                    'errors' => $errors
                ], "Marks saved successfully for {$saved} students");
            } else {
                $this->error('Failed to save marks', $errors);
            }

        } catch (Exception $e) {
            $this->error('An error occurred while saving marks: ' . $e->getMessage());
        }
    }

    /**
     * Student performance records page
     */
    public function performance() {
        try {
            $teacher = $this->getCurrentTeacher();
            $classId = $this->input('class_id');
            $studentId = $this->input('student_id');

            $assignedSubjects = $teacher->getAssignedSubjects();
            $classes = array_unique(array_column($assignedSubjects, 'class_id'));
            $classData = null;
            $studentData = null;
            $performanceData = null;
            $students = [];

            if ($classId) {
                $classData = ClassModel::find($classId);
                if ($classData && in_array($classId, $classes)) {
                    // Get students in the class
                    $students = $classData->students();

                    if ($studentId) {
                        $studentData = Student::find($studentId);
                        if ($studentData && $studentData->class_id == $classId) {
                            $performanceData = $this->getStudentExamPerformanceData($studentId);
                        }
                    }
                }
            }

            $data = [
                'title' => 'Student Performance Records',
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects,
                'classes' => $classes,
                'class_id' => $classId,
                'student_id' => $studentId,
                'class_data' => $classData,
                'student_data' => $studentData,
                'performance_data' => $performanceData,
                'students' => $students
            ];

            echo $this->view('teacher.exams.performance', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading performance records: ' . $e->getMessage());
            echo $this->view('teacher.exams.performance', [
                'title' => 'Student Performance Records',
                'teacher' => null,
                'assignedSubjects' => [],
                'classes' => [],
                'class_id' => null,
                'student_id' => null,
                'class_data' => null,
                'student_data' => null,
                'performance_data' => null
            ]);
        }
    }

    /**
     * Performance analytics and summaries
     */
    public function analytics() {
        try {
            $teacher = $this->getCurrentTeacher();
            $classId = $this->input('class_id');
            $subjectId = $this->input('subject_id');
            $examType = $this->input('exam_type');

            $assignedSubjects = $teacher->getAssignedSubjects();
            $analyticsData = null;

            if ($classId && $subjectId) {
                // Verify teacher is assigned to this class/subject
                $isAssigned = false;
                foreach ($assignedSubjects as $subject) {
                    if ($subject['class_id'] == $classId && $subject['subject_id'] == $subjectId) {
                        $isAssigned = true;
                        break;
                    }
                }

                if ($isAssigned) {
                    $analyticsData = $this->getPerformanceAnalytics($classId, $subjectId, $examType);
                }
            }

            $data = [
                'title' => 'Performance Analytics',
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects,
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'exam_type' => $examType,
                'analytics_data' => $analyticsData
            ];

            echo $this->view('teacher.exams.analytics', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading analytics: ' . $e->getMessage());
            echo $this->view('teacher.exams.analytics', [
                'title' => 'Performance Analytics',
                'teacher' => null,
                'assignedSubjects' => [],
                'class_id' => null,
                'subject_id' => null,
                'exam_type' => null,
                'analytics_data' => null
            ]);
        }
    }

    /**
     * Exam schedule viewing
     */
    public function schedule() {
        try {
            $teacher = $this->getCurrentTeacher();

            $assignedSubjects = $teacher->getAssignedSubjects();
            $examSchedule = $this->getExamSchedule($teacher->id);

            $data = [
                'title' => 'Exam Schedule',
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects,
                'exam_schedule' => $examSchedule
            ];

            echo $this->view('teacher.exams.schedule', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading exam schedule: ' . $e->getMessage());
            echo $this->view('teacher.exams.schedule', [
                'title' => 'Exam Schedule',
                'teacher' => null,
                'assignedSubjects' => [],
                'exam_schedule' => []
            ]);
        }
    }

    /**
     * Get exams for teacher
     */
    private function getTeacherExams($teacherId) {
        $query = "SELECT e.*, c.class_name, c.section
                  FROM exams e
                  LEFT JOIN classes c ON e.class_id = c.id
                  LEFT JOIN class_subjects cs ON e.class_id = cs.class_id
                  WHERE cs.teacher_id = ? AND e.is_active = 1
                  GROUP BY e.id
                  ORDER BY e.start_date DESC";

        return $this->db->fetchAll($query, [$teacherId]);
    }

    /**
     * Get existing marks for an exam and subject
     */
    private function getExistingMarks($examId, $subjectId) {
        $query = "SELECT student_id, marks_obtained, max_marks, grade
                  FROM exam_results
                  WHERE exam_id = ? AND subject_id = ?";

        $results = $this->db->fetchAll($query, [$examId, $subjectId]);
        $marksMap = [];
        foreach ($results as $result) {
            $marksMap[$result['student_id']] = $result;
        }

        return $marksMap;
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

    /**
     * Get student performance data
     */
    private function getStudentPerformanceData($studentId) {
        $query = "SELECT er.*, e.exam_name, e.exam_type, s.subject_name,
                         c.class_name, c.section
                  FROM exam_results er
                  LEFT JOIN exams e ON er.exam_id = e.id
                  LEFT JOIN subjects s ON er.subject_id = s.id
                  LEFT JOIN classes c ON e.class_id = c.id
                  WHERE er.student_id = ?
                  ORDER BY e.start_date DESC, s.subject_name";

        return $this->db->fetchAll($query, [$studentId]);
    }

    /**
     * Get performance analytics
     */
    private function getPerformanceAnalytics($classId, $subjectId, $examType = null) {
        $params = [$classId, $subjectId];
        $typeCondition = "";

        if ($examType) {
            $typeCondition = " AND e.exam_type = ?";
            $params[] = $examType;
        }

        $query = "SELECT
                     AVG(er.percentage) as avg_percentage,
                     MAX(er.percentage) as highest_percentage,
                     MIN(er.percentage) as lowest_percentage,
                     COUNT(CASE WHEN er.grade = 'A+' THEN 1 END) as grade_ap,
                     COUNT(CASE WHEN er.grade = 'A' THEN 1 END) as grade_a,
                     COUNT(CASE WHEN er.grade = 'B+' THEN 1 END) as grade_bp,
                     COUNT(CASE WHEN er.grade = 'B' THEN 1 END) as grade_b,
                     COUNT(CASE WHEN er.grade = 'C+' THEN 1 END) as grade_cp,
                     COUNT(CASE WHEN er.grade = 'C' THEN 1 END) as grade_c,
                     COUNT(CASE WHEN er.grade = 'D' THEN 1 END) as grade_d,
                     COUNT(CASE WHEN er.grade = 'F' THEN 1 END) as grade_f,
                     COUNT(er.id) as total_students
                  FROM exam_results er
                  LEFT JOIN exams e ON er.exam_id = e.id
                  WHERE e.class_id = ? AND er.subject_id = ? {$typeCondition}";

        $stats = $this->db->fetch($query, $params);

        // Get grade distribution
        $gradeDistribution = [];
        if ($stats) {
            $gradeDistribution = [
                'A+' => $stats['grade_ap'] ?? 0,
                'A' => $stats['grade_a'] ?? 0,
                'B+' => $stats['grade_bp'] ?? 0,
                'B' => $stats['grade_b'] ?? 0,
                'C+' => $stats['grade_cp'] ?? 0,
                'C' => $stats['grade_c'] ?? 0,
                'D' => $stats['grade_d'] ?? 0,
                'F' => $stats['grade_f'] ?? 0
            ];
        }

        return [
            'statistics' => $stats,
            'grade_distribution' => $gradeDistribution
        ];
    }

    /**
     * Get exam schedule for teacher
     */
    private function getExamSchedule($teacherId) {
        $query = "SELECT e.*, c.class_name, c.section, s.subject_name
                  FROM exams e
                  LEFT JOIN classes c ON e.class_id = c.id
                  LEFT JOIN class_subjects cs ON e.class_id = cs.class_id
                  LEFT JOIN subjects s ON cs.subject_id = s.id
                  WHERE cs.teacher_id = ? AND e.is_active = 1
                  ORDER BY e.start_date ASC, e.start_date ASC";

        return $this->db->fetchAll($query, [$teacherId]);
    }

    /**
     * Get Bootstrap color class for grade
     */
    private function getGradeColor($grade) {
        $colors = [
            'A+' => 'success',
            'A' => 'success',
            'B+' => 'primary',
            'B' => 'primary',
            'C+' => 'warning',
            'C' => 'warning',
            'D' => 'danger',
            'F' => 'danger'
        ];
        return $colors[$grade] ?? 'secondary';
    }

    /**
      * Profile management page
      */
     public function profile() {
         try {
             $teacher = $this->getCurrentTeacher();

             $data = [
                 'title' => 'My Profile',
                 'teacher' => $teacher
             ];

             echo $this->view('teacher.profile.index', $data);

         } catch (Exception $e) {
             $this->flash('error', 'Error loading profile: ' . $e->getMessage());
             echo $this->view('teacher.profile.index', [
                 'title' => 'My Profile',
                 'teacher' => null
             ]);
         }
     }

     /**
      * Get profile statistics via AJAX
      */
     public function getProfileStats() {
         if (!$this->isAjax()) {
             $this->error('Invalid request');
         }

         try {
             $teacher = $this->getCurrentTeacher();

             // Get basic stats
             $assignedSubjects = $teacher->getAssignedSubjects();
             $performanceMetrics = $teacher->getPerformanceMetrics();

             // Calculate profile completion
             $requiredFields = ['first_name', 'last_name', 'email'];
             $importantFields = ['mobile', 'permanent_address', 'qualification', 'designation', 'department'];
             $allFields = array_merge($requiredFields, $importantFields);

             $completedFields = 0;
             foreach ($allFields as $field) {
                 if (!empty($teacher->$field)) {
                     $completedFields++;
                 }
             }
             $profileCompletion = round(($completedFields / count($allFields)) * 100);

             // Get last login (simplified - would need audit log table)
             $lastLogin = 'Recently'; // Placeholder

             $data = [
                 'total_subjects' => count($assignedSubjects),
                 'total_classes' => count(array_unique(array_column($assignedSubjects, 'class_id'))),
                 'workload' => $performanceMetrics['workload'],
                 'attendance_marked' => $performanceMetrics['total_sessions'],
                 'results_entered' => $performanceMetrics['total_results_entered'],
                 'last_login' => $lastLogin,
                 'profile_completion' => $profileCompletion
             ];

             $this->json($data);

         } catch (Exception $e) {
             $this->error('Failed to load profile stats: ' . $e->getMessage());
         }
     }

     /**
      * Get assignments data via AJAX
      */
     public function getAssignments() {
         if (!$this->isAjax()) {
             $this->error('Invalid request');
         }

         try {
             $teacher = $this->getCurrentTeacher();
             $assignedSubjects = $teacher->getAssignedSubjects();

             // Add student count for each class
             foreach ($assignedSubjects as &$subject) {
                 $studentCount = $this->db->fetch(
                     "SELECT COUNT(*) as count FROM students WHERE class_id = ? AND is_active = 1",
                     [$subject['class_id']]
                 )['count'];
                 $subject['student_count'] = $studentCount;
             }

             $this->json($assignedSubjects);

         } catch (Exception $e) {
             $this->error('Failed to load assignments: ' . $e->getMessage());
         }
     }

     /**
      * Get timetable data via AJAX
      */
     public function getTimetable() {
         if (!$this->isAjax()) {
             $this->error('Invalid request');
         }

         try {
             $teacher = $this->getCurrentTeacher();
             $assignedSubjects = $teacher->getAssignedSubjects();

             // Generate sample timetable (in real implementation, this would come from a timetable table)
             $timetable = $this->generateSampleTimetable($assignedSubjects);

             $this->json(['timetable' => $timetable]);

         } catch (Exception $e) {
             $this->error('Failed to load timetable: ' . $e->getMessage());
         }
     }

     /**
      * Get attendance records via AJAX
      */
     public function getAttendanceRecords() {
         if (!$this->isAjax()) {
             $this->error('Invalid request');
         }

         try {
             $teacher = $this->getCurrentTeacher();
             $month = $this->input('month', date('m'));
             $year = $this->input('year', date('Y'));

             $query = "SELECT
                             DATE(a.attendance_date) as date,
                             c.class_name, c.section,
                             s.subject_name,
                             SUM(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) as present,
                             SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent,
                             SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late,
                             COUNT(a.id) as total
                         FROM attendance a
                         LEFT JOIN classes c ON a.class_id = c.id
                         LEFT JOIN subjects s ON a.subject_id = s.id
                         WHERE a.marked_by = ? AND MONTH(a.attendance_date) = ? AND YEAR(a.attendance_date) = ?
                         GROUP BY DATE(a.attendance_date), c.class_name, c.section, s.subject_name
                         ORDER BY a.attendance_date DESC";

             $records = $this->db->fetchAll($query, [$teacher->user_id, $month, $year]);
             $this->json($records);

         } catch (Exception $e) {
             $this->error('Failed to load attendance records: ' . $e->getMessage());
         }
     }

     /**
      * Get performance data via AJAX
      */
     public function getPerformanceData() {
         if (!$this->isAjax()) {
             $this->error('Invalid request');
         }

         try {
             $teacher = $this->getCurrentTeacher();
             $assignedSubjects = $teacher->getAssignedSubjects();

             $performance = [];
             $attendanceChart = ['labels' => [], 'datasets' => []];
             $resultsChart = ['labels' => [], 'datasets' => []];

             foreach ($assignedSubjects as $subject) {
                 // Get attendance rate
                 $attendanceRate = $this->getSubjectAttendanceRate($subject['class_id'], $subject['subject_id']);

                 // Get performance average
                 $avgPerformance = $this->getSubjectPerformance($subject['class_id'], $subject['subject_id']);

                 // Get student count
                 $studentCount = $this->db->fetch(
                     "SELECT COUNT(*) as count FROM students WHERE class_id = ? AND is_active = 1",
                     [$subject['class_id']]
                 )['count'];

                 $performance[] = [
                     'class_name' => $subject['class_name'],
                     'section' => $subject['section'],
                     'subject_name' => $subject['subject_name'],
                     'attendance_rate' => $attendanceRate,
                     'avg_performance' => $avgPerformance,
                     'student_count' => $studentCount
                 ];
             }

             // Generate chart data for last 6 months
             $chartLabels = [];
             $attendanceData = [];
             $resultsData = [];

             for ($i = 5; $i >= 0; $i--) {
                 $date = date('Y-m', strtotime("-$i months"));
                 list($year, $month) = explode('-', $date);

                 $chartLabels[] = date('M Y', strtotime($date));

                 // Attendance data
                 $attQuery = "SELECT COUNT(*) as count FROM attendance WHERE marked_by = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ?";
                 $attResult = $this->db->fetch($attQuery, [$teacher->user_id, $month, $year]);
                 $attendanceData[] = $attResult['count'] ?? 0;

                 // Results data
                 $resQuery = "SELECT COUNT(*) as count FROM exam_results WHERE entered_by = ?";
                 $resResult = $this->db->fetch($resQuery, [$teacher->user_id]);
                 $resultsData[] = $resResult['count'] ?? 0;
             }

             $attendanceChart = [
                 'labels' => $chartLabels,
                 'datasets' => array([
                     'label' => 'Attendance Sessions',
                     'data' => $attendanceData,
                     'borderColor' => 'rgb(75, 192, 192)',
                     'tension' => 0.1
                 ])
             ];

             $resultsChart = [
                 'labels' => $chartLabels,
                 'datasets' => array([
                     'label' => 'Results Entered',
                     'data' => $resultsData,
                     'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                     'borderColor' => 'rgb(54, 162, 235)',
                     'borderWidth' => 1
                 ])
             ];

             $this->json([
                 'performance' => $performance,
                 'charts' => [
                     'attendance' => $attendanceChart,
                     'results' => $resultsChart
                 ]
             ]);

         } catch (Exception $e) {
             $this->error('Failed to load performance data: ' . $e->getMessage());
         }
     }

     /**
      * Edit profile form
      */
     public function editProfile() {
         try {
             $teacher = $this->getCurrentTeacher();

             // For AJAX requests, return the form HTML
             if ($this->isAjax()) {
                 ob_start();
                 include __DIR__ . '/../views/teacher/profile/edit.php';
                 $html = ob_get_clean();
                 echo $html;
                 return;
             }

             // For regular requests, show the form in a page
             $data = [
                 'title' => 'Edit Profile',
                 'teacher' => $teacher
             ];

             echo $this->view('teacher.profile.edit', $data);

         } catch (Exception $e) {
             $this->error('Failed to load edit form: ' . $e->getMessage());
         }
     }

     /**
      * Update profile via AJAX
      */
     public function updateProfile() {
         if (!$this->isAjax()) {
             $this->error('Invalid request');
         }

         try {
             $teacher = $this->getCurrentTeacher();

             // Validate required fields
             $requiredFields = ['first_name', 'last_name'];
             foreach ($requiredFields as $field) {
                 if (empty($this->input($field))) {
                     $this->error('Required field missing: ' . $field);
                     return;
                 }
             }

             // Prepare update data
             $updateData = [
                 'first_name' => $this->input('first_name'),
                 'middle_name' => $this->input('middle_name'),
                 'last_name' => $this->input('last_name'),
                 'dob' => $this->input('dob'),
                 'gender' => $this->input('gender'),
                 'marital_status' => $this->input('marital_status'),
                 'blood_group' => $this->input('blood_group'),
                 'qualification' => $this->input('qualification'),
                 'specialization' => $this->input('specialization'),
                 'designation' => $this->input('designation'),
                 'department' => $this->input('department'),
                 'date_of_joining' => $this->input('date_of_joining'),
                 'experience_years' => $this->input('experience_years'),
                 'permanent_address' => $this->input('permanent_address'),
                 'temporary_address' => $this->input('temporary_address'),
                 'mobile' => $this->input('mobile'),
                 'email' => $this->input('email'),
                 'aadhar' => $this->input('aadhar'),
                 'pan' => $this->input('pan'),
                 'samagra_id' => $this->input('samagra_id'),
                 'medical_conditions' => $this->input('medical_conditions')
             ];

             // Handle file upload
             if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                 $uploadDir = __DIR__ . '/../../uploads/';
                 if (!is_dir($uploadDir)) {
                     mkdir($uploadDir, 0755, true);
                 }

                 $fileName = time() . '_' . basename($_FILES['photo']['name']);
                 $uploadPath = $uploadDir . $fileName;

                 if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadPath)) {
                     $updateData['photo_path'] = $fileName;
                 }
             }

             // Update teacher record
             $teacher->fill($updateData);
             $teacher->save();

             $this->success(['message' => 'Profile updated successfully']);

         } catch (Exception $e) {
             $this->error('Failed to update profile: ' . $e->getMessage());
         }
     }

     /**
      * Change password via AJAX
      */
     public function changePassword() {
         if (!$this->isAjax()) {
             $this->error('Invalid request');
         }

         try {
             $teacher = $this->getCurrentTeacher();
             $currentPassword = $this->input('current_password');
             $newPassword = $this->input('new_password');
             $confirmPassword = $this->input('confirm_password');

             // Validate input
             if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                 $this->error('All password fields are required');
                 return;
             }

             if ($newPassword !== $confirmPassword) {
                 $this->error('New passwords do not match');
                 return;
             }

             if (strlen($newPassword) < 6) {
                 $this->error('New password must be at least 6 characters long');
                 return;
             }

             // Verify current password
             $user = $this->db->fetch("SELECT password FROM users WHERE id = ?", [$teacher->user_id]);
             if (!$user || !password_verify($currentPassword, $user['password'])) {
                 $this->error('Current password is incorrect');
                 return;
             }

             // Update password
             $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
             $this->db->update('users', ['password' => $hashedPassword], 'id = ?', [$teacher->user_id]);

             $this->success(['message' => 'Password changed successfully']);

         } catch (Exception $e) {
             $this->error('Failed to change password: ' . $e->getMessage());
         }
     }

 }