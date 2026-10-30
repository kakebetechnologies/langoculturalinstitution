<?php
declare(strict_types=1);

const UPLOAD_KINDS = [
    'image'    => ['mimes' => ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'], 'max' => 12],
    'audio'    => ['mimes' => ['audio/mpeg' => 'mp3', 'audio/wav' => 'wav', 'audio/x-wav' => 'wav', 'audio/ogg' => 'ogg', 'audio/mp4' => 'm4a', 'audio/x-m4a' => 'm4a', 'audio/webm' => 'webm'], 'max' => 40],
    'video'    => ['mimes' => ['video/mp4' => 'mp4', 'video/webm' => 'webm', 'video/quicktime' => 'mov'], 'max' => 120],
    'document' => ['mimes' => ['application/pdf' => 'pdf', 'application/msword' => 'doc', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx', 'text/plain' => 'txt', 'application/vnd.ms-excel' => 'xls', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx'], 'max' => 25],
];

/** finfo does not recognise every MP3 (for example one with a large ID3 tag), so audio is also recognised by its first bytes. */
function sniff_audio_mime(string $file): ?string
{
    $h = (string)file_get_contents($file, false, null, 0, 12);
    if (str_starts_with($h, 'ID3')) return 'audio/mpeg';                                                 // an MP3 with an ID3 tag
    if (strlen($h) >= 2 && ord($h[0]) === 0xFF && (ord($h[1]) & 0xE0) === 0xE0) return 'audio/mpeg';      // an MP3 frame
    if (str_starts_with($h, 'OggS')) return 'audio/ogg';
    if (str_starts_with($h, 'RIFF') && substr($h, 8, 4) === 'WAVE') return 'audio/wav';
    if (substr($h, 4, 4) === 'ftyp') return 'audio/mp4';
    return null;
}

/**
 * Validate and store an uploaded file. Returns the relative path or null when nothing was uploaded.
 * @throws RuntimeException with a user-friendly message
 */
function save_upload(string $field, string $kind = 'image'): ?string
{
    $f = $_FILES[$field] ?? null;
    if (!$f || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($f['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE
            ? 'The file is larger than the server allows.' : 'The file could not be uploaded. Please try again.');
    }
    $spec = UPLOAD_KINDS[$kind] ?? null;
    if (!$spec) throw new RuntimeException('Unsupported upload type.');
    if ($f['size'] > $spec['max'] * 1024 * 1024) throw new RuntimeException("The file is too large (max {$spec['max']} MB).");
    if (!is_uploaded_file($f['tmp_name'])) throw new RuntimeException('Invalid upload.');

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if ($kind === 'audio' && !isset($spec['mimes'][$mime])) $mime = sniff_audio_mime($f['tmp_name']) ?? $mime;
    $ext = $spec['mimes'][$mime] ?? null;
    if (!$ext) throw new RuntimeException('This file type is not allowed for ' . $kind . ' uploads.');

    $dir = 'uploads/' . date('Y/m');
    if (!is_dir(ROOT . '/' . $dir)) mkdir(ROOT . '/' . $dir, 0775, true);
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    $rel = $dir . '/' . $name;
    $dest = ROOT . '/' . $rel;

    if ($kind === 'image' && $ext !== 'gif' && function_exists('imagecreatefromstring')) {
        // re-encode: strips EXIF/GPS metadata, fixes orientation, caps size for fast pages
        $img = @imagecreatefromstring((string)file_get_contents($f['tmp_name']));
        if (!$img) throw new RuntimeException('The image appears to be corrupt.');
        if ($ext === 'jpg' && function_exists('exif_read_data')) {
            $ex = @exif_read_data($f['tmp_name']);
            $o = (int)($ex['Orientation'] ?? 1);
            if ($o === 3) $img = imagerotate($img, 180, 0);
            elseif ($o === 6) $img = imagerotate($img, -90, 0);
            elseif ($o === 8) $img = imagerotate($img, 90, 0);
        }
        $w = imagesx($img); $h = imagesy($img); $max = 2000;
        if ($w > $max || $h > $max) {
            $r = min($max / $w, $max / $h);
            $n = imagecreatetruecolor((int)($w * $r), (int)($h * $r));
            imagealphablending($n, false); imagesavealpha($n, true);
            imagecopyresampled($n, $img, 0, 0, 0, 0, (int)($w * $r), (int)($h * $r), $w, $h);
            $img = $n;
        }
        if ($ext === 'png') { imagesavealpha($img, true); imagepng($img, $dest, 6); }
        elseif ($ext === 'webp') imagewebp($img, $dest, 82);
        else imagejpeg($img, $dest, 84);
    } elseif (!move_uploaded_file($f['tmp_name'], $dest)) {
        throw new RuntimeException('The file could not be saved.');
    }
    return $rel;
}

function media_kind_for(?string $path): string
{
    $ext = strtolower(pathinfo((string)$path, PATHINFO_EXTENSION));
    foreach (UPLOAD_KINDS as $kind => $spec) if (in_array($ext, $spec['mimes'], true)) return $kind;
    return 'none';
}
