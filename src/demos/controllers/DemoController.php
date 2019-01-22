<?php
/**
 * @link https://github.com/devzyj/yii2-oauth2-server
 * @copyright Copyright (c) 2018 Zhang Yan Jiong
 * @license http://opensource.org/licenses/BSD-3-Clause
 */
namespace devzyj\yii2\oauth2\server\demos\controllers;

use Yii;
use yii\widgets\ActiveForm;
use yii\web\Response;
use yii\helpers\Url;
use devzyj\yii2\oauth2\server\demos\models\DemoLoginForm;
use devzyj\yii2\oauth2\server\demos\models\DemoAuthorizationForm;
use devzyj\yii2\oauth2\server\entities\ClientEntity;
use devzyj\yii2\oauth2\server\entities\ScopeEntity;

/**
 * DemoController class.
 * 
 * @author ZhangYanJiong <zhangyanjiong@163.com>
 * @since 1.0
 */
class DemoController extends \yii\web\Controller
{
    /**
     * @var \devzyj\yii2\oauth2\server\Module
     */
    public $module;
    
    /**
     * @var null|string|false
     */
    public $layout = false;
    
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        
        $this->viewPath = $this->module->getBasePath() . DIRECTORY_SEPARATOR . 'demos' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $this->id;
    }
    
    /**
     * 用户登录。
     */
    public function actionLogin()
    {
        // 获取请求参数。
        $request = Yii::$app->getRequest();
        $clientId = $request->getQueryParam('client_id');
        $scope = $request->getQueryParam('scope');
        $scopes = $scope ? explode(' ', $scope) : [];
        $returnUrl = $request->getQueryParam('return_url');

        /* @var $model DemoLoginForm */
        $model = Yii::createObject(DemoLoginForm::class);
        
        // 处理提交后的数据。
        if ($model->load($request->post())) {
            if ($request->getIsAjax()) {
                // AJAX 数据验证。
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } elseif ($model->login($this->getUser())) {
                // 登录成功。
                return $this->redirect($returnUrl);
            }
        }
        
        // 显示登录页面。
        return $this->render('login', [
            'model' => $model,
            'clientEntity' => $this->getClientEntity($clientId),
            'scopeEntities' => $this->getScopeEntities($scopes),
            'authorizationUrl' => $this->makeAuthorizationUrl(),
        ]);
    }
    
    /**
     * 用户确认授权。
     * 
     * @todo 显示的权限列表中，已登录用户的有效权限判断。
     */
    public function actionAuthorization()
    {
        // 获取请求参数。
        $request = Yii::$app->getRequest();
        $clientId = $request->getQueryParam('client_id');
        $scope = $request->getQueryParam('scope');
        $scopes = $scope ? explode(' ', $scope) : [];
        $returnUrl = $request->getQueryParam('return_url');

        /* @var $model DemoAuthorizationForm */
        $model = Yii::createObject(DemoAuthorizationForm::class);

        // 处理提交后的数据。
        if ($model->load($request->post())) {
            if ($request->getIsAjax()) {
                // AJAX 数据验证。
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model);
            } elseif ($model->authorization($this->getUser())) {
                // 授权成功。
                return $this->redirect($returnUrl);
            }
        }
        
        // 显示登录页面。
        return $this->render('authorization', [
            'model' => $model,
            'clientEntity' => $this->getClientEntity($clientId),
            'scopeEntities' => $this->getScopeEntities($scopes),
            'user' => $this->getUser(),
            'loginUrl' => $this->makeLoginUrl(),
            'logoutUrl' => Url::to(['logout']),
        ]);
    }
    
    /**
     * 注销用户。
     */
    public function actionLogout()
    {
        $this->getUser()->logout();
        return $this->redirect(Yii::$app->getRequest()->getReferrer());
    }
    
    /**
     * 获取授权用户。
     * 
     * @return User
     */
    protected function getUser()
    {
        return $this->module->getUser();
    }
    
    /**
     * 获取客户端。
     * 
     * @param string $clientId
     * @return ClientEntity
     */
    protected function getClientEntity($clientId)
    {
        return ClientEntity::findOneByIdentifier($clientId);
    }
    
    /**
     * 获取权限。
     * 
     * @param string[] $scopes
     * @return ScopeEntity[]
     */
    protected function getScopeEntities($scopes)
    {
        if (empty($scopes)) {
            return [];
        }
        
        return ScopeEntity::findAll(['identifier' => $scopes]);
    }

    /**
     * 构造登录地址。
     *
     * @return string
     */
    protected function makeLoginUrl()
    {
        return $this->makeUrl($this->module->loginUrl);
    }

    /**
     * 构造确认授权地址。
     *
     * @return string
     */
    protected function makeAuthorizationUrl()
    {
        return $this->makeUrl($this->module->authorizationUrl);
    }
    
    /**
     * 构造 URL。
     * 
     * @param string|array $url
     * @return string
     */
    protected function makeUrl($url)
    {
        if ($url === null) {
            return '';
        }
        
        $request = Yii::$app->getRequest();
        $params['client_id'] = $request->getQueryParam('client_id');
        $params['scope'] = $request->getQueryParam('scope');
        $params['return_url'] = $request->getQueryParam('return_url');

        $url = Url::to($url);
        if (strpos($url, '?') === false) {
            return $url . '?' . http_build_query($params);
        } else {
            return $url . '&' . http_build_query($params);
        }
        
    }
}
