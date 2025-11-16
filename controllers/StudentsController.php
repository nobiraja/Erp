<?php
/**
 * Students Controller
 * Handles admin student management operations
 */

class StudentsController extends BaseController {

    /**
     * Display students list
     */
    public function index() {
        try {
            // Get filter parameters
            $classId = $this->input('class_id');
            $section = $this->input('section');
            $search = $this->input('search');
            $page = (int) $this->input('page', 1);
            $perPage = 25;

            // Get classes for filter dropdown
            $classes = $this->db->fetchAll(
                "SELECT id, class_name, section, academic_year FROM classes ORDER BY class_name, section"
            );

            // Build query
            $query = "SELECT s.*, c.class_name, c.section as class_section, c.academic_year
                     FROM students s
                     LEFT JOIN classes c ON s.class_id = c.id
                     WHERE 1=1";

            $params = [];
            $conditions = [];

            if ($classId) {
                $conditions[] = "s.class_id = ?";
                $params[] = $classId;
            }

            if ($section) {
                $conditions[] = "s.section = ?";
                $params[] = $section;
            }

            if ($search) {
                $conditions[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.scholar_number LIKE ? OR s.admission_number LIKE ? OR CONCAT(s.first_name, ' ', s.last_name) LIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
            }

            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            // Get total count for pagination
            $countQuery = str_replace("SELECT s.*, c.class_name, c.section as class_section, c.academic_year", "SELECT COUNT(*) as total", $query);
            $totalResult = $this->db->fetch($countQuery, $params);
            $total = $totalResult['total'] ?? 0;

            // Add ordering and pagination
            $query .= " ORDER BY s.first_name, s.last_name LIMIT " . (($page - 1) * $perPage) . ", {$perPage}";

            $students = $this->db->fetchAll($query, $params);

            // Calculate pagination
            $totalPages = ceil($total / $perPage);

            $data = [
                'title' => 'Students Management',
                'students' => $students,
                'classes' => $classes,
                'filters' => [
                    'class_id' => $classId,
                    'section' => $section,
                    'search' => $search
                ],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total' => $total,
                    'per_page' => $perPage
                ]
            ];

            echo $this->view('admin.students.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading students: ' . $e->getMessage());
            echo $this->view('admin.students.index', [
                'title' => 'Students Management',
                'students' => [],
                'classes' => [],
                'filters' => [],
                'pagination' => ['current_page' => 1, 'total_pages' => 0, 'total' => 0, 'per_page' => 25]
            ]);
        }
    }

    /**
     * Show create student form
     */
    public function create() {
        try {
            // Get classes for dropdown
            $classes = $this->db->fetchAll(
                "SELECT id, class_name, section, academic_year FROM classes ORDER BY class_name, section"
            );

            $data = [
                'title' => 'Add New Student',
                'classes' => $classes,
                'student' => null
            ];

            echo $this->view('admin.students.create', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading form: ' . $e->getMessage());
            $this->redirect('/admin/students');
        }
    }

    /**
     * Store new student
     */
    public function store() {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/students');
        }

        // Validation rules
        $rules = [
            'scholar_number' => 'required|max:20',
            'admission_number' => 'required|max:20',
            'admission_date' => 'required|date',
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'class_id' => 'required|integer',
            'section' => 'required|max:10',
            'father_name' => 'max:100',
            'mother_name' => 'max:100',
            'guardian_name' => 'max:100',
            'guardian_contact' => 'max:15',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'caste_category' => 'max:50',
            'nationality' => 'max:50',
            'religion' => 'max:50',
            'blood_group' => 'max:10',
            'village_address' => 'max:500',
            'permanent_address' => 'max:500',
            'temporary_address' => 'max:500',
            'mobile' => 'max:15',
            'email' => 'email|max:100',
            'aadhar' => 'max:12',
            'samagra' => 'max:20',
            'apaar_id' => 'max:20',
            'pan' => 'max:10',
            'previous_school' => 'max:100',
            'medical_conditions' => 'max:500'
        ];

        $validated = $this->validate($rules);

        if (!$validated) {
            $this->flash('error', 'Validation failed');
            $this->flash('old_input', $this->all());
            $this->flash('validation_errors', $this->getValidationErrors());
            $this->redirect('/admin/students/create');
        }

        try {
            // Check for duplicate scholar/admission numbers
            if (Student::scholarNumberExists($validated['scholar_number'])) {
                $this->flash('error', 'Scholar number already exists');
                $this->flash('old_input', $this->all());
                $this->redirect('/admin/students/create');
            }

            if (Student::admissionNumberExists($validated['admission_number'])) {
                $this->flash('error', 'Admission number already exists');
                $this->flash('old_input', $this->all());
                $this->redirect('/admin/students/create');
            }

            // Handle photo upload
            $photoPath = null;
            if ($this->file('photo')) {
                $upload = $this->handleUpload('photo', 'uploads/students/photos', ['jpg', 'jpeg', 'png'], 2 * 1024 * 1024); // 2MB limit
                if ($upload['success']) {
                    $photoPath = $upload['path'];
                }
            }

            // Create student
            $studentData = $validated;
            $studentData['photo_path'] = $photoPath;
            $studentData['is_active'] = 1;

            $student = Student::create($studentData);

            if ($student) {
                $this->flash('success', 'Student created successfully');
                $this->redirect('/admin/students');
            } else {
                $this->flash('error', 'Failed to create student');
                $this->redirect('/admin/students/create');
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error creating student: ' . $e->getMessage());
            $this->flash('old_input', $this->all());
            $this->redirect('/admin/students/create');
        }
    }

    /**
     * Show student details
     */
    public function show($id) {
        try {
            $student = Student::withClass($id);

            if (!$student) {
                $this->flash('error', 'Student not found');
                $this->redirect('/admin/students');
            }

            $data = [
                'title' => 'Student Profile - ' . $student->getFullName(),
                'student' => $student
            ];

            echo $this->view('admin.students.profile', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading student profile: ' . $e->getMessage());
            $this->redirect('/admin/students');
        }
    }

    /**
     * Show edit student form
     */
    public function edit($id) {
        try {
            $student = Student::find($id);

            if (!$student) {
                $this->flash('error', 'Student not found');
                $this->redirect('/admin/students');
            }

            // Get classes for dropdown
            $classes = $this->db->fetchAll(
                "SELECT id, class_name, section, academic_year FROM classes ORDER BY class_name, section"
            );

            $data = [
                'title' => 'Edit Student - ' . $student->getFullName(),
                'student' => $student,
                'classes' => $classes
            ];

            echo $this->view('admin.students.edit', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading edit form: ' . $e->getMessage());
            $this->redirect('/admin/students');
        }
    }

    /**
     * Update student
     */
    public function update($id) {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/students');
        }

        $student = Student::find($id);
        if (!$student) {
            $this->flash('error', 'Student not found');
            $this->redirect('/admin/students');
        }

        // Validation rules (similar to create but make some fields optional for updates)
        $rules = [
            'scholar_number' => 'required|max:20',
            'admission_number' => 'required|max:20',
            'admission_date' => 'required|date',
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'class_id' => 'required|integer',
            'section' => 'required|max:10',
            'father_name' => 'max:100',
            'mother_name' => 'max:100',
            'guardian_name' => 'max:100',
            'guardian_contact' => 'max:15',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'caste_category' => 'max:50',
            'nationality' => 'max:50',
            'religion' => 'max:50',
            'blood_group' => 'max:10',
            'village_address' => 'max:500',
            'permanent_address' => 'max:500',
            'temporary_address' => 'max:500',
            'mobile' => 'max:15',
            'email' => 'email|max:100',
            'aadhar' => 'max:12',
            'samagra' => 'max:20',
            'apaar_id' => 'max:20',
            'pan' => 'max:10',
            'previous_school' => 'max:100',
            'medical_conditions' => 'max:500',
            'is_active' => 'in:0,1'
        ];

        $validated = $this->validate($rules);

        if (!$validated) {
            $this->flash('error', 'Validation failed');
            $this->flash('old_input', $this->all());
            $this->flash('validation_errors', $this->getValidationErrors());
            $this->redirect("/admin/students/{$id}/edit");
        }

        try {
            // Check for duplicate scholar/admission numbers (excluding current student)
            if (Student::scholarNumberExists($validated['scholar_number'], $id)) {
                $this->flash('error', 'Scholar number already exists');
                $this->flash('old_input', $this->all());
                $this->redirect("/admin/students/{$id}/edit");
            }

            if (Student::admissionNumberExists($validated['admission_number'], $id)) {
                $this->flash('error', 'Admission number already exists');
                $this->flash('old_input', $this->all());
                $this->redirect("/admin/students/{$id}/edit");
            }

            // Handle photo upload
            if ($this->file('photo')) {
                $upload = $this->handleUpload('photo', 'uploads/students/photos', ['jpg', 'jpeg', 'png'], 2 * 1024 * 1024);
                if ($upload['success']) {
                    // Delete old photo if exists
                    if ($student->photo_path && file_exists($student->photo_path)) {
                        unlink($student->photo_path);
                    }
                    $validated['photo_path'] = $upload['path'];
                }
            }

            // Update student
            $student->fill($validated);
            if ($student->save()) {
                $this->flash('success', 'Student updated successfully');
                $this->redirect('/admin/students');
            } else {
                $this->flash('error', 'Failed to update student');
                $this->redirect("/admin/students/{$id}/edit");
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error updating student: ' . $e->getMessage());
            $this->flash('old_input', $this->all());
            $this->redirect("/admin/students/{$id}/edit");
        }
    }

    /**
     * Delete student
     */
    public function destroy($id) {
        try {
            $student = Student::find($id);

            if (!$student) {
                if ($this->isAjax()) {
                    $this->error('Student not found');
                }
                $this->flash('error', 'Student not found');
                $this->redirect('/admin/students');
            }

            // Delete photo if exists
            if ($student->photo_path && file_exists($student->photo_path)) {
                unlink($student->photo_path);
            }

            if ($student->delete()) {
                if ($this->isAjax()) {
                    $this->success(['message' => 'Student deleted successfully']);
                }
                $this->flash('success', 'Student deleted successfully');
            } else {
                if ($this->isAjax()) {
                    $this->error('Failed to delete student');
                }
                $this->flash('error', 'Failed to delete student');
            }

        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->error('Error deleting student: ' . $e->getMessage());
            }
            $this->flash('error', 'Error deleting student: ' . $e->getMessage());
        }

        if (!$this->isAjax()) {
            $this->redirect('/admin/students');
        }
    }

    /**
     * Bulk import students
     */
    public function import() {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/students');
        }

        if (!$this->file('import_file')) {
            $this->flash('error', 'Please select a file to import');
            $this->redirect('/admin/students');
        }

        $upload = $this->handleUpload('import_file', 'uploads/temp', ['csv', 'xlsx'], 5 * 1024 * 1024); // 5MB limit

        if (!$upload['success']) {
            $this->flash('error', $upload['error']);
            $this->redirect('/admin/students');
        }

        try {
            // Process CSV file
            $file = fopen($upload['path'], 'r');
            $header = fgetcsv($file);
            $imported = 0;
            $errors = [];

            while (($row = fgetcsv($file)) !== false) {
                try {
                    $studentData = array_combine($header, $row);

                    // Validate required fields
                    if (empty($studentData['scholar_number']) || empty($studentData['first_name']) || empty($studentData['last_name'])) {
                        $errors[] = "Row " . ($imported + 2) . ": Missing required fields";
                        continue;
                    }

                    // Check for duplicates
                    if (Student::scholarNumberExists($studentData['scholar_number'])) {
                        $errors[] = "Row " . ($imported + 2) . ": Scholar number already exists";
                        continue;
                    }

                    // Create student
                    $student = Student::create($studentData);
                    if ($student) {
                        $imported++;
                    } else {
                        $errors[] = "Row " . ($imported + 2) . ": Failed to create student";
                    }

                } catch (Exception $e) {
                    $errors[] = "Row " . ($imported + 2) . ": " . $e->getMessage();
                }
            }

            fclose($file);
            unlink($upload['path']); // Clean up temp file

            $message = "Import completed. {$imported} students imported.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " and " . (count($errors) - 5) . " more errors.";
                }
            }

            $this->flash('success', $message);

        } catch (Exception $e) {
            $this->flash('error', 'Import failed: ' . $e->getMessage());
        }

        $this->redirect('/admin/students');
    }

    /**
     * Export students to CSV
     */
    public function export() {
        try {
            $classId = $this->input('class_id');
            $section = $this->input('section');

            $query = "SELECT s.*, c.class_name, c.section as class_section
                     FROM students s
                     LEFT JOIN classes c ON s.class_id = c.id
                     WHERE s.is_active = 1";

            $params = [];

            if ($classId) {
                $query .= " AND s.class_id = ?";
                $params[] = $classId;
            }

            if ($section) {
                $query .= " AND s.section = ?";
                $params[] = $section;
            }

            $query .= " ORDER BY s.first_name, s.last_name";

            $students = $this->db->fetchAll($query, $params);

            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="students_' . date('Y-m-d') . '.csv"');

            $output = fopen('php://output', 'w');

            // CSV headers
            fputcsv($output, [
                'Scholar Number', 'Admission Number', 'Admission Date', 'First Name', 'Middle Name', 'Last Name',
                'Class', 'Section', 'Father Name', 'Mother Name', 'Guardian Name', 'Guardian Contact',
                'Date of Birth', 'Gender', 'Caste Category', 'Nationality', 'Religion', 'Blood Group',
                'Village Address', 'Permanent Address', 'Temporary Address', 'Mobile', 'Email',
                'Aadhar', 'Samagra', 'Apaar ID', 'PAN', 'Previous School', 'Medical Conditions'
            ]);

            // CSV data
            foreach ($students as $student) {
                fputcsv($output, [
                    $student['scholar_number'], $student['admission_number'], $student['admission_date'],
                    $student['first_name'], $student['middle_name'], $student['last_name'],
                    $student['class_name'], $student['section'], $student['father_name'], $student['mother_name'],
                    $student['guardian_name'], $student['guardian_contact'], $student['dob'], $student['gender'],
                    $student['caste_category'], $student['nationality'], $student['religion'], $student['blood_group'],
                    $student['village_address'], $student['permanent_address'], $student['temporary_address'],
                    $student['mobile'], $student['email'], $student['aadhar'], $student['samagra'],
                    $student['apaar_id'], $student['pan'], $student['previous_school'], $student['medical_conditions']
                ]);
            }

            fclose($output);
            exit;

        } catch (Exception $e) {
            $this->flash('error', 'Export failed: ' . $e->getMessage());
            $this->redirect('/admin/students');
        }
    }

    /**
     * Generate ID card
     */
    public function idCard($id) {
        try {
            $student = Student::withClass($id);

            if (!$student) {
                $this->flash('error', 'Student not found');
                $this->redirect('/admin/students');
            }

            // Include TCPDF
            require_once __DIR__ . '/../libraries/tcpdf/tcpdf.php';

            // Create new PDF document
            $pdf = new TCPDF('P', 'mm', array(85.6, 54), true, 'UTF-8', false);

            // Set document information
            $pdf->SetCreator('School Management System');
            $pdf->SetAuthor('School Management System');
            $pdf->SetTitle('Student ID Card - ' . $student->scholar_number);

            // Remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Set margins
            $pdf->SetMargins(5, 5, 5);
            $pdf->SetAutoPageBreak(false, 0);

            // Add a page
            $pdf->AddPage();

            // School logo and header
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 8, 'SCHOOL MANAGEMENT SYSTEM', 0, 1, 'C');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 5, 'Student ID Card', 0, 1, 'C');

            // Student photo
            if ($student->photo_path && file_exists($student->photo_path)) {
                $pdf->Image($student->photo_path, 10, 20, 20, 25, '', '', '', true, 150, '', false, false, 0, false, false, false);
            } else {
                $pdf->Rect(10, 20, 20, 25);
                $pdf->SetFont('helvetica', '', 6);
                $pdf->Text(12, 28, 'No Photo');
            }

            // Student details
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Text(35, 22, 'Scholar No:');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Text(55, 22, $student->scholar_number);

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Text(35, 27, 'Name:');
            $pdf->SetFont('helvetica', '', 8);
            $name = $student->first_name . ' ' . ($student->middle_name ? $student->middle_name . ' ' : '') . $student->last_name;
            $pdf->Text(45, 27, substr($name, 0, 25));

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Text(35, 32, 'Class:');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Text(45, 32, $student->class_name . ' ' . $student->section);

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Text(35, 37, 'DOB:');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Text(45, 37, $student->dob ? date('d/m/Y', strtotime($student->dob)) : 'N/A');

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->Text(35, 42, 'Blood Group:');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Text(55, 42, $student->blood_group ?: 'N/A');

            // Emergency contact
            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->Text(10, 48, 'Emergency Contact:');
            $pdf->SetFont('helvetica', '', 7);
            $contact = $student->guardian_contact ?: $student->mobile ?: 'N/A';
            $pdf->Text(10, 51, substr($contact, 0, 20));

            // Valid until
            $pdf->SetFont('helvetica', 'B', 6);
            $pdf->Text(50, 48, 'Valid Until:');
            $pdf->SetFont('helvetica', '', 6);
            $validUntil = date('d/m/Y', strtotime('+1 year'));
            $pdf->Text(50, 51, $validUntil);

            // Barcode or QR code could be added here
            // For now, just add a simple barcode-like element
            $pdf->SetFont('helvetica', '', 6);
            $pdf->Text(10, 55, 'ID: ' . $student->scholar_number);

            // Output the PDF
            $filename = 'ID_Card_' . $student->scholar_number . '.pdf';
            $pdf->Output($filename, 'D');

        } catch (Exception $e) {
            $this->flash('error', 'Error generating ID card: ' . $e->getMessage());
            $this->redirect('/admin/students');
        }
    }

    /**
     * AJAX: Get students for datatable
     */
    public function ajaxList() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $start = (int) $this->input('start', 0);
            $length = (int) $this->input('length', 10);
            $search = $this->input('search')['value'] ?? '';
            $classId = $this->input('class_id');
            $section = $this->input('section');

            // Build query
            $query = "SELECT s.*, c.class_name, c.section as class_section
                     FROM students s
                     LEFT JOIN classes c ON s.class_id = c.id
                     WHERE s.is_active = 1";

            $params = [];
            $conditions = [];

            if ($classId) {
                $conditions[] = "s.class_id = ?";
                $params[] = $classId;
            }

            if ($section) {
                $conditions[] = "s.section = ?";
                $params[] = $section;
            }

            if ($search) {
                $conditions[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.scholar_number LIKE ? OR s.admission_number LIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam]);
            }

            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            // Get total count
            $countQuery = str_replace("SELECT s.*, c.class_name, c.section as class_section", "SELECT COUNT(*) as total", $query);
            $totalResult = $this->db->fetch($countQuery, $params);
            $total = $totalResult['total'] ?? 0;

            // Add ordering and pagination
            $query .= " ORDER BY s.first_name, s.last_name LIMIT {$start}, {$length}";

            $students = $this->db->fetchAll($query, $params);

            // Format data for DataTables
            $data = [];
            foreach ($students as $student) {
                $data[] = [
                    'id' => $student['id'],
                    'scholar_number' => $student['scholar_number'],
                    'admission_number' => $student['admission_number'],
                    'name' => $student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name'],
                    'class' => $student['class_name'] . ' ' . $student['section'],
                    'gender' => ucfirst($student['gender']),
                    'mobile' => $student['mobile'] ?? '-',
                    'email' => $student['email'] ?? '-',
                    'actions' => '<a href="/admin/students/' . $student['id'] . '" class="btn btn-sm btn-info">View</a> ' .
                                '<a href="/admin/students/' . $student['id'] . '/edit" class="btn btn-sm btn-warning">Edit</a> ' .
                                '<button onclick="deleteStudent(' . $student['id'] . ')" class="btn btn-sm btn-danger">Delete</button>'
                ];
            }

            $this->json([
                'draw' => (int) $this->input('draw', 1),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $data
            ]);

        } catch (Exception $e) {
            $this->error('Error loading students: ' . $e->getMessage());
        }
    }
}