<?php
/**
 * Classes Controller
 * Handles class management operations
 */

class ClassesController extends BaseController {

    /**
     * Display list of classes
     */
    public function index() {
        try {
            $page = $this->input('page', 1);
            $perPage = $this->input('per_page', 10);
            $search = $this->input('search', '');
            $academicYear = $this->input('academic_year', '');

            $offset = ($page - 1) * $perPage;

            if (!empty($search) || !empty($academicYear)) {
                $classes = ClassModel::search($search, $academicYear);
            } else {
                $classes = ClassModel::allWithTeachers();
            }

            // Manual pagination
            $totalClasses = count($classes);
            $classes = array_slice($classes, $offset, $perPage);

            $academicYears = ClassModel::getAcademicYears();
            $classNames = ClassModel::getClassNames();

            $data = [
                'title' => 'Manage Classes',
                'classes' => $classes,
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalClasses,
                'search' => $search,
                'academic_year' => $academicYear,
                'academic_years' => $academicYears,
                'class_names' => $classNames,
                'total_pages' => ceil($totalClasses / $perPage)
            ];

            echo $this->view('admin.classes.index', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show create class form
     */
    public function create() {
        try {
            $academicYears = ClassModel::getAcademicYears();
            $teachers = Teacher::where('is_active', 1)->orderBy('first_name')->get();

            $data = [
                'title' => 'Create Class',
                'academic_years' => $academicYears,
                'teachers' => $teachers
            ];

            echo $this->view('admin.classes.create', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Store new class
     */
    public function store() {
        try {
            $rules = [
                'class_name' => 'required|max:20',
                'section' => 'required|max:10',
                'academic_year' => 'required|max:10',
                'class_teacher_id' => 'nullable|integer'
            ];

            $validated = $this->validate($rules);

            if (!$validated) {
                $this->flash('error', 'Validation failed');
                $this->flash('errors', $this->getValidationErrors());
                $this->back();
                return;
            }

            // Check for duplicate class-section-year combination
            $existing = ClassModel::where('class_name', $validated['class_name'])
                                ->where('section', $validated['section'])
                                ->where('academic_year', $validated['academic_year'])
                                ->first();

            if ($existing) {
                $this->flash('error', 'A class with this name, section, and academic year already exists');
                $this->back();
                return;
            }

            $class = ClassModel::create($validated);

            if ($class) {
                $this->flash('success', 'Class created successfully');
                $this->redirect('/admin/classes');
            } else {
                $this->flash('error', 'Failed to create class');
                $this->back();
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show class details
     */
    public function show($id) {
        try {
            $class = ClassModel::withTeacher($id);

            if (!$class) {
                $this->flash('error', 'Class not found');
                $this->redirect('/admin/classes');
                return;
            }

            $subjects = ClassSubjectModel::getByClass($id);
            $students = $class->students();

            $data = [
                'title' => 'Class Details',
                'class' => $class,
                'subjects' => $subjects,
                'students' => $students,
                'student_count' => count($students)
            ];

            echo $this->view('admin.classes.show', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show edit class form
     */
    public function edit($id) {
        try {
            $class = ClassModel::find($id);

            if (!$class) {
                $this->flash('error', 'Class not found');
                $this->redirect('/admin/classes');
                return;
            }

            $academicYears = ClassModel::getAcademicYears();
            $teachers = Teacher::where('is_active', 1)->orderBy('first_name')->get();

            $data = [
                'title' => 'Edit Class',
                'class' => $class,
                'academic_years' => $academicYears,
                'teachers' => $teachers
            ];

            echo $this->view('admin.classes.edit', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Update class
     */
    public function update($id) {
        try {
            $class = ClassModel::find($id);

            if (!$class) {
                $this->flash('error', 'Class not found');
                $this->redirect('/admin/classes');
                return;
            }

            $rules = [
                'class_name' => 'required|max:20',
                'section' => 'required|max:10',
                'academic_year' => 'required|max:10',
                'class_teacher_id' => 'nullable|integer'
            ];

            $validated = $this->validate($rules);

            if (!$validated) {
                $this->flash('error', 'Validation failed');
                $this->flash('errors', $this->getValidationErrors());
                $this->back();
                return;
            }

            // Check for duplicate class-section-year combination (excluding current)
            $existing = ClassModel::where('class_name', $validated['class_name'])
                                ->where('section', $validated['section'])
                                ->where('academic_year', $validated['academic_year'])
                                ->where('id', '!=', $id)
                                ->first();

            if ($existing) {
                $this->flash('error', 'A class with this name, section, and academic year already exists');
                $this->back();
                return;
            }

            $class->fill($validated);

            if ($class->save()) {
                $this->flash('success', 'Class updated successfully');
                $this->redirect('/admin/classes');
            } else {
                $this->flash('error', 'Failed to update class');
                $this->back();
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Delete class
     */
    public function destroy($id) {
        try {
            $class = ClassModel::find($id);

            if (!$class) {
                if ($this->isAjax()) {
                    $this->error('Class not found');
                } else {
                    $this->flash('error', 'Class not found');
                    $this->redirect('/admin/classes');
                }
                return;
            }

            // Check if class has students
            $students = $class->students();
            if (!empty($students)) {
                $message = 'Cannot delete class with enrolled students';
                if ($this->isAjax()) {
                    $this->error($message);
                } else {
                    $this->flash('error', $message);
                    $this->redirect('/admin/classes');
                }
                return;
            }

            if ($class->delete()) {
                if ($this->isAjax()) {
                    $this->success([], 'Class deleted successfully');
                } else {
                    $this->flash('success', 'Class deleted successfully');
                    $this->redirect('/admin/classes');
                }
            } else {
                $message = 'Failed to delete class';
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
     * Bulk delete classes
     */
    public function bulkDelete() {
        try {
            $ids = $this->input('ids', []);

            if (empty($ids)) {
                $this->error('No classes selected');
                return;
            }

            $deleted = 0;
            $errors = [];

            foreach ($ids as $id) {
                $class = ClassModel::find($id);
                if ($class) {
                    $students = $class->students();
                    if (!empty($students)) {
                        $errors[] = "Class {$class->class_name} {$class->section} has enrolled students";
                        continue;
                    }

                    if ($class->delete()) {
                        $deleted++;
                    }
                }
            }

            if ($deleted > 0) {
                $this->success([
                    'deleted' => $deleted,
                    'errors' => $errors
                ], "{$deleted} class(es) deleted successfully");
            } else {
                $this->error('No classes could be deleted', $errors);
            }
        } catch (Exception $e) {
            $this->error('An error occurred while deleting classes');
        }
    }

    /**
     * Get classes for AJAX requests
     */
    public function getClasses() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
            return;
        }

        try {
            $academicYear = $this->input('academic_year');
            $classes = ClassModel::byAcademicYear($academicYear);

            $data = array_map(function($class) {
                return [
                    'id' => $class->id,
                    'class_name' => $class->class_name,
                    'section' => $class->section,
                    'academic_year' => $class->academic_year,
                    'teacher_name' => $class->teacher() ? $class->teacher()->getFullName() : null
                ];
            }, $classes);

            $this->success($data);
        } catch (Exception $e) {
            $this->error('Failed to load classes');
        }
    }

    /**
     * Manage subjects for a class
     */
    public function manageSubjects($classId) {
        try {
            $class = ClassModel::find($classId);

            if (!$class) {
                $this->flash('error', 'Class not found');
                $this->redirect('/admin/classes');
                return;
            }

            // Get all subjects
            $allSubjects = SubjectModel::all();

            // Get assigned subjects for this class
            $assignedSubjects = ClassSubjectModel::getByClass($classId);

            // Get available teachers
            $teachers = Teacher::where('is_active', 1)->orderBy('first_name')->get();

            $data = [
                'title' => 'Manage Subjects - ' . $class->class_name . ' ' . $class->section,
                'class' => $class,
                'allSubjects' => $allSubjects,
                'assignedSubjects' => $assignedSubjects,
                'teachers' => $teachers
            ];

            echo $this->view('admin.classes.manage_subjects', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Assign subject to class
     */
    public function assignSubject($classId, $subjectId) {
        try {
            $class = ClassModel::find($classId);
            $subject = SubjectModel::find($subjectId);

            if (!$class || !$subject) {
                $this->error('Class or subject not found');
                return;
            }

            // Check if already assigned
            if (ClassSubjectModel::assignmentExists($classId, $subjectId)) {
                $this->error('Subject is already assigned to this class');
                return;
            }

            // Create assignment
            $assignment = ClassSubjectModel::create([
                'class_id' => $classId,
                'subject_id' => $subjectId,
                'teacher_id' => null
            ]);

            if ($assignment) {
                $this->success([
                    'assignment' => $assignment,
                    'subject' => $subject,
                    'message' => 'Subject assigned successfully'
                ]);
            } else {
                $this->error('Failed to assign subject');
            }
        } catch (Exception $e) {
            $this->error('An error occurred while assigning subject');
        }
    }

    /**
     * Unassign subject from class
     */
    public function unassignSubject($classId, $subjectId) {
        try {
            $assignment = ClassSubjectModel::where('class_id', $classId)
                                          ->where('subject_id', $subjectId)
                                          ->first();

            if (!$assignment) {
                $this->error('Assignment not found');
                return;
            }

            if ($assignment->delete()) {
                $this->success([], 'Subject unassigned successfully');
            } else {
                $this->error('Failed to unassign subject');
            }
        } catch (Exception $e) {
            $this->error('An error occurred while unassigning subject');
        }
    }

    /**
     * Assign teacher to class-subject
     */
    public function assignTeacher($classId, $subjectId) {
        try {
            $teacherId = $this->input('teacher_id');

            if (!$teacherId) {
                $this->error('Teacher ID is required');
                return;
            }

            $teacher = Teacher::find($teacherId);
            if (!$teacher) {
                $this->error('Teacher not found');
                return;
            }

            if (ClassSubjectModel::assignTeacher($classId, $subjectId, $teacherId)) {
                $this->success([
                    'teacher' => $teacher,
                    'message' => 'Teacher assigned successfully'
                ]);
            } else {
                $this->error('Failed to assign teacher');
            }
        } catch (Exception $e) {
            $this->error('An error occurred while assigning teacher');
        }
    }

    /**
     * Show class timetable
     */
    public function timetable($classId) {
        try {
            $class = ClassModel::find($classId);

            if (!$class) {
                $this->flash('error', 'Class not found');
                $this->redirect('/admin/classes');
                return;
            }

            $subjects = ClassSubjectModel::getByClass($classId);

            $data = [
                'title' => 'Timetable - ' . $class->class_name . ' ' . $class->section,
                'class' => $class,
                'subjects' => $subjects
            ];

            echo $this->view('admin.classes.timetable', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }
}