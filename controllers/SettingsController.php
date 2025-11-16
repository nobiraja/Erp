<?php
/**
 * Settings Controller
 * Handles admin settings management including user management, permissions, school info, etc.
 */

class SettingsController extends BaseController {

    /**
     * Constructor - Apply admin middleware
     */
    public function __construct() {
        parent::__construct();
        $this->middleware(RoleCheckMiddleware::admin());
    }

    /**
     * Display settings dashboard
     */
    public function index() {
        try {
            // Get settings data
            $settingsData = SettingsModel::getAllSettings();

            // Get school settings
            $schoolName = $this->getSetting('school_name', 'School Management System');
            $schoolLogo = $this->getSetting('school_logo', '/images/logo.png');

            // Prepare view data
            $data = [
                'title' => 'Settings - ' . $schoolName,
                'school_name' => $schoolName,
                'school_logo' => $schoolLogo,
                'settings_data' => $settingsData,
                'current_user' => $this->getUserData(),
                'current_year' => date('Y'),
                'current_month' => date('F Y')
            ];

            // Render settings view
            echo $this->view('admin.settings.index', $data);

        } catch (Exception $e) {
            // Log error and show error page
            error_log("Settings error: " . $e->getMessage());
            $this->error('Failed to load settings data', [], 500);
        }
    }

    /**
     * Get all users for user management
     */
    public function getUsers() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $users = SettingsModel::getAllUsers();
            $this->success($users);
        } catch (Exception $e) {
            $this->error('Failed to load users');
        }
    }

    /**
     * Create new user
     */
    public function createUser() {
        if (!$this->isAjax() || !$this->isMethod('POST')) {
            $this->error('Invalid request method');
        }

        $rules = [
            'username' => 'required|min:3|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|integer|exists:user_roles,id'
        ];

        $validated = $this->validate($rules);
        if (!$validated) {
            $this->error('Validation failed', $this->getValidationErrors());
        }

        try {
            $userId = SettingsModel::createUser($validated);
            $this->success(['user_id' => $userId], 'User created successfully');
        } catch (Exception $e) {
            $this->error('Failed to create user: ' . $e->getMessage());
        }
    }

    /**
     * Update user
     */
    public function updateUser() {
        if (!$this->isAjax() || !$this->isMethod('POST')) {
            $this->error('Invalid request method');
        }

        $userId = $this->input('user_id');
        if (!$userId) {
            $this->error('User ID is required');
        }

        $rules = [
            'username' => 'required|min:3|max:50|unique:users,username,' . $userId,
            'email' => 'required|email|unique:users,email,' . $userId,
            'role_id' => 'required|integer|exists:user_roles,id',
            'is_active' => 'boolean'
        ];

        $validated = $this->validate($rules);
        if (!$validated) {
            $this->error('Validation failed', $this->getValidationErrors());
        }

        try {
            SettingsModel::updateUser($userId, $validated);
            $this->success([], 'User updated successfully');
        } catch (Exception $e) {
            $this->error('Failed to update user: ' . $e->getMessage());
        }
    }

    /**
     * Delete user
     */
    public function deleteUser() {
        if (!$this->isAjax() || !$this->isMethod('POST')) {
            $this->error('Invalid request method');
        }

        $userId = $this->input('user_id');
        if (!$userId) {
            $this->error('User ID is required');
        }

        try {
            SettingsModel::deleteUser($userId);
            $this->success([], 'User deleted successfully');
        } catch (Exception $e) {
            $this->error('Failed to delete user: ' . $e->getMessage());
        }
    }

    /**
     * Get user roles
     */
    public function getRoles() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $roles = SettingsModel::getAllRoles();
            $this->success($roles);
        } catch (Exception $e) {
            $this->error('Failed to load roles');
        }
    }

    /**
     * Get permissions for a role
     */
    public function getPermissions() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        $roleId = $this->input('role_id');
        if (!$roleId) {
            $this->error('Role ID is required');
        }

        try {
            $permissions = SettingsModel::getPermissionsByRole($roleId);
            $this->success($permissions);
        } catch (Exception $e) {
            $this->error('Failed to load permissions');
        }
    }

    /**
     * Update permissions for a role
     */
    public function updatePermissions() {
        if (!$this->isAjax() || !$this->isMethod('POST')) {
            $this->error('Invalid request method');
        }

        $roleId = $this->input('role_id');
        $permissions = $this->input('permissions');

        if (!$roleId || !is_array($permissions)) {
            $this->error('Role ID and permissions are required');
        }

        try {
            SettingsModel::updatePermissions($roleId, $permissions);
            $this->success([], 'Permissions updated successfully');
        } catch (Exception $e) {
            $this->error('Failed to update permissions: ' . $e->getMessage());
        }
    }

    /**
     * Update school settings
     */
    public function updateSchoolSettings() {
        if (!$this->isAjax() || !$this->isMethod('POST')) {
            $this->error('Invalid request method');
        }

        $settings = $this->input('settings');
        if (!is_array($settings)) {
            $this->error('Settings data is required');
        }

        try {
            SettingsModel::updateSettings($settings);
            $this->success([], 'School settings updated successfully');
        } catch (Exception $e) {
            $this->error('Failed to update settings: ' . $e->getMessage());
        }
    }

    /**
     * Get homepage content
     */
    public function getHomepageContent() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $content = SettingsModel::getHomepageContent();
            $this->success($content);
        } catch (Exception $e) {
            $this->error('Failed to load homepage content');
        }
    }

    /**
     * Update homepage content
     */
    public function updateHomepageContent() {
        if (!$this->isAjax() || !$this->isMethod('POST')) {
            $this->error('Invalid request method');
        }

        $content = $this->input('content');
        if (!is_array($content)) {
            $this->error('Content data is required');
        }

        try {
            SettingsModel::updateHomepageContent($content);
            $this->success([], 'Homepage content updated successfully');
        } catch (Exception $e) {
            $this->error('Failed to update homepage content: ' . $e->getMessage());
        }
    }

    /**
     * Create database backup
     */
    public function createBackup() {
        if (!$this->isAjax() || !$this->isMethod('POST')) {
            $this->error('Invalid request method');
        }

        try {
            $backupPath = SettingsModel::createBackup();
            $this->success(['backup_path' => $backupPath], 'Backup created successfully');
        } catch (Exception $e) {
            $this->error('Failed to create backup: ' . $e->getMessage());
        }
    }

    /**
     * Get backup list
     */
    public function getBackups() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $backups = SettingsModel::getBackupList();
            $this->success($backups);
        } catch (Exception $e) {
            $this->error('Failed to load backups');
        }
    }

    /**
     * Restore from backup
     */
    public function restoreBackup() {
        if (!$this->isAjax() || !$this->isMethod('POST')) {
            $this->error('Invalid request method');
        }

        $backupFile = $this->input('backup_file');
        if (!$backupFile) {
            $this->error('Backup file is required');
        }

        try {
            SettingsModel::restoreBackup($backupFile);
            $this->success([], 'Database restored successfully');
        } catch (Exception $e) {
            $this->error('Failed to restore backup: ' . $e->getMessage());
        }
    }

    /**
     * Get API security settings
     */
    public function getApiSettings() {
        if (!$this->isAjax()) {
            $this->error('Invalid request method');
        }

        try {
            $settings = SettingsModel::getApiSettings();
            $this->success($settings);
        } catch (Exception $e) {
            $this->error('Failed to load API settings');
        }
    }

    /**
     * Update API security settings
     */
    public function updateApiSettings() {
        if (!$this->isAjax() || !$this->isMethod('POST')) {
            $this->error('Invalid request method');
        }

        $settings = $this->input('settings');
        if (!is_array($settings)) {
            $this->error('Settings data is required');
        }

        try {
            SettingsModel::updateApiSettings($settings);
            $this->success([], 'API settings updated successfully');
        } catch (Exception $e) {
            $this->error('Failed to update API settings: ' . $e->getMessage());
        }
    }

    /**
     * Get setting value from database
     */
    private function getSetting($key, $default = null) {
        try {
            $result = $this->db->fetch(
                "SELECT setting_value FROM settings WHERE setting_key = ?",
                [$key]
            );
            return $result ? $result['setting_value'] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }
}