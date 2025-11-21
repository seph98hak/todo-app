<?php
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
if ($base === '/' || $base === '\\') $base = '';
$page_title = $page_title ?? 'TODO App';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?php echo htmlspecialchars($page_title); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Minimal embedded styles so app looks okay even without external CSS */
    :root{--primary:#4b7cff;--accent:#ff6b5f}
    body{font-family:Inter,system-ui,Arial;background:#f6f8ff;color:#0b1220;margin:0}
    .container{max-width:1100px;margin:2rem auto;padding:1rem}
    .card-glass{background:linear-gradient(180deg,rgba(255,255,255,0.9),#fff);border-radius:12px;box-shadow:0 8px 24px rgba(30,60,120,0.06);border:1px solid rgba(15,23,42,0.04)}
    .app-title{font-weight:700}
    .btn-accent{background:var(--accent);border:none;color:#fff}
  </style>
</head>
<body>