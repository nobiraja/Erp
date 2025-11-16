<?php
/**
 * File Upload Configuration
 * File upload settings, size limits, and allowed file types
 */

return [
    // General Upload Settings
    'enabled' => getenv('UPLOAD_ENABLED') ? filter_var(getenv('UPLOAD_ENABLED'), FILTER_VALIDATE_BOOLEAN) : true,
    'max_file_uploads' => getenv('UPLOAD_MAX_FILES') ?: ini_get('max_file_uploads') ?: 20,
    'upload_path' => getenv('UPLOAD_PATH') ?: __DIR__ . '/../uploads',

    // File Size Limits
    'max_file_size' => getenv('UPLOAD_MAX_FILE_SIZE') ?: 10 * 1024 * 1024, // 10MB in bytes
    'max_post_size' => getenv('UPLOAD_MAX_POST_SIZE') ?: 50 * 1024 * 1024, // 50MB in bytes
    'memory_limit' => getenv('UPLOAD_MEMORY_LIMIT') ?: 128 * 1024 * 1024, // 128MB in bytes

    // PHP Upload Settings (these override php.ini if set)
    'php_settings' => [
        'upload_max_filesize' => getenv('PHP_UPLOAD_MAX_FILESIZE') ?: '10M',
        'post_max_size' => getenv('PHP_POST_MAX_SIZE') ?: '50M',
        'max_execution_time' => getenv('PHP_MAX_EXECUTION_TIME') ?: 300, // 5 minutes
        'memory_limit' => getenv('PHP_MEMORY_LIMIT') ?: '128M'
    ],

    // Allowed File Types by Category
    'allowed_types' => [
        'images' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'],
            'mime_types' => ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'],
            'max_size' => getenv('UPLOAD_MAX_IMAGE_SIZE') ?: 5 * 1024 * 1024 // 5MB
        ],
        'documents' => [
            'extensions' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'rtf'],
            'mime_types' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'application/rtf'
            ],
            'max_size' => getenv('UPLOAD_MAX_DOCUMENT_SIZE') ?: 10 * 1024 * 1024 // 10MB
        ],
        'archives' => [
            'extensions' => ['zip', 'rar', '7z', 'tar', 'gz'],
            'mime_types' => [
                'application/zip',
                'application/x-rar-compressed',
                'application/x-7z-compressed',
                'application/x-tar',
                'application/gzip'
            ],
            'max_size' => getenv('UPLOAD_MAX_ARCHIVE_SIZE') ?: 50 * 1024 * 1024 // 50MB
        ],
        'videos' => [
            'extensions' => ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm'],
            'mime_types' => [
                'video/mp4',
                'video/avi',
                'video/quicktime',
                'video/x-ms-wmv',
                'video/x-flv',
                'video/webm'
            ],
            'max_size' => getenv('UPLOAD_MAX_VIDEO_SIZE') ?: 100 * 1024 * 1024 // 100MB
        ],
        'audio' => [
            'extensions' => ['mp3', 'wav', 'ogg', 'aac', 'wma'],
            'mime_types' => [
                'audio/mpeg',
                'audio/wav',
                'audio/ogg',
                'audio/aac',
                'audio/x-ms-wma'
            ],
            'max_size' => getenv('UPLOAD_MAX_AUDIO_SIZE') ?: 20 * 1024 * 1024 // 20MB
        ]
    ],

    // Image-specific Settings
    'image_settings' => [
        'resize_enabled' => getenv('IMAGE_RESIZE_ENABLED') ? filter_var(getenv('IMAGE_RESIZE_ENABLED'), FILTER_VALIDATE_BOOLEAN) : true,
        'max_width' => getenv('IMAGE_MAX_WIDTH') ?: 1920,
        'max_height' => getenv('IMAGE_MAX_HEIGHT') ?: 1080,
        'quality' => getenv('IMAGE_QUALITY') ?: 85, // JPEG quality 0-100
        'create_thumbnails' => getenv('IMAGE_CREATE_THUMBNAILS') ? filter_var(getenv('IMAGE_CREATE_THUMBNAILS'), FILTER_VALIDATE_BOOLEAN) : true,
        'thumbnail_sizes' => [
            'small' => ['width' => 150, 'height' => 150],
            'medium' => ['width' => 300, 'height' => 300],
            'large' => ['width' => 600, 'height' => 600]
        ]
    ],

    // Security Settings
    'security' => [
        'scan_for_viruses' => getenv('UPLOAD_VIRUS_SCAN') ? filter_var(getenv('UPLOAD_VIRUS_SCAN'), FILTER_VALIDATE_BOOLEAN) : false,
        'check_mime_type' => getenv('UPLOAD_CHECK_MIME') ? filter_var(getenv('UPLOAD_CHECK_MIME'), FILTER_VALIDATE_BOOLEAN) : true,
        'randomize_filename' => getenv('UPLOAD_RANDOMIZE_FILENAME') ? filter_var(getenv('UPLOAD_RANDOMIZE_FILENAME'), FILTER_VALIDATE_BOOLEAN) : true,
        'allowed_chars' => getenv('UPLOAD_ALLOWED_CHARS') ?: 'a-zA-Z0-9._-',
        'block_executables' => getenv('UPLOAD_BLOCK_EXECUTABLES') ? filter_var(getenv('UPLOAD_BLOCK_EXECUTABLES'), FILTER_VALIDATE_BOOLEAN) : true,
        'executable_extensions' => ['exe', 'bat', 'cmd', 'com', 'scr', 'pif', 'jar', 'msi', 'deb', 'rpm']
    ],

    // Storage Settings
    'storage' => [
        'driver' => getenv('UPLOAD_STORAGE_DRIVER') ?: 'local', // local, s3, ftp
        'permissions' => getenv('UPLOAD_FILE_PERMISSIONS') ?: 0644,
        'directory_permissions' => getenv('UPLOAD_DIR_PERMISSIONS') ?: 0755,
        'organize_by_date' => getenv('UPLOAD_ORGANIZE_BY_DATE') ? filter_var(getenv('UPLOAD_ORGANIZE_BY_DATE'), FILTER_VALIDATE_BOOLEAN) : true,
        'date_format' => getenv('UPLOAD_DATE_FORMAT') ?: 'Y/m/d'
    ],

    // Upload Directories
    'directories' => [
        'students' => getenv('UPLOAD_DIR_STUDENTS') ?: 'students',
        'teachers' => getenv('UPLOAD_DIR_TEACHERS') ?: 'teachers',
        'documents' => getenv('UPLOAD_DIR_DOCUMENTS') ?: 'documents',
        'gallery' => getenv('UPLOAD_DIR_GALLERY') ?: 'gallery',
        'temp' => getenv('UPLOAD_DIR_TEMP') ?: 'temp',
        'exports' => getenv('UPLOAD_DIR_EXPORTS') ?: 'exports'
    ],

    // Chunked Upload Settings (for large files)
    'chunked_upload' => [
        'enabled' => getenv('UPLOAD_CHUNKED_ENABLED') ? filter_var(getenv('UPLOAD_CHUNKED_ENABLED'), FILTER_VALIDATE_BOOLEAN) : false,
        'chunk_size' => getenv('UPLOAD_CHUNK_SIZE') ?: 1 * 1024 * 1024, // 1MB chunks
        'cleanup_temp_files' => getenv('UPLOAD_CLEANUP_TEMP') ? filter_var(getenv('UPLOAD_CLEANUP_TEMP'), FILTER_VALIDATE_BOOLEAN) : true,
        'temp_lifetime' => getenv('UPLOAD_TEMP_LIFETIME') ?: 3600 // 1 hour
    ],

    // Error Messages
    'error_messages' => [
        'file_too_large' => 'File size exceeds the maximum allowed limit.',
        'invalid_type' => 'File type not allowed.',
        'upload_failed' => 'File upload failed.',
        'no_file' => 'No file was uploaded.',
        'partial_upload' => 'File was only partially uploaded.',
        'missing_temp_dir' => 'Temporary upload directory missing.',
        'write_failed' => 'Failed to write file to disk.',
        'extension_blocked' => 'File extension is blocked for security reasons.'
    ]
];