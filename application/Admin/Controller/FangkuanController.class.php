<?php

	namespace Admin\Controller;
	use Common\Controller\AdminbaseController;
	use Think\Model;

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
			//当前添加的用户id
			$uid = get_current_admin_id();
			$data = array();
			if(IS_POST){
				$data['uid'] = $uid;
				$data['type'] = I('fangkuantype');
				$type = I('fangkuantype');
				$data['customername'] = I('customername');
				$data['phone'] = I('phone');
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
				$data['imgs'] = I('imgs');
				$data['time_int'] = I('time_int');
				//添加imgs_path
				foreach($data['imgs'] as $k => $v){
					$data['imgs'][ $k ] = "data/upload/" . $v;
				}
				//转json;
				$data['imgs'] = json_encode($data['imgs']);
				if(empty($type)){
					$this->error('分期时间类型为空');
				}
				if(empty($data['customername'])){
					$this->error('请填写客户姓名');
				}
				if(empty($data['phone'])){
					$this->error('请填写手机号');
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
				if(empty($data['interest'])){
					$this->error('请填写利息');
				}
				if(empty($data['periods'])){
					$this->error('请填写期数');
				}
				if( ! empty($type)){
					//按期
					if($type == 1){
						if(empty($data['time_int'])){
							$this->error('请填写每期间隔');
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
				//判断图片是否更换过
				foreach($data['imgs'] as $k => $v){
					$data['imgs'][ $k ] = strpos($v, 'data/upload') === false ? 'data/upload/' . $v : $v;
				}
				$data['imgs'] = json_encode($data['imgs']);
				/*验证参数*/
				if($this->fangkuan_model->save($data) !== false){
					$this->success("更新成功");
				}
				else{
					$this->error('更新失败');
				}
			}
			$fangkuan = $this->fangkuan_model->where(array('id' => $id))->find();
			//decode 图片
			$fangkuan['imgs'] = json_decode($fangkuan['imgs'], true);
			$this->assign('fangkuan', $fangkuan);
			$this->display();

		}

		/**
		 * 删除功能
		 */
		public function delete(){
			$id = I('id', 0, 'intval');
			if(empty($id)){
				$this->error('参数错误');
			}
			if($this->fangkuan_model->where(array('id' => $id))->delete() === false){
				$this->error('删除失败');
			}
			else{
				M('FangkuanHistory')->where(array('oid' => $id))->delete();
				$this->success("删除成功");
			}

		}

		/**
		 * 创建并展示订单
		 */
		public function orderlist(){
			$id = I('id', 0, 'intval');
			if(empty($id)){
				$this->error('参数错误');
			}
			$data = M("fangkuan")->where(array('id' => $id))->find();

			//为空就添加数据
			$this->_addHistory($id, $data);

			$order = M("fangkuan_history")->alias('f')->field('f.every_pay as f_pay,f.*,o.* ,f.id as f_id')->join(' __FANGKUAN__ as o on f.oid= o.id', 'right')->where(array('o.id' => $id))->order('f.id asc')->select();
			$this->assign('lists', $order);
			$this->assign('data', $data);
			$this->display();
		}


		/**
		 * 还款列表
		 */
		public function huankuan(){
			$id = I('id', 0, 'intval');

			if(IS_POST){
				$repayment_img = I('repayment_img');

				$fangkuan  =M("fangkuan_history")->where(array('id'=>$id));

				$fangkuanData  = $fangkuan->find();
				$oid  =$fangkuanData['oid'];
				if(empty($fangkuanData)){
					$this->error("参数非法");
				}
				if($fangkuanData['status']==1){
					$this->error('本期已还');
				}


				$fangkuan->status=1;
				$fangkuan->huankuan_time=time();

				//如果有图片
				if(!empty($repayment_img)){
					$repayment_img = "data/upload/" . $repayment_img;
				}

				$fangkuan->repayment_img = $repayment_img;
				$res  =$fangkuan->save();
				if($res===false){
					$this->error('还款失败');
				}else{
					$this->success('还款成功');
				}
			}

			$this->assign('id',$id);
			$this->display();

		}

		/**
		 * @param $id       fangkuan 表的id
		 * @param $data     fangkuan表的值
		 */
		private function _addHistory($id, $data){
			$result = M("fangkuan_history")->where(array('oid' => $id))->find();
			if( ! $result){
				$periods = $data['periods'];
				$saveData = array();
				$time = time();
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
		}

	}
