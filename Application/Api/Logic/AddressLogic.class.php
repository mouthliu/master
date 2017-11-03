<?php
namespace Api\Logic;
/**
 * Class AddressLogic
 * @package Api\Logic
 * 送货地址模块
 */
class AddressLogic extends BaseLogic{
    /*
     * 新增收货地址
     * 收货人姓名  name
     * 联系方式    telephone
     * 收货人地址  address_info
     */
    public function addAddress($request = array()){
        $member = $this ->searchMember($request['token']);

        //向地址表写入数据
        $data['m_id']        = $member['id'];
        $data['name']        = $request['name'];
        $data['telephone']   = $request['telephone'];
        $data['address_info']= $request['address_info'];
        $data['province']    = $request['province_id'];
        $data['city']        = $request['city_id'];
        $data['area']        = $request['area_id'];
        $data['is_default']  = 2;
        $data['create_time'] = time();
        $data['status']      = 1;
        $address = $this ->easyMysql('Address','1','',$data);
        if(!$address){
            apiResponse('0','新增收货地址失败');
        }

        apiResponse('1','新增收货地址成功');
    }

    /*
     * 修改收货地址
     * 用户token  token
     * 地址ID     address_id
     * 收货人姓名 name
     * 联系方式   telephone
     * 收货地址   address_info
     * 所在省     province_id
     * 所在市     city_id
     * 所在区     area_id
     */
    public function modifyAddress($request = array()){
        $member = searchMember($request['token']);
        //收货人姓名可以为空
        if($request['name']){
            $data['name'] = $request['name'];
        }
        //联系方式可以为空
        if($request['telephone']){
            $data['telephone'] = $request['telephone'];
        }
        //收货地址可以为空
        if($request['address_info']){
            $data['address_info'] = $request['address_info'];
        }
        //选择所在省可以为空
        if($request['province_id']){
            $data['province'] = $request['province_id'];
        }
        //选择所在市可以为空
        if($request['city_id']){
            $data['city'] = $request['city_id'];
        }
        //选择所在省可以为空
        if($request['area_id']){
            $data['area'] = $request['area_id'];
        }
        //修改地址
        $where['id']         = $request['address_id'];
        $where['m_id']       = $member['id'];
        $where['status']     = array('neq',9);
        $data['update_time'] = time();
        $address_data = $this ->easyMysql('Address', '2', $where, $data);
        if(!$address_data){
            apiResponse('0','修改地址失败');
        }
        apiResponse('1','修改地址成功');
    }

    /*
     * 收货地址列表
     */
    public function addressList($request = array()){
        $member = searchMember($request['token']);
        //根据用户ID查询地址
        $where['m_id'] = $member['id'];
        $where['status'] = array('neq',9);
        $field = 'id as address_id, name, telephone, address_info, is_default, province, city, area';
        $order = 'is_default asc, create_time desc';
        $address_info = $this ->easyMysql('Address','4',$where,'',$field, $order);

        if(!$address_info){
            $address_info = array();
        }else{
            foreach($address_info as $k => $v){
                unset($province);
                unset($city);
                unset($area);
                $province = $this ->easyMysql('Region','5',array('id'=>$v['province']),'','region_name');
                $city = $this ->easyMysql('Region','5',array('id'=>$v['city']),'','region_name');
                $area = $this ->easyMysql('Region','5',array('id'=>$v['area']),'','region_name');

                $address_info[$k]['address_info'] = $province.$city.$area.$v['address_info'];
            }
        }

        apiResponse('1','',$address_info);
    }

    /*
     * 删除收货地址
     * 地址ID     address_id
     */
    public function deleteAddress($request = array()){
        $member = searchMember($request['token']);
        //删除所选地址
        $where['id']     = $request['address_id'];
        $where['m_id']   = $member['id'];
        $where['status'] = array('neq',9);
        $data['status']  = 9;
        $data['update_time'] = time();
        $result = $this ->easyMysql('Address','2',$where,$data);
        if(!$result){
            apiResponse('0','删除失败');
        }
        apiResponse('1','删除成功');
    }

    /*
    * 收货地址详情
    * 传递参数的方式：post
    * 需要传递的参数：地址id：address_id
    */
    public function addressInfo($request = array()){
        $member = searchMember($request['token']);
        //根据用户ID查询地址
        $where['id']   = $request['address_id'];
        $where['m_id'] = $member['id'];
        $field = 'id as address_id, name, telephone, address_info, province, city, area';
        $address_info  = $this ->easyMysql('Address','3',$where, '',$field);
        $province_id = $this ->easyMysql('Region','3',array('id'=>$address_info['province']),'','id, region_name');
        $city_id = $this ->easyMysql('Region','3',array('id'=>$address_info['city']),'','id, region_name');
        $area_id = $this ->easyMysql('Region','3',array('id'=>$address_info['area']),'','id, region_name');

        $address_info['province_name'] = $province_id['region_name'];
        $address_info['city_name'] = $city_id['region_name'];
        $address_info['area_name'] = $area_id['region_name'];
        if(!$address_info){
            apiResponse('0','收货地址信息不存在');
        }
        apiResponse('1','',$address_info);
    }

    /*
     * 设置默认地址
     * 传递参数的方式：post
     * 需要传递的参数：
     * 地址id：address_id
     */
    public function defaultAddress($request = array()){
        $member = searchMember($request['token']);
        $where['id']   = $request['address_id'];
        $where['m_id'] = $member['id'];
        $where['status'] = array('neq',9);
        $data['is_default'] = $request['type'];
        $data['update_time'] = time();
        $result = M('Address') ->where($where) ->data($data) ->save();
        if(!$result){
            apiResponse('0','设置失败');
        }
        if($request['type'] == 1){
            unset($where);
            unset($data);
            $where['id'] = array('neq', $request['address_id']);
            $where['m_id'] = $member['id'];
            $where['status'] = array('neq',9);
            $data['is_default'] = 2;
            $data['update_time'] = time();
            M('Address') ->where($where) ->data($data) ->save();
        }
        apiResponse('1','设置成功');
    }

    /**
     * @param array $request
     * 地区列表
     * 父级ID    parent_id
     */
    public function cityLibrary($request = array()){
        if(empty($request['parent_id'])){
            $request['parent_id'] = 1;
        }
        $where['parent_id'] = $request['parent_id'];
        $where['status'] = array('neq',9);
        $region = $this ->easyMysql('Region','4',$where,'','id as region_id, region_name, letter','letter');
        $result = $region?$region:array();
        apiResponse('1','',$result);
    }

    /**
     * 城市列表
     */
    public function cityList(){
        $city = $this ->easyMysql('Region','4',array('region_type'=>2),'','id as region_id, region_name, letter','letter');
        if(!$city){
            apiResponse('0','',array());
        }
        apiResponse('1','',$city);
    }
}