<?php
/**
 * 库存分布式锁（基于文件锁，简单实现）
 * 用于预订创建/取消的并发控制
 * 生产环境建议改为 Redis lock
 */

namespace extend\gch;

use think\Log;

class Inventory
{
    /**
     * 加锁并执行
     *
     * @param string $key    锁键（如 goods_id）
     * @param callable $fn 要执行的业务逻辑
     * @param int $timeout 超时（秒）
     * @return mixed 业务逻辑返回值
     * @throws \Exception
     */
    public static function lock($key, callable $fn, $timeout = 5)
    {
        $lockFile = self::lockFile($key);
        $start = microtime(true);
        $fp = null;

        try {
            // 等待锁
            do {
                $fp = @fopen($lockFile, 'w+');
                if ($fp && flock($fp, LOCK_EX | LOCK_NB)) {
                    break;
                }
                if ($fp) {
                    fclose($fp);
                    $fp = null;
                }
                if ((microtime(true) - $start) > $timeout) {
                    throw new \Exception('系统繁忙，请稍后重试');
                }
                usleep(100000); // 100ms
            } while (true);

            // 执行业务逻辑
            $result = $fn();

            return $result;
        } finally {
            if ($fp) {
                flock($fp, LOCK_UN);
                fclose($fp);
            }
            @unlink($lockFile);
        }
    }

    /**
     * 计算可预订数量
     *
     * @param int $totalStock
     * @param int $reservedQty
     * @return int
     */
    public static function available($totalStock, $reservedQty)
    {
        return max(0, (int)$totalStock - (int)$reservedQty);
    }

    /**
     * 锁文件路径
     */
    private static function lockFile($key)
    {
        $dir = RUNTIME_PATH . 'lock' . DS;
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        return $dir . md5((string)$key) . '.lock';
    }
}
