<?php
/**
 * Created by JetBrains PhpStorm.
 * User: liz
 * Date: 13-11-25
 * Time: 下午4:47
 * To change this template use File | Settings | File Templates.
 */
/**
 * 微商城用户中心：地址管理
*/
class Member_addressApp extends MallbaseApp{
    //所有的收货地址
    function all(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");

        $model_address =& m('address');
        $addresses = $model_address->find("user_id = " . $userId);
        $this->assign('addresses', $addresses);
        $this->assign('count', count($addresses) -1);
        $this->display("member_address.all.html");

    }
    function addAddress(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");

        $region_mod = &m("region");
        $provinces = $region_mod->find("parent_id = 1 and dropState =  1");
        $model_address =& m('address');
        $addresses = $model_address->get("user_id=".$userId);
        $this->assign("addresses",$addresses);
        $this->assign("provinces",$provinces );
        $this->display('member_address.add.html');
    }

    //添加收货地址
    function add(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $memberMod = &m("member");
        $user = $memberMod->get("user_id=".$userId);

        if (!IS_POST){
            $this->assign('act', 'add');
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->addAddress();
        }else{
            /* 必须要填手机号码 */
            if ( !$_POST['phone_mob']){
                $this->pop_warning('phone_required');
                return;
            }

            $model_address =& m('address');
            //0表示默认的收货地址，1不是默认收货地址
            $state = intval($_POST['state']);
            if($state ==0){
                $model_address->edit("user_id=".$user['user_id'],array("state"=>1));
            }

            $data = array(
                'user_id'       => $userId,
                'consignee'     => $_POST['consignee'],
                'region_id'     => $_POST['region_id'],
                'region_name'   => $_POST['region_name'],
                'address'       => $_POST['address'],
                'zipcode'       => $_POST['zipcode'],
                'phone_mob'     => $_POST['phone_mob'],
                'state'          => $state,
            );

            $model_address =& m('address');
            $address_id = $model_address->add($data);
            if (!$address_id){
                $this->pop_warning($model_address->get_error());
                return;
            }
            $this->assign("user",$user);
            header("Location: ". SITE_URL . "/weixin/index.php?app=member_address&act=all");
        }
    }

    //删除收货地址
    function delete(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");

        $addr_id = isset($_GET['addr_id']) ? trim($_GET['addr_id']) : 0;
        if (!$addr_id){
            $this->json_result("该地址不存在");
            return;
        }
        $model_address  =& m('address');
        $drop_count = $model_address->drop("addr_id= " .$addr_id." and user_id=".$userId);
        /* 没有可删除的项 */
        if (!$drop_count){
            $this->json_result($drop_count,'该地址不存在');
            return;
        }
        //出错了
        if ($model_address->has_error()){
            $this->show_warning($model_address->get_error());
            return;
        }
        $this->json_result($drop_count,"删除成功！");
    }

    function editPage(){
        $this->isLogin();

        $region_mod = &m("region");
        $provinces = $region_mod->find("parent_id = 1 and dropState =  1");
        $this->assign("provinces",$provinces );

        $addr_id = $_GET['addr_id'];
        $model_address  =& m('address');
        $address = $model_address->get("addr_id=".$addr_id);
        if(!$address){
            header("Location: ". SITE_URL . "/weixin/index.php?app=member_address&act=all");
        }else{
            $this->assign("address",$address);
            $this->display("member_address.edit.html");
        }
    }
    function edit(){
        $this->isLogin();
        $userId = $this -> visitor ->get("user_id");
        $user_mod = &m("member");
        $user = $user_mod->get('user_id='.$userId);

        if (!IS_POST){
            $this->assign('act', 'all');
            header('Content-Type:text/html;charset=' . CHARSET);
            $this->addAddress();
        }else{
            /* 必须要填手机号码 */
            if ( !$_POST['phone_mob']){
                $this->pop_warning('phone_required');
                return;
            }

            $model_address =& m('address');
            //0表示默认的收货地址，1不是默认收货地址
            $state = intval($_POST['state']);
            if($state ==0){
                $model_address->edit("user_id=".$user['user_id'],array("state"=>1));
            }
            $address_id = $_POST['addr_id'];
            if(!$address_id){
                $this->pop_warning('phone_required');
                return;
            }
            $regionName =$_POST['region_name'];
            $regionName = empty($regionName) ? $_POST['historyRegionName'] : $_POST['region_name'];

            $data = array(
                'user_id'       => $userId,
                'consignee'     => $_POST['consignee'],
                'region_id'     => $_POST['region_id'],
                'region_name'   => $regionName,
                'address'       => $_POST['address'],
                'zipcode'       => $_POST['zipcode'],
                'phone_mob'     => $_POST['phone_mob'],
                'state'          => $state,
            );

            $model_address =& m('address');
            $addressId = $model_address->edit("addr_id=".$address_id,$data);
            header("Location: ". SITE_URL . "/weixin/index.php?app=member_address&act=all");
        }
    }


    function select(){
        in_array($_GET['type'], array('region', 'gcategory')) or $this->json_error('invalid type');
        $pid = empty($_GET['pid']) ? 0 : $_GET['pid'];
        $type = $_GET['type'];
        switch ($type){
            case 'region':
                $mod_region =& m('region');
                $regions = $mod_region->get_list($pid);
                foreach ($regions as $key => $region){
                    $regions[$key]['region_name'] = htmlspecialchars($region['region_name']);
                }
                $this->json_result(array_values($regions));
                break;
            case 'gcategory':
                $mod_gcategory =& m('gcategory');
                $cates = $mod_gcategory->get_list($pid, true);
                foreach ($cates as $key => $cate){
                    $cates[$key]['cate_name'] = htmlspecialchars($cate['cate_name']);
                }
                $this->json_result(array_values($cates));
                break;
        }
    }

}