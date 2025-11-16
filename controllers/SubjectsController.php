<?php
/**
 * Subjects Controller
 * Handles subject management operations
 */

class SubjectsController extends BaseController {

    /**
     * Display list of subjects
     */
    public function index() {
        try {
            $page = $this->input('page', 1);
            $perPage = $this->input('per_page', 10);
            $search = $this->input('search', '');

            $offset = ($page - 1) * $perPage;

            if (!empty($search)) {
                $subjects = SubjectModel::search($search);
            } else {
                $subjects = SubjectModel::allWithClassCount();
            }

            // Manual pagination
            $totalSubjects = count($subjects);
            $subjects = array_slice($subjects, $offset, $perPage);

            $data = [
                'title' => 'Manage Subjects',
                'subjects' => $subjects,
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $totalSubjects,
                'search' => $search,
                'total_pages' => ceil($totalSubjects / $perPage)
            ];

            echo $this->view('admin.subjects.index', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show create subject form
     */
    public function create() {
        try {
            $data = [
                'title' => 'Create Subject'
            ];

            echo $this->view('admin.subjects.create', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Store new subject
     */
    public function store() {
        try {
            $rules = [
                'subject_name' => 'required|max:100',
                'subject_code' => 'required|max:20|unique:subjects,subject_code',
                'description' => 'nullable|max:500'
            ];

            $validated = $this->validate($rules);

            if (!$validated) {
                $this->flash('error', 'Validation failed');
                $this->flash('errors', $this->getValidationErrors());
                $this->back();
                return;
            }

            // Check if subject code already exists
            if (SubjectModel::codeExists($validated['subject_code'])) {
                $this->flash('error', 'Subject code already exists');
                $this->back();
                return;
            }

            $subject = SubjectModel::create($validated);

            if ($subject) {
                $this->flash('success', 'Subject created successfully');
                $this->redirect('/admin/subjects');
            } else {
                $this->flash('error', 'Failed to create subject');
                $this->back();
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show subject details
     */
    public function show($id) {
        try {
            $subject = SubjectModel::withClasses($id);

            if (!$subject) {
                $this->flash('error', 'Subject not found');
                $this->redirect('/admin/subjects');
                return;
            }

            $classes = $subject->classes();
            $teachers = $subject->teachers();

            $data = [
                'title' => 'Subject Details',
                'subject' => $subject,
                'classes' => $classes,
                'teachers' => $teachers,
                'class_count' => count($classes),
                'teacher_count' => count($teachers)
            ];

            echo $this->view('admin.subjects.show', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Show edit subject form
     */
    public function edit($id) {
        try {
            $subject = SubjectModel::find($id);

            if (!$subject) {
                $this->flash('error', 'Subject not found');
                $this->redirect('/admin/subjects');
                return;
            }

            $data = [
                'title' => 'Edit Subject',
                'subject' => $subject
            ];

            echo $this->view('admin.subjects.edit', $data);
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Update subject
     */
    public function update($id) {
        try {
            $subject = SubjectModel::find($id);

            if (!$subject) {
                $this->flash('error', 'Subject not found');
                $this->redirect('/admin/subjects');
                return;
            }

            $rules = [
                'subject_name' => 'required|max:100',
                'subject_code' => 'required|max:20',
                'description' => 'nullable|max:500'
            ];

            $validated = $this->validate($rules);

            if (!$validated) {
                $this->flash('error', 'Validation failed');
                $this->flash('errors', $this->getValidationErrors());
                $this->back();
                return;
            }

            // Check if subject code already exists (excluding current)
            if (SubjectModel::codeExists($validated['subject_code'], $id)) {
                $this->flash('error', 'Subject code already exists');
                $this->back();
                return;
            }

            $subject->fill($validated);

            if ($subject->save()) {
                $this->flash('success', 'Subject updated successfully');
                $this->redirect('/admin/subjects');
            } else {
                $this->flash('error', 'Failed to update subject');
                $this->back();
            }
        } catch (Exception $e) {
            $this->handleException($e);
        }
    }

    /**
     * Delete subject
     */
    public function destroy($id) {
        try {
            $subject = SubjectModel::find($id);

            if (!$subject) {
                if ($this->isAjax()) {
                    $this->error('Subject not found');
                } else {
                    $this->flash('error', 'Subject not found');
                    $this->redirect('/admin/subjects');
                }
                return;
            }

            // Check if subject is assigned to any classes
            $classes = $subject->classes();
            if (!empty($classes)) {
                $message = 'Cannot delete subject that is assigned to classes';
                if ($this->isAjax()) {
                    $this->error($message);
                } else {
                    $this->flash('error', $message);
                    $this->redirect('/admin/subjects');
                }
                return;
            }

            if ($subject->delete()) {
                if ($this->isAjax()) {
                    $this->success([], 'Subject deleted successfully');
                } else {
                    $this->flash('success', 'Subject deleted successfully');
                    $this->redirect('/admin/subjects');
                }
            } else {
                $message = 'Failed to delete subject';
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
     * Bulk delete subjects
     */
    public function bulkDelete() {
        try {
            $ids = $this->input('ids', []);

            if (empty($ids)) {
                $this->error('No subjects selected');
                return;
            }

            $deleted = 0;
            $errors = [];

            foreach ($ids as $id) {
                $subject = SubjectModel::find($id);
                if ($subject) {
                    $classes = $subject->classes();
                    if (!empty($classes)) {
                        $errors[] = "Subject {$subject->subject_name} is assigned to classes";
                        continue;
                    }

                    if ($subject->delete()) {
                        $deleted++;
                    }
                }
            }

            if ($deleted > 0) {
                $this->success([
                    'deleted' => $deleted,
                    'errors' => $errors
                ], "{$deleted} subject(s) deleted successfully");
            } else {
                $this->error('No subjects could be deleted', $errors);
            }
        } catch (Exception $e) {
            $this->error('An error occurred while deleting subjects');
        }
    }

    /**
     * Get subjects for AJAX requests
     */
    public function getSubjects() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
            return;
        }

        try {
            $subjects = SubjectModel::all();

            $data = array_map(function($subject) {
                return [
                    'id' => $subject->id,
                    'subject_name' => $subject->subject_name,
                    'subject_code' => $subject->subject_code,
                    'description' => $subject->description
                ];
            }, $subjects);

            $this->success($data);
        } catch (Exception $e) {
            $this->error('Failed to load subjects');
        }
    }
}