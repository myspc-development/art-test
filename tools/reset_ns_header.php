<?php
/* Usage: php tools/reset_ns_header.php <file> "Vendor\\Pkg\\Tests" */
$f = $argv[1] ?? null; $ns = $argv[2] ?? null;
if (!$f || !$ns) { fwrite(STDERR,"args: file ns\n"); exit(1); }
$lines = file($f, FILE_IGNORE_NEW_LINES);
if ($lines === false) { fwrite(STDERR,"read failed\n"); exit(1); }
if ($lines && strncmp($lines[0], "\xEF\xBB\xBF", 3) === 0) $lines[0] = substr($lines[0], 3);

$out = "<?php\nnamespace $ns;\n";
$started = false;
foreach ($lines as $line) {
  if (!$started) {
    if (preg_match('/^\s*<\?php\b/', $line)) continue;
    if (preg_match('/^\s*namespace\b/', $line)) continue;   // drop ALL prior namespace lines
    if (preg_match('/^\s*\{\s*$/', $line)) continue;        // drop stray { from old bracketed ns
    $started = true;
  }
  $out .= $line . "\n";
}
file_put_contents($f, $out);
