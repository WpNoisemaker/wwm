<?php
/**
 * A thin PDO wrapper. One connection, prepared statements only, exceptions on.
 */
declare(strict_types=1);

final class Db
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        $c = App::config('db');
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], (int) $c['port'], $c['name']);
        self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_STRINGIFY_FETCHES  => false,
        ]);
        // The database keeps its own clock (often a US one on shared hosting). Telling it the site's
        // time zone keeps every "2 minutes ago" honest: rows are stamped in the same zone PHP reads them in.
        self::$pdo->exec("SET time_zone = '" . date('P') . "'");
        return self::$pdo;
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(string $sql, array $args = []): array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($args);
        return $st->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function one(string $sql, array $args = []): ?array
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($args);
        $row = $st->fetch();
        return $row === false ? null : $row;
    }

    public static function val(string $sql, array $args = [], mixed $default = null): mixed
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($args);
        $v = $st->fetchColumn();
        return $v === false ? $default : $v;
    }

    public static function run(string $sql, array $args = []): int
    {
        $st = self::pdo()->prepare($sql);
        $st->execute($args);
        return $st->rowCount();
    }

    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql  = sprintf(
            'INSERT INTO `%s` (`%s`) VALUES (%s)',
            $table,
            implode('`,`', $cols),
            implode(',', array_fill(0, count($cols), '?'))
        );
        self::run($sql, array_values($data));
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $table, array $data, string $where, array $args = []): int
    {
        $set = implode(',', array_map(static fn($c) => "`$c`=?", array_keys($data)));
        return self::run("UPDATE `$table` SET $set WHERE $where", array_merge(array_values($data), $args));
    }
}
