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
				$this->error("参数错误111");
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

		public function delete(){
			$id = I('id');
			$ids = I('ids');
			if(empty($id) && empty($ids)){
				$this->error('参数错误');
			}
			if($id){
				if($this->fangkuan_model->where(array('id' => $id))->delete() == false){
					$this->error('删除失败');
				}
				else{
					$this->success("删除成功");
				}
			}
			if($ids){
				foreach($ids as $id){
					if($this->fangkuan_model->where(array('id' => $id))->delete() == false){
						$this->error('删除失败');
					}
				}
				$this->success('删除成功');
			}
		}

		public function order(){
			$id = I('id');
			$data = M("fangkuan")->where(array('id' => $id))->find();
			if( ! empty($data['periods'])){
				$periods = $data['periods'];
			}
			else{
				$periods = $data['w_periods'];
			}
			$saveData = array();
			$money = $data['paid_money'];
			$every_pay = ($money / $periods);
			for($i = 1; $i <= $periods; $i++){
				$saveData[] = array(
					'oid'          => $data['id'],
					'qichu'        => $i,
					'create_time'  => time(),
					'payment_time' => ($data['type'] == 1) ? strtotime($data['addtime']) + ($i * 24 * 3600) : strtotime($data['addtime']) + ($i * 7 * 24 * 3600),
					'type'         => $data['type'],
					'every_pay'    => $every_pay,
				);
			}
			$result = M("Fangkuan_history")->where(array('oid' => $id))->find();
			if($result){
				$this->error('请勿重复生成还款计划', U('Fangkuan/index'));
			}
			else{
				$res = M("Fangkuan_history")->addAll($saveData);
			}
			if($res){
				$this->success('生成还款计划', U('Fangkuan/index'));
			}

		}

		public function orderlist(){
			$id = get_current_admin_id() == 1 ? array(
				'gt',
				0,
			) : I('id');
			$order = M("fangkuan_history")->alias('f')->field('f.every_pay as f_pay,f.*,o.* ,f.id as f_id')->join(' __FANGKUAN__ as o on f.oid= o.id', 'right')->where(array('o.id' => $id))->select();
			$this->assign('lists', $order);
			$this->display();
		}

		public function huankuan(){
			$id = I('id');
			$data = M("fangkuan_history")->where(array('id' => $id))->find();
			$data['status'] = 1;
			$res = M('fangkuan_history')->where(array('id' => $id))->save($data);
			if($res){
				$this->success('本期已还', U('Fangkuan/index'));
			}
			else{
				$this->error('还款失败', U('Fangkuan/order'));
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
