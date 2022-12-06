<?php
namespace MoneyCollect\Classes;

include_once 'MoneyCollectHttp.php';
include_once 'MoneyCollectLogger.php';

class MoneyCollectIntegration
{
    const VERSION = '1.1';

    const ENV_PRO = 'https://api.moneycollect.com/api/services/v1';
    const ENV_TEST = 'https://sandbox.moneycollect.com/api/services/v1';

    protected $mode;
    protected $_header = array('Content-type' => "application/json");
    protected $payment_url;
    protected $logger = false;
    protected $default_dir;
    protected $request_type = [
        "balance" => "/balance", //查看余额G

        "checkoutSession" => "/checkout/session", //列出所有支付会话G
        "checkout_create" => "/checkout/session/create", //创建会话P
        "checkout_retrieve" => "/checkout/session/{id}", //检索会话G

        "customers" => "/customers", //列出所有客户G
        "customers_create" => "/customers/create", //创建客户P
        "customers_retrieve" => "/customers/{id}", //检索客户G
        "customers_delete" => "/customers/{id}", //删除客户D
        "customers_update" => "/customers/{id}/update", //更新客户P

        "device" => "/0",//装置P

        "dispute_retrieve" => "/dispute/{id}",//找回争议G
        "dispute" => "/disputes",//列出所有争议G
        "dispute_accept" => "/disputes/{id}/accepted",//接受争议P
        "accept_pre_arbitration" => "/disputes/{id}/accepted_pre_arbitration",//接受之前的仲裁P

        "paymentMethods" => "/payment_methods/create", //创建付款方法P
        "paymentMethods_list" => "/payment_methods/list/{customerId}", //列出客户的付款方法G
        "paymentMethods_retrieve" => "/payment_methods/{id}", //检索付款方法G
        "paymentMethods_attach" => "/payment_methods/{id}/attach", //将付款方法附加到客户P
        "paymentMethods_detach" => "/payment_methods/{id}/detach", //从客户身上分离付款方法P
        "paymentMethods_update" => "/payment_methods/{id}/update", //更新付款方法P

        "payment" => "/payment", //列出所有付款G
        "payment_create" => "/payment/create", //创建付款P
        "payment_retrieve" => "/payment/{id}", //取回付款G
        "payment_cancel" => "/payment/{id}/cancel", //取消付款P
        "payment_capture" => "/payment/{id}/capture", //捕获付款P
        "confirmPayment" => "/payment/{id}/confirm", //确认付款P
        "payment_update" => "/payment/{id}/update", //更新付款P

        "payout"=>"/payouts",//列出所有付款G
        "payout_retrieve"=>"/payouts/{id}",//检索付款G

        "refunds"=>"/refunds",//列出所有退款G
        "refund_create"=>"/refunds/create",//创建退款P
        "refund_retrieve"=>"/refunds/{id}",//取回退款G
        "refund_update"=>"/refunds/{id}/update",//更新退款P

        "transactions" => "/transactions",//列出所有交易记录G
        "transactions_retrieve" => "/transactions/{id}",//检索交易G
    ];


    /**
     * 初始化方法
     * @param $private_key // API key
     * @throws \Exception
     */
    public function __construct($private_key)
    {
        if( empty($private_key) ){
            throw new \Exception('Initialization error');
        }

        $this->default_dir = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'log'.DIRECTORY_SEPARATOR;
        $this->_header['Authorization'] = $this->getPrKey($private_key);
        $this->payment_url = ($this->mode == "test" ? self::ENV_TEST :self::ENV_PRO);

    }

    function startLogger($bool = true, $dir = '' )
    {
        if( !empty($dir)){
            $this->default_dir = $dir;
        }

        if( $bool ){
            $this->logger = new MoneyCollectLogger($this->default_dir);
        }else{
            $this->logger = false;
        }

    }

    function isLogger()
    {
        return ($this->logger && is_object($this->logger));
    }

    public static function amountTransform($amount,$currency){
        switch ($currency){
            case strpos('CLP,ISK,VND,KRW,JPY',$currency) !== false:
                return (int)$amount;
            case strpos('IQD,KWD,TND',$currency) !== false:
                return (int)($amount*1000);
            default:
                return (int)($amount*100);
        }
    }

    private function getPrKey($prkey){
        $mode = substr($prkey,0,4);
        if( $mode == 'test' || $mode == 'live' ){
            $this->mode = $mode;
        }
        return 'Bearer '.$prkey;
    }

    /**
     * @param $type
     * @param array $data array('path'=>array(),'query'=>array(),'body'=>array())
     * @return mixed
     * @throws \Exception
     */
    function request($type,array $data = array())
    {

        if( !key_exists($type,$this->request_type) ){
            return array(
                'code' => 'fail',
                'msg' => 'the request_type is non-existent'
            );
        }

        return $this->requestCommon($type,$data);
    }


    private function requestCommon($type,$data)
    {
        $method = 'POST';

        $parameters = array(
            'header' => $this->_header
        );

        if( is_array($data) ){

            if( isset($data['path']) ){
                $parameters['path'] = $data['path'];
            }

            if( isset($data['query']) ){
                $parameters['query'] = $data['query'];
            }

            if( isset($data['body']) ){
                $parameters['body'] = $data['body'];
            }

        }

        if( in_array($type,["balance","checkoutSession","checkout_retrieve","customers","customers_retrieve", "dispute_retrieve","dispute","paymentMethods_list","paymentMethods_retrieve", "payment","payment_retrieve","payout","payout_retrieve","refunds","refund_retrieve","transactions","transactions_retrieve"]) )
        {
            $method = 'GET';
        }

        if($type == "customers_delete"){
            $method = 'DELETE';
        }

        $uri = $this->request_type[$type];

        if( isset($parameters['path']) ){
            foreach ($parameters['path'] as $key => $val){
                $uri = str_replace('{'.$key.'}',$val,$uri);
            }
        }

        if( isset($parameters['query']) ){
            $uri .= '?'.http_build_query($parameters['query']);
        }

        return $this->handle($uri,$parameters,$method);
    }


    private function handle($uri,$parameters,$method='POST')
    {

        if( $this->isLogger() ){
            $this->logger->addLog('request-api : '.$uri,'request');
            $this->logger->addLog('parameters : '.json_encode($parameters),'request');
        }
       $mcHttp = new MoneyCollectHttp($this->payment_url.$uri);

        if( $mcHttp->request($parameters,$method) ){

            if( $this->isLogger() ){
                $this->logger->addLog('response : '.$mcHttp->getResponseInfo('Response'),'request');
            }
            return  $mcHttp->getResponsetoArr();

        }else{ // 请求失败，报错提示
            $info = $mcHttp->getResponseInfo();
            return $info;
        }
    }


    /**
     * @param $request_type 请求类型，自定义
     * @param $request_path api路径，参考文档资料
     * @return $this
     * @throws \Exception
     */
    function addRequest($request_type,$request_path){
        if( isset($this->request_type[$request_type]) ){
            throw new \Exception('the request key is is exists!');
        }
        $this->request_type[$request_type] = $request_path;
        return $this;
    }


    /**
     * 校验结果签名信息
     * @return bool
     */
    function verification($webhook_token)
    {
        // notify 接收验证签名
        if( $_SERVER['REQUEST_METHOD'] == 'POST' ){

            $request_header = getallheaders();

            $data = array(
                'header' =>  [
                    "Request-Time" => $request_header['Request-Time'],
                ],
                'body' => $this->getWebhookData()
            );

            $check_sign_info = $this->signInfo($data,$webhook_token);

            if( $this->isLogger() ){
                $this->logger->addLog('webhook header: '.json_encode($data,JSON_UNESCAPED_SLASHES),'result');//取消了对 / 的编译
                $this->logger->addLog('check sign info : '.$request_header['Signature'].' & '.$check_sign_info,'result');
            }

            return @$request_header['Signature'] == strtoupper( $check_sign_info );

        }

        return false;
    }

    /**
     * 获取webhook内容
     * @return mixed
     */
    function getWebhookData()
    {
        if( empty( $this->receive_data) && $_SERVER['REQUEST_METHOD'] == 'POST' ){
            $this->receive_data = json_decode(file_get_contents( 'php://input' ),true);
        }

        return $this->receive_data;
    }

    private function signInfo($data,$webhook_token){
     $sign_arr = array();
    if( isset($data['header']) ){
        $sign_arr[] = $data['header']['Request-Time'];
    }

    if( isset($data['body']) ){
        $sign_arr[] = json_encode($data['body'],JSON_UNESCAPED_SLASHES);
    }

    $data_str = implode('.',array_filter($sign_arr));

    return hash_hmac('sha256', $data_str,$webhook_token);
    }
}
