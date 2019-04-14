<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace HZEX\Think\Response;

use finfo;
use SplFileObject;
use think\Exception;
use think\Response;

class Download2 extends Response
{
    protected $expire = 360;
    protected $fileName = '';
    protected $mimeType;
    protected $isContent = false;
    protected $openinBrowser = false;

    const DTAT_FORMAT = 'D, d M Y H:i:s \G\M\T'; // DATE_RFC1123

    /**
     * @param string $data
     * @param string $type
     * @param int $code
     * @param array $header
     * @param array $options
     * @return static
     */
    public static function create($data = '', $type = '', $code = 200, array $header = [], $options = [])
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return parent::create($data, $type, $code, $header, $options);
    }

    /**
     * 处理数据
     * @access protected
     * @param  mixed $data 要处理的数据
     * @return mixed
     * @throws \Exception
     */
    protected function output($data)
    {
        if (!$this->isContent && !is_file($data)) {
            throw new Exception('file not exists:' . $data);
        }

        ob_end_clean();

        $newData = new SplFileObject($data, 'r');

        if (!empty($this->fileName)) {
            $fileName = $this->fileName;
        } else {
            $fileName = !$this->isContent ? $newData->getFilename() : '';
        }

        if ($this->isContent) {
            $mimeType = $this->mimeType;
            $size     = strlen($data);
            // 设置最后更改时间
            $this->lastModified(time());
        } else {
            $newData->rewind();
            $mimeType = $this->mimeType ?: $this->getContentMimeType($newData->fread($newData->getSize()));
            $size     = $newData->getSize();
            // 设置最后更改时间
            $this->lastModified($newData->getMTime());
        }

        if ($this->openinBrowser) {
            $content_disposition = 'inline';
        } else {
            $fileName = rawurlencode($fileName);
            $content_disposition = "attachment; filename=\"{$fileName}\"; filename*=utf-8''{$fileName}";
        }

        $this->header['Pragma']                    = 'public';
        $this->header['Content-Type']              = $mimeType ?: 'application/octet-stream';
        $this->header['Cache-control']             = 'max-age=' . $this->expire;
        $this->header['Content-Disposition']       = $content_disposition;
        $this->header['Content-Length']            = $size;
        $this->header['Content-Transfer-Encoding'] = 'binary';
        $this->header['Expires'] = gmdate(self::DTAT_FORMAT, time() + $this->expire);


        if ($this->app->request->isHead()) {
            $this->data($data = '');
        }
        // 禁用缓存
        $this->allowCache(false);

        return $data;
    }

    protected function sendData($data)
    {
        if (empty($data)) {
            return;
        }
        if ($this->isContent) {
            echo $data;
        } else {
            // 清空输出缓冲区
            if (ob_get_length() > 0) {
                ob_clean();
            }
            // 输出缓存全部内容并关闭输出缓存
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            readfile($data);
        }
    }

    /**
     * 设置是否为内容 必须配合mimeType方法使用
     * @access public
     * @param  bool $content
     * @return $this
     */
    public function isContent($content = true)
    {
        $this->isContent = $content;
        return $this;
    }

    /**
     * 设置有效期
     * @access public
     * @param  int $expire 有效期
     * @return $this
     */
    public function expire($expire)
    {
        $this->expire = $expire;
        return $this;
    }

    /**
     * @param $time
     * @return $this|Response
     */
    public function lastModified($time)
    {
        $this->header['Last-Modified'] = gmdate(self::DTAT_FORMAT, $time);
        return $this;
    }

    /**
     * 设置文件类型
     * @access public
     * @param  string $mimeType
     * @return $this
     */
    public function mimeType($mimeType)
    {
        $this->mimeType = $mimeType;
        return $this;
    }

    /**
     * 获取内容Mime
     * @param string $content
     * @return mixed
     */
    protected function getContentMimeType($content)
    {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        return $finfo->buffer($content);
    }

    /**
     * 设置下载文件的显示名称
     * @access public
     * @param  string $filename 文件名
     * @param  bool   $extension 后缀自动识别
     * @return $this
     */
    public function name($filename, $extension = true)
    {
        $this->fileName = $filename;

        if ($extension && false === strpos($filename, '.')) {
            $this->fileName .= '.' . pathinfo($this->data, PATHINFO_EXTENSION);
        }

        return $this;
    }

    /**
     * 设置是否在浏览器中显示文件
     * @access public
     * @param  bool  $openinBrowser 是否在浏览器中显示文件
     * @return $this
     */
    public function openinBrowser($openinBrowser)
    {
        $this->openinBrowser = $openinBrowser;
        return $this;
    }
}
