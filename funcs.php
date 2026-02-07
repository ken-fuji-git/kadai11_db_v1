<?php
declare(strict_types=1);


//.env読み込み
require_once __DIR__ . '/src/Env.php';
Env::loadForHost(__DIR__);


//SQLエラー
function sql_error($stmt)
{
    //execute（SQL実行時にエラーがある場合）
    $error = $stmt->errorInfo();
    exit("SQLError:" . $error[2]);
}

/**
 * リダイレクト
 */
function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

/**
 * フラッシュ（1回だけ表示するメッセージ）
 */
function set_flash(string $key, $value): void
{
    $_SESSION['flash'][$key] = $value;
}

function get_flash(string $key, $default = null)
{
    if (!isset($_SESSION['flash'][$key])) return $default;
    $v = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $v;
}

/**
 * DB接続（PDO）を返す
 * .env のキー:
 * DB_HOST, DB_NAME, DB_USER, DB_PASS
 */
function db_conn(): PDO
{
    $host = Env::get('DB_HOST', '127.0.0.1');
    $name = Env::get('DB_NAME', '');
    $user = Env::get('DB_USER', 'root');
    $pass = Env::get('DB_PASS', '');

    // localhost はUnixソケット優先になるため、環境差分で失敗しやすい
    if ($host === 'localhost') {
        $host = '127.0.0.1';
    }

    $dsn = "mysql:host={$host};dbname={$name};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        echo 'DB Connection Error: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        exit;
    }
}

/**
 * XSS対策
 */
function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// function h(?string $s): string {
//     return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8');
// }





//SessionCheck
function sschk()
{
    //isset()でchk_ssidがあるか？を確認
    //chk_ssidはログイン成功した時のsession_idが入っている
    if (!isset($_SESSION["chk_ssid"]) || $_SESSION["chk_ssid"] != session_id()) {
        exit("LOGIN ERROR");
    } else {
        session_regenerate_id(true); //新しいセッションIDを発行する
        $_SESSION["chk_ssid"] = session_id(); //新しいセッションIDを保存する
    }
}
