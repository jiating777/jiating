<?php

namespace app\lib\traits;

trait Image
{

    /**
     * 允许最大上传图片数量
     * @var int
     */
    private $_image_max_num = 9;

    /**
     * 允许最大上传图片大小
     * @var int
     */
    private $_image_max_size = 3145728; // 3 * 1024 * 1024

    /**
     * 允许图片类型
     * @var array
     */
    private $_image_allow_types = array('png', 'jpg', 'jpeg','gif');


    /**
     * 将 $_FILES 的多个文件类型格式化
     * @param array $files $_FILES['xxx']
     * @return array 格式化后返回的新数组
     */
    private function _format_files($files) {
        $result = array();
        foreach ($files as $field => $value) {
            if (!is_array($value)) {
                continue;
            }
            foreach ($value as $key => $val) {
                $result[$key][$field] = $val;
            }
        }

        return $result;
    }

    /**
     * 检查上传文件是否合法
     * @param array $files
     * @param string $msg 错误提示语
     * @return boolean TRUE:合法 or FALSE:不合法
     */
    private function _check_images($files, &$msg) {
        if ($this->_image_max_num < count($files)) {
            $msg = '图片数量过多';
            return FALSE;
        }

        foreach ($files as $file) {
            // 检查文件类型
            if (!in_array(strtolower($this->_get_file_ext($file['name'])), $this->_image_allow_types)) {
                $msg = '图片类型非法';
                return FALSE;
            }

            // 检查上传错误
            if (0 !== $file['error']) {
                $msg = '图片文件损坏';
                return FALSE;
            }

            // 检查文件大小
            if ($file['size'] >= $this->_image_max_size) {
                $msg = '图片文件过大';
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * 获取文件后缀名
     * @param string $filename
     * @return string 后缀名
     */
    private function _get_file_ext($filename) {
        return substr(strrchr($filename, '.'), 1);
    }

}