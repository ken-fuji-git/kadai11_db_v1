<?php

class Env
{
    private static $path;

    public static function load($path)
    {
        if (!file_exists($path)) {
            return;
        }
        self::$path = $path;

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);

            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    public static function get($key, $default = null)
    {
        $value = getenv($key);
        if ($value === false) {
            return $default;
        }
        return $value;
    }

    /**
     * HTTP_HOST に応じて読み込む .env を自動選択する
     * - fujiwarakenta.sakura.ne.jp: .env.sakura
     * - それ以外: .env.local
     * - 見つからない場合: .env
     */
    public static function loadForHost(string $baseDir): void
    {
        $host = (string)($_SERVER['HTTP_HOST'] ?? '');
        $hostOnly = strtolower(trim(explode(':', $host)[0]));
        $target = ($hostOnly === 'fujiwarakenta.sakura.ne.jp') ? '.env.sakura' : '.env.local';

        $targetPath = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR . $target;
        if (!file_exists($targetPath)) {
            $targetPath = rtrim($baseDir, '/\\') . DIRECTORY_SEPARATOR . '.env';
        }

        self::load($targetPath);
    }
}
