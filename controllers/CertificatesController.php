<?php
/**
 * Certificates Controller
 * Handles transfer certificate management operations
 */

class CertificatesController extends BaseController {

    /**
     * Display certificates dashboard
     */
    public function index() {
        try {
            $page = $this->input('page', 1);
            $perPage = $this->input('per_page', 10);
            $search = $this->input('search', '');

            $offset = ($page - 1) * $perPage;

            // Get certificates with pagination
            $certificates = TransferCertificate::getAllWithDetails($page, $perPage, $search);
            $totalRecords = TransferCertificate::getTotalCount($search);

            $data = [
                'title' => 'Transfer Certificates Management',
                'certificates' => $certificates,
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalRecords,
                'search' => $search,
                'total_pages' => ceil($totalRecords / $perPage)
            ];

            echo $this->view('admin.certificates.index', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show create certificate form
     */
    public function create() {
        try {
            // Get active students
            $students = $this->db->fetchAll(
                "SELECT s.id, s.scholar_number, s.first_name, s.middle_name, s.last_name,
                        s.father_name, c.class_name, c.section
                 FROM students s
                 LEFT JOIN classes c ON s.class_id = c.id
                 WHERE s.is_active = 1
                 ORDER BY s.first_name, s.last_name
                 LIMIT 50"
            );

            // Get classes for filter
            $classes = $this->db->fetchAll(
                "SELECT DISTINCT c.id, c.class_name, c.section
                 FROM classes c
                 INNER JOIN students s ON c.id = s.class_id
                 WHERE s.is_active = 1
                 ORDER BY c.class_name, c.section"
            );

            $data = [
                'title' => 'Issue Transfer Certificate',
                'students' => $students,
                'classes' => $classes
            ];

            echo $this->view('admin.certificates.create', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Store new certificate
     */
    public function store() {
        try {
            $data = [
                'student_id' => $this->input('student_id'),
                'certificate_number' => $this->input('certificate_number'),
                'issue_date' => $this->input('issue_date'),
                'transfer_reason' => $this->input('transfer_reason'),
                'transfer_to_school' => $this->input('transfer_to_school'),
                'conduct_grade' => $this->input('conduct_grade'),
                'remarks' => $this->input('remarks'),
                'issued_by' => $this->getUserId()
            ];

            // Validate required fields
            $required = ['student_id', 'certificate_number', 'issue_date', 'transfer_reason', 'conduct_grade'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    $this->error("{$field} is required");
                    return;
                }
            }

            // Check if certificate number already exists
            if (TransferCertificate::certificateNumberExists($data['certificate_number'])) {
                $this->error('Certificate number already exists');
                return;
            }

            // Check if student already has an active certificate
            $existingCertificates = TransferCertificate::getByStudent($data['student_id']);
            $activeCertificates = array_filter($existingCertificates, function($cert) {
                return $cert->is_active;
            });

            if (!empty($activeCertificates)) {
                $this->error('Student already has an active transfer certificate');
                return;
            }

            // Get academic record
            $certificate = new TransferCertificate();
            $data['academic_record'] = $certificate->getAcademicRecord($data['student_id']);

            // Set class teacher and principal (you may need to adjust this logic)
            $student = $this->db->fetch(
                "SELECT s.class_id FROM students s WHERE s.id = ?",
                [$data['student_id']]
            );

            if ($student) {
                $classTeacher = $this->db->fetch(
                    "SELECT teacher_id FROM class_subjects WHERE class_id = ? LIMIT 1",
                    [$student['class_id']]
                );
                if ($classTeacher) {
                    $data['class_teacher_id'] = $classTeacher['teacher_id'];
                }

                // Set principal (assuming admin user with role_id = 1 is principal)
                $principal = $this->db->fetch(
                    "SELECT id FROM users WHERE role_id = 1 LIMIT 1"
                );
                if ($principal) {
                    $data['principal_id'] = $principal['id'];
                }
            }

            $certificate = TransferCertificate::create($data);

            if ($certificate) {
                $this->success(['id' => $certificate->id], 'Transfer certificate issued successfully');
            } else {
                $this->error('Failed to issue transfer certificate');
            }
        } catch (Exception $e) {
            $this->error('An error occurred while issuing the certificate');
        }
    }

    /**
     * Show certificate details
     */
    public function view($id) {
        try {
            $certificate = TransferCertificate::withDetails($id);

            if (!$certificate) {
                $this->flash('error', 'Certificate not found');
                $this->redirect('/admin/certificates');
                return;
            }

            $data = [
                'title' => 'Transfer Certificate - ' . $certificate->certificate_number,
                'certificate' => $certificate
            ];

            echo $this->view('admin.certificates.view', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Generate PDF certificate
     */
    public function generate($id) {
        try {
            $certificate = TransferCertificate::withDetails($id);

            if (!$certificate) {
                $this->flash('error', 'Certificate not found');
                $this->redirect('/admin/certificates');
                return;
            }

            $this->generateCertificatePDF($certificate);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Generate certificate number
     */
    public function generateNumber() {
        try {
            $certificateNumber = TransferCertificate::generateCertificateNumber();

            $this->json(['certificate_number' => $certificateNumber]);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to generate certificate number'], 500);
        }
    }

    /**
     * Get academic record for student
     */
    public function getAcademicRecord($studentId) {
        try {
            $certificate = new TransferCertificate();
            $record = $certificate->getAcademicRecord($studentId);

            $this->json(['record' => $record]);
        } catch (Exception $e) {
            $this->json(['error' => 'Failed to get academic record'], 500);
        }
    }

    /**
     * Preview certificate
     */
    public function preview() {
        try {
            $studentId = $this->input('student_id');
            $certificateData = [
                'certificate_number' => $this->input('certificate_number', 'PREVIEW001'),
                'issue_date' => $this->input('issue_date', date('Y-m-d')),
                'transfer_reason' => $this->input('transfer_reason', 'Preview'),
                'transfer_to_school' => $this->input('transfer_to_school'),
                'conduct_grade' => $this->input('conduct_grade', 'good'),
                'remarks' => $this->input('remarks'),
                'academic_record' => 'Preview academic record'
            ];

            // Get student details
            $student = $this->db->fetch(
                "SELECT s.*, c.class_name, c.section
                 FROM students s
                 LEFT JOIN classes c ON s.class_id = c.id
                 WHERE s.id = ?",
                [$studentId]
            );

            if (!$student) {
                $this->json(['success' => false, 'message' => 'Student not found']);
                return;
            }

            // Create preview certificate object
            $previewCertificate = (object) array_merge($student, $certificateData);
            $previewCertificate->getConductGradeText = function() use ($certificateData) {
                $grades = [
                    'excellent' => 'Excellent',
                    'very_good' => 'Very Good',
                    'good' => 'Good',
                    'satisfactory' => 'Satisfactory',
                    'needs_improvement' => 'Needs Improvement'
                ];
                return $grades[$certificateData['conduct_grade']] ?? 'Good';
            };

            $this->generateCertificatePDF($previewCertificate, true);
        } catch (Exception $e) {
            $this->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Generate certificate PDF
     */
    private function generateCertificatePDF($certificate, $isPreview = false) {
        require_once 'libraries/tcpdf/tcpdf.php';

        // Create new PDF document (A4 size)
        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Set document information
        $pdf->SetCreator('School Management System');
        $pdf->SetAuthor('School Management System');
        $pdf->SetTitle('Transfer Certificate - ' . $certificate->certificate_number);

        // Remove default header/footer
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Set margins
        $pdf->SetMargins(20, 20, 20);
        $pdf->SetAutoPageBreak(true, 20);

        // Add a page
        $pdf->AddPage();

        // School Header
        $pdf->SetFont('helvetica', 'B', 18);
        $pdf->Cell(0, 12, 'SCHOOL MANAGEMENT SYSTEM', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'TRANSFER CERTIFICATE', 0, 1, 'C');
        $pdf->Ln(5);

        // Certificate details
        $pdf->SetFont('helvetica', '', 11);

        // Left column
        $pdf->Cell(80, 8, 'Certificate No: ' . $certificate->certificate_number, 0, 0);
        $pdf->Cell(0, 8, 'Date of Issue: ' . date('d-m-Y', strtotime($certificate->issue_date)), 0, 1);

        $pdf->Ln(5);

        // Student details section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'STUDENT DETAILS', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(50, 8, 'Scholar Number:', 0, 0);
        $pdf->Cell(0, 8, $certificate->scholar_number, 0, 1);

        $pdf->Cell(50, 8, 'Student Name:', 0, 0);
        $pdf->Cell(0, 8, $certificate->getStudentFullName(), 0, 1);

        $pdf->Cell(50, 8, 'Father\'s Name:', 0, 0);
        $pdf->Cell(0, 8, $certificate->father_name ?: 'N/A', 0, 1);

        $pdf->Cell(50, 8, 'Class:', 0, 0);
        $pdf->Cell(0, 8, $certificate->class_name . ' ' . $certificate->section, 0, 1);

        $pdf->Cell(50, 8, 'Date of Birth:', 0, 0);
        $pdf->Cell(0, 8, date('d-m-Y', strtotime($certificate->dob)), 0, 1);

        $pdf->Cell(50, 8, 'Date of Admission:', 0, 0);
        $pdf->Cell(0, 8, date('d-m-Y', strtotime($certificate->admission_date)), 0, 1);

        $pdf->Ln(5);

        // Transfer details section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'TRANSFER DETAILS', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);

        $pdf->Cell(50, 8, 'Transfer Reason:', 0, 0);
        $pdf->Cell(0, 8, $certificate->transfer_reason, 0, 1);

        if ($certificate->transfer_to_school) {
            $pdf->Cell(50, 8, 'Transferring To:', 0, 0);
            $pdf->Cell(0, 8, $certificate->transfer_to_school, 0, 1);
        }

        $pdf->Cell(50, 8, 'Conduct Grade:', 0, 0);
        $pdf->Cell(0, 8, $certificate->getConductGradeText(), 0, 1);

        $pdf->Ln(5);

        // Academic record section
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'ACADEMIC RECORD', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 10);

        // Split academic record into lines and display
        $academicLines = explode("\n", $certificate->academic_record);
        foreach ($academicLines as $line) {
            if (!empty(trim($line))) {
                $pdf->MultiCell(0, 6, trim($line), 0, 'L');
            }
        }

        // Remarks
        if ($certificate->remarks) {
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, 'REMARKS', 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 10);
            $pdf->MultiCell(0, 6, $certificate->remarks, 0, 'L');
        }

        // Signatures section
        $pdf->Ln(20);
        $pdf->SetFont('helvetica', '', 10);

        // Left signature
        $pdf->Cell(80, 6, 'Class Teacher', 0, 0, 'L');
        $pdf->Cell(0, 6, 'Principal', 0, 1, 'R');

        $pdf->Ln(15);

        // Signature lines
        $pdf->Cell(80, 6, '___________________', 0, 0, 'L');
        $pdf->Cell(0, 6, '___________________', 0, 1, 'R');

        // Output the PDF
        $filename = 'Transfer_Certificate_' . $certificate->certificate_number . '.pdf';
        $pdf->Output($filename, 'D');
        exit;
    }

    /**
     * Get current user ID
     */
    private function getUserId() {
        return $_SESSION['user_id'] ?? 1; // Default to admin user
    }
}