<?php
require_once(__DIR__."/vendor/autoload.php");
use QL\QueryList;
use QL\Ext\PhantomJs;
use QL\Ext\CurlMulti;
use Medoo\Medoo;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

// create a log channel
$log = new Logger('spider');
$log->pushHandler(new StreamHandler(__DIR__.'/monolog.log', Logger::WARNING));

// Initialize
$database = new Medoo([
    'database_type' => 'mysql',
    'database_name' => 'cdiscount',
    'server' => 'localhost',
    'username' => 'root',
    'password' => 'root'
]);

$query = $database->select('t_walmart_set', ['id','url','type'],['status'=>1]);

$ql = QueryList::getInstance();
$ql->use(PhantomJs::class,'/usr/local/bin/phantomjs','browser');

$log->addWarning("开始");
$start_time = microtime(true);
$success = 0;

foreach ($query as $key => $walmart) {
    $index = 0;
    while ($index<=10) {
        $curl_detail_multi  = [];
        $curl_comment_multi = [];
        $commnet_url = 'https://www.walmart.com/reviews/product/';

        $url = str_ireplace(' ','%20',$walmart['url']);

        $ext = '';
        if(preg_match('/#(.*)?/', $url,$match)){
            $url = preg_replace('/#(.*)?/','',$url);
            $ext = $match[0];
        }
        if($index>0){
            $url .= "?page={$index}{$ext}#searchProductResult";
        }

        if($index==0) echo "开始\n";
        echo "执行第".($success+1)."条\n";
        echo "请求链接：".$url."\n";
        $log->addWarning($url,["请求链接"]);

        //列表
        $data = $ql->browser(function (\JonnyW\PhantomJs\Http\RequestInterface $req)use($url){
            $req->setMethod('GET');
            $req->setUrl($url);
            $req->setTimeout(10000); // 10 seconds
            $req->setDelay(3); // 3 seconds 为了js动态内容加载完全
            return $req;
        })->rules([])->query()->getHtml();
        echo "列表数据：\n";

        $html = QueryList::html($data);
        $items = $html->find('.search-product-result .search-result-gridview-items>li.Grid-col')->htmls();
        foreach ($items as $key => $li) {
            $li = QueryList::html($li);

            $save_data = [];
            $href = $li->find('.product-title-link')->attr('href');
            if(empty($href)){
                continue;
            }

            if(preg_match('/\/([\d]{6,})/',$href,$match)){
                $save_data['goods_code'] = $match[1];
            }

            if(empty($save_data['goods_code'])){
                continue;
            }

            $save_data['href'] = "https://www.walmart.com".$href;
            $save_data['walmart_id']  = $walmart['id'];
            $save_data['type']        = $walmart['type'];

            $img = $li->find('.search-result-productimage .Tile-img')->attr('src');
            if($img){
                $save_data['img'] = preg_replace(['/odnHeight=200/','/odnWidth=200/'], ['/odnHeight=450/','/odnWidth=450/'], $img);
            }

            $save_data['sale_description'] = $li->find('.search-result-flag .prod-FlagList-container .flag')->text();
            $save_data['price'] = $li->find('.search-result-productprice.gridview .price-main-block')->text();
            $save_data['seller_min_priece'] = $save_data['price'];
            $save_data['evaluation_score'] = $li->find('.search-result-product-rating .stars-container')->text();
            $save_data['name'] = $li->find('.product-title-link')->text();

            if($res = $database->select('t_walmart_goods','id',['goods_code'=>$save_data['goods_code']])){
                $save_data['updated_at']        =  date('Y-m-d H:i:s');
                $database->update('t_walmart_goods',$save_data,['id'=>$res[0]]);
            }else{
                $save_data['created_at']        =  date('Y-m-d H:i:s');
                $database->Insert('t_walmart_goods',$save_data);
            }

            echo "列表保存数据：\n";
            print_r($save_data);

            array_push($curl_detail_multi, $save_data['href']);
            array_push($curl_comment_multi, $commnet_url.$save_data['goods_code']);
        }
        $ql->destruct();
        //详情
        $ql->use(CurlMulti::class,'curlMulti');
        $ql->curlMulti($curl_detail_multi)
        ->success(function (QueryList $ql,CurlMulti $curl,$r) use($database){
            echo "Current url:{$r['info']['url']} \r\n";
            $data = $ql->rules([])->query()->getHtml();
            $html = QueryList::html($data);

            $save_data                          = [];
            if(preg_match('/\/([\d]{6,})/',$r['info']['url'],$match)){
                $save_data['goods_code'] = $match[1];
            }else{
                return false;
            }

            $name = $html->find('.hf-PositionedRelative .ProductTitle>.prod-ProductTitle>div')->text();
            $walmart_number = $html->find('.hf-PositionedRelative .prod-productsecondaryinformation .wm-item-number')->text();
            $sku_info = $html->find('.hf-PositionedRelative .prod-VariantsSection')->text();
            $sku_attr = $html->find('.hf-PositionedRelative .prod-VariantsSection .variant-category-container')->attr('data-tl-id');
            $free_shipping_msg = $html->find('.hf-PositionedRelative .prod-ShippingFulfillmentSection .prod-fulfillment-messaging-text>div>div:not(".fulfillment-secondary-row"]')->text();
            $seller_info = $html->find('.hf-PositionedRelative .secondary-bot-container .secondary-bot .btn-compare"]')->text();
            $seller_price = $html->find('.hf-PositionedRelative .sbot-Container  .secondary-bot-container .secondary-bot .marketplace-body .seller-offer-sort-active .seller-offer-sm-price"]')->texts();
            $brand = $html->find('.hf-PositionedRelative .prod-productsecondaryinformation .secondary-info-margin-right .prod-brandName"]')->text();
            $evaluation = $html->find('.hf-PositionedRelative .prod-productsecondaryinformation .stars-reviews .stars-reviews-count-node')->text();
            $price = $html->find('.hf-PositionedRelative .prod-PriceHero .price-group:even')->attrs('aria-label');

            $save_data['name']              = $name;
            $save_data['is_owner']          = !empty($walmart_number)?1:0;
            if(!empty($seller_info) && preg_match('/\d+/',$seller_info,$match)){
                $save_data['seller_num']       = $match[0];
            }else{
                $save_data['seller_num']       = 1;
            }

            $save_data['brand_name'] = $brand;
            $save_data['evaluation_num'] = 0;
             $save_data['shipping_fee']       = $free_shipping_msg;
            if(preg_match('/[1-9]\d*.\d*|0.\d*[1-9]\d*/',$evaluation,$match)){
                $save_data['evaluation_num'] = $match[0];
            }

            $attribute = '';
            if(!empty($sku_attr) && preg_match('/color/',$sku_attr)){
                $attribute .= '颜色,';
            }

            if(!empty($sku_attr) && preg_match('/size/',$sku_attr)){
                $attribute .= '尺码,';
            }


            if(empty($sku_info)){
                $attribute .= '单属性';
            }else{
                $attribute .= '多属性';
            }

            $save_data['attribute_description'] = $attribute;

            $seller_price_arr = [];
            foreach($seller_price as $val){
                if(preg_match('/[1-9]\d*.\d*|0.\d*[1-9]\d*/',$val,$match)){
                    array_push($seller_price_arr,round($match[0],2));
                }
            }
            if($seller_price_arr){
                $save_data['seller_min_priece'] = min($seller_price_arr);
            }

            if(!empty($price)){
                $save_data['price'] = count($price)==2?$price[0].'-'.$price[1]:$price[0];
            }

            if($res = $database->select('t_walmart_goods','id',['goods_code'=>$save_data['goods_code']])){
                $save_data['updated_at']        =  date('Y-m-d H:i:s');
                $database->update('t_walmart_goods',$save_data,['id'=>$res[0]]);
            }else{
                $save_data['created_at']        =  date('Y-m-d H:i:s');
                $database->Insert('t_walmart_goods',$save_data);
            }

            echo "详情保存数据：\n";
            print_r($save_data);
        })->error(function ($errorInfo,CurlMulti $curl){
            echo "Current url:{$errorInfo['info']['url']} \r\n";
            print_r($errorInfo['error']);
        })->start([
            // 最大并发数，这个值可以运行中动态改变。
            'maxThread' => 10,
            // 触发curl错误或用户错误之前最大重试次数，超过次数$error指定的回调会被调用。
            'maxTry' => 3,
            // 全局CURLOPT_*
            'opt' => [
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_RETURNTRANSFER => true
            ],
            // 缓存选项很容易被理解，缓存使用url来识别。如果使用缓存类库不会访问网络而是直接返回缓存。
            'cache' => ['enable' => false, 'compress' => false, 'dir' => null, 'expire' =>86400, 'verifyPost' => false]
        ]);
        $ql->destruct();
        //评论
        $ql->use(CurlMulti::class,'curlMulti');
        $ql->curlMulti($curl_comment_multi)
        ->success(function (QueryList $ql,CurlMulti $curl,$r) use($database){
            echo "Current url:{$r['info']['url']} \r\n";
            $data = $ql->rules([])->query()->getHtml();
            $html = QueryList::html($data);

            $evaluation = $html->find('.ProductReviewsWrapper .CustomerReviews-container .review-footer .review-footer-submissionTime')->texts();

            $save_data                          = [];
            $save_data['evaluation_item']       = 0;

            if(preg_match('/\/([\d]{6,})/',$r['info']['url'],$match)){
                $save_data['goods_code'] = $match[1];
            }else{
                return false;
            }

            foreach($evaluation as $val){
                if(strtotime('2018-00-0 00:0:0')<=strtotime($val) && strtotime($val)<strtotime('2019-00-0 00:0:0')){
                    $save_data['evaluation_item'] ++;
                }
            }

            if($res = $database->select('t_walmart_goods','id',['goods_code'=>$save_data['goods_code']])){
                $save_data['updated_at']        =  date('Y-m-d H:i:s');
                $database->update('t_walmart_goods',$save_data,['id'=>$res[0]]);
            }else{
                $save_data['created_at']        =  date('Y-m-d H:i:s');
                $database->Insert('t_walmart_goods',$save_data);
            }


            echo "评论保存数据：\n";
            print_r($save_data);
        })->error(function ($errorInfo,CurlMulti $curl){
            echo "Current url:{$errorInfo['info']['url']} \r\n";
            print_r($errorInfo['error']);
        })->start([
            // 最大并发数，这个值可以运行中动态改变。
            'maxThread' => 10,
            // 触发curl错误或用户错误之前最大重试次数，超过次数$error指定的回调会被调用。
            'maxTry' => 3,
            // 全局CURLOPT_*
            'opt' => [
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 1,
                CURLOPT_RETURNTRANSFER => true
            ],
            // 缓存选项很容易被理解，缓存使用url来识别。如果使用缓存类库不会访问网络而是直接返回缓存。
            'cache' => ['enable' => false, 'compress' => false, 'dir' => null, 'expire' =>86400, 'verifyPost' => false]
        ]);
        $ql->destruct();

        $index ++;
        $success++;
    }

    $sum = $database->count('t_walmart_goods',['walmart_id'=>$walmart['id']]);
    $last_time = time();
    $database->update('t_walmart_goods',['last_time'=>$last_time],['walmart_id'=>$walmart['id']]);
    $database->update('t_walmart_set',['sum'=>$sum,'last_time'=>$last_time],['id'=>$walmart['id']]);
    $end_time = microtime(true);
    $log->addWarning("一共".$success."执行条数据，耗时：".round($end_time-$start_time,3)."秒");
    $log->addWarning("结束");

    echo "一共执行".$success."条链接数据，耗时：".round($end_time-$start_time,3)."秒\n";
    echo "结束\n";
}



