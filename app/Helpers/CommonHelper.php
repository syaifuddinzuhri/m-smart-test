<?php

if (!function_exists('generate_exam_system_id')) {
    function generate_exam_system_id($userId, $examId)
    {
        return strtoupper(substr(md5($userId . $examId), 0, 12));
    }
}
