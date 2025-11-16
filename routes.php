<?php
/**
 * Application Routes
 * Define all application routes here
 */

$router = Router::getInstance();

// Global middleware
// $router->middleware(['SecurityMiddleware']);

// Public routes
$router->get('/', 'Homepage@index');
$router->get('/about', 'About@index');
$router->get('/courses', 'Courses@index');
$router->get('/events', 'Events@index');
$router->get('/gallery', 'Gallery@index');
$router->get('/contact', 'Contact@index');
$router->post('/contact/submit', 'Contact@submit');
$router->get('/admission', 'Admission@index');

// Homepage AJAX routes
$router->get('/api/homepage/content', 'Homepage@getContent');
$router->get('/api/homepage/carousel', 'Homepage@getCarousel');
$router->get('/api/homepage/events', 'Homepage@getEvents');
$router->get('/api/homepage/gallery', 'Homepage@getGallery');

// About page AJAX routes
$router->get('/api/about/faculty', 'About@getFaculty');

// Courses page AJAX routes
$router->get('/api/courses/subjects', 'Courses@getSubjects');

// Events page AJAX routes
$router->get('/api/events/calendar', 'Events@getCalendar');
$router->get('/api/events/upcoming', 'Events@getUpcoming');

// Gallery page AJAX routes
$router->get('/api/gallery/category', 'Gallery@getByCategory');
$router->get('/api/gallery/search', 'Gallery@search');

// Contact page AJAX routes
$router->get('/api/contact/info', 'Contact@getInfo');

// Admission page AJAX routes
$router->get('/api/admission/fees', 'Admission@getFees');
$router->get('/api/admission/requirements', 'Admission@getRequirements');

$router->get('/login', 'Auth@login');
$router->post('/login', 'Auth@authenticate');
$router->get('/logout', 'Auth@logout');

// Password reset routes
$router->get('/reset-password', 'Auth@resetPassword');
$router->post('/reset-password', 'Auth@processPasswordReset');

// API routes for authentication
$router->post('/api/v1/auth/login', 'Auth@apiLogin');
$router->post('/api/v1/auth/forgot-password', 'Auth@apiForgotPassword');

// Protected routes (require authentication)
$router->get('/dashboard', 'DashboardController@index', ['AuthMiddleware']);

// Admin routes
$router->get('/admin/dashboard', 'AdminDashboardController@index', [RoleCheckMiddleware::admin()]);
$router->get('/admin/dashboard/getStats', 'AdminDashboardController@getStats', [RoleCheckMiddleware::admin()]);
$router->get('/admin/dashboard/getUserStats', 'AdminDashboardController@getUserStats', [RoleCheckMiddleware::admin()]);
$router->get('/admin/dashboard/getAttendanceStats', 'AdminDashboardController@getAttendanceStats', [RoleCheckMiddleware::admin()]);
$router->get('/admin/dashboard/getFeeStats', 'AdminDashboardController@getFeeStats', [RoleCheckMiddleware::admin()]);
$router->get('/admin/dashboard/getRecentActivities', 'AdminDashboardController@getRecentActivities', [RoleCheckMiddleware::admin()]);
$router->get('/admin/dashboard/getClassAttendanceChart', 'AdminDashboardController@getClassAttendanceChart', [RoleCheckMiddleware::admin()]);
$router->get('/admin/dashboard/getMonthlyFeeChart', 'AdminDashboardController@getMonthlyFeeChart', [RoleCheckMiddleware::admin()]);
$router->get('/admin/dashboard/getUpcomingEvents', 'AdminDashboardController@getUpcomingEvents', [RoleCheckMiddleware::admin()]);
$router->resource('/admin/students', 'StudentsController', [RoleCheckMiddleware::admin()]);
$router->resource('/admin/teachers', 'TeachersController', [RoleCheckMiddleware::admin()]);
$router->get('/admin/teachers/get-classes', 'TeachersController@getClasses', [RoleCheckMiddleware::admin()]);
$router->get('/admin/teachers/get-subjects', 'TeachersController@getSubjects', [RoleCheckMiddleware::admin()]);
$router->resource('/admin/classes', 'ClassesController', [RoleCheckMiddleware::admin()]);
$router->get('/admin/classes/{id}/subjects', 'ClassesController@manageSubjects', [RoleCheckMiddleware::admin()]);
$router->post('/admin/classes/{classId}/subjects/{subjectId}/assign', 'ClassesController@assignSubject', [RoleCheckMiddleware::admin()]);
$router->delete('/admin/classes/{classId}/subjects/{subjectId}', 'ClassesController@unassignSubject', [RoleCheckMiddleware::admin()]);
$router->post('/admin/classes/{classId}/subjects/{subjectId}/teacher', 'ClassesController@assignTeacher', [RoleCheckMiddleware::admin()]);
$router->get('/admin/classes/{id}/timetable', 'ClassesController@timetable', [RoleCheckMiddleware::admin()]);
$router->resource('/admin/subjects', 'SubjectsController', [RoleCheckMiddleware::admin()]);
$router->resource('/admin/attendance', 'AttendanceController', [RoleCheckMiddleware::adminOrTeacher()]);
$router->get('/admin/attendance/mark', 'AttendanceController@mark', [RoleCheckMiddleware::adminOrTeacher()]);
$router->post('/admin/attendance/save', 'AttendanceController@save', [RoleCheckMiddleware::adminOrTeacher()]);
$router->get('/admin/attendance/reports', 'AttendanceController@reports', [RoleCheckMiddleware::adminOrTeacher()]);
$router->get('/admin/attendance/analytics', 'AttendanceController@analytics', [RoleCheckMiddleware::adminOrTeacher()]);
$router->post('/admin/attendance/bulk-upload', 'AttendanceController@bulkUpload', [RoleCheckMiddleware::adminOrTeacher()]);
$router->get('/admin/attendance/export', 'AttendanceController@export', [RoleCheckMiddleware::adminOrTeacher()]);
$router->get('/admin/attendance/get-students', 'AttendanceController@getStudents', [RoleCheckMiddleware::adminOrTeacher()]);
$router->get('/admin/attendance/download-template', 'AttendanceController@downloadTemplate', [RoleCheckMiddleware::adminOrTeacher()]);
$router->resource('/admin/exams', 'ExamsController', [RoleCheckMiddleware::adminOrTeacher()]);
$router->resource('/admin/fees', 'FeesController', [RoleCheckMiddleware::adminOrCashier()]);
$router->resource('/admin/events', 'AdminEventsController', [RoleCheckMiddleware::admin()]);
$router->get('/admin/events/calendar', 'AdminEventsController@calendar', [RoleCheckMiddleware::admin()]);
$router->get('/admin/events/ajax-calendar', 'AdminEventsController@ajaxCalendar', [RoleCheckMiddleware::admin()]);
$router->get('/admin/events/ajax-list', 'AdminEventsController@ajaxList', [RoleCheckMiddleware::admin()]);
$router->resource('/admin/announcements', 'AdminAnnouncementsController', [RoleCheckMiddleware::admin()]);
$router->resource('/admin/gallery', 'GalleryController', [RoleCheckMiddleware::admin()]);

// Admin Reports routes
$router->get('/admin/reports', 'AdminReportsController@index', [RoleCheckMiddleware::admin()]);
$router->get('/admin/reports/academic', 'AdminReportsController@academic', [RoleCheckMiddleware::admin()]);
$router->get('/admin/reports/financial', 'AdminReportsController@financial', [RoleCheckMiddleware::admin()]);
$router->get('/admin/reports/attendance', 'AdminReportsController@attendance', [RoleCheckMiddleware::admin()]);
$router->get('/admin/reports/custom', 'AdminReportsController@custom', [RoleCheckMiddleware::admin()]);
$router->post('/admin/reports/export-pdf', 'AdminReportsController@exportPdf', [RoleCheckMiddleware::admin()]);
$router->post('/admin/reports/export-excel', 'AdminReportsController@exportExcel', [RoleCheckMiddleware::admin()]);
$router->post('/admin/reports/get-data', 'AdminReportsController@getData', [RoleCheckMiddleware::admin()]);
$router->post('/admin/reports/get-chart-data', 'AdminReportsController@getChartData', [RoleCheckMiddleware::admin()]);

$router->get('/admin/settings', 'SettingsController@index', [RoleCheckMiddleware::admin()]);
$router->get('/admin/settings/getUsers', 'SettingsController@getUsers', [RoleCheckMiddleware::admin()]);
$router->post('/admin/settings/createUser', 'SettingsController@createUser', [RoleCheckMiddleware::admin()]);
$router->post('/admin/settings/updateUser', 'SettingsController@updateUser', [RoleCheckMiddleware::admin()]);
$router->post('/admin/settings/deleteUser', 'SettingsController@deleteUser', [RoleCheckMiddleware::admin()]);
$router->get('/admin/settings/getRoles', 'SettingsController@getRoles', [RoleCheckMiddleware::admin()]);
$router->get('/admin/settings/getPermissions', 'SettingsController@getPermissions', [RoleCheckMiddleware::admin()]);
$router->post('/admin/settings/updatePermissions', 'SettingsController@updatePermissions', [RoleCheckMiddleware::admin()]);
$router->post('/admin/settings/updateSchoolSettings', 'SettingsController@updateSchoolSettings', [RoleCheckMiddleware::admin()]);
$router->get('/admin/settings/getHomepageContent', 'SettingsController@getHomepageContent', [RoleCheckMiddleware::admin()]);
$router->post('/admin/settings/updateHomepageContent', 'SettingsController@updateHomepageContent', [RoleCheckMiddleware::admin()]);
$router->get('/admin/settings/getApiSettings', 'SettingsController@getApiSettings', [RoleCheckMiddleware::admin()]);
$router->post('/admin/settings/updateApiSettings', 'SettingsController@updateApiSettings', [RoleCheckMiddleware::admin()]);
$router->post('/admin/settings/createBackup', 'SettingsController@createBackup', [RoleCheckMiddleware::admin()]);
$router->get('/admin/settings/getBackups', 'SettingsController@getBackups', [RoleCheckMiddleware::admin()]);
$router->post('/admin/settings/restoreBackup', 'SettingsController@restoreBackup', [RoleCheckMiddleware::admin()]);

// Teacher routes
$router->get('/teacher/dashboard', 'TeacherController@dashboard', [RoleCheckMiddleware::teacher()]);
$router->get('/teacher/dashboard/getDashboardData', 'TeacherController@getDashboardData', [RoleCheckMiddleware::teacher()]);
$router->get('/teacher/classes', 'TeacherController@classes', [RoleCheckMiddleware::teacher()]);
$router->get('/teacher/attendance', 'TeacherController@attendance', [RoleCheckMiddleware::teacher()]);
$router->get('/teacher/exams', 'TeacherController@exams', [RoleCheckMiddleware::teacher()]);
$router->get('/teacher/profile', 'TeacherController@profile', [RoleCheckMiddleware::teacher()]);
$router->get('/teacher/profile/get-stats', 'TeacherController@getProfileStats', [RoleCheckMiddleware::teacher()]);
$router->get('/teacher/profile/get-assignments', 'TeacherController@getAssignments', [RoleCheckMiddleware::teacher()]);
$router->get('/teacher/profile/get-timetable', 'TeacherController@getTimetable', [RoleCheckMiddleware::teacher()]);
$router->get('/teacher/profile/get-attendance-records', 'TeacherController@getAttendanceRecords', [RoleCheckMiddleware::teacher()]);
$router->get('/teacher/profile/get-performance-data', 'TeacherController@getPerformanceData', [RoleCheckMiddleware::teacher()]);
$router->get('/teacher/profile/edit', 'TeacherController@editProfile', [RoleCheckMiddleware::teacher()]);
$router->post('/teacher/profile/update', 'TeacherController@updateProfile', [RoleCheckMiddleware::teacher()]);
$router->post('/teacher/profile/change-password', 'TeacherController@changePassword', [RoleCheckMiddleware::teacher()]);

// Student routes
$router->get('/student/dashboard', 'StudentController@dashboard', [RoleCheckMiddleware::student()]);
$router->get('/student/dashboard/getAttendance', 'StudentController@getAttendance', [RoleCheckMiddleware::student()]);
$router->get('/student/dashboard/getExamResults', 'StudentController@getExamResults', [RoleCheckMiddleware::student()]);
$router->get('/student/dashboard/getFeeStatus', 'StudentController@getFeeStatus', [RoleCheckMiddleware::student()]);
$router->get('/student/dashboard/getNotifications', 'StudentController@getNotifications', [RoleCheckMiddleware::student()]);
$router->get('/student/dashboard/getUpcomingEvents', 'StudentController@getUpcomingEvents', [RoleCheckMiddleware::student()]);
$router->get('/student/attendance', 'StudentController@attendance', [RoleCheckMiddleware::student()]);
$router->post('/student/attendance/getAttendanceHistory', 'StudentController@getAttendanceHistory', [RoleCheckMiddleware::student()]);
$router->post('/student/attendance/getMonthlyCalendar', 'StudentController@getMonthlyCalendar', [RoleCheckMiddleware::student()]);
$router->get('/student/results', 'StudentController@results', [RoleCheckMiddleware::student()]);
$router->get('/student/results/getDetailedResults', 'StudentController@getDetailedResults', [RoleCheckMiddleware::student()]);
$router->get('/student/results/getPerformanceData', 'StudentController@getPerformanceData', [RoleCheckMiddleware::student()]);
$router->get('/student/results/getExamSchedules', 'StudentController@getExamSchedules', [RoleCheckMiddleware::student()]);
$router->get('/student/results/downloadReportCard', 'StudentController@downloadReportCard', [RoleCheckMiddleware::student()]);
$router->get('/student/fees', 'StudentController@fees', [RoleCheckMiddleware::student()]);
$router->get('/student/fees/getFeeDetails', 'StudentController@getFeeDetails', [RoleCheckMiddleware::student()]);
$router->get('/student/fees/getPaymentHistory', 'StudentController@getPaymentHistory', [RoleCheckMiddleware::student()]);
$router->get('/student/fees/viewReceipt', 'StudentController@viewReceipt', [RoleCheckMiddleware::student()]);
$router->get('/student/fees/downloadReceipt', 'StudentController@downloadReceipt', [RoleCheckMiddleware::student()]);
$router->get('/student/profile', 'StudentController@profile', [RoleCheckMiddleware::student()]);
$router->get('/student/profile/edit', 'StudentController@editProfile', [RoleCheckMiddleware::student()]);
$router->post('/student/profile/update', 'StudentController@updateProfile', [RoleCheckMiddleware::student()]);
$router->get('/student/profile/change_password', 'StudentController@changePassword', [RoleCheckMiddleware::student()]);
$router->post('/student/profile/update_password', 'StudentController@updatePassword', [RoleCheckMiddleware::student()]);

// Cashier routes
$router->get('/cashier/dashboard', 'CashierController@dashboard', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/dashboard/getDashboardStats', 'CashierController@getDashboardStats', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/dashboard/getFeeCollectionTrends', 'CashierController@getFeeCollectionTrends', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/dashboard/getOverduePayments', 'CashierController@getOverduePayments', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees', 'CashierFeesController@index', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees/collect', 'CashierFeesController@collect', [RoleCheckMiddleware::cashier()]);
$router->post('/cashier/fees/process-payment', 'CashierFeesController@processPayment', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees/receipt/{id}', 'CashierFeesController@receipt', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees/generate-receipt/{id}', 'CashierFeesController@generateReceipt', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees/outstanding', 'CashierFeesController@outstanding', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees/reports', 'CashierFeesController@reports', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees/ajax-search-student', 'CashierFeesController@ajaxSearchStudent', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees/ajax-get-students', 'CashierFeesController@ajaxGetStudents', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees/ajax-get-student-fees', 'CashierFeesController@ajaxGetStudentFees', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees/ajax-get-outstanding', 'CashierFeesController@ajaxGetOutstanding', [RoleCheckMiddleware::cashier()]);
$router->post('/cashier/fees/send-reminder', 'CashierFeesController@sendReminder', [RoleCheckMiddleware::cashier()]);
$router->post('/cashier/fees/send-reminders', 'CashierFeesController@sendReminders', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees/analytics', 'CashierFeesController@analytics', [RoleCheckMiddleware::cashier()]);
$router->post('/cashier/fees/bulk-update', 'CashierFeesController@bulkUpdate', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/fees/export-outstanding', 'CashierFeesController@exportOutstanding', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/expenses', 'CashierController@expenses', [RoleCheckMiddleware::cashier()]);

// Cashier Reports routes
$router->get('/cashier/reports', 'CashierReportsController@index', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/reports/collection-summary', 'CashierReportsController@collectionSummary', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/reports/expenses', 'CashierReportsController@expenseReports', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/reports/analytics', 'CashierReportsController@analytics', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/reports/export-pdf', 'CashierReportsController@exportPdf', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/reports/export-excel', 'CashierReportsController@exportExcel', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/reports/ajax-collection-data', 'CashierReportsController@ajaxGetCollectionData', [RoleCheckMiddleware::cashier()]);
$router->get('/cashier/reports/ajax-expense-data', 'CashierReportsController@ajaxGetExpenseData', [RoleCheckMiddleware::cashier()]);

// Parent routes
$router->get('/parent/dashboard', 'ParentController@dashboard', [RoleCheckMiddleware::parent()]);
$router->get('/parent/children', 'ParentController@children', [RoleCheckMiddleware::parent()]);
$router->get('/parent/attendance', 'ParentController@attendance', [RoleCheckMiddleware::parent()]);
$router->get('/parent/results', 'ParentController@results', [RoleCheckMiddleware::parent()]);
$router->get('/parent/fees', 'ParentController@fees', [RoleCheckMiddleware::parent()]);
$router->get('/parent/events', 'ParentController@events', [RoleCheckMiddleware::parent()]);
$router->get('/parent/profile', 'ParentController@profile', [RoleCheckMiddleware::parent()]);

// Parent AJAX routes
$router->get('/parent/dashboard/getChildDetails', 'ParentController@getChildDetails', [RoleCheckMiddleware::parent()]);
$router->get('/parent/dashboard/getChildAttendance', 'ParentController@getChildAttendance', [RoleCheckMiddleware::parent()]);
$router->get('/parent/dashboard/getChildResults', 'ParentController@getChildResults', [RoleCheckMiddleware::parent()]);
$router->get('/parent/dashboard/getChildFees', 'ParentController@getChildFees', [RoleCheckMiddleware::parent()]);
$router->get('/parent/dashboard/getNotifications', 'ParentController@getNotifications', [RoleCheckMiddleware::parent()]);
$router->post('/parent/profile/updateProfile', 'ParentController@updateProfile', [RoleCheckMiddleware::parent()]);
$router->post('/parent/profile/changePassword', 'ParentController@changePassword', [RoleCheckMiddleware::parent()]);
$router->get('/parent/children/export', 'ParentController@exportChildReport', [RoleCheckMiddleware::parent()]);

// Parent Fee routes
$router->get('/parent/fees/download', 'ParentController@downloadFeeStatement', [RoleCheckMiddleware::parent()]);
$router->get('/parent/fees/export', 'ParentController@exportFeeDetails', [RoleCheckMiddleware::parent()]);
$router->get('/parent/fees/receipt', 'ParentController@viewReceipt', [RoleCheckMiddleware::parent()]);

// API routes
$router->get('/parent/profile', 'ParentController@profile', [RoleCheckMiddleware::parent()]);

// API routes
$router->get('/api/v1/students', 'ApiController@getStudents', ['AuthMiddleware']);
$router->post('/api/v1/students', 'ApiController@createStudent', [RoleCheckMiddleware::admin()]);
$router->get('/api/v1/attendance', 'ApiController@getAttendance', ['AuthMiddleware']);
$router->post('/api/v1/attendance', 'ApiController@markAttendance', [RoleCheckMiddleware::adminOrTeacher()]);