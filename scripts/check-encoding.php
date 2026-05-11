<?php

declare(strict_types=1);

$rootDir = dirname(__DIR__);

$scanRoots = [
    'app',
    'resources/js',
    'resources/views',
    'routes',
    'config',
    'database',
];

$excludedDirs = [
    'vendor',
    'node_modules',
    'storage',
    'bootstrap/cache',
    'public/build',
    'public/js',
    'public/admin/assets',
    '.git',
];

$mojibakePatterns = [
    'SÄ',
    'boÅ',
    'gÃ',
    'Ä±',
    'Ã¼',
    'Ã¶',
    'ÅŸ',
    'Ã‡',
    'Ä°',
    'Ãƒ',
    'Ã„',
    'Ã…',
    'ï¿½',
];

// "Â" tek başına Türkçede (ör. KÂHYALAR) geçebilir; sadece tipik mojibake bağlamlarında işaretle.
$mojibakeRegexPatterns = [
    '/Â(?:©|®|°|±|·|»|«|¼|½|¾)/u',
];

$bomIssues = [];
$mojibakeIssues = [];

$normalizePath = static function (string $path): string {
    return str_replace('\\', '/', $path);
};

$isExcluded = static function (string $path) use ($excludedDirs, $normalizePath): bool {
    $normalizedPath = $normalizePath($path);

    foreach ($excludedDirs as $excludedDir) {
        $normalizedExcluded = '/' . trim($normalizePath($excludedDir), '/');
        if (strpos($normalizedPath, $normalizedExcluded) !== false) {
            return true;
        }
    }

    return false;
};

$relativePath = static function (string $absolutePath) use ($rootDir, $normalizePath): string {
    $normalizedRoot = rtrim($normalizePath($rootDir), '/');
    $normalizedFile = $normalizePath($absolutePath);

    if (strpos($normalizedFile, $normalizedRoot . '/') === 0) {
        return substr($normalizedFile, strlen($normalizedRoot) + 1);
    }

    return $normalizedFile;
};

foreach ($scanRoots as $scanRoot) {
    $absoluteRoot = $rootDir . DIRECTORY_SEPARATOR . $scanRoot;
    if (!is_dir($absoluteRoot)) {
        continue;
    }

    $directoryIterator = new RecursiveDirectoryIterator(
        $absoluteRoot,
        FilesystemIterator::SKIP_DOTS
    );

    $filter = new RecursiveCallbackFilterIterator(
        $directoryIterator,
        static function (SplFileInfo $current) use ($isExcluded): bool {
            return !$isExcluded($current->getPathname());
        }
    );

    $iterator = new RecursiveIteratorIterator($filter);

    foreach ($iterator as $fileInfo) {
        /** @var SplFileInfo $fileInfo */
        if (!$fileInfo->isFile()) {
            continue;
        }

        $filePath = $fileInfo->getPathname();
        if ($isExcluded($filePath)) {
            continue;
        }

        $content = @file_get_contents($filePath);
        if ($content === false) {
            continue;
        }

        if (strncmp($content, "\xEF\xBB\xBF", 3) === 0) {
            $bomIssues[] = $relativePath($filePath);
        }

        if (strpos($content, "\0") !== false) {
            continue;
        }

        $lines = preg_split('/\R/', $content) ?: [];
        foreach ($lines as $index => $line) {
            $matched = false;

            foreach ($mojibakePatterns as $pattern) {
                if (strpos($line, $pattern) !== false) {
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                foreach ($mojibakeRegexPatterns as $regexPattern) {
                    if (preg_match($regexPattern, $line) === 1) {
                        $matched = true;
                        break;
                    }
                }
            }

            if ($matched) {
                $lineNumber = $index + 1;
                $mojibakeIssues[] = sprintf(
                    '%s:%d:%s',
                    $relativePath($filePath),
                    $lineNumber,
                    trim($line)
                );
            }
        }
    }
}

$hasIssues = false;

if (!empty($bomIssues)) {
    $hasIssues = true;
    echo "BOM issues found:\n";
    foreach ($bomIssues as $bomIssue) {
        echo "- {$bomIssue}\n";
    }
    echo "\n";
}

if (!empty($mojibakeIssues)) {
    $hasIssues = true;
    echo "Mojibake issues found:\n";
    foreach ($mojibakeIssues as $mojibakeIssue) {
        echo "- {$mojibakeIssue}\n";
    }
    echo "\n";
}

if ($hasIssues) {
    exit(1);
}

echo "Encoding check passed.\n";
exit(0);
