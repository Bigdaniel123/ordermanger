<?php
	namespace Home\Controller;
	use Think\Controller;
	class HuankuanController extends Controller {
		public function _initialize(){
			// if(session('usertype')!=0){
			//     $this->error('此操作需要管理员权限！',U('Index/login'),2);
			// }
			$webset=M('fangkuan')->find();
			$this->assign('webset',$webset);
			$usertype=session('usertype');
			switch ($usertype) {
				case 0:
					if (session('userid')==1) {
						$usertpname='超级管理员';
					}else{
						$usertpname='管理员';
					}
					break;
				case 1:
					$usertpname='访问员';
					break;
			}
			$this->assign('usertypename',$usertpname);
		}
		public function index() {
			$this->assign('current',1);
			$this->display();
		}
		public function exportExcel($expTitle,$expCellName,$expTableData){
			$xlsTitle = iconv('utf-8', 'gb2312', $expTitle);//文件名称
			$fileName = $expTitle.date('_YmdHis');//or $xlsTitle 文件名称可根据自己情况设定
			$cellNum = count($expCellName);
			$dataNum = count($expTableData);

			vendor("PHPExcel.PHPExcel");

			$objPHPExcel = new \PHPExcel();
			$cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');

			$objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  Export time:'.date('Y-m-d H:i:s'));
			for($i=0;$i<$cellNum;$i++){
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]);
			}
			// Miscellaneous glyphs, UTF-8
			for($i=0;$i<$dataNum;$i++){
				for($j=0;$j<$cellNum;$j++){
					$objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
				}
			}

			header('pragma:public');
			header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
			header("Content-Disposition:attachment;filename=$fileName.xls");//attachment新窗口打印inline本窗口打印
			$objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
			$objWriter->save('php://output');
			exit;
		}

		/**
		 *
		 * 导出Excel
		 */
		function expUser(){//导出Excel
			if(session('userid')!=1){
				$this->error('此操作需要超级管理员权限！',U('Index/contacts'),2);
			}
			$xlsName  = "Contacts";
			$xlsCell  = array(
				array('id','账号序列'),
				array('name','姓名'),
				array('tname','所属乡镇'),
				array('danwei','单位'),
				array('phone','电话')
			);
			$xlsModel = M('fangkuan');
			$xlsData  = $xlsModel->Field('id,tid,name,danwei,phone')->select();
			foreach ($xlsData as $k => $v)
			{
				$xlsData[$k]['tname']=Gettname($v['tid']);
				array_splice($xlsData[$k]['tid']);
			}
			$this->exportExcel($xlsName,$xlsCell,$xlsData);

		}
		/**
		 *
		 * 显示导入页面 ...
		 */

		/**实现导入excel
		 **/
		function impUser(){
			if (!empty($_FILES)) {
				$upload = new \Think\Upload();// 实例化上传类
				$filepath='./Public/Excle/';
				$upload->exts = array('xlsx','xls');// 设置附件上传类型
				$upload->rootPath  =  $filepath; // 设置附件上传根目录
				$upload->saveName  =     'time';
				$upload->autoSub   =     false;
				if (!$info=$upload->upload()) {
					$this->error($upload->getError());
				}
				foreach ($info as $key => $value) {
					unset($info);
					$info[0]=$value;
					$info[0]['savepath']=$filepath;
				}
				vendor("PHPExcel.PHPExcel");
				$file_name=$info[0]['savepath'].$info[0]['savename'];
				$objReader = \PHPExcel_IOFactory::createReader('Excel5');
				$objPHPExcel = $objReader->load($file_name,$encode='utf-8');
				$sheet = $objPHPExcel->getSheet(0);
				$highestRow = $sheet->getHighestRow(); // 取得总行数
				$highestColumn = $sheet->getHighestColumn(); // 取得总列数
				$j=0;
				for($i=3;$i<=$highestRow;$i++)
				{
					$data['name']= $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
					$tname= $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
					$data['tid']=Gettid($tname);
					$data['danwei']= $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
					$data['phone']= $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
					// if(M('Contacts')->where("name='".$data['name']."' and phone=$data['phone']")->find()){
					if(M('fangkuan')->where("phone='".$data['phone']."'")->find()){
						//如果存在相同联系人。判断条件：电话 两项一致，上面注释的代码是用姓名/电话判断
					}else{
						M('fangkuan')->add($data);
						$j++;
					}
				}
				unlink($file_name);
				User_log('批量导入联系人，数量：'.$j);
				$this->success('导入成功！本次导入联系人数量：'.$j);
			}else
			{
				$this->error("请选择上传的文件");
			}
		}
	}

	?>