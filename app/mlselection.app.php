<?php

/* 多级选择：地区选择，分类选择 */
class MlselectionApp extends MallbaseApp
{
    function index()
    {
        in_array($_GET['type'], array('region', 'gcategory')) or $this->json_error('invalid type');
        $pid = empty($_GET['pid']) ? 0 : $_GET['pid'];

        switch ($_GET['type'])
        {
            case 'region':
                $mod_region =& m('region');
                $regions = $mod_region->get_list($pid);
                foreach ($regions as $key => $region)
                {
                    $regions[$key]['region_name'] = htmlspecialchars($region['region_name']);
                }
                $this->json_result(array_values($regions));
                break;
            case 'gcategory':
                $mod_gcategory =& m('gcategory');
                $cates = $mod_gcategory->get_list($pid, true);
                foreach ($cates as $key => $cate)
                {
                    $cates[$key]['cate_name'] = htmlspecialchars($cate['cate_name']);
                }
                $this->json_result(array_values($cates));
//                print_r($this->json_result(array_values($cates)));
                break;
        }
    }

    function dizhi()
    {
        in_array($_GET['type'], array('region', 'gcategory')) or $this->json_error('invalid type');
        $pid = empty($_GET['pid']) ? 0 : $_GET['pid'];

        switch ($_GET['type'])
        {
            case 'region':
                $mod_region =& m('region');
                $regions1 = $mod_region -> get($pid);
                $regions = $mod_region->get_list($pid);
                if(count($regions) < 2){
                    foreach ($regions as $key => $region)
                    {
                        if($regions1['region_name'] == $region['region_name']){
                            $rid = $region['region_id'];
                        }
                        $regions = $mod_region->get_list($rid);
                    }
                }

                foreach ($regions as $key => $region)
                {
                    $regions[$key]['region_name'] = htmlspecialchars($region['region_name']);
                }
                $this->json_result(array_values($regions));
                break;
            case 'gcategory':
                $mod_gcategory =& m('gcategory');
                $cates = $mod_gcategory->get_list($pid, true);
                foreach ($cates as $key => $cate)
                {
                    $cates[$key]['cate_name'] = htmlspecialchars($cate['cate_name']);
                }
                $this->json_result(array_values($cates));
//                print_r($this->json_result(array_values($cates)));
                break;
        }
    }
}

?>