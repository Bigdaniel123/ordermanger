<?php


	function changetype($type,$time_int){
		if($type==1){
			echo "期<br/>/每期{$time_int}天";
		}elseif($type==2){
			echo "天";
		}

	}