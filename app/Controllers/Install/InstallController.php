<?php

declare(strict_types=1);

namespace App\Controllers\Install;

use App\Core\Controller;
use App\Core\Response;
use App\Core\Database;
use PDO;
use PDOException;

/**
 * Installation Wizard Controller
 *
 * Handles the multi-step installation process for FWP Image Gallery.
 */
class InstallController extends Controller
{
    private const LOCK_FILE = 'installed.lock';

    /**
     * Step 1: Welcome & Requirements Check
     */
    public function welcome(): Response
    {
        if ($this->isInstalled()) {
            return $this->redirect(url('/'));
        }

        $requirements = $this->checkRequirements();
        $allPassed = !in_array(false, $requirements, true);

        return $this->view('install/welcome', [
            'requirements' => $requirements,
            'allPassed' => $allPassed,
            'step' => 1,
            'totalSteps' => 6,
        ], 'install');
    }

    /**
     * Step 2: Database Configuration Form
     */
    public function database(): Response
    {
        if ($this->isInstalled()) {
            return $this->redirect(url('/'));
        }

        // Check if requirements are met
        $requirements = $this->checkRequirements();
        if (in_array(false, $requirements, true)) {
            return $this->redirectWithError(url('/install'), 'Please fix all requirements before proceeding.');
        }

        // Load existing database config if available
        $dbConfig = [];
        if (file_exists(CONFIG_PATH . '/database.php')) {
            $dbConfig = require CONFIG_PATH . '/database.php';
        }

        return $this->view('install/database', [
            'step' => 2,
            'totalSteps' => 6,
            'dbConfig' => $dbConfig,
        ], 'install');
    }

    /**
     * Step 2: Save Database Configuration
     */
    public function databaseSave(): Response
    {
        if ($this->isInstalled()) {
            return $this->redirect(url('/'));
        }

        $host = trim($this->request->input('host') ?? 'localhost');
        $port = (int) ($this->request->input('port') ?? 3306);
        $database = trim($this->request->input('database') ?? '');
        $username = trim($this->request->input('username') ?? '');
        $password = $this->request->input('password') ?? '';
        $createDb = $this->request->input('create_database') === '1';

        // Validate input
        $errors = [];
        if (empty($host)) {
            $errors['host'] = 'Host is required.';
        }
        if (empty($database)) {
            $errors['database'] = 'Database name is required.';
        }
        if (empty($username)) {
            $errors['username'] = 'Username is required.';
        }

        if (!empty($errors)) {
            return $this->backWithErrors($errors);
        }

        // Test connection
        try {
            // First try to connect without database to check credentials
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            // Check if database exists
            $stmt = $pdo->query("SHOW DATABASES LIKE " . $pdo->quote($database));
            $dbExists = $stmt->fetch() !== false;

            if (!$dbExists) {
                if ($createDb) {
                    // Create the database
                    $pdo->exec("CREATE DATABASE `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                } else {
                    return $this->backWithErrors([
                        'database' => "Database '{$database}' does not exist. Enable 'Create database' option to create it automatically.",
                    ]);
                }
            }

            // Test connection to the actual database
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
        } catch (PDOException $e) {
            return $this->backWithErrors([
                'connection' => 'Database connection failed: ' . $e->getMessage(),
            ]);
        }

        // Save database configuration
        $configContent = $this->generateDatabaseConfig($host, $port, $database, $username, $password);

        if (!file_put_contents(CONFIG_PATH . '/database.php', $configContent)) {
            return $this->backWithErrors([
                'config' => 'Failed to write database configuration file. Check directory permissions.',
            ]);
        }

        session_flash('success', 'Database connection successful!');
        return $this->redirect(url('/install/setup'));
    }

    /**
     * Step 3: Database Setup (run migrations)
     */
    public function setup(): Response
    {
        if ($this->isInstalled()) {
            return $this->redirect(url('/'));
        }

        // Check if database config exists
        if (!file_exists(CONFIG_PATH . '/database.php')) {
            return $this->redirectWithError(url('/install/database'), 'Please configure the database first.');
        }

        return $this->view('install/setup', [
            'step' => 3,
            'totalSteps' => 6,
        ], 'install');
    }

    /**
     * Step 3: Run Database Setup (AJAX endpoint)
     */
    public function runSetup(): Response
    {
        if ($this->isInstalled()) {
            return $this->json(['error' => 'Already installed'], 400);
        }

        try {
            $dbConfig = require CONFIG_PATH . '/database.php';
            $db = new Database($dbConfig);

            $results = [];

            // Drop ALL existing tables first
            $db->execute('SET FOREIGN_KEY_CHECKS = 0');

            // Get all tables in the database
            $tables = $db->fetchAll("SHOW TABLES");
            foreach ($tables as $tableRow) {
                $tableName = array_values($tableRow)[0];
                try {
                    $db->execute("DROP TABLE IF EXISTS `{$tableName}`");
                } catch (PDOException $e) {
                    // Ignore errors
                }
            }

            $db->execute('SET FOREIGN_KEY_CHECKS = 1');
            $results[] = ['file' => 'cleanup', 'status' => 'success'];

            // Run schema.sql using PDO exec with multi-statement support
            $schemaPath = ROOT_PATH . '/database/schema.sql';
            if (file_exists($schemaPath)) {
                $schema = file_get_contents($schemaPath);

                // Remove CREATE DATABASE and USE statements for safety
                $schema = preg_replace('/CREATE DATABASE.*?;/is', '', $schema);
                $schema = preg_replace('/USE\s+\w+\s*;/i', '', $schema);

                // Get PDO instance and execute with multi-query support
                $pdo = $db->getPdo();

                // Split into individual statements more carefully
                // Remove comments first
                $schema = preg_replace('/--.*$/m', '', $schema);

                // Split by semicolon followed by whitespace and newline (end of statement)
                $statements = preg_split('/;\s*[\r\n]+/', $schema, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($statements as $index => $statement) {
                    $statement = trim($statement);
                    if (!empty($statement) && !preg_match('/^\s*$/', $statement)) {
                        try {
                            $pdo->exec($statement);
                        } catch (PDOException $e) {
                            // Ignore duplicate key/table exists errors
                            $msg = $e->getMessage();
                            if (strpos($msg, 'Duplicate') === false
                                && strpos($msg, 'already exists') === false
                                && strpos($msg, '1062') === false
                                && strpos($msg, '1050') === false) {
                                // Add statement info to error for debugging
                                $preview = substr($statement, 0, 100);
                                throw new PDOException("Statement #{$index} failed: {$msg}\nSQL: {$preview}...");
                            }
                        }
                    }
                }
                $results[] = ['file' => 'schema.sql', 'status' => 'success'];
            }

            // Run migration files
            $migrationsPath = ROOT_PATH . '/database/migrations';
            if (is_dir($migrationsPath)) {
                $files = glob($migrationsPath . '/*.sql');
                sort($files); // Ensure order

                foreach ($files as $file) {
                    $sql = file_get_contents($file);

                    // Remove comments
                    $sql = preg_replace('/--.*$/m', '', $sql);

                    // Split by semicolon followed by whitespace/newline
                    $statements = preg_split('/;\s*[\r\n]+/', $sql, -1, PREG_SPLIT_NO_EMPTY);

                    foreach ($statements as $statement) {
                        $statement = trim($statement);
                        if (!empty($statement) && !preg_match('/^\s*$/', $statement)) {
                            try {
                                $pdo->exec($statement);
                            } catch (PDOException $e) {
                                // Ignore duplicate/exists errors
                                $msg = $e->getMessage();
                                if (strpos($msg, 'Duplicate') === false
                                    && strpos($msg, 'already exists') === false
                                    && strpos($msg, '1062') === false
                                    && strpos($msg, '1050') === false
                                    && strpos($msg, '1060') === false) {
                                    $preview = substr($statement, 0, 100);
                                    throw new PDOException("Migration " . basename($file) . " failed: {$msg}\nSQL: {$preview}...");
                                }
                            }
                        }
                    }
                    $results[] = ['file' => basename($file), 'status' => 'success'];
                }
            }

            return $this->json([
                'success' => true,
                'message' => 'Database setup completed successfully!',
                'results' => $results,
            ]);
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Database setup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Step 4: Admin Account Form
     */
    public function admin(): Response
    {
        if ($this->isInstalled()) {
            return $this->redirect(url('/'));
        }

        // Check if database is set up
        if (!file_exists(CONFIG_PATH . '/database.php')) {
            return $this->redirectWithError(url('/install/database'), 'Please configure the database first.');
        }

        return $this->view('install/admin', [
            'step' => 4,
            'totalSteps' => 6,
        ], 'install');
    }

    /**
     * Step 4: Create Admin Account
     */
    public function adminSave(): Response
    {
        if ($this->isInstalled()) {
            return $this->redirect(url('/'));
        }

        $username = trim($this->request->input('username') ?? '');
        $email = trim($this->request->input('email') ?? '');
        $password = $this->request->input('password') ?? '';
        $passwordConfirm = $this->request->input('password_confirmation') ?? '';

        // Validate input
        $errors = [];
        if (empty($username)) {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Username must be at least 3 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = 'Username can only contain letters, numbers, and underscores.';
        }

        if (empty($email)) {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (empty($password)) {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 12) {
            $errors['password'] = 'Password must be at least 12 characters.';
        }

        if ($password !== $passwordConfirm) {
            $errors['password_confirmation'] = 'Passwords do not match.';
        }

        if (!empty($errors)) {
            return $this->backWithErrors($errors);
        }

        try {
            $dbConfig = require CONFIG_PATH . '/database.php';
            $db = new Database($dbConfig);

            // Delete any existing admin users from default install
            $db->execute("DELETE FROM users WHERE email = 'admin@example.com'");

            // Check if username or email already exists
            $existing = $db->fetch(
                "SELECT id FROM users WHERE username = :username OR email = :email",
                ['username' => $username, 'email' => $email]
            );

            if ($existing) {
                return $this->backWithErrors([
                    'username' => 'Username or email already exists.',
                ]);
            }

            // Create admin user
            $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

            $db->insert('users', [
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash,
                'role' => 'admin',
                'status' => 'active',
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);

            session_flash('success', 'Admin account created successfully!');
            return $this->redirect(url('/install/settings'));
        } catch (\Throwable $e) {
            return $this->backWithErrors([
                'database' => 'Failed to create admin account: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Step 5: Site Settings Form
     */
    public function settings(): Response
    {
        if ($this->isInstalled()) {
            return $this->redirect(url('/'));
        }

        // Get current settings if available
        $siteSettings = [];
        try {
            $dbConfig = require CONFIG_PATH . '/database.php';
            $db = new Database($dbConfig);

            $rows = $db->fetchAll("SELECT setting_key, setting_value FROM settings");
            foreach ($rows as $row) {
                $siteSettings[$row['setting_key']] = $row['setting_value'];
            }
        } catch (\Throwable $e) {
            // Ignore - use defaults
        }

        // Detect site URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $detectedUrl = "{$protocol}://{$host}";

        return $this->view('install/settings', [
            'step' => 5,
            'totalSteps' => 6,
            'siteSettings' => $siteSettings,
            'detectedUrl' => $detectedUrl,
        ], 'install');
    }

    /**
     * Step 5: Save Site Settings
     */
    public function settingsSave(): Response
    {
        if ($this->isInstalled()) {
            return $this->redirect(url('/'));
        }

        $siteName = trim($this->request->input('site_name') ?? 'FWP Image Gallery');
        $siteUrl = trim($this->request->input('site_url') ?? '');
        $siteDescription = trim($this->request->input('site_description') ?? '');

        // Validate
        $errors = [];
        if (empty($siteName)) {
            $errors['site_name'] = 'Site name is required.';
        }
        if (empty($siteUrl)) {
            $errors['site_url'] = 'Site URL is required.';
        } elseif (!filter_var($siteUrl, FILTER_VALIDATE_URL)) {
            $errors['site_url'] = 'Please enter a valid URL.';
        }

        if (!empty($errors)) {
            return $this->backWithErrors($errors);
        }

        // Remove trailing slash from URL
        $siteUrl = rtrim($siteUrl, '/');

        try {
            $dbConfig = require CONFIG_PATH . '/database.php';
            $db = new Database($dbConfig);

            // Update settings
            $settings = [
                'site_name' => $siteName,
                'site_url' => $siteUrl,
                'site_description' => $siteDescription,
            ];

            foreach ($settings as $key => $value) {
                // Try to update first
                $affected = $db->update(
                    'settings',
                    ['setting_value' => $value, 'updated_at' => date('Y-m-d H:i:s')],
                    'setting_key = :key',
                    ['key' => $key]
                );

                // If no rows affected, insert
                if ($affected === 0) {
                    $existing = $db->fetch("SELECT id FROM settings WHERE setting_key = :key", ['key' => $key]);
                    if (!$existing) {
                        $db->insert('settings', [
                            'setting_key' => $key,
                            'setting_value' => $value,
                            'setting_type' => 'string',
                            'is_public' => true,
                        ]);
                    }
                }
            }

            session_flash('success', 'Site settings saved successfully!');
            return $this->redirect(url('/install/complete'));
        } catch (\Throwable $e) {
            return $this->backWithErrors([
                'database' => 'Failed to save settings: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Step 6: Installation Complete
     */
    public function complete(): Response
    {
        if ($this->isInstalled()) {
            return $this->redirect(url('/'));
        }

        // Create the lock file
        $lockPath = STORAGE_PATH . '/' . self::LOCK_FILE;
        $lockContent = json_encode([
            'installed_at' => date('Y-m-d H:i:s'),
            'version' => '1.0.0',
            'php_version' => PHP_VERSION,
        ], JSON_PRETTY_PRINT);

        if (!file_put_contents($lockPath, $lockContent)) {
            return $this->view('install/complete', [
                'step' => 6,
                'totalSteps' => 6,
                'success' => false,
                'error' => 'Failed to create installation lock file. Please ensure the storage directory is writable.',
            ], 'install');
        }

        // Auto-login the admin user and redirect to dashboard
        try {
            $dbConfig = require CONFIG_PATH . '/database.php';
            $db = new Database($dbConfig);

            // Get the admin user (most recently created admin)
            $admin = $db->fetch(
                "SELECT id FROM users WHERE role = 'admin' ORDER BY id DESC LIMIT 1"
            );

            if ($admin) {
                // Regenerate session ID for security
                session_regenerate_id(true);

                // Set session data to log in the admin
                $_SESSION['user_id'] = $admin['id'];
                unset($_SESSION['_user_cache']);

                // Update last login time
                $db->execute(
                    "UPDATE users SET last_login_at = NOW() WHERE id = :id",
                    ['id' => $admin['id']]
                );

                session_flash('success', 'Installation complete! Welcome to your new site.');
                return $this->redirect(url('/admin'));
            }
        } catch (\Throwable $e) {
            // If auto-login fails, show the complete page instead
        }

        return $this->view('install/complete', [
            'step' => 6,
            'totalSteps' => 6,
            'success' => true,
        ], 'install');
    }

    /**
     * Check if the application is already installed
     */
    private function isInstalled(): bool
    {
        return file_exists(STORAGE_PATH . '/' . self::LOCK_FILE);
    }

    /**
     * Check system requirements
     */
    private function checkRequirements(): array
    {
        return [
            'php_version' => [
                'name' => 'PHP 8.2+',
                'current' => PHP_VERSION,
                'passed' => version_compare(PHP_VERSION, '8.2.0', '>='),
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL Extension',
                'current' => extension_loaded('pdo_mysql') ? 'Installed' : 'Not installed',
                'passed' => extension_loaded('pdo_mysql'),
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'current' => extension_loaded('mbstring') ? 'Installed' : 'Not installed',
                'passed' => extension_loaded('mbstring'),
            ],
            'gd' => [
                'name' => 'GD Extension',
                'current' => extension_loaded('gd') ? 'Installed' : 'Not installed',
                'passed' => extension_loaded('gd'),
            ],
            'json' => [
                'name' => 'JSON Extension',
                'current' => extension_loaded('json') ? 'Installed' : 'Not installed',
                'passed' => extension_loaded('json'),
            ],
            'fileinfo' => [
                'name' => 'Fileinfo Extension',
                'current' => extension_loaded('fileinfo') ? 'Installed' : 'Not installed',
                'passed' => extension_loaded('fileinfo'),
            ],
            'storage_writable' => [
                'name' => 'Storage Directory Writable',
                'current' => is_writable(STORAGE_PATH) ? 'Writable' : 'Not writable',
                'passed' => is_writable(STORAGE_PATH),
            ],
            'uploads_writable' => [
                'name' => 'Uploads Directory Writable',
                'current' => is_writable(PUBLIC_PATH . '/uploads') ? 'Writable' : 'Not writable',
                'passed' => is_writable(PUBLIC_PATH . '/uploads'),
            ],
            'config_writable' => [
                'name' => 'Config Directory Writable',
                'current' => is_writable(CONFIG_PATH) ? 'Writable' : 'Not writable',
                'passed' => is_writable(CONFIG_PATH),
            ],
        ];
    }

    /**
     * Generate database configuration file content
     */
    private function generateDatabaseConfig(string $host, int $port, string $database, string $username, string $password): string
    {
        $escapedPassword = addslashes($password);

        return <<<PHP
<?php

declare(strict_types=1);

/**
 * Database Configuration
 *
 * Generated by Installation Wizard
 * Generated at: {$this->getCurrentTimestamp()}
 */

return [
    'driver' => 'mysql',
    'host' => '{$host}',
    'port' => {$port},
    'database' => '{$database}',
    'username' => '{$username}',
    'password' => '{$escapedPassword}',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ],
];

PHP;
    }

    /**
     * Get current timestamp for config file
     */
    private function getCurrentTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Split SQL file into individual statements
     */
    private function splitSqlStatements(string $sql): array
    {
        // Remove comments
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

        // Split by semicolons, but be careful with strings
        $statements = [];
        $current = '';
        $inString = false;
        $stringChar = '';

        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];

            if ($inString) {
                $current .= $char;
                if ($char === $stringChar && ($i === 0 || $sql[$i - 1] !== '\\')) {
                    $inString = false;
                }
            } else {
                if ($char === "'" || $char === '"') {
                    $inString = true;
                    $stringChar = $char;
                    $current .= $char;
                } elseif ($char === ';') {
                    $statements[] = $current;
                    $current = '';
                } else {
                    $current .= $char;
                }
            }
        }

        if (trim($current)) {
            $statements[] = $current;
        }

        return $statements;
    }
}
