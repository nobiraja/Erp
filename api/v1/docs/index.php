<?php
/**
 * API Documentation
 * Provides comprehensive documentation for all API endpoints
 */

require_once '../../../controllers/ApiController.php';

class ApiDocsController extends ApiController {
    /**
     * Handle API documentation requests
     */
    public function handleRequest() {
        $format = $_GET['format'] ?? 'html';

        switch ($format) {
            case 'json':
                $this->outputJsonDocs();
                break;
            case 'html':
            default:
                $this->outputHtmlDocs();
                break;
        }
    }

    /**
     * Output JSON documentation
     */
    private function outputJsonDocs() {
        $docs = $this->getApiDocumentation();
        header('Content-Type: application/json');
        echo json_encode($docs, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Output HTML documentation
     */
    private function outputHtmlDocs() {
        $docs = $this->getApiDocumentation();
        ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System API Documentation</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1, h2, h3 { color: #333; }
        .endpoint { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #007bff; border-radius: 4px; }
        .method { font-weight: bold; color: #007bff; }
        .method.GET { color: #28a745; }
        .method.POST { color: #007bff; }
        .method.PUT { color: #ffc107; }
        .method.DELETE { color: #dc3545; }
        .parameters { background: #e9ecef; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .response { background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .error { background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 4px; }
        code { background: #f1f3f4; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto; }
        .auth-notice { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 4px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏫 School Management System API Documentation</h1>
        <p>Version 1.0.0 | Base URL: <code>/api/v1/</code></p>

        <div class="auth-notice">
            <h3>🔐 Authentication</h3>
            <p>All API requests require authentication via Bearer token in the Authorization header:</p>
            <pre><code>Authorization: Bearer your_api_token_here</code></pre>
            <p>Get your token by logging in via <code>POST /api/v1/auth</code></p>
        </div>

        <?php foreach ($docs['endpoints'] as $category => $endpoints): ?>
        <h2><?php echo htmlspecialchars($category); ?></h2>
        <?php foreach ($endpoints as $endpoint): ?>
        <div class="endpoint">
            <h3>
                <span class="method <?php echo $endpoint['method']; ?>">
                    <?php echo $endpoint['method']; ?>
                </span>
                <code><?php echo htmlspecialchars($endpoint['path']); ?></code>
            </h3>
            <p><?php echo htmlspecialchars($endpoint['description']); ?></p>

            <?php if (!empty($endpoint['parameters'])): ?>
            <h4>Parameters:</h4>
            <div class="parameters">
                <ul>
                <?php foreach ($endpoint['parameters'] as $param): ?>
                    <li><code><?php echo $param['name']; ?></code>
                        <?php if ($param['required']): ?><strong>(required)</strong><?php endif; ?>:
                        <?php echo htmlspecialchars($param['description']); ?>
                        <?php if (isset($param['type'])): ?> (<?php echo $param['type']; ?>)<?php endif; ?>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <h4>Response:</h4>
            <div class="response">
                <p><strong>Success (200):</strong></p>
                <pre><code><?php echo htmlspecialchars(json_encode($endpoint['response_example'], JSON_PRETTY_PRINT)); ?></code></pre>
            </div>

            <?php if (!empty($endpoint['errors'])): ?>
            <h4>Possible Errors:</h4>
            <?php foreach ($endpoint['errors'] as $error): ?>
            <div class="error">
                <strong><?php echo $error['code']; ?>:</strong> <?php echo htmlspecialchars($error['message']); ?>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endforeach; ?>

        <h2>📊 Response Format</h2>
        <p>All API responses follow this standard format:</p>
        <pre><code>{
    "success": true|false,
    "message": "Response message",
    "data": { ... } // Present only on success
}</code></pre>

        <h2>📋 Status Codes</h2>
        <ul>
            <li><strong>200:</strong> Success</li>
            <li><strong>201:</strong> Created</li>
            <li><strong>400:</strong> Bad Request</li>
            <li><strong>401:</strong> Unauthorized</li>
            <li><strong>403:</strong> Forbidden</li>
            <li><strong>404:</strong> Not Found</li>
            <li><strong>429:</strong> Too Many Requests (Rate Limited)</li>
            <li><strong>500:</strong> Internal Server Error</li>
        </ul>

        <h2>🔒 Rate Limiting</h2>
        <p>API requests are rate limited to 1000 requests per hour per IP address.</p>

        <h2>📞 Support</h2>
        <p>For API support, please refer to the main system documentation or contact the development team.</p>
    </div>
</body>
</html>
        <?php
        exit;
    }

    /**
     * Get API documentation data
     */
    private function getApiDocumentation() {
        return [
            'version' => '1.0.0',
            'base_url' => '/api/v1/',
            'authentication' => 'Bearer token required',
            'rate_limit' => '1000 requests per hour',
            'endpoints' => [
                'Authentication' => [
                    [
                        'method' => 'POST',
                        'path' => '/auth',
                        'description' => 'Authenticate user and get API token',
                        'parameters' => [
                            ['name' => 'username', 'type' => 'string', 'required' => true, 'description' => 'User username or email'],
                            ['name' => 'password', 'type' => 'string', 'required' => true, 'description' => 'User password']
                        ],
                        'response_example' => [
                            'success' => true,
                            'message' => 'Login successful',
                            'data' => [
                                'user' => [
                                    'id' => 1,
                                    'username' => 'admin',
                                    'email' => 'admin@school.com',
                                    'role' => 'admin'
                                ],
                                'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...',
                                'expires_in' => 86400
                            ]
                        ],
                        'errors' => [
                            ['code' => 401, 'message' => 'Invalid credentials']
                        ]
                    ],
                    [
                        'method' => 'DELETE',
                        'path' => '/auth',
                        'description' => 'Logout and revoke API token',
                        'parameters' => [],
                        'response_example' => [
                            'success' => true,
                            'message' => 'Logged out successfully'
                        ],
                        'errors' => [
                            ['code' => 401, 'message' => 'Authentication required']
                        ]
                    ]
                ],
                'Students' => [
                    [
                        'method' => 'GET',
                        'path' => '/students',
                        'description' => 'Get list of students with pagination and filtering',
                        'parameters' => [
                            ['name' => 'page', 'type' => 'integer', 'required' => false, 'description' => 'Page number (default: 1)'],
                            ['name' => 'limit', 'type' => 'integer', 'required' => false, 'description' => 'Items per page (default: 20)'],
                            ['name' => 'class_id', 'type' => 'integer', 'required' => false, 'description' => 'Filter by class ID'],
                            ['name' => 'section', 'type' => 'string', 'required' => false, 'description' => 'Filter by section'],
                            ['name' => 'search', 'type' => 'string', 'required' => false, 'description' => 'Search by name or scholar number']
                        ],
                        'response_example' => [
                            'success' => true,
                            'data' => [
                                'data' => [
                                    [
                                        'id' => 1,
                                        'scholar_number' => '2024001',
                                        'first_name' => 'John',
                                        'last_name' => 'Doe',
                                        'class_name' => '10th',
                                        'section' => 'A',
                                        'attendance_percentage' => 95.5
                                    ]
                                ],
                                'pagination' => [
                                    'page' => 1,
                                    'limit' => 20,
                                    'total' => 150,
                                    'pages' => 8
                                ]
                            ]
                        ],
                        'errors' => [
                            ['code' => 401, 'message' => 'Authentication required'],
                            ['code' => 403, 'message' => 'Access denied']
                        ]
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/students/{id}',
                        'description' => 'Get detailed information about a specific student',
                        'parameters' => [
                            ['name' => 'id', 'type' => 'integer', 'required' => true, 'description' => 'Student ID']
                        ],
                        'response_example' => [
                            'success' => true,
                            'data' => [
                                'id' => 1,
                                'scholar_number' => '2024001',
                                'first_name' => 'John',
                                'last_name' => 'Doe',
                                'class_name' => '10th',
                                'section' => 'A',
                                'attendance_percentage' => 95.5,
                                'fees_status' => [
                                    'total' => 50000,
                                    'paid' => 45000,
                                    'pending' => 5000,
                                    'percentage' => 90
                                ]
                            ]
                        ],
                        'errors' => [
                            ['code' => 404, 'message' => 'Student not found']
                        ]
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/students',
                        'description' => 'Create a new student record',
                        'parameters' => [
                            ['name' => 'scholar_number', 'type' => 'string', 'required' => true, 'description' => 'Unique scholar number'],
                            ['name' => 'admission_number', 'type' => 'string', 'required' => true, 'description' => 'Unique admission number'],
                            ['name' => 'first_name', 'type' => 'string', 'required' => true, 'description' => 'Student first name'],
                            ['name' => 'last_name', 'type' => 'string', 'required' => true, 'description' => 'Student last name'],
                            ['name' => 'class_id', 'type' => 'integer', 'required' => true, 'description' => 'Class ID'],
                            ['name' => 'section', 'type' => 'string', 'required' => true, 'description' => 'Class section'],
                            ['name' => 'dob', 'type' => 'date', 'required' => true, 'description' => 'Date of birth'],
                            ['name' => 'gender', 'type' => 'string', 'required' => true, 'description' => 'Gender (male/female/other)']
                        ],
                        'response_example' => [
                            'success' => true,
                            'message' => 'Student created successfully',
                            'data' => [
                                'id' => 151,
                                'scholar_number' => '2024151',
                                'first_name' => 'Jane',
                                'last_name' => 'Smith'
                            ]
                        ],
                        'errors' => [
                            ['code' => 400, 'message' => 'Scholar number already exists']
                        ]
                    ]
                ],
                'Fees' => [
                    [
                        'method' => 'GET',
                        'path' => '/fees?action=list',
                        'description' => 'Get fees list with filtering',
                        'parameters' => [
                            ['name' => 'student_id', 'type' => 'integer', 'required' => false, 'description' => 'Filter by student ID'],
                            ['name' => 'class_id', 'type' => 'integer', 'required' => false, 'description' => 'Filter by class ID'],
                            ['name' => 'academic_year', 'type' => 'string', 'required' => false, 'description' => 'Filter by academic year'],
                            ['name' => 'is_paid', 'type' => 'boolean', 'required' => false, 'description' => 'Filter by payment status']
                        ],
                        'response_example' => [
                            'success' => true,
                            'data' => [
                                'data' => [
                                    [
                                        'id' => 1,
                                        'student_id' => 1,
                                        'fee_type' => 'Tuition Fee',
                                        'amount' => 12000,
                                        'due_date' => '2024-04-01',
                                        'is_paid' => true
                                    ]
                                ],
                                'pagination' => [
                                    'page' => 1,
                                    'limit' => 20,
                                    'total' => 500,
                                    'pages' => 25
                                ]
                            ]
                        ]
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/fees?action=collect',
                        'description' => 'Collect fee payment',
                        'parameters' => [
                            ['name' => 'fee_id', 'type' => 'integer', 'required' => true, 'description' => 'Fee ID to pay'],
                            ['name' => 'amount_paid', 'type' => 'decimal', 'required' => true, 'description' => 'Amount being paid'],
                            ['name' => 'payment_mode', 'type' => 'string', 'required' => true, 'description' => 'Payment mode (cash/online/cheque/upi)'],
                            ['name' => 'payment_date', 'type' => 'date', 'required' => false, 'description' => 'Payment date (default: today)']
                        ],
                        'response_example' => [
                            'success' => true,
                            'message' => 'Payment collected successfully',
                            'data' => [
                                'payment_id' => 1234,
                                'receipt_number' => 'RCP20241116001',
                                'amount_paid' => 12000,
                                'payment_mode' => 'cash'
                            ]
                        ]
                    ]
                ],
                'Exams' => [
                    [
                        'method' => 'GET',
                        'path' => '/exams?action=results',
                        'description' => 'Get exam results for a specific exam',
                        'parameters' => [
                            ['name' => 'exam_id', 'type' => 'integer', 'required' => true, 'description' => 'Exam ID'],
                            ['name' => 'student_id', 'type' => 'integer', 'required' => false, 'description' => 'Filter by student ID']
                        ],
                        'response_example' => [
                            'success' => true,
                            'data' => [
                                'exam_id' => 1,
                                'results' => [
                                    [
                                        'student_id' => 1,
                                        'subject_name' => 'Mathematics',
                                        'marks_obtained' => 85,
                                        'max_marks' => 100,
                                        'percentage' => 85,
                                        'grade' => 'A'
                                    ]
                                ],
                                'statistics' => [
                                    'total_students' => 45,
                                    'average_percentage' => 78.5,
                                    'highest_score' => 98,
                                    'lowest_score' => 45,
                                    'pass_percentage' => 87.5
                                ]
                            ]
                        ]
                    ],
                    [
                        'method' => 'POST',
                        'path' => '/exams?action=result',
                        'description' => 'Enter exam result for a student',
                        'parameters' => [
                            ['name' => 'exam_id', 'type' => 'integer', 'required' => true, 'description' => 'Exam ID'],
                            ['name' => 'student_id', 'type' => 'integer', 'required' => true, 'description' => 'Student ID'],
                            ['name' => 'subject_id', 'type' => 'integer', 'required' => true, 'description' => 'Subject ID'],
                            ['name' => 'marks_obtained', 'type' => 'decimal', 'required' => true, 'description' => 'Marks obtained'],
                            ['name' => 'max_marks', 'type' => 'decimal', 'required' => true, 'description' => 'Maximum marks']
                        ],
                        'response_example' => [
                            'success' => true,
                            'message' => 'Result entered successfully',
                            'data' => [
                                'result_id' => 123,
                                'marks_obtained' => 85,
                                'percentage' => 85,
                                'grade' => 'A'
                            ]
                        ]
                    ]
                ],
                'Reports' => [
                    [
                        'method' => 'GET',
                        'path' => '/reports?action=list',
                        'description' => 'Get list of available reports',
                        'parameters' => [],
                        'response_example' => [
                            'success' => true,
                            'data' => [
                                'reports' => [
                                    [
                                        'id' => 'student_list',
                                        'name' => 'Student List Report',
                                        'description' => 'Complete list of students with details',
                                        'category' => 'academic',
                                        'formats' => ['json', 'csv', 'pdf']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    [
                        'method' => 'GET',
                        'path' => '/reports?action=generate',
                        'description' => 'Generate a specific report',
                        'parameters' => [
                            ['name' => 'report_id', 'type' => 'string', 'required' => true, 'description' => 'Report ID'],
                            ['name' => 'format', 'type' => 'string', 'required' => false, 'description' => 'Export format (json/csv/pdf)'],
                            ['name' => 'parameters', 'type' => 'object', 'required' => false, 'description' => 'Report parameters']
                        ],
                        'response_example' => [
                            'success' => true,
                            'data' => [
                                'report_type' => 'student_list',
                                'generated_at' => '2024-11-16 12:00:00',
                                'total_records' => 150,
                                'data' => [
                                    // Report data here
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}

// Initialize and handle request
$controller = new ApiDocsController();
$controller->handleRequest();