<?php
/**
 * 路径: customer/logout.php
 * 功能: 彻底销毁客户会话并安全重定向
 */

// 1. 启动 Session 以便操作当前会话
// 即使是要销毁它，也必须先启动它
session_start();

// 2. 清空内存中的所有 Session 变量
// 将全局变量 $_SESSION 设置为空数组
$_SESSION = array();

// 3. 彻底抹除客户端的 Session Cookie (重要补丁)
// 如果 Session 是基于 Cookie 的（默认都是），则让该 Cookie 立即过期
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000, // 设置为过去的时间，强制浏览器删除
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// 4. 在服务器端彻底销毁 Session 数据文件
session_destroy();

// 5. 重定向回首页
// 提示：../ 表示跳出当前的 customer 文件夹，回到根目录的 homepage.php
header("Location: ../homepage.php?logout=success");
exit();
?>