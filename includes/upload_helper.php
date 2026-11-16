<?php
/**
 * Safe Image & Document Upload Helper
 */

/**
 * Handle citizen photograph upload.
 * 
 * @param array $file $_FILES['photo']
 * @param string &$error_out Error message output
 * @return string|null Relative file path if uploaded, or null
 */
function handle_citizen_photo_upload($file, &$error_out) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_out = "Upload error code: " . $file['error'];
        return null;
    }

    // Max 2MB
    $max_bytes = 2 * 1024 * 1024;
    if ($file['size'] > $max_bytes) {
        $error_out = "Image file is too large. Maximum allowed size is 2MB.";
        return null;
    }

    // Check MIME type
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed_mimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!array_key_exists($mime, $allowed_mimes)) {
        $error_out = "Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.";
        return null;
    }

    $ext = $allowed_mimes[$mime];
    $filename = 'cit_' . bin2hex(random_bytes(10)) . '.' . $ext;
    $target_dir = __DIR__ . '/../uploads/citizens/';

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    $target_path = $target_dir . DIRECTORY_SEPARATOR . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return 'uploads/citizens/' . $filename;
    } else {
        $error_out = "Failed to save uploaded photograph.";
        return null;
    }
}

function divorce_document_storage_directory() {
    $document_root = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
    if ($document_root === false) {
        throw new RuntimeException('Unable to determine the web document root.');
    }

    $project_name = basename(dirname(__DIR__));
    return dirname($document_root) . DIRECTORY_SEPARATOR
        . $project_name . '_private_uploads' . DIRECTORY_SEPARATOR . 'divorce';
}

function resolve_divorce_document_path($stored_path) {
    $stored_path = str_replace('\\', '/', (string)$stored_path);
    if (!preg_match('/^(private_divorce|uploads\\/divorce)\\/(doc_[a-f0-9]{20}\\.(?:pdf|jpg|png))$/i', $stored_path, $matches)) {
        return null;
    }

    if ($matches[1] === 'private_divorce') {
        $base_path = divorce_document_storage_directory();
    } else {
        $base_path = __DIR__ . '/../uploads/divorce';
    }

    $real_base = realpath($base_path);
    $real_file = realpath($base_path . DIRECTORY_SEPARATOR . $matches[2]);
    if ($real_base === false || $real_file === false || !is_file($real_file)) {
        return null;
    }

    $base_prefix = rtrim($real_base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    if (stripos($real_file, $base_prefix) !== 0) {
        return null;
    }
    return $real_file;
}

/**
 * Handle divorce supporting document upload (PDF, JPG, PNG).
 */
function handle_divorce_document_upload($file, &$error_out) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_out = "Document upload error: " . $file['error'];
        return null;
    }

    // Max 5MB
    $max_bytes = 5 * 1024 * 1024;
    if ($file['size'] > $max_bytes) {
        $error_out = "Supporting document exceeds maximum limit of 5MB.";
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);

    $allowed_mimes = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png'
    ];

    if (!array_key_exists($mime, $allowed_mimes)) {
        $error_out = "Invalid document type. Allowed formats: PDF, JPG, PNG.";
        return null;
    }

    $ext = $allowed_mimes[$mime];
    $filename = 'doc_' . bin2hex(random_bytes(10)) . '.' . $ext;
    try {
        $target_dir = divorce_document_storage_directory();
    } catch (RuntimeException $exception) {
        $error_out = "Unable to determine a private document storage location.";
        return null;
    }

    if (!is_dir($target_dir)) {
        if (!mkdir($target_dir, 0750, true) && !is_dir($target_dir)) {
            $error_out = "Unable to create the private document storage directory.";
            return null;
        }
    }

    $target_path = $target_dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $target_path)) {
        return 'private_divorce/' . $filename;
    } else {
        $error_out = "Failed to save supporting document.";
        return null;
    }
}
