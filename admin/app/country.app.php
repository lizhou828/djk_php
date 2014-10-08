<?php

/* 国家控制器 */
class CountryApp extends BackendApp
{
    var $_country_mod;

    function __construct()
    {
        $this->CountryApp();
    }

    function CountryApp()
    {
		
        parent::__construct();
        $this->_country_mod =& m('country');		
    }

    /* 管理 */
    function index()
    {
		
		
		
		 //更新排序
        if (isset($_GET['sort']) && !empty($_GET['order']))
        {
            $sort  = strtolower(trim($_GET['sort']));
            $order = strtolower(trim($_GET['order']));
            if (!in_array($order,array('asc','desc')))
            {
             $sort  = 'country_id';
             $order = 'asc';
            }
        }
        else
        {
            if (isset($_GET['sort']) && empty($_GET['order']))
            {
                $sort  = strtolower(trim($_GET['sort']));
                $order = "";
            }
            else
            {
                $sort  = 'country_id';
                $order = 'asc';
            }
        }
		

		$page = $this->_get_page();
        $countrys = $this->_country_mod->find(array(
				 "fields"=>"this.*",
				 'limit' => $page['limit'],
                 'order' => "$sort $order",
                 'count' => true,
				 'conditions'=>" dropState=1 "
				 ));
       
	    $page['item_count'] = $this->_country_mod->getCount();
        $this->_format_page($page);      
        $this->assign('page_info', $page);		
        $this->assign('countrys', $countrys);
        
        $this->import_resource(array(
            'script' => 'inline_edit.js,jqtreetable.js',
            'style' => 'res:style/jqtreetable.css'
        ));
		//$this->pr($countrys);
		
        $this->display('country.index.html');
		//$this->pr($this->_country_mod);
    }
	
	
	    /* 更新排序 */
    function update_order()
    {
        if (empty($_GET['id']))
        {
            $this->show_warning('Hacking Attempt');
            return;
        }

        $ids = explode(',', $_GET['id']);
        $sort_orders = explode(',', $_GET['sort_order']);
        foreach ($ids as $key => $id)
        {
            $this->_country_mod->edit($id, array('sort_order' => $sort_orders[$key]));
        }

        $this->show_message('update_order_ok');
    }
	
	     //异步修改数据
   function ajax_col()
   {
       $id     = empty($_GET['id']) ? 0 : intval($_GET['id']);
       $column = empty($_GET['column']) ? '' : trim($_GET['column']);
       $value  = isset($_GET['value']) ? trim($_GET['value']) : '';
       $data   = array();

       if (in_array($column ,array( 'sort_order')))
       {
           $data[$column] = $value;
           $this->_country_mod->edit($id, $data);
           if(!$this->_country_mod->has_error())
           {
               echo ecm_json_encode(true);
           }
       }
       else
       {
           return ;
       }
       return ;
   }
   
   
   /* 新增 */
    function add()
    {
        if (!IS_POST)
        {
            $this->display('country.form.html');
        }
        else
        {
			$country_name=$_POST['country_name'];
            $data = array(
                'country_name' => $country_name,              
                'sort_order' => $_POST['sort_order'],
            );

            /* 检查名称是否已存在 */
            if (count($this->_country_mod->find("country_name='$country_name' and dropState=1 "))>0)
            {
                exit("名字已经存在");
                return;
            }

            /* 保存 */
            $country_id = $this->_country_mod->add($data);
            if (!$country_id)
            {
                $this->show_warning($this->_country_mod->get_error());
                return;
            }

            $this->show_message('add_ok',
                'back_list',    'index.php?app=country',
                'continue_add', 'index.php?app=country&amp;act=add&amp;pid=' . $data['parent_id']
                );
        }
    }

   
   /* 编辑 */
    function edit()
    {
        $id = empty($_GET['id']) ? 0 : intval($_GET['id']);
        if (!IS_POST)
        {
            /* 是否存在 */
            $country = $this->_country_mod->get_info($id);
            if (!$country)
            {
                //$this->show_warning('region_empty');
				exit("没有找到国家");
                return;
            }
            $this->assign('country', $country);
           
            $this->display('country.form.html');
        }
        else
        {
            $country = $this->_country_mod->get_info($id);
            if (empty($country))
            {
                //$this->show_warning('no_such_region');
				exit("没有找到国家");
                return;
            }

			$country_name=$_POST['country_name'];
            $data = array(
                'country_name' => $country_name,               
                'sort_order'  => $_POST['sort_order'],
            );

            /* 检查名称是否已存在 */
            if (count($this->_country_mod->find("country_name='$country_name' and country_id !=$id and dropState=1 "))>0)
            {
                exit("名字已经存在");
                return;
            }

            /* 保存 */
            $rows = $this->_country_mod->edit($id, $data);
            if ($this->_country_mod->has_error())
            {
                $this->show_warning($this->_country_mod->get_error());
                return;
            }

            $this->show_message('edit_ok',
                'back_list',    'index.php?app=country',
                'edit_again',   'index.php?app=country&amp;act=edit&amp;id=' . $id
            );
        }
    }
	
	
	    /* 删除 */
    function drop()
    {
        $id = isset($_GET['id']) ? trim($_GET['id']) : '';
        if (!$id)
        {
            exit("没有找到国家");
            return;
        }

        /*$ids = explode(',', $id);
        if (!$this->_region_mod->drop($ids))
        {
            $this->show_warning($this->_region_mod->get_error());
            return;
        }*/
        //系统改造，2013-08-02  去掉物理删除改用逻辑删除
        if (!$this->_country_mod->edit("country_id in ($id)",array("dropState"=>0)))
        {
            $this->show_warning($this->_country_mod->get_error());
            return;
        }


        $this->show_message('drop_ok');
    }

    
}

?>
