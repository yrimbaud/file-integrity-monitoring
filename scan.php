<?php
define('DB_FILE', 'file_monitor.sqlite');
define('SCAN_PATH', __DIR__);

class Database {
    private $db;

    public function __construct() {
        $this->initDatabase();
    }

    private function initDatabase() {
        $this->db = new SQLite3(DB_FILE);
        $this->db->exec('
            CREATE TABLE IF NOT EXISTS files (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                path TEXT UNIQUE,
                md5 TEXT,
                size INTEGER,
                last_scan DATETIME,
                is_hidden INTEGER DEFAULT 0
            );
            CREATE INDEX IF NOT EXISTS idx_path ON files(path);
        ');
    }

    public function insertOrUpdateFile($path, $md5, $size) {
        $stmt = $this->db->prepare('
            INSERT OR REPLACE INTO files (path, md5, size, last_scan)
            VALUES (:path, :md5, :size, datetime("now"))
        ');
        $stmt->bindValue(':path', $path, SQLITE3_TEXT);
        $stmt->bindValue(':md5', $md5, SQLITE3_TEXT);
        $stmt->bindValue(':size', $size, SQLITE3_INTEGER);
        return $stmt->execute();
    }

    public function getModifiedFiles() {
        return $this->db->query('
            SELECT f1.path, f1.size as current_size, f2.size as previous_size
            FROM files f1
            LEFT JOIN files f2 ON f1.path = f2.path
            WHERE (f1.md5 != f2.md5 OR f1.size != f2.size) AND f1.is_hidden = 0
            ORDER BY f1.path
        ');
    }

    public function hideFile($path) {
        $stmt = $this->db->prepare('
            UPDATE files SET is_hidden = 1
            WHERE path = :path
        ');
        $stmt->bindValue(':path', $path, SQLITE3_TEXT);
        return $stmt->execute();
    }

    public function getAllFiles() {
        return $this->db->query('
            SELECT path, md5, size, last_scan, is_hidden
            FROM files
            WHERE is_hidden = 0
            ORDER BY path
        ');
    }
}

class FileScanner {
    private $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    public function scan($path) {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            if ($file->isFile()) {
                $filePath = $file->getPathname();
                $md5 = md5_file($filePath);
                $size = filesize($filePath);
                $this->db->insertOrUpdateFile($filePath, $md5, $size);
            }
        }
    }
}

$db = new Database();
$scanner = new FileScanner($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['scan'])) {
        $scanner->scan(SCAN_PATH);
    } elseif (isset($_POST['hide']) && isset($_POST['files'])) {
        foreach ($_POST['files'] as $path) {
            $db->hideFile($path);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>File Monitor</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .modified { background-color: #ffe6e6; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
        th { background-color: #f2f2f2; }
        .actions { margin-bottom: 20px; }
        button { padding: 10px; margin-right: 10px; }
    </style>
</head>
<body>
    <h1>File Monitor</h1>
    
    <div class="actions">
        <form method="post">
            <button type="submit" name="scan">Start Scan</button>
        </form>
    </div>

    <form method="post">
        <button type="submit" name="hide">Hide Selected Files</button>
        <table>
            <thead>
                <tr>
                    <th>Select</th>
                    <th>Path</th>
                    <th>Size</th>
                    <th>Last Scan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $files = $db->getAllFiles();
                while ($file = $files->fetchArray(SQLITE3_ASSOC)) {
                    echo "<tr>";
                    echo "<td><input type='checkbox' name='files[]' value='" . htmlspecialchars($file['path']) . "'></td>";
                    echo "<td>" . htmlspecialchars($file['path']) . "</td>";
                    echo "<td>" . number_format($file['size'] / 1024, 2) . " KB</td>";
                    echo "<td>" . htmlspecialchars($file['last_scan']) . "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </form>

    <h2>Modified Files</h2>
    <table>
        <thead>
            <tr>
                <th>Path</th>
                <th>Previous Size</th>
                <th>Current Size</th>
                <th>Difference</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $modifiedFiles = $db->getModifiedFiles();
            while ($file = $modifiedFiles->fetchArray(SQLITE3_ASSOC)) {
                $sizeDiff = $file['current_size'] - $file['previous_size'];
                $diffFormatted = ($sizeDiff >= 0 ? '+' : '') . number_format($sizeDiff / 1024, 2) . ' KB';
                
                echo "<tr class='modified'>";
                echo "<td>" . htmlspecialchars($file['path']) . "</td>";
                echo "<td>" . number_format($file['previous_size'] / 1024, 2) . " KB</td>";
                echo "<td>" . number_format($file['current_size'] / 1024, 2) . " KB</td>";
                echo "<td>" . $diffFormatted . "</td>";
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
