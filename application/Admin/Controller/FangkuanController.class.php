<?php

	namespace Admin\Controller;
	use Common\Controller\AdminbaseController;
	use PHPExcel;

	class FangkuanController extends AdminbaseController{
		protected $fangkuan_model;

		public function _initialize(){
			parent::_initialize();
			$this->fangkuan_model = M('fangkuan');
		}

		public function index(){
			$data = I('post.');
			/*条件查询*/
			if(IS_POST){
				if( ! empty($data['name'])){
					$name = trim($data['name']);
					$where['customername'] = array(
						'like',
						"%{$name}%",
					);
				}
				if( ! empty($data['phone'])){
					$where['phone'] = trim($data['phone']);
				}
			}
			$currentuid = get_current_admin_id();
			$where['uid'] = $currentuid;
			$count = $this->fangkuan_model->where($where)->count();
			$page = $this->page($count, 20);
			$list = M('fangkuan')->where($where)->limit($page->firstRow, $page->listRows)->select();
			$this->assign("page", $page->show());
			$this->assign('formget', array_merge($_POST, $_GET));
			$this->assign('lists', $list);
			$this->display();
		}

		public function add(){
			$uid = get_current_admin_id();
			$data = array();
			if($_POST){
				$data['uid'] = $uid;
				$data['type'] = I('fangkuantype');
				$type = I('fangkuantype');
				$data['customername'] = I('customername');
				$data['phone'] = I('phone');
				$data['identify_person'] = I('identify_person');
				$data['opening_bank'] = I('opening_bank');
				$data['paid_money'] = I('paid_money');
				$data['credit_limit'] = I('credit_limit');
				$data['date'] = I('date');
				$data['card_number'] = I('card_number');
				$data['actual_loan'] = I('actual_loan');
				$data['total_deduction'] = I('total_deduction');
				$data['intermediary_fee'] = I('intermediary_fee');
				$data['manage_cost'] = I('manage_cost');
				$data['deposit'] = I('deposit');
				$data['firstdate_pay'] = I('firstdate_pay');
				$data['interest'] = I('interest');
				$data['periods'] = I('periods');
				$data['w_interest'] = I('w_interest');
				$data['w_periods'] = I('w_periods');
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
				if( ! empty($type)){
					if($type == 1){
						if(empty($data['w_interest']) && empty($data['w_periods'])){
							$this->error('请填写利息或者期数');
						}
					}
					if($type == 2){
						if(empty($data['interest']) && empty($data['periods'])){
							$this->error('请填写利息或者天数');
						}
					}
				}
				$result = M('fangkuan')->add($data);
				if($result){
					$this->success('添加成功', U("Fangkuan/index"));
				}
				else{
					$this->error('添加失败', U("Fangkuan/add"));
				}

			}
			else{
				$record = M('fangkuan')->order("id ASC")->select();
				$this->assign('record', $record);
				$this->display();
			}
		}

		public function edit(){
			$id = I('id');
			if(empty($id)){
				$this->error("参数错误");
			}
			if(IS_POST){
				$data = I('post.');
				$data['id'] = $id;
				/*验证参数*/
				if($this->fangkuan_model->save($data) !== false){
					$this->success("更新成功", U("Fangkuan/index"));
				}
				else{
					$this->error('更新失败');
				}
			}
			$fangkuan = $this->fangkuan_model->where(array('id' => $id))->find();
			$this->assign('fangkuan', $fangkuan);
			$this->display();

		}

		/**
		 * 删除功能
		 */
		public function delete(){
		$id = I('id',0,'intval');
		if(empty($id)){
			$this->error('参数错误');
		}
		if($this->fangkuan_model->where(array('id' => $id))->delete() === false){
			$this->error('删除失败');
		}
		else{
			M('FangkuanHistory')->where(array('oid'=>$id))->delete();
			$this->success("删除成功");
		}

	}

		/**
		 * 查询并更新分期数据
		 */
		public function orderlist(){
			$id = I('id',0,'intval');
			if(empty($id)){
				$this->error('参数错误');
			}
			$data = M("fangkuan")->where(array('id' => $id))->find();
			$result = M("fangkuan_history")->where(array('oid' => $id))->find();
			if(!$result){
				$periods = $data['periods'];
				$saveData = array();
				$time=time();
				for($i = 1; $i <= $periods; $i++){
					$saveData[] = array(
						'oid'          => $data['id'],
						'qichu'        => $i,
						'create_time'  => $time,
						'payment_time' => ($data['type'] == 1) ? strtotime($data['date']) + ($i * 24 * 3600) : strtotime($data['date']) + ($i * $data['time_int'] * 24 * 3600),
						'type'         => $data['type'],
						'every_pay'    => $data['firstdate_pay'],
					);
				}
				M("Fangkuan_history")->addAll($saveData);
			}
			$order = M("fangkuan_history")->alias('f')->field('f.every_pay as f_pay,f.*,o.* ,f.id as f_id')->join(' __FANGKUAN__ as o on f.oid= o.id', 'right')->where(array('o.id' => $id))->select();
			$this->assign('lists', $order);
			$this->display();
		}

		/**
		 * 还款列表
		 */
		public function huankuan(){
			$id = I('id',0,'intval');
			$data = M("fangkuan_history")->where(array('id' => $id))->find();
			if($data['status']==1){
				$this->error('本期已还');
			}
			$save['status'] = 1;
			$save['huankuan_time']=time();
			$res = M('fangkuan_history')->where(array('id' => $id))->save($save);
			if($res !== false){
				$this->success('还款成功');
			}
			else{
				$this->error('还款失败');
			}
		}

		public function upload(){
			if(IS_POST){
				$upload = new \Think\Upload();
				$upload->maxSize = 3145728;
				$upload->exts = array();
				$upload->rootPath = './data/upload/';
				$upload->savePath = '';
				$info = $upload->upload();
				if( ! $info){
					$this->error($upload->getError());
				}
				else{
					$this->success('上传成功！', U('Fangkuan/index'));
				}
			}
			$this->display();
		}

		function add1(){
			$arr = array(
				array(
					'age'   => 20,
					'name'  => 'hhhh',
					'id'    => '1',
					'name1' => '444',
				),
			);
			foreach($arr as $k => $v){

			}
		}
	}
