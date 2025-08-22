<?php
/**
 * Convert bracketed namespaces to semicolon namespaces in a PHP file,
 * removing only the matching closing brace for each namespace.
 *
 * Usage: php tools/fix_bracketed_namespaces.php path/to/file.php
 */
if ($argc < 2) {
    fwrite(STDERR, "Usage: php {$argv[0]} <file.php>\n");
    exit(1);
}
$path = $argv[1];
$code = file_get_contents($path);
if ($code === false) {
    fwrite(STDERR, "Cannot read $path\n");
    exit(1);
}

$tokens = token_get_all($code);
$out = '';
$braceDepth = 0;
/** @var int[] $nsTargets depth markers for open bracketed namespaces */
$nsTargets = [];

$N = count($tokens);
for ($i = 0; $i < $N; $i++) {
    $t = $tokens[$i];

    if (is_array($t)) {
        [$id, $text] = $t;

        if ($id === T_NAMESPACE) {
            // Emit the namespace header tokens verbatim until we hit '{' or ';'
            $out .= $text;
            $j = $i + 1;
            $buffer = '';
            $found = null; // '{' or ';'

            for (; $j < $N; $j++) {
                $tt = $tokens[$j];
                if (is_array($tt)) {
                    $buffer .= $tt[1];
                    continue;
                }
                if ($tt === '{') { $found = '{'; break; }
                if ($tt === ';') { $found = ';'; break; }
                $buffer .= $tt; // whitespace/comments
            }

            if ($found === '{') {
                // Convert to semicolon style: keep header, replace '{' with ';'
                $out .= $buffer . ';';
                // Skip the '{' token
                $j++;
                // Record that when we later see a '}' at the *current* braceDepth,
                // it's the namespace closer: we will drop it.
                $nsTargets[] = $braceDepth;
                // Advance main loop
                $i = $j - 1;
                continue;
            } elseif ($found === ';') {
                // Already semicolon namespace, just emit and continue
                $out .= $buffer . ';';
                $i = $j;
                continue;
            } else {
                // Malformed namespace; emit buffer and continue defensively
                $out .= $buffer;
                $i = $j - 1;
                continue;
            }
        }

        // Any other token: just emit
        $out .= $text;
        continue;
    }

    // Single-char token
    $ch = $t;
    if ($ch === '{') {
        // normal code block
        $braceDepth++;
        $out .= '{';
        continue;
    }
    if ($ch === '}') {
        // If a bracketed-namespace was open at this depth, this '}' closes that namespace: drop it.
        if (!empty($nsTargets) && end($nsTargets) === $braceDepth) {
            array_pop($nsTargets);
            // do NOT change $braceDepth (we never incremented it for namespace '{')
            continue;
        }
        // Otherwise it's a real code block close
        if ($braceDepth > 0) $braceDepth--;
        $out .= '}';
        continue;
    }

    $out .= $ch;
}

// Final sanity: if any nsTargets remain, append closers (shouldn't happen)
while (!empty($nsTargets)) {
    array_pop($nsTargets);
    $out .= "\n";
}

file_put_contents($path, $out);
