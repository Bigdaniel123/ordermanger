<?php
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
use PHPExcel;

class FangkuanController extends AdminbaseController{
	protected $fangkuan_model;

	public function _initialize(){
		parent::_initialize();
		$this->fangkuan_model=M('fangkuan');
	}
	//放款信息列表
	//public function index(){
	//	//
	//	$Data = M('fangkuan'); // 实例化Data数据对象  date 是你的表名
	//	//import('ORG.Util.Page');// 导入分页类
	//	$map['id']=array('gt',0);
	//	$count = $Data->where($map)->count();// 查询满足要求的总记录数 $map表示查询条件
	//	$Page = new \Page($count);// 实例化分页类 传入总记录数
	//	$show = $Page->show();// 分页显示输出
	//	// 进行分页数据查询
	//
	//	$list = $Data->where($map)->order('id')->limit($Page->firstRow.','.$Page->listRows)->select(); // $Page->firstRow 起始条数 $Page->listRows 获取多少条
	//
	//	$this->assign('lists',$list);// 赋值数据集
	//	$this->assign('page',$show);// 赋值分页输出
	//	$this->display(); // 输出模板
	//}
	public function index(){
		$data = I('post.');
		/*条件查询*/
		if(IS_POST){
			////$where['createtime']=array();
			//if(!empty($data['start_time'])){
			//	$where['createtime'][]=array('EGT',strtotime($data['start_time']));
			//}
			//if(!empty($data['end_time'])){
			//	array_push($where['createtime'],array('ELT',strtotime($data['end_time'])+24*3600));
			//}
			//$where =array();
			if(!empty($data['name'])){
				$name=trim($data['name']);
				$where['customername']=array('like',"%{$name}%");//where[]数据库的字段
			}
			if(!empty($data['phone'])){
				$where['phone']=trim($data['phone']);
			}
		}
		//$where['ostatus']=0;
		$currentuid =  get_current_admin_id();
		$where['uid']=$currentuid;
		$count = $this->fangkuan_model->where($where)->count();
		//dump($count);
		//var_dump($count);
		//exit;
		$page = $this->page($count, 20);
		$list = M('fangkuan')->where($where)->limit($page->firstRow, $page->listRows)->select();
		//dump($list);
		//exit;
		//var_dump($this->fangkuan_model->getlastsql());

		$this->assign("page", $page->show());
		$this->assign('formget',array_merge($_POST,$_GET));
		$this->assign('lists',$list);
		$this->display();
	}
	//放款信息添加
	public function add(){
		//$uid = sp_get_current_userid();
		$uid =  get_current_admin_id();
		//dump($uid);
		//exit;
		$data = array();
		if ($_POST) {
			$data['uid'] = $uid;
			$data['type'] = I('fangkuantype');
			//var_dump($data['type']);
			//exit;
			$type= I('fangkuantype');//放款类型
			$data['customername'] = I('customername');//客户姓名
			$data['phone'] = I('phone');
			$data['identify_person'] = I('identify_person');//确认人
			$data['opening_bank'] = I('opening_bank');//开户行
			$data['paid_money'] = I('paid_money');//打款金额
			$data['credit_limit'] = I('credit_limit');//借款额度
			$data['date'] = I('date');//日期
			$data['card_number'] = I('card_number');//银行卡号
			$data['actual_loan'] = I('actual_loan');//实际放款
			$data['total_deduction'] = I('total_deduction');//共计扣除
			$data['intermediary_fee'] = I('intermediary_fee');//代收中介费
			$data['manage_cost'] = I('manage_cost');//公司管理费
			$data['deposit'] = I('deposit');//押金
			$data['firstdate_pay'] = I('firstdate_pay');//第一期本金及利息
			$data['interest'] = I('interest');//利息
			$data['periods'] = I('periods');//天数
			$data['w_interest'] = I('w_interest');//利息
			$data['w_periods'] = I('w_periods');//天数

			if(empty($data['customername'])){
				$this->error('请填写客户姓名');
			}
			if(empty($data['phone'])){
				$this->error('请填写手机号');
			}
			if(empty($data['identify_person'])){
				$this->error('请填写确认人');
			}
			if(empty($data['opening_bank'])){
				$this->error('请填写开户行');
			}
			if(empty($data['paid_money'])){
				$this->error('请填写打款金额');
			}
			if(empty($data['credit_limit'])){
				$this->error('请填写借款额度');
			}
			if(empty($data['date'])){
				$this->error('请填写日期');
			}
			if(empty($data['card_number'])){
				$this->error('请填写银行卡号');
			}
			if(empty($data['total_deduction'])){
				$this->error('请填写共计扣除');
			}
			if(empty($data['actual_loan'])){
				$this->error('请填写实际放款');
			}
			if(empty($data['intermediary_fee'])){
				$this->error('代收中介费');
			}
			if(empty($data['manage_cost'])){
				$this->error('请填写公司管理费');
			}
			if(empty($data['deposit'])){
				$this->error('请填写押金');
			}
			if(empty($data['firstdate_pay'])){
				$this->error('请填写第一期本金及利息');
			}
			if(!empty($type)){
				//var_dump($type);
				//exit;
				if($type==1){
					//echo '123';
					//exit;
					if(empty($data['w_interest']) && empty($data['w_periods'])){
						$this->error('请填写利息或者期数');
					}
				}
				if($type==2){
					if(empty($data['interest']) && empty($data['periods'])){
						$this->error('请填写利息或者天数');
					}
				}
			}
			//if(empty($data['periods'])){
			//	$this->error('请填写天数');
			//}
			//$info = upload('ad/');
			//if(!is_array($info)){
			//	$this->error($info);
			//}
			//$data['images'] = $info['images']['savepath'].$info['images']['savename'];

			$result = M('fangkuan')->add($data);
			if($result) {
				$this->success('添加成功', U("Fangkuan/index"));
			}else{
				$this->error('添加失败', U("Fangkuan/add"));
			}

		} else{
			$record=M('fangkuan')->order("id ASC")->select();
			$this->assign('record',$record);
			$this->display();
		}
	}
	//public function upload(){
	//	if(IS_POST){
	//		$upload = new \Think\Upload();
	//		$upload->maxsize = 31420;
	//		$upload->exts=array('png','jpeg','jpg','pdf');
	//		$upload->savepath='./Upload';
	//	}
	//	$info = $upload->upload();
	//	if($info){
	//		$this->success('上传成功',U('Fangkuan/index'));
	//	}
	//}
	//放款信息修改
		public function edit(){
			$id = I('id');
			if(empty($id)){
				$this->error("参数错误111");
			}
			if(IS_POST){
				//echo'12';
				//exit;
				$data=I('post.');
				$data['id']=$id;
				//var_dump($data['id']);
				//exit;
				/*验证参数*/
				//$this->_verify($data);
				//$data['plate']=strtoupper($data['plate']);
				if($this->fangkuan_model->save($data)!==false){
					$this->success("更新成功", U("Fangkuan/index"));
				}else{
					$this->error('更新失败');
				}
			}
			$fangkuan =$this->fangkuan_model->where(array('id'=>$id))->find();
			$this->assign('fangkuan',$fangkuan);
			//var_dump($fangkuan);
			$this->display();

		}
	//放款信息删除
	public function delete(){
		$id=I('id');
		$ids= I('ids');
		if(empty($id) && empty($ids)){
			$this->error('参数错误');
		}
		if($id){
			if($this->fangkuan_model->where(array('id' =>$id))->delete() ==false){
				$this->error('删除失败');
			}else{
				$this->success("删除成功");
			}
		}
		if($ids){
			foreach($ids as $id){
				if($this->fangkuan_model->where(array('id' =>$id))->delete() ==false){
					$this->error('删除失败');
				}
			}
			$this->success('删除成功');
		}
	}
	//还款计划
	//public function repayment_plan()
	//{
	//	$id = I('id');
	//	$data = $this->fangkuan_model->where(array('id' => $id))->find();
	//	//dump($data);
	//	//exit;
	//	$type = $data['type'];
	//	//dump($type);
	//	//exit;
	//
	//	$time = $data['addtime'];
	//	//dump($time);
	//	//exit;
	//	if($type == 1){
	//		$periods = $data['periods'];
	//	}
	//	elseif($type == 2){
	//		$periods = $data['w_periods'];
	//	}
	//	//dump($data['periods']);
	//	//exit;
	//	$data['every_pay']=($data['paid_money']/$periods);
	//
	//	//dump($data['every_pay']);
	//	//exit;
	//	$this->assign('data', $data);
	//	$this->assign('periods',$periods);
	//	$this->display();
	//}
	//本次还款
	//public function every_pay_add(){
	//	$id = I('id');
	//	$data = $this->fangkuan_model->where(array('id' => $id))->find();
	//	$type = $data['type'];
	//	//dump($type);
	//	//exit;
	//
	//	$time = $data['addtime'];
	//	//dump($time);
	//	//exit;
	//	if($type == 1){
	//		$periods = $data['periods'];
	//	}
	//	elseif($type == 2){
	//		$periods = $data['w_periods'];
	//	}
	//	//dump($data['periods']);
	//	//exit;
	//	$data['every_pay']=($data['paid_money']/$periods);
	//
	//	//dump($data['every_pay']);
	//	//exit;
	//	if(IS_POST){
	//		$data['every_pay'] = I('every_pay');
	//		dump($data['every_pay']);
	//		exit;
	//	}
	//	$this->assign('data', $data);
	//	$this->assign('periods',$periods);
	//	$this->display();
	//
	//
	//}
	//当前分期列表
	//public function orderhistory(){
	//	$search = I("post.");
	//	//默认显示当天还款订单
	//	if($search["repayDate"]){
	//		$where['repayDate'] = substr($search["repayDate"], 0, 11) . '00:00:00';
	//		$search['repayDate'] = strtotime($where['repayDate']);
	//	}
	//	if($search['userName']==' '){
	//		$where['EXP'] = '1=1';
	//	}elseif($search['userName'] &&$search['userName']!=' '){
	//		$where['userName'] = $search['userName'];
	//	}
	//	if(empty($where)){
	//		$where['repayDate'] = date('Y-m-d ', time()) . '00:00:00';
	//	}
	//	$count= M("orderhistory")->where($where)->order('id asc')->count();
	//	$page = $this->page($count, 100);
	//	$orderf = M("orderhistory")->where($where)->limit($page->firstRow,$page->listRows)->select();
	//
	//	////加总利息
	//	//$sumamount =0;
	//	//foreach($orderf as $v){
	//	//	$sumamount+=$v['interest'];
	//	//}
	//	$this->assign('search', $search);
	//	$this->assign('lists', $orderf);
	//	//$this->assign('sumamount', $sumamount);
	//	$this->assign('page',$page->show('Admin'));
	//	$this->display();
	//}
	//生成个人所有期订单
	public function order(){
		$id = I('id');
		//echo $id;
		//exit;
		$data = M("fangkuan")->where(array('id'=>$id))->find();
		//dump($data);
		//exit;
		if(!empty($data['periods'])){
			$periods = $data['periods'];
		}else{
			$periods = $data['w_periods'];
		}
		//$periods = $data['w_periods'];
		//$periods = $data['periods'];
		$saveData=array();
		$money = $data['paid_money'];
		//dump($money);
		//exit;
		$every_pay = ($money/$periods);
		//dump($every_pay);
		//exit;
		for($i=1;$i<=$periods;$i++){
			$saveData[]=array(
				'oid'=>$data['id'],
			    'qichu'=>$i,
			    'create_time'=>time(),
			    'payment_time'=>($data['type']==1)?strtotime($data['addtime'])+($i*24*3600):strtotime($data['addtime'])+($i*7*24*3600),
			    'type'=>$data['type'],
			    'every_pay'=>$every_pay
			);
		}
		//dump($saveData);
		//exit;

		//先查询是否在histroy 有值
		$result = M("Fangkuan_history")->where(array('oid'=>$id))->find();
		if($result){

			$this->error('请勿重复生成还款计划',U('Fangkuan/index'));
		}
		else{
			$res=M("Fangkuan_history")->addAll($saveData);
		}
		//dump($res);
		//exit;
		if($res){
			$this->success('生成还款计划',U('Fangkuan/index'));
		}
		//var_dump($res);
	}
	//还款列表
	public function orderlist(){
		//查询本人所有期数订单
		$id = I('id');
		$order = M("fangkuan_history")
			->alias('f')
			->field('f.every_pay as f_pay,f.*,o.* ,f.id as f_id' )
			//->field('f.id as f_id,f.*,o.* ')//放宽表的id为f——id
			->join(' __FANGKUAN__ as o on f.oid= o.id','right')
			->where(array('o.id'=>$id))
			->select();
		//dump(M()->getLastSql());
		//dump($order);
		//exit;
		$this->assign('lists',$order);
		//$this->assign('lists',$data);
		$this->display();
	}
	//本次还款
	public function huankuan(){
		$id= I('id');
		//dump($id);
		//exit;
		$data = M("fangkuan_history")->where(array('id'=>$id))->find();
		//dump(M()->getLastSql());
		//exit;
		//dump($data);
		//exit;
		$data['status'] = 1;

		$res = M('fangkuan_history')->where(array('id'=>$id))->save($data);
		if($res){
			//$data = M("fangkuan_history")->where(array('id'=>$id))->find();
			//$status =
			//M("fangkuan_history")->where(array('id'=>$id))->save($status);
			$this->success('本期已还',U('Fangkuan/index'));
		}else{
			$this->error('还款失败',U('Fangkuan/order'));
		}

	}
	//显示某个人的订单
	//public function showorder(){
	//	$orderf = array();
	//	$oid= I('get.oid');
	//	$where=array();
	//	if($oid){
	//		$where['oid']=$oid;
	//	}
	//	$orderf = $this->fangkuan_model->where($where)->order('id asc')->select();
	//	$orderf['oid'] = "select a.id from fangkuan as a,fangkuan_history as b where a.id=b.oid";
	//	dump($orderf['oid']);
	//	exit;
	//	$this->assign('lists', $orderf);
	//	//$this->display('orderhistory');
	//	$this->display();
	//}
	//上传文件
	public function upload(){
		//echo'123';
		//exit;
		if(IS_POST){
			$upload = new \Think\Upload();// 实例化上传类
			//var_dump($upload);
			//exit;
			$upload->maxSize = 3145728 ;// 设置附件上传大小
			$upload->exts = array();// 设置附件上传类型
			$upload->rootPath = './data/upload/'; // 设置附件上传根目录
			$upload->savePath = ''; // 设置附件上传（子）目录

			// 上传文件
			$info = $upload->upload();
			//var_dump($info);
			//exit;
			//print_r($info);exit;
			if(!$info) {// 上传错误提示错误信息
				$this->error($upload->getError());
			}else{// 上传成功
				$this->success('上传成功！',U('Fangkuan/index'));
			}
		}
		$this->display();
	}

	function add1(){
		$arr =array(array('age'=>20,'name'=>'hhhh','id'=>'1','name1'=>'444'));
		//dump($arr);
		//for($i=1;$i<=100;$i++){
		//	dump($arr."<br/>");
		//}
		foreach($arr as $k=>$v){

		}
	}
}
