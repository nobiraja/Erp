<?php
/**
 * Application Routes
 * Define all application routes here
 */

// Get router instance
$router = Router::getInstance();

// Public routes
$router->get('/', 'Homepage@index');
$router->get('/about', 'AboutController@index');
$router->get('/contact', 'ContactController@index');
$router->post('/contact', 'ContactController@sendMessage');
$router->get('/courses', 'CoursesController@index');
$router->get('/admission', 'AdmissionController@index');
$router->post('/admission', 'AdmissionController@submit');
$router->get('/events', 'EventsController@index');
$router->get('/gallery', 'GalleryController@index');

// Authentication routes
$router->get('/login', 'Auth@login');
$router->post('/login', 'Auth@authenticate');
$router->get('/logout', 'Auth@logout');
$router->get('/reset-password', 'Auth@resetPassword');
$router->post('/reset-password', 'Auth@processPasswordReset');

// API routes
$router->post('/api/login', 'Auth@apiLogin');
$router->post('/api/forgot-password', 'Auth@apiForgotPassword');

// Admin routes (with admin middleware)
$adminMiddleware = [new AuthMiddleware(), RoleCheckMiddleware::admin()];

$router->get('/admin/dashboard', 'AdminDashboardController@index', $adminMiddleware);
$router->get('/admin/students', 'StudentsController@index', $adminMiddleware);
$router->get('/admin/students/create', 'StudentsController@create', $adminMiddleware);
$router->post('/admin/students', 'StudentsController@store', $adminMiddleware);
$router->get('/admin/students/{id}', 'StudentsController@show', $adminMiddleware);
$router->get('/admin/students/{id}/edit', 'StudentsController@edit', $adminMiddleware);
$router->put('/admin/students/{id}', 'StudentsController@update', $adminMiddleware);
$router->delete('/admin/students/{id}', 'StudentsController@destroy', $adminMiddleware);

$router->get('/admin/teachers', 'TeachersController@index', $adminMiddleware);
$router->get('/admin/teachers/create', 'TeachersController@create', $adminMiddleware);
$router->post('/admin/teachers', 'TeachersController@store', $adminMiddleware);
$router->get('/admin/teachers/{id}', 'TeachersController@show', $adminMiddleware);
$router->get('/admin/teachers/{id}/edit', 'TeachersController@edit', $adminMiddleware);
$router->put('/admin/teachers/{id}', 'TeachersController@update', $adminMiddleware);
$router->delete('/admin/teachers/{id}', 'TeachersController@destroy', $adminMiddleware);

$router->get('/admin/classes', 'ClassesController@index', $adminMiddleware);
$router->get('/admin/classes/create', 'ClassesController@create', $adminMiddleware);
$router->post('/admin/classes', 'ClassesController@store', $adminMiddleware);
$router->get('/admin/classes/{id}', 'ClassesController@show', $adminMiddleware);
$router->get('/admin/classes/{id}/edit', 'ClassesController@edit', $adminMiddleware);
$router->put('/admin/classes/{id}', 'ClassesController@update', $adminMiddleware);
$router->delete('/admin/classes/{id}', 'ClassesController@destroy', $adminMiddleware);

$router->get('/admin/subjects', 'SubjectsController@index', $adminMiddleware);
$router->get('/admin/subjects/create', 'SubjectsController@create', $adminMiddleware);
$router->post('/admin/subjects', 'SubjectsController@store', $adminMiddleware);
$router->get('/admin/subjects/{id}', 'SubjectsController@show', $adminMiddleware);
$router->get('/admin/subjects/{id}/edit', 'SubjectsController@edit', $adminMiddleware);
$router->put('/admin/subjects/{id}', 'SubjectsController@update', $adminMiddleware);
$router->delete('/admin/subjects/{id}', 'SubjectsController@destroy', $adminMiddleware);

$router->get('/admin/fees', 'FeesController@index', $adminMiddleware);
$router->get('/admin/fees/collect', 'FeesController@collect', $adminMiddleware);
$router->post('/admin/fees/collect', 'FeesController@processCollection', $adminMiddleware);
$router->get('/admin/fees/receipt/{id}', 'FeesController@receipt', $adminMiddleware);

$router->get('/admin/attendance', 'AttendanceController@index', $adminMiddleware);
$router->get('/admin/attendance/mark', 'AttendanceController@mark', $adminMiddleware);
$router->post('/admin/attendance/mark', 'AttendanceController@save', $adminMiddleware);
$router->get('/admin/attendance/reports', 'AttendanceController@reports', $adminMiddleware);

$router->get('/admin/exams', 'ExamsController@index', $adminMiddleware);
$router->get('/admin/exams/create', 'ExamsController@create', $adminMiddleware);
$router->post('/admin/exams', 'ExamsController@store', $adminMiddleware);
$router->get('/admin/exams/{id}/edit', 'ExamsController@edit', $adminMiddleware);
$router->put('/admin/exams/{id}', 'ExamsController@update', $adminMiddleware);
$router->delete('/admin/exams/{id}', 'ExamsController@destroy', $adminMiddleware);

$router->get('/admin/reports', 'AdminReportsController@index', $adminMiddleware);
$router->get('/admin/settings', 'SettingsController@index', $adminMiddleware);
$router->post('/admin/settings', 'SettingsController@update', $adminMiddleware);

// Student routes
$studentMiddleware = [new AuthMiddleware(), RoleCheckMiddleware::student()];

$router->get('/student/dashboard', 'StudentController@dashboard', $studentMiddleware);
$router->get('/student/profile', 'StudentController@profile', $studentMiddleware);
$router->post('/student/profile', 'StudentController@updateProfile', $studentMiddleware);
$router->get('/student/attendance', 'StudentController@attendance', $studentMiddleware);
$router->get('/student/fees', 'StudentController@fees', $studentMiddleware);
$router->get('/student/results', 'StudentController@results', $studentMiddleware);

// Teacher routes
$teacherMiddleware = [new AuthMiddleware(), RoleCheckMiddleware::teacher()];

$router->get('/teacher/dashboard', 'TeacherController@dashboard', $teacherMiddleware);
$router->get('/teacher/classes', 'TeacherController@classes', $teacherMiddleware);
$router->get('/teacher/attendance', 'TeacherController@attendance', $teacherMiddleware);
$router->post('/teacher/attendance', 'TeacherController@saveAttendance', $teacherMiddleware);

// Parent routes
$parentMiddleware = [new AuthMiddleware(), RoleCheckMiddleware::parent()];

$router->get('/parent/dashboard', 'ParentController@dashboard', $parentMiddleware);
$router->get('/parent/children', 'ParentController@children', $parentMiddleware);
$router->get('/parent/attendance', 'ParentController@attendance', $parentMiddleware);
$router->get('/parent/fees', 'ParentController@fees', $parentMiddleware);
$router->get('/parent/results', 'ParentController@results', $parentMiddleware);

// Cashier routes
$cashierMiddleware = [new AuthMiddleware(), RoleCheckMiddleware::cashier()];

$router->get('/cashier/dashboard', 'CashierController@dashboard', $cashierMiddleware);
$router->get('/cashier/fees', 'CashierFeesController@index', $cashierMiddleware);
$router->get('/cashier/fees/collect', 'CashierFeesController@collect', $cashierMiddleware);
$router->post('/cashier/fees/collect', 'CashierFeesController@processCollection', $cashierMiddleware);
$router->get('/cashier/reports', 'CashierReportsController@index', $cashierMiddleware);