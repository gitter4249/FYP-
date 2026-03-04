<?php
// 1. 开启 Session 以便能访问到当前会话
session_start();

// 2. 清空所有 Session 变量
$_SESSION = array();

// 3. 彻底销毁 Session 会话
session_destroy();

// 4. 重定向回首页
// 因为 logout.php 在 customer/ 文件夹里，首页在上一级，所以用 ../
header("Location: ../homepage.php");
exit();
?>