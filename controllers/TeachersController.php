<?php
/**
 * Teachers Controller
 * Handles admin teacher management operations
 */

class TeachersController extends BaseController {

    /**
     * Display teachers list
     */
    public function index() {
        try {
            // Get filter parameters
            $department = $this->input('department');
            $designation = $this->input('designation');
            $search = $this->input('search');
            $page = (int) $this->input('page', 1);
            $perPage = 25;

            // Get unique departments and designations for filter dropdown
            $departments = $this->db->fetchAll(
                "SELECT DISTINCT department FROM teachers WHERE department IS NOT NULL AND department != '' ORDER BY department"
            );
            $designations = $this->db->fetchAll(
                "SELECT DISTINCT designation FROM teachers WHERE designation IS NOT NULL AND designation != '' ORDER BY designation"
            );

            // Build query
            $query = "SELECT t.*, u.username, u.email as user_email
                     FROM teachers t
                     LEFT JOIN users u ON t.user_id = u.id
                     WHERE 1=1";

            $params = [];
            $conditions = [];

            if ($department) {
                $conditions[] = "t.department = ?";
                $params[] = $department;
            }

            if ($designation) {
                $conditions[] = "t.designation = ?";
                $params[] = $designation;
            }

            if ($search) {
                $conditions[] = "(t.first_name LIKE ? OR t.last_name LIKE ? OR t.employee_id LIKE ? OR t.email LIKE ? OR CONCAT(t.first_name, ' ', t.last_name) LIKE ?)";
                $searchParam = "%{$search}%";
                $params = array_merge($params, [$searchParam, $searchParam, $searchParam, $searchParam, $searchParam]);
            }

            if (!empty($conditions)) {
                $query .= " AND " . implode(" AND ", $conditions);
            }

            // Get total count for pagination
            $countQuery = str_replace("SELECT t.*, u.username, u.email as user_email", "SELECT COUNT(*) as total", $query);
            $totalResult = $this->db->fetch($countQuery, $params);
            $total = $totalResult['total'] ?? 0;

            // Add ordering and pagination
            $query .= " ORDER BY t.first_name, t.last_name LIMIT " . (($page - 1) * $perPage) . ", {$perPage}";

            $teachers = $this->db->fetchAll($query, $params);

            // Calculate pagination
            $totalPages = ceil($total / $perPage);

            $data = [
                'title' => 'Teachers Management',
                'teachers' => $teachers,
                'departments' => $departments,
                'designations' => $designations,
                'filters' => [
                    'department' => $department,
                    'designation' => $designation,
                    'search' => $search
                ],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total' => $total,
                    'per_page' => $perPage
                ]
            ];

            echo $this->view('admin.teachers.index', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading teachers: ' . $e->getMessage());
            echo $this->view('admin.teachers.index', [
                'title' => 'Teachers Management',
                'teachers' => [],
                'departments' => [],
                'designations' => [],
                'filters' => [],
                'pagination' => ['current_page' => 1, 'total_pages' => 0, 'total' => 0, 'per_page' => 25]
            ]);
        }
    }

    /**
     * Show create teacher form
     */
    public function create() {
        try {
            // Get departments and designations for dropdown
            $departments = $this->db->fetchAll(
                "SELECT DISTINCT department FROM teachers WHERE department IS NOT NULL AND department != '' ORDER BY department"
            );
            $designations = $this->db->fetchAll(
                "SELECT DISTINCT designation FROM teachers WHERE designation IS NOT NULL AND designation != '' ORDER BY designation"
            );

            $data = [
                'title' => 'Add New Teacher',
                'departments' => $departments,
                'designations' => $designations,
                'teacher' => null
            ];

            echo $this->view('admin.teachers.create', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading form: ' . $e->getMessage());
            $this->redirect('/admin/teachers');
        }
    }

    /**
     * Store new teacher
     */
    public function store() {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/teachers');
        }

        // Validation rules
        $rules = [
            'employee_id' => 'required|max:20',
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'marital_status' => 'in:single,married,divorced,widowed',
            'blood_group' => 'max:10',
            'qualification' => 'max:100',
            'specialization' => 'max:100',
            'designation' => 'required|max:50',
            'department' => 'required|max:50',
            'date_of_joining' => 'required|date',
            'experience_years' => 'integer|min:0',
            'permanent_address' => 'max:500',
            'temporary_address' => 'max:500',
            'mobile' => 'max:15',
            'email' => 'email|max:100',
            'aadhar' => 'max:12',
            'pan' => 'max:10',
            'samagra_id' => 'max:20',
            'medical_conditions' => 'max:500'
        ];

        $validated = $this->validate($rules);

        if (!$validated) {
            $this->flash('error', 'Validation failed');
            $this->flash('old_input', $this->all());
            $this->flash('validation_errors', $this->getValidationErrors());
            $this->redirect('/admin/teachers/create');
        }

        try {
            // Check for duplicate employee ID
            if (Teacher::employeeIdExists($validated['employee_id'])) {
                $this->flash('error', 'Employee ID already exists');
                $this->flash('old_input', $this->all());
                $this->redirect('/admin/teachers/create');
            }

            // Handle photo upload
            $photoPath = null;
            if ($this->file('photo')) {
                $upload = $this->handleUpload('photo', 'uploads/teachers/photos', ['jpg', 'jpeg', 'png'], 2 * 1024 * 1024); // 2MB limit
                if ($upload['success']) {
                    $photoPath = $upload['path'];
                }
            }

            // Create teacher
            $teacherData = $validated;
            $teacherData['photo_path'] = $photoPath;
            $teacherData['is_active'] = 1;

            $teacher = Teacher::create($teacherData);

            if ($teacher) {
                $this->flash('success', 'Teacher created successfully');
                $this->redirect('/admin/teachers');
            } else {
                $this->flash('error', 'Failed to create teacher');
                $this->redirect('/admin/teachers/create');
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error creating teacher: ' . $e->getMessage());
            $this->flash('old_input', $this->all());
            $this->redirect('/admin/teachers/create');
        }
    }

    /**
     * Show teacher details
     */
    public function show($id) {
        try {
            $teacher = Teacher::withUser($id);

            if (!$teacher) {
                $this->flash('error', 'Teacher not found');
                $this->redirect('/admin/teachers');
            }

            // Get assigned subjects
            $assignedSubjects = $teacher->getAssignedSubjects();

            // Get performance metrics
            $performanceMetrics = $teacher->getPerformanceMetrics();

            $data = [
                'title' => 'Teacher Profile - ' . $teacher->getFullName(),
                'teacher' => $teacher,
                'assignedSubjects' => $assignedSubjects,
                'performanceMetrics' => $performanceMetrics
            ];

            echo $this->view('admin.teachers.profile', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading teacher profile: ' . $e->getMessage());
            $this->redirect('/admin/teachers');
        }
    }

    /**
     * Show edit teacher form
     */
    public function edit($id) {
        try {
            $teacher = Teacher::find($id);

            if (!$teacher) {
                $this->flash('error', 'Teacher not found');
                $this->redirect('/admin/teachers');
            }

            // Get departments and designations for dropdown
            $departments = $this->db->fetchAll(
                "SELECT DISTINCT department FROM teachers WHERE department IS NOT NULL AND department != '' ORDER BY department"
            );
            $designations = $this->db->fetchAll(
                "SELECT DISTINCT designation FROM teachers WHERE designation IS NOT NULL AND designation != '' ORDER BY designation"
            );

            $data = [
                'title' => 'Edit Teacher - ' . $teacher->getFullName(),
                'teacher' => $teacher,
                'departments' => $departments,
                'designations' => $designations
            ];

            echo $this->view('admin.teachers.edit', $data);

        } catch (Exception $e) {
            $this->flash('error', 'Error loading edit form: ' . $e->getMessage());
            $this->redirect('/admin/teachers');
        }
    }

    /**
     * Update teacher
     */
    public function update($id) {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/teachers');
        }

        $teacher = Teacher::find($id);
        if (!$teacher) {
            $this->flash('error', 'Teacher not found');
            $this->redirect('/admin/teachers');
        }

        // Validation rules (similar to create but make some fields optional for updates)
        $rules = [
            'employee_id' => 'required|max:20',
            'first_name' => 'required|max:50',
            'last_name' => 'required|max:50',
            'dob' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'marital_status' => 'in:single,married,divorced,widowed',
            'blood_group' => 'max:10',
            'qualification' => 'max:100',
            'specialization' => 'max:100',
            'designation' => 'required|max:50',
            'department' => 'required|max:50',
            'date_of_joining' => 'required|date',
            'experience_years' => 'integer|min:0',
            'permanent_address' => 'max:500',
            'temporary_address' => 'max:500',
            'mobile' => 'max:15',
            'email' => 'email|max:100',
            'aadhar' => 'max:12',
            'pan' => 'max:10',
            'samagra_id' => 'max:20',
            'medical_conditions' => 'max:500',
            'is_active' => 'in:0,1'
        ];

        $validated = $this->validate($rules);

        if (!$validated) {
            $this->flash('error', 'Validation failed');
            $this->flash('old_input', $this->all());
            $this->flash('validation_errors', $this->getValidationErrors());
            $this->redirect("/admin/teachers/{$id}/edit");
        }

        try {
            // Check for duplicate employee ID (excluding current teacher)
            if (Teacher::employeeIdExists($validated['employee_id'], $id)) {
                $this->flash('error', 'Employee ID already exists');
                $this->flash('old_input', $this->all());
                $this->redirect("/admin/teachers/{$id}/edit");
            }

            // Handle photo upload
            if ($this->file('photo')) {
                $upload = $this->handleUpload('photo', 'uploads/teachers/photos', ['jpg', 'jpeg', 'png'], 2 * 1024 * 1024);
                if ($upload['success']) {
                    // Delete old photo if exists
                    if ($teacher->photo_path && file_exists($teacher->photo_path)) {
                        unlink($teacher->photo_path);
                    }
                    $validated['photo_path'] = $upload['path'];
                }
            }

            // Update teacher
            $teacher->fill($validated);
            if ($teacher->save()) {
                $this->flash('success', 'Teacher updated successfully');
                $this->redirect('/admin/teachers');
            } else {
                $this->flash('error', 'Failed to update teacher');
                $this->redirect("/admin/teachers/{$id}/edit");
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error updating teacher: ' . $e->getMessage());
            $this->flash('old_input', $this->all());
            $this->redirect("/admin/teachers/{$id}/edit");
        }
    }

    /**
     * Delete teacher
     */
    public function destroy($id) {
        try {
            $teacher = Teacher::find($id);

            if (!$teacher) {
                if ($this->isAjax()) {
                    $this->error('Teacher not found');
                }
                $this->flash('error', 'Teacher not found');
                $this->redirect('/admin/teachers');
            }

            // Delete photo if exists
            if ($teacher->photo_path && file_exists($teacher->photo_path)) {
                unlink($teacher->photo_path);
            }

            if ($teacher->delete()) {
                if ($this->isAjax()) {
                    $this->success(['message' => 'Teacher deleted successfully']);
                }
                $this->flash('success', 'Teacher deleted successfully');
            } else {
                if ($this->isAjax()) {
                    $this->error('Failed to delete teacher');
                }
                $this->flash('error', 'Failed to delete teacher');
            }

        } catch (Exception $e) {
            if ($this->isAjax()) {
                $this->error('Error deleting teacher: ' . $e->getMessage());
            }
            $this->flash('error', 'Error deleting teacher: ' . $e->getMessage());
        }

        if (!$this->isAjax()) {
            $this->redirect('/admin/teachers');
        }
    }

    /**
     * Assign subjects to teacher
     */
    public function assignSubjects($id) {
        if (!$this->isMethod('POST')) {
            $this->redirect('/admin/teachers');
        }

        $teacher = Teacher::find($id);
        if (!$teacher) {
            $this->flash('error', 'Teacher not found');
            $this->redirect('/admin/teachers');
        }

        $classId = $this->input('class_id');
        $subjectIds = $this->input('subject_ids', []);

        if (!$classId || empty($subjectIds)) {
            $this->flash('error', 'Please select class and subjects');
            $this->redirect("/admin/teachers/{$id}");
        }

        try {
            $assigned = 0;
            foreach ($subjectIds as $subjectId) {
                // Check if already assigned
                $existing = $this->db->fetch(
                    "SELECT id FROM class_subjects WHERE class_id = ? AND subject_id = ? AND teacher_id = ?",
                    [$classId, $subjectId, $id]
                );

                if (!$existing) {
                    $result = $teacher->assignSubject($classId, $subjectId);
                    if ($result) $assigned++;
                }
            }

            $this->flash('success', "{$assigned} subject(s) assigned successfully");
            $this->redirect("/admin/teachers/{$id}");

        } catch (Exception $e) {
            $this->flash('error', 'Error assigning subjects: ' . $e->getMessage());
            $this->redirect("/admin/teachers/{$id}");
        }
    }

    /**
     * Remove subject assignment
     */
    public function removeSubject($id, $assignmentId) {
        $teacher = Teacher::find($id);
        if (!$teacher) {
            $this->flash('error', 'Teacher not found');
            $this->redirect('/admin/teachers');
        }

        try {
            $result = $this->db->delete('class_subjects', 'id = ? AND teacher_id = ?', [$assignmentId, $id]);

            if ($result) {
                $this->flash('success', 'Subject assignment removed successfully');
            } else {
                $this->flash('error', 'Failed to remove subject assignment');
            }

        } catch (Exception $e) {
            $this->flash('error', 'Error removing subject assignment: ' . $e->getMessage());
        }

        $this->redirect("/admin/teachers/{$id}");
    }

    /**
     * Export teachers to CSV
     */
    public function export() {
        try {
            $department = $this->input('department');
            $designation = $this->input('designation');

            $query = "SELECT t.*, u.username, u.email as user_email
                     FROM teachers t
                     LEFT JOIN users u ON t.user_id = u.id
                     WHERE t.is_active = 1";

            $params = [];

            if ($department) {
                $query .= " AND t.department = ?";
                $params[] = $department;
            }

            if ($designation) {
                $query .= " AND t.designation = ?";
                $params[] = $designation;
            }

            $query .= " ORDER BY t.first_name, t.last_name";

            $teachers = $this->db->fetchAll($query, $params);

            // Set headers for CSV download
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="teachers_' . date('Y-m-d') . '.csv"');

            $output = fopen('php://output', 'w');

            // CSV headers
            fputcsv($output, [
                'Employee ID', 'First Name', 'Middle Name', 'Last Name', 'Date of Birth', 'Gender',
                'Marital Status', 'Blood Group', 'Qualification', 'Specialization', 'Designation',
                'Department', 'Date of Joining', 'Experience Years', 'Permanent Address',
                'Temporary Address', 'Mobile', 'Email', 'Aadhar', 'PAN', 'Samagra ID', 'Medical Conditions'
            ]);

            // CSV data
            foreach ($teachers as $teacher) {
                fputcsv($output, [
                    $teacher['employee_id'], $teacher['first_name'], $teacher['middle_name'], $teacher['last_name'],
                    $teacher['dob'], $teacher['gender'], $teacher['marital_status'], $teacher['blood_group'],
                    $teacher['qualification'], $teacher['specialization'], $teacher['designation'],
                    $teacher['department'], $teacher['date_of_joining'], $teacher['experience_years'],
                    $teacher['permanent_address'], $teacher['temporary_address'], $teacher['mobile'],
                    $teacher['email'], $teacher['aadhar'], $teacher['pan'], $teacher['samagra_id'],
                    $teacher['medical_conditions']
                ]);
            }

            fclose($output);
            exit;

        } catch (Exception $e) {
            $this->flash('error', 'Export failed: ' . $e->getMessage());
            $this->redirect('/admin/teachers');
        }
    }

    /**
     * Get classes for AJAX
     */
    public function getClasses() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $classes = $this->db->fetchAll(
                "SELECT id, class_name, section, academic_year FROM classes ORDER BY class_name, section"
            );

            $this->json($classes);

        } catch (Exception $e) {
            $this->error('Error loading classes: ' . $e->getMessage());
        }
    }

    /**
     * Get subjects for AJAX
     */
    public function getSubjects() {
        if (!$this->isAjax()) {
            $this->error('Invalid request');
        }

        try {
            $classId = $this->input('class_id');
            $query = "SELECT s.* FROM subjects s";

            if ($classId) {
                $query .= " INNER JOIN class_subjects cs ON s.id = cs.subject_id WHERE cs.class_id = ?";
                $subjects = $this->db->fetchAll($query, [$classId]);
            } else {
                $subjects = $this->db->fetchAll($query . " ORDER BY s.subject_name");
            }

            $this->json($subjects);

        } catch (Exception $e) {
            $this->error('Error loading subjects: ' . $e->getMessage());
        }
    }
}