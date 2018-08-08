<?php
/**
 * Created by PhpStorm.
 * User:  我是一颗菜
 * Date: 2018/6/11
 * Time: 13:34
 */

class Collection extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index()
    {
        //分类数据
        //$categoryData      = $this->db->select('id,name_cn')->get('t_category')->result_array();
        //用户数据
        $userData          = $this->db->select('id,username')->get('t_user')->result_array();
        //店铺数据
        $storeData         = $this->db->select('id,store_code')->where("find_in_set({$_SESSION['id']},owner)!=",0)->get('t_store')->result_array();
        //性别数据
        $sexData           = $this->config->item('sexes_fr');
        //受众类型
        $audiencesData           = $this->config->item('audiences_fr');
        
        
        
        //echo $this->db->last_query();
        //查询条件
        extract($_POST);
        
        $wh = [];
        if(!empty($pro))            $wh["a.title like '%$pro%' or a.title1 like '%$pro%' or a.title_zh like '%$pro%' or a.title1_zh like '%$pro%' or a.cd_id like '%$pro%' or a.sku like '%$pro%' "] = NULL;
        if(!empty($category))       $wh['b.category_name_cn']                 = trim($category);
        if(!empty($gm_ed))          $wh["find_in_set($gm_ed ,a.gmer_id)!="]   = 0;//已跟卖 就是已经存在与集合中
        if(!empty($kd_ed))          $wh["find_in_set($kd_ed ,a.kder_id)!="]   = 0;
        if(!empty($add_time)) {
            $time_s = strtotime(preg_replace('/\(.*\)/', '', str_replace('GMT 0800', 'GMT+0800', $add_time[0])));
            $time_e = strtotime(preg_replace('/\(.*\)/', '', str_replace('GMT 0800', 'GMT+0800', $add_time[1])));
            $wh['a.addtime >=']  = date('Y-m-d H:i:s',$time_s);
            $wh['a.addtime <']   = date('Y-m-d H:i:s',$time_e+86400);
        }
        
        if(!empty($reviews))        $reviews == 1 ? $wh['reviews >'] = 0 : $wh['reviews '] = 0;
        
        //假删除
        $wh['is_deleted']  =1;
        $currentpage = element('currentPage', $_POST, 1);
        $pagesize = element('pageSize', $_POST, 10);
        $page = ($currentpage - 1) * $pagesize;
        
        $table  = 'cd_product a';
        $join   =array();
        $join[] =array('t_collection_set b','b.id = a.url_id','left');
        $fields = 'a.id,a.title,a.title1,a.title_zh,a.title1_zh,a.simg,a.simgs,a.sku,a.navs,a.cd_id,a.price,a.gm_price,a.shipfee,a.stars,a.reviews,a.colors,a.sizes,a.maidian,a.stock,a.desc,a.kder_id,a.gmer_id,b.category_name_cn,b.category_code,b.created_man_name as laiyuan';
        
        $result=$this->getJoinData($table,$join,$wh,$fields,'','',$page,$pagesize);
        //echo $this->db->last_query();
        $all=$this->getJoinData($table,$join,$wh,$fields,'','',0,0);
       // echo $this->db->last_query();
        
        //获取刊登人和跟卖人信息
        foreach($result as &$v){
            if(!empty($v['kder_id'])){
                if(strpos($v['kder_id'],',')!==false){
                    $ids= explode(',',$v['kder_id']);
                    $kder_info = $this->db->select('username')->where_in("id",$ids)->get('t_user')->result_array();
                    foreach($kder_info as $k=>$info){
                        $arr[$k]= $info['username'];
                    }
                    $v['kder_id'] = implode(',',$arr);
                }else{
                    $kder_info = $this->db->select('username')->where('id',$v['kder_id'])->get('t_user')->row_array();
                    $v['kder_id'] = $kder_info['username'];
                }
                
            }else{
                $v['kder_id'] = '';
            }
            if(!empty($v['gmer_id'])){
              if(strpos($v['gmer_id'],',')!==false){
                  $gmer_ids=explode(',',$v['gmer_id']);
                  $gmer_info=$this->db->select('username')->where_in('id',$gmer_ids)->get('t_user')->result_array();
                  foreach($gmer_info as $gm_k=>$gm_v){
                      $gm_arr[$gm_k]=$gm_v['username'];
                  }
                  $v['gmer_id']=implode(',',$gm_arr);
              }else{
                  $gmer_info=$this->db->select('username')->where('id',$v['gmer_id'])->get('t_user')->row_array();
                  $v['gmer_id']=$gmer_info['username'];
              }
            }else{
                $v['gmer_id'] = '';
            }
            
        }
        
        $sub    = (float)0.01;
        $hor    = (float)0.00;
        foreach($result as &$v){
            //刊登价格默认为价格和最低跟卖价中较低的一个-0.01
            if(round(floatval($v['gm_price']),2) <= round($sub,2)){
                $v['price_my'] = bcsub(floatval($v['price']),$sub,2);
            }else{
                $pri = (round(floatval($v['price']),2) > round(floatval($v['gm_price']),2)) ? floatval($v['gm_price']):floatval($v['price']) ;
                $v['price_my'] = bcsub($pri,$sub,2);
            }
            
            //跟卖价格默认为跟卖价 如果跟卖价不存在 就等于售价
            if(floatval($v['gm_price'])===$hor){
                $v['price_my_gm'] = $v['price'];
            }else{
                $v['price_my_gm'] = $v['gm_price'];
            }
            
            $v['price_my_d']   = bcsub($v['price'],1.00,2);
            
            //库存默认设置成100
            $v['stock']        = empty($v['stock']) ?100:$v['stock'];
    
            //删除标识
            $v['checked']=false;
            
        }
        
        
//        $data['categoryData'] = $categoryData;
        $data['total']        = count($all);
        $data['data']         = $result;
        $data['storeData']    = $storeData;
        $data['userData']     = $userData;
        $data['sexData']      = $sexData;
        $data['audiencesData']   = $audiencesData ;
        
        
        
        echo json_encode($data);
       // var_dump($sexData);
        //var_dump($_POST);
       //var_dump($result);
        
    }
    
    //返回子体sku和母体sku
    // 如果是单品 则只有子体sku
    
    public function  kd_before(){
        $posts = $this->input->post(NULL,true);
        $colors=$posts['colors'];
        $sizes =$posts['sizes']  ;
        $simgs =$posts['simgs'];
        $arr = [];
        $sx  = [];
        $data= [];
        $color_res=[];
        $size_res =[];
        
        
        
        if(!empty($sizes)){
            if(strpos($sizes,',') !==false){
                $size_array=explode(',',$sizes);
            }
            if(strpos($sizes,'-') !==false){
                $size_array=explode('-',$sizes);
            }
            if(strpos($sizes,'/') !==false){
                $size_array=explode('/',$sizes);
            }
            if(strpos($sizes,',') ==false && strpos($sizes,'-') ==false &&strpos($sizes,'/') ==false){
                $size_array[]= $sizes;
            }
            
        }else{
            $size_array = '';
        }
        if(!empty($colors)){
            if(strpos($colors,',') !== false){
                $color_array = explode(',',$colors);
                $simg_array  =explode('/n',$simgs);
            }else{
                $color_array[] = $colors;
                $simg_array[]  =$simgs;
            }
        }else{
            
            $color_array = '';
            $simg_array = '';
        }
        
        
    
        if(!empty($colors)){
            //颜色字段不为空
            if(strpos($colors,',') !==false){
                //多个颜色
                $color_arr=explode(',',$colors);
                $simgs_arr=explode("\n",$simgs);
                if(!empty($sizes)){
                    //尺寸字段不为空
                    if(strpos($sizes,',')!==false || strpos($sizes,'/')!==false || strpos($sizes,'-')!==false){
                        //多个尺寸
                        if(strpos($sizes,',')!==false){
                            //sizes 以，分割
                            $size_arr=explode(',',$sizes);
                        
                        }elseif(strpos($sizes,'/')!==false){
                            //sizes 以/分割
                            $size_arr=explode('/',$sizes);
                        
                        }elseif(strpos($sizes,'-')!==false){
                            //sizes以-分割
                            $size_arr=explode('-',$sizes);
                        }
                        for($i=0;$i<count($color_arr);$i++){
                            for($j=0;$j<count($size_arr);$j++){
                                array_push($arr,$color_arr[$i],$size_arr[$j],$simgs_arr[$i]);
                            }
                        }
                    
                    }else{
                        //单个尺寸
                        for($i=0;$i<count($color_arr);$i++){
                            array_push($arr,$color_arr[$i],$sizes,$simgs_arr[$i]);
                        }
                    }
                
                }else{
                    //尺寸字段为空
                    for($i=0;$i<count($color_arr);$i++){
                        array_push($arr,$color_arr[$i],$sizes,$simgs_arr[$i]);
                    }
                }
            
            }else{
                //单个颜色
                if(!empty($sizes)){
                    //尺寸字段不为空
                    if(strpos($sizes,',')!==false || strpos($sizes,'/')!==false || strpos($sizes,'-')!==false){
                        //多个尺寸
                        if(strpos($sizes,',')!==false){
                            //sizes 以，分割
                            $size_arr=explode(',',$sizes);
                        }elseif(strpos($sizes,'/')!==false){
                            //sizes 以/分割
                            $size_arr=explode('/',$sizes);
                        }elseif(strpos($sizes,'-')!==false){
                            //sizes以-分割
                            $size_arr=explode('-',$sizes);
                        }
                        for($j=0;$j<count($size_arr);$j++){
                            array_push($arr,$colors,$size_arr[$j],$simgs);
                        }
                    }else{
                        //单个尺寸
                    
                        array_push($arr,$colors,$sizes,$simgs);
                    }
                }else{
                    //尺寸字段为空
                    array_push($arr,$colors,$sizes,$simgs);
                }
            }
        
        }else{
            //颜色字段为空
            if(!empty($sizes)){
                //尺寸字段不为空
                if(strpos($sizes,',')!==false || strpos($sizes,'/')!==false || strpos($sizes,'-')!==false){
                    //多个尺寸
                    if(strpos($sizes,',')!==false){
                        //sizes 以，分割
                        $size_arr=explode(',',$sizes);
                    
                    }elseif(strpos($sizes,'/')!==false){
                    
                        //sizes 以/分割
                        $size_arr=explode('/',$sizes);
                    }elseif(strpos($sizes,'-')!==false){
                        //sizes以-分割
                        $size_arr=explode('-',$sizes);
                    }
                    for($j=0;$j<count($size_arr);$j++){
                        array_push($arr,$colors,$size_arr[$j],$simgs);
                    }
                }else{
                    //单个尺寸
                    array_push($arr,$colors,$sizes,$simgs);
                }
            }else{
                //尺寸字段为空
                array_push($arr,$colors,$sizes,$simgs);
            }
        }
        $sx=array_chunk($arr,3);
        
        foreach($sx as $k=>$v){
            if(empty($v[0]) && empty($v[1])){
                $data['mt_sku']     = '';
                $data['zt_sku'][$k] = 'cd'.$posts['id'].date('YmdHis').$k;
            }else{
                $data['mt_sku']     = 'cd'.$posts['id'].date('YmdHis');
                $data['zt_sku'][$k] = 'cd'.$posts['id'].date('YmdHis').$k;
               
            }
            
        }
        $res['colorData'] =$color_array;
        $res['sizeData']  =$size_array;
        $res['simgData']  =$simg_array;
        $data['colors']   =$color_array;
        $data['sizes']    =$size_array;
        $data['simg']     =$simg_array;
        $res['data']      = $data;
        echo json_encode($res);
        
    }
    
    //刊登
    //按尺寸和颜色把数据分条存入产品刊登表
    public function  kd()
    {
        $posts    = $this->input->post(NULL,true);
        if(!empty($posts['category_code'])){
            $cat_info =$this->db->from('t_collection_set')->select('category_id')->where('category_code',$posts['category_code'])->get()->row();
        }else{
            $cat_info ='';
        }
        
        $is_ean   =$this->db->select('is_ean')->where('id',$cat_info->category_id)->get('t_category')->row_array();
        $colors   =$posts['colors'];//
        $sizes    =$posts['sizes'];
        $simgs_data= $this->db->select("simgs")->where('id',$posts['id'])->get('cd_product')->row_array();
        if(!empty($simgs_data['simgs'])){
            if(strpos($simgs_data['simgs'],"\n")!==false){
               $simgs=explode("\n",$simgs_data['simgs']);
            }else{
                $simgs[]=$simgs_data;
            }
        }else{
            $simgs='';
        }
        if(!empty($posts['simgData'])){
            $simgskey  =array_unique($posts['simgData']);
    
        }
       
       if(!empty($simgs)){
           foreach($simgskey as $k=>$v){
               $arr[$k]=$simgs[$simgskey[$v]];
               if(empty($arr[$k])){
                   unset($arr[$k]);
               }
           }
       }else{
            $arr='';
       }
        //存放参数
        
        $sx=[];
       if(!empty($colors)){
           if(!empty($sizes)){
               for($i=0;$i<count($colors);$i++){
                   for($j=0;$j<count($sizes);$j++){
                       array_push($sx,$colors[$i],$arr[$i],$sizes[$j]);
                   }
               }
           }else{
               for($i=0;$i<count($colors);$i++){
                       array_push($sx,$colors[$i],$arr[$i],$sizes);
               }
           }
           
       }else{
           if(!empty($sizes)){
              
                   for($j=0;$j<count($sizes);$j++){
                       array_push($sx,$colors,$arr,$sizes[$j]);
                   }
              
           }else{
               array_push($sx,$colors,$arr,$sizes);
           }
           
       }
       $sx_data= array_chunk($sx,3);
       
       $data=array();
       foreach($sx_data as $key=>$v){
           if($is_ean['is_ean'] == 1){
              
               $ean_info=$this->db->select('ean_code')->where('is_use',1)->limit($key+1)->get('t_ean')->result_array();
               $data[$key]['mt_sku']        = empty($posts['mt_sku']) ? '' : $posts['mt_sku'];
               $data[$key]['zt_sku']        = empty($posts['mt_sku']) ? ('cd'.$posts['id'].date('YmdHis').$key ):($posts['mt_sku'].$key);
               $data[$key]['color']         = $sx_data[$key][0];
               $data[$key]['size']          = $sx_data[$key][2];
               $data[$key]['simg']          = $sx_data[$key][1];
               $data[$key]['product_id']    = $posts['id'];
               $data[$key]['title']         = $posts['title'];
               $data[$key]['title1']        = $posts['title1'];
               $data[$key]['maidian']       = $posts['maidian'];
               $data[$key]['desc']          = $posts['desc'];
               $data[$key]['sex']           = $posts['sex'];
               $data[$key]['price']         = $posts['price_my'];
               $data[$key]['audiences']     = $posts['audiences'];
               $data[$key]['store']         = $posts['store'];
               $data[$key]['shipfee']       = $posts['shipfee'];

               $data[$key]['cat_id']        = empty($cat_info)?0:($cat_info->category_id); //分类id

               $data[$key]['kd_man']        = $_SESSION['id'];
               $data[$key]['addtime']       = strtotime(date('Y-m-d H:i:s'));
               $data[$key]['ean']           = $ean_info[$key]['ean_code'];
               $this->db->update('t_ean',array('is_use'=>2,'sku'=>$data[$key]['zt_sku']),array('ean_code'=>$ean_info[$k]['ean_code']));
           }else{
               $data[$key]['zt_sku']        = empty($posts['mt_sku']) ? ('cd'.$posts['id'].date('YmdHis').$key ):($posts['mt_sku'].$key);
               $data[$key]['mt_sku']        = empty($posts['mt_sku']) ? '' : $posts['mt_sku'];
               $data[$key]['color']         = $sx_data[$key][0];
               $data[$key]['size']          = $sx_data[$key][2];
               $data[$key]['simg']          = $sx_data[$key][1];
               $data[$key]['product_id']    = $posts['id'];
               $data[$key]['title']         = $posts['title'];
               $data[$key]['title1']        = $posts['title1'];
               $data[$key]['maidian']       = $posts['maidian'];
               $data[$key]['desc']          = $posts['desc'];
               $data[$key]['sex']           = $posts['sex'];
               $data[$key]['price']         = $posts['price_my'];
               $data[$key]['audiences']     = $posts['audiences'];
               $data[$key]['store']         = $posts['store'];
               $data[$key]['shipfee']       = $posts['shipfee'];
               $data[$key]['cat_id']        = empty($cat_info)?0:($cat_info->category_id); //分类id
               $data[$key]['kd_man']        = $_SESSION['id'];
               $data[$key]['addtime']       = strtotime(date('Y-m-d H:i:s'));
           }
       }
        //存入刊登人id   如果已经有 就用,隔开
        $kd_info  = $this->db->select('kder_id')->where('id',$posts['id'])->get('cd_product')->row_array();
        $kder_res = $this->db->where(array("find_in_set({$_SESSION['id']},kder_id) !="=>0,'id'=>$posts['id']))->get('cd_product')->row_array();
        if(empty($kd_info['kder_id'])){
            $kder = $_SESSION['id'];
        }else{
            $kder = empty($kder_res)?$kd_info['kder_id'].','.$_SESSION['id']:$kd_info['kder_id'];
        }
        $this->db->update('cd_product',array('kder_id'=>$kder),array('id'=>$posts['id']));
        
        //批量插入商品
        //在刊登前需判断  标题重复的不能刊登
        $kd_row=$this->db->select('is_kd')->where('title1',$posts['title1'])->get('kd_product')->row_array();
        if(intval($kd_row['is_kd'])===1){
            re_json([],1,"标题重复，不能刊登");
        }else{
            
                $this->db->insert_batch('kd_product',$data);
                $this->db->update('kd_product',array('is_kd'=>1),array('mt_sku'=>$data[0]['mt_sku']));
                re_json([],0,"刊登成功");
          
           
        }
    }
    
    //传入qu_sku
    public function  gm_before()
    {
       $data=[];
       $qu_sku= 'cd'.date("YmdHis");
       $data['data']['qu_sku'] =$qu_sku;
       echo json_encode($data);
    }
    //跟卖
    //母体不为空不能跟卖
    public  function gm(){
        $id   =$this->input->post('id');
        $data =[];
        $posts= $this->input->post(NULL,true);
        $store_info = $this->db->select('store_code')->where('id',$posts['store'])->get('t_store')->row_array();
        
  
        //获取店铺编号
        $data['price']        = $posts['price_my_gm'];
        $data['shipfee']      = trim($posts['shipfee']);
        $data['stock_1']      = trim($posts['stock_1']);
        $data['stock_2']      = trim($posts['stock_2']);
        $data['stock_3']      = trim($posts['stock_3']);
        $data['stock_4']      = trim($posts['stock_4']);
        $data['stock_5']      = trim($posts['stock_5']);
        $data['stock_6']      = trim($posts['stock_6']);
        $data['commission_rate'] = trim($posts['commission_rate']);
        $data['time_of_pre']  = trim($posts['time_of_pre']);
        $data['d_price']      = trim($posts['price_my_d']);
        $data['status']       =  0;
        $data['pro_id']       = $posts['id'];
        $data['create_time']  = date('Y-m-d H:i:s');
        $data['qd_sku']       = trim($posts['qd_sku']);
        $data['qd_sku_my']    = trim($posts['qd_sku_my']);
        $data['created_man']  = $_SESSION['id'];
        $data['store_id']     = trim($posts['store']);
        $data['store']        = $store_info['store_code'];
        
        //存入跟卖人id   如果已经有 就用,隔开
        $gm_info  =$this->db->select('gmer_id')->where('id',$id)->get('cd_product')->row_array();
        $gmer_res =$this->db->where(array("find_in_set({$_SESSION['id']},gmer_id) !="=>0,'id'=>$id))->get('cd_product')->row_array();
        $gmer     =empty($gm_info['gmer_id'])?$_SESSION['id']:(empty($gmer_res)?$gm_info['gmer_id'].','.$_SESSION['id']:$gm_info['gmer_id']);
        $this->db->update('cd_product',array('gmer_id'=>$gmer),array('id'=>$posts['id']));
        
        $muti_info=$this->db->select('muti')->where('id',$id)->get('cd_product')->row();
        if(empty($muti_info->muti)){
            $this->db->insert('t_gm',$data);
            re_json([],0,"跟卖成功");
        }else{
            re_json([],1,"不能跟卖");
        }
        
    }
    //删除
    public function delete(){
        $id=intval($this->input->get("id"));
        $this->db->where('id',$id)->update("cd_product",array('is_deleted'=>2));
        re_json([],0,'删除成功');
    }
    
    //批量删除
    public function delAll(){
        $post = $this->input->post(NULL,true);
        if(!empty($post)){
            $this->db->where_in('id',$post['id'])->update('cd_product',array('is_deleted'=>2));
            re_json([],0,'删除成功');
        }else{
            re_json([],1,'请选择要批量删除的商品');
        }
       
        
    }
    
    //联表查询
    public function getJoinData($table="", $join='', $where="", $fields="", $order='', $groupby='', $first_row='0', $num='0',$array_in=array()){
        $table = $table==''?$this->table:$table;
        
        if(is_array($join)){
            foreach($join as $key=>$value){
                if($value[2]){
                    $this->db->join($value[0], $value[1],$value[2]);
                }else{
                    $this->db->join($value[0], $value[1]);
                }
            }
        }
        if(!empty($array_in)){
            foreach($array_in as $keyin=>$inkay){
                $this->db->where_in($keyin, $inkay);
            }
        }
        if($where){
            $this->setWhere($where);
        }
        if($fields){
            $this->db->select($fields);
        }
        
        if($order){
            $this->db->order_by($order);
        }
        
        if($groupby){
            $this->db->group_by($groupby);
        }
        
        if($first_row>0){
            $this->db->limit($num,$first_row);
        }elseif($num > 0){
            $this->db->limit($num);
        }
        
        $data = $this->db->get($table)->result_array();
        //echo $this->db->last_query();
        
        return $data;
    }
    
    //类别  远程搜索框
    public function  get_select_cat(){
        $gets      = $this->input->get(NULL,TRUE);
        
        $queryData = strtoupper($gets['queryData']);
        
        
        $wh=[];
        if(!empty($queryData)){
            $wh["name_cn like '$queryData%' or cat_1 like '$queryData%'  or cat_2 like '$queryData%' or cat_3 like '$queryData%' or cat_4 like '$queryData%' "] = null;
        }
        
        if(!empty($queryData)){
            $name_cn = $this->db->select('id,name_cn,cat_2,cat_3,cat_4')->where($wh)->limit(7)->get('t_category')->result_array();
            
            $data['data'] =$name_cn;
            echo   json_encode($data);
        }else{
            return false;
        }
        
        
    }
    
    
    
}