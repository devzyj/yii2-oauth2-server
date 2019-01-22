<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server;

use devzyj\oauth2\server\interfaces\ServerRequestInterface;

/**
 * ServerRequest 实现了 [[devzyj\oauth2\server\interfaces\ServerRequestInterface]] 中的方法。
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class ServerRequest extends \yii\web\Request implements ServerRequestInterface
{
    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return parent::getHeaders()->toArray();
    }
    
    /**
     * {@inheritdoc}
     */
    public function getParsedBody()
    {
        return $this->getBodyParams();
    }

    /**
     * {@inheritdoc}
     */
    public function getServerParams()
    {
        return $_SERVER;
    }
}