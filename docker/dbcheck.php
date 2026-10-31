<?php
// Small database checks for the container scripts, without booting Symfony.
//   php dbcheck.php wait        wait (up to DB_WAIT_SECONDS, default 60) until the database accepts connections
//   php dbcheck.php has-cards   exit 0 if the card table exists and has rows, 1 otherwise
//   php dbcheck.php has-user X  exit 0 if a user with username X exists, 1 otherwise

$url = getenv('DATABASE_URL');
if (!$url) {
    fwrite(STDERR, "DATABASE_URL is not set\n");
    exit(2);
}
$u = parse_url($url);
$dsn = sprintf('mysql:host=%s;port=%d;dbname=%s', $u['host'], $u['port'] ?? 3306, ltrim($u['path'] ?? '', '/'));
$user = urldecode($u['user'] ?? '');
$pass = urldecode($u['pass'] ?? '');

function connect($dsn, $user, $pass)
{
    return new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
}

switch ($argv[1] ?? '') {
    case 'wait':
        $deadline = time() + (int) (getenv('DB_WAIT_SECONDS') ?: 60);
        while (true) {
            try {
                connect($dsn, $user, $pass);
                exit(0);
            } catch (PDOException $e) {
                if (time() >= $deadline) {
                    fwrite(STDERR, 'Database not reachable: ' . $e->getMessage() . "\n");
                    exit(1);
                }
                sleep(2);
            }
        }
        // no break
    case 'has-cards':
        try {
            $count = (int) connect($dsn, $user, $pass)->query('SELECT COUNT(*) FROM card')->fetchColumn();
            exit($count > 0 ? 0 : 1);
        } catch (PDOException $e) {
            exit(1);
        }
        // no break
    case 'has-user':
        try {
            $stmt = connect($dsn, $user, $pass)->prepare('SELECT COUNT(*) FROM user WHERE username_canonical = ?');
            $stmt->execute([mb_strtolower($argv[2] ?? '')]);
            exit((int) $stmt->fetchColumn() > 0 ? 0 : 1);
        } catch (PDOException $e) {
            exit(1);
        }
        // no break
    default:
        fwrite(STDERR, "usage: php dbcheck.php wait|has-cards|has-user <username>\n");
        exit(2);
}
