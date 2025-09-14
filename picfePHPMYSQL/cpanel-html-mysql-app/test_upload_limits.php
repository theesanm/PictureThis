<?php
// PHP Upload Limits Test
echo "<h1>PHP Upload Limits Test</h1>";
echo "<h2>Current PHP Configuration:</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Setting</th><th>Value</th></tr>";
echo "<tr><td>upload_max_filesize</td><td>" . ini_get('upload_max_filesize') . "</td></tr>";
echo "<tr><td>post_max_size</td><td>" . ini_get('post_max_size') . "</td></tr>";
echo "<tr><td>memory_limit</td><td>" . ini_get('memory_limit') . "</td></tr>";
echo "<tr><td>max_execution_time</td><td>" . ini_get('max_execution_time') . "</td></tr>";
echo "<tr><td>max_input_time</td><td>" . ini_get('max_input_time') . "</td></tr>";
echo "<tr><td>max_file_uploads</td><td>" . ini_get('max_file_uploads') . "</td></tr>";
echo "<tr><td>file_uploads</td><td>" . (ini_get('file_uploads') ? 'Enabled' : 'Disabled') . "</td></tr>";
echo "</table>";

// Test file upload form
echo "<h2>Test File Upload</h2>";
echo "<form method='POST' enctype='multipart/form-data'>";
echo "<input type='file' name='test_image' accept='image/*'>";
echo "<input type='submit' value='Upload Test'>";
echo "</form>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])) {
    echo "<h3>Upload Result:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Property</th><th>Value</th></tr>";
    echo "<tr><td>Name</td><td>" . htmlspecialchars($_FILES['test_image']['name']) . "</td></tr>";
    echo "<tr><td>Type</td><td>" . htmlspecialchars($_FILES['test_image']['type']) . "</td></tr>";
    echo "<tr><td>Size</td><td>" . $_FILES['test_image']['size'] . " bytes</td></tr>";
    echo "<tr><td>Error</td><td>" . $_FILES['test_image']['error'] . "</td></tr>";
    echo "<tr><td>Temporary Name</td><td>" . htmlspecialchars($_FILES['test_image']['tmp_name']) . "</td></tr>";
    echo "</table>";

    if ($_FILES['test_image']['error'] === UPLOAD_ERR_OK) {
        echo "<p style='color: green; font-weight: bold;'>✓ Upload successful!</p>";
        echo "<p>File size: " . number_format($_FILES['test_image']['size']) . " bytes</p>";

        // Check if file actually exists
        if (file_exists($_FILES['test_image']['tmp_name'])) {
            echo "<p style='color: green;'>✓ Temporary file exists on server</p>";
        } else {
            echo "<p style='color: red;'>✗ Temporary file does not exist</p>";
        }
    } else {
        echo "<p style='color: red; font-weight: bold;'>✗ Upload failed with error: " . $_FILES['test_image']['error'] . "</p>";
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE in form',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'PHP extension stopped the upload'
        ];
        if (isset($errors[$_FILES['test_image']['error']])) {
            echo "<p>Error description: " . $errors[$_FILES['test_image']['error']] . "</p>";
        }
    }
}
?>