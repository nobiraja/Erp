<?php
/**
 * Student Results Model
 * Handles exam results, schedules, ranks, and performance analytics
 */

class StudentResultsModel extends BaseModel {

    /**
     * Get exam schedules for student's class
     */
    public static function getExamSchedules($studentId) {
        $instance = new static();

        // Get student's class
        $studentInfo = $instance->db->fetch(
            "SELECT class_id FROM students WHERE id = ? AND is_active = 1",
            [$studentId]
        );

        if (!$studentInfo) {
            return [];
        }

        $classId = $studentInfo['class_id'];

        $schedules = $instance->db->fetchAll(
            "SELECT es.*, e.exam_name, e.exam_type, e.start_date, e.end_date,
                    s.subject_name, s.subject_code,
                    CONCAT(t.first_name, ' ', t.last_name) as teacher_name
             FROM exam_subjects es
             LEFT JOIN exams e ON es.exam_id = e.id
             LEFT JOIN subjects s ON es.subject_id = s.id
             LEFT JOIN teachers t ON es.teacher_id = t.id
             WHERE e.class_id = ? AND e.is_active = 1
             ORDER BY es.exam_date ASC, es.start_time ASC",
            [$classId]
        );

        return $schedules ?: [];
    }

    /**
     * Get exam results for a student
     */
    public static function getExamResults($studentId, $examId = null) {
        $instance = new static();

        $query = "SELECT er.*, e.exam_name, e.exam_type, e.exam_date, e.academic_year,
                         s.subject_name, s.subject_code,
                         CONCAT(t.first_name, ' ', t.last_name) as teacher_name
                  FROM exam_results er
                  LEFT JOIN exams e ON er.exam_id = e.id
                  LEFT JOIN subjects s ON er.subject_id = s.id
                  LEFT JOIN teachers t ON e.teacher_id = t.id
                  WHERE er.student_id = ?";

        $params = [$studentId];

        if ($examId) {
            $query .= " AND er.exam_id = ?";
            $params[] = $examId;
        }

        $query .= " ORDER BY e.exam_date DESC, s.subject_name ASC";

        $results = $instance->db->fetchAll($query, $params);

        // Calculate percentages and grades
        foreach ($results as &$result) {
            $result['percentage'] = $result['max_marks'] > 0 ?
                round(($result['marks_obtained'] / $result['max_marks']) * 100, 2) : 0;

            // Assign grade based on percentage (customize as needed)
            $result['calculated_grade'] = self::calculateGrade($result['percentage']);
        }

        return $results ?: [];
    }

    /**
     * Get overall results summary for a student
     */
    public static function getResultsSummary($studentId) {
        $instance = new static();

        $results = self::getExamResults($studentId);

        if (empty($results)) {
            return [
                'total_exams' => 0,
                'average_percentage' => 0,
                'overall_grade' => 'N/A',
                'total_subjects' => 0,
                'passed_subjects' => 0,
                'failed_subjects' => 0
            ];
        }

        $totalPercentage = 0;
        $totalSubjects = count($results);
        $passedSubjects = 0;

        foreach ($results as $result) {
            $totalPercentage += $result['percentage'];
            if ($result['percentage'] >= 33) { // Assuming 33% pass mark
                $passedSubjects++;
            }
        }

        $averagePercentage = round($totalPercentage / $totalSubjects, 2);
        $overallGrade = self::calculateGrade($averagePercentage);

        return [
            'total_exams' => count(array_unique(array_column($results, 'exam_id'))),
            'average_percentage' => $averagePercentage,
            'overall_grade' => $overallGrade,
            'total_subjects' => $totalSubjects,
            'passed_subjects' => $passedSubjects,
            'failed_subjects' => $totalSubjects - $passedSubjects
        ];
    }

    /**
     * Get student rank in class
     */
    public static function getStudentRank($studentId) {
        $instance = new static();

        // Get student's class
        $studentInfo = $instance->db->fetch(
            "SELECT class_id FROM students WHERE id = ? AND is_active = 1",
            [$studentId]
        );

        if (!$studentInfo) {
            return null;
        }

        $classId = $studentInfo['class_id'];

        // Get all students in the class with their average percentages
        $classResults = $instance->db->fetchAll(
            "SELECT s.id, s.first_name, s.last_name,
                    AVG((er.marks_obtained / er.max_marks) * 100) as avg_percentage
             FROM students s
             LEFT JOIN exam_results er ON s.id = er.student_id
             WHERE s.class_id = ? AND s.is_active = 1
             GROUP BY s.id, s.first_name, s.last_name
             HAVING COUNT(er.id) > 0
             ORDER BY avg_percentage DESC",
            [$classId]
        );

        // Find student's rank
        $rank = null;
        $totalStudents = count($classResults);

        foreach ($classResults as $index => $student) {
            if ($student['id'] == $studentId) {
                $rank = $index + 1;
                break;
            }
        }

        return [
            'rank' => $rank,
            'total_students' => $totalStudents,
            'percentage' => $rank ? round(($totalStudents - $rank + 1) / $totalStudents * 100, 2) : 0
        ];
    }

    /**
     * Get performance analytics data
     */
    public static function getPerformanceAnalytics($studentId) {
        $instance = new static();

        // Get results grouped by exam
        $examResults = $instance->db->fetchAll(
            "SELECT e.exam_name, e.exam_date, e.academic_year,
                    AVG((er.marks_obtained / er.max_marks) * 100) as avg_percentage,
                    COUNT(er.id) as subjects_count
             FROM exam_results er
             LEFT JOIN exams e ON er.exam_id = e.id
             WHERE er.student_id = ?
             GROUP BY e.id, e.exam_name, e.exam_date, e.academic_year
             ORDER BY e.exam_date ASC",
            [$studentId]
        );

        // Get subject-wise performance
        $subjectPerformance = $instance->db->fetchAll(
            "SELECT s.subject_name,
                    AVG((er.marks_obtained / er.max_marks) * 100) as avg_percentage,
                    COUNT(er.id) as exams_count,
                    MAX((er.marks_obtained / er.max_marks) * 100) as highest_score,
                    MIN((er.marks_obtained / er.max_marks) * 100) as lowest_score
             FROM exam_results er
             LEFT JOIN subjects s ON er.subject_id = s.id
             WHERE er.student_id = ?
             GROUP BY s.id, s.subject_name
             ORDER BY avg_percentage DESC",
            [$studentId]
        );

        // Calculate trends
        $trends = [];
        if (count($examResults) > 1) {
            for ($i = 1; $i < count($examResults); $i++) {
                $current = $examResults[$i]['avg_percentage'];
                $previous = $examResults[$i-1]['avg_percentage'];
                $trend = $current - $previous;
                $trends[] = [
                    'exam' => $examResults[$i]['exam_name'],
                    'change' => round($trend, 2),
                    'direction' => $trend > 0 ? 'up' : ($trend < 0 ? 'down' : 'stable')
                ];
            }
        }

        return [
            'exam_trends' => $examResults,
            'subject_performance' => $subjectPerformance,
            'performance_trends' => $trends,
            'strengths' => array_slice($subjectPerformance, 0, 3), // Top 3 subjects
            'weaknesses' => array_slice(array_reverse($subjectPerformance), 0, 3) // Bottom 3 subjects
        ];
    }

    /**
     * Get data for report card generation
     */
    public static function getReportCardData($studentId, $examId = null) {
        $instance = new static();

        // Get student info
        $student = $instance->db->fetch(
            "SELECT s.*, c.class_name, c.section, c.academic_year,
                    CONCAT(s.first_name, ' ', s.middle_name, ' ', s.last_name) as full_name
             FROM students s
             LEFT JOIN classes c ON s.class_id = c.id
             WHERE s.id = ? AND s.is_active = 1",
            [$studentId]
        );

        if (!$student) {
            return null;
        }

        // Get exam results
        $results = self::getExamResults($studentId, $examId);

        // Get summary
        $summary = self::getResultsSummary($studentId);

        // Get rank
        $rank = self::getStudentRank($studentId);

        // Get attendance for the period
        $attendance = $instance->db->fetch(
            "SELECT
                COUNT(*) as total_days,
                SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
             FROM attendance
             WHERE student_id = ? AND YEAR(attendance_date) = ?",
            [$studentId, date('Y')]
        );

        $attendance_percentage = $attendance && $attendance['total_days'] > 0 ?
            round(($attendance['present_days'] / $attendance['total_days']) * 100, 2) : 0;

        return [
            'student' => $student,
            'results' => $results,
            'summary' => $summary,
            'rank' => $rank,
            'attendance' => [
                'total_days' => $attendance['total_days'] ?? 0,
                'present_days' => $attendance['present_days'] ?? 0,
                'percentage' => $attendance_percentage
            ],
            'generated_date' => date('Y-m-d H:i:s'),
            'exam_id' => $examId
        ];
    }

    /**
     * Calculate grade based on percentage
     */
    private static function calculateGrade($percentage) {
        if ($percentage >= 91) return 'A+';
        if ($percentage >= 81) return 'A';
        if ($percentage >= 71) return 'B+';
        if ($percentage >= 61) return 'B';
        if ($percentage >= 51) return 'C+';
        if ($percentage >= 41) return 'C';
        if ($percentage >= 33) return 'D';
        return 'F';
    }

    /**
     * Get upcoming exams for notifications
     */
    public static function getUpcomingExams($studentId, $days = 7) {
        $instance = new static();

        // Get student's class
        $studentInfo = $instance->db->fetch(
            "SELECT class_id FROM students WHERE id = ? AND is_active = 1",
            [$studentId]
        );

        if (!$studentInfo) {
            return [];
        }

        $classId = $studentInfo['class_id'];
        $futureDate = date('Y-m-d', strtotime("+{$days} days"));

        $exams = $instance->db->fetchAll(
            "SELECT es.*, e.exam_name, e.exam_type,
                    s.subject_name, s.subject_code
             FROM exam_subjects es
             LEFT JOIN exams e ON es.exam_id = e.id
             LEFT JOIN subjects s ON es.subject_id = s.id
             WHERE e.class_id = ? AND es.exam_date BETWEEN CURDATE() AND ?
             ORDER BY es.exam_date ASC, es.start_time ASC",
            [$classId, $futureDate]
        );

        return $exams ?: [];
    }
}