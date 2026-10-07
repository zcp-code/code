<?php
/**
 * 单元测试基类
 */

// 注意：本文件由 console.php 在加载 thinkphp/base.php 后 require_once
// 这里不再重复定义常量（已被 base.php 定义过）

// 注册我们的业务扩展 autoloader（如果还没注册）
spl_autoload_register(function ($class) {
    if (strpos($class, 'extend\\gch\\') === 0) {
        $rel = str_replace('extend\\gch\\', '', $class);
        $file = __DIR__ . '/../extend/gch/' . $rel . '.php';
        if (file_exists($file)) require_once $file;
    }
});

class TestCase
{
    protected $pass = 0;
    protected $fail = 0;
    protected $currentTest = '';

    /**
     * 子类覆盖此方法
     */
    public function run()
    {
        return ['pass' => 0, 'fail' => 0];
    }

    /**
     * 断言相等（宽松比较，处理 PDO 返回的字符串 ID）
     */
    protected function assertEquals($expected, $actual, $msg = '')
    {
        // 处理字符串数字和整数的比较
        if ($expected == $actual && gettype($expected) == gettype($actual)) {
            $this->pass++;
            $this->printOk($msg ?: 'assertEquals');
        } elseif ($expected == $actual) {
            // 类型不同但值相等（如 '1' == 1）
            $this->pass++;
            $this->printOk($msg ?: 'assertEquals (类型转换)');
        } else {
            $this->fail++;
            $this->printFail($msg ?: 'assertEquals',
                "expected: " . var_export($expected, true) . " (" . gettype($expected) . ")\n  actual: " . var_export($actual, true) . " (" . gettype($actual) . ")");
        }
    }

    /**
     * 断言为真
     */
    protected function assertTrue($actual, $msg = '')
    {
        if ($actual === true) {
            $this->pass++;
            $this->printOk($msg ?: 'assertTrue');
        } else {
            $this->fail++;
            $this->printFail($msg ?: 'assertTrue', var_export($actual, true));
        }
    }

    /**
     * 断言为假
     */
    protected function assertFalse($actual, $msg = '')
    {
        if ($actual === false) {
            $this->pass++;
            $this->printOk($msg ?: 'assertFalse');
        } else {
            $this->fail++;
            $this->printFail($msg ?: 'assertFalse', var_export($actual, true));
        }
    }

    /**
     * 断言非空
     */
    protected function assertNotEmpty($actual, $msg = '')
    {
        if (!empty($actual)) {
            $this->pass++;
            $this->printOk($msg ?: 'assertNotEmpty');
        } else {
            $this->fail++;
            $this->printFail($msg ?: 'assertNotEmpty', var_export($actual, true));
        }
    }

    /**
     * 输出绿色 OK
     */
    private function printOk($msg)
    {
        echo "  \033[32m✓\033[0m $msg\n";
    }

    /**
     * 输出红色 FAIL
     */
    private function printFail($msg, $detail = '')
    {
        echo "  \033[31m✗\033[0m $msg\n";
        if ($detail) {
            echo "      $detail\n";
        }
    }

    /**
     * 子类开始时调用
     */
    protected function startTest($name)
    {
        $this->currentTest = $name;
        echo "\n\033[1m$name\033[0m\n";
    }
}
