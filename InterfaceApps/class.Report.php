<?PHP

class Report extends InterfaceVIEWS {

    public function __Public() {
        IsLogin();
    }

    public function CusCount() {
        $level = $_SESSION['Level'];
        $agent = $_SESSION['AgentID'];
        $type = $this->_GET['type'];
        $start = date('Y-m-d',strtotime($this->_GET['start']));
        $end = date('Y-m-d',strtotime($this->_GET['end']));
        $startInt = strtotime($start);
        $endInt = strtotime($end);
        $result = $this->ErrJudge($startInt, $endInt, $type);
        if($result['err'] !== 0)
            return $result;
        if ($level == 1) {
            if ($type == 'day')
                $sel = "select CreateTime date,count(1) count from tb_customers_project where CreateTime >= '$start' and CreateTime <= '$end' group by date";
            elseif ($type == 'week')
                //已7天为单位统计数量
                $sel = "select CreateTime,COUNT(1) as count,(DATEDIFF(CreateTime, '$start') DIV 7) as groupNum from tb_customers_project where CreateTime>='$start' and CreateTime<='$end' group by groupNum";
            elseif ($type == 'month')
                $sel = "select DATE_FORMAT(CreateTime,'%Y-%m') date,count(1) count from tb_customers_project where CreateTime >= '$start' and CreateTime <= '$end' group by date";
            else
                $sel = "select DATE_FORMAT(CreateTime,'%Y') date,count(1) count from tb_customers_project where CreateTime >= '$start' and CreateTime <= '$end' group by date";
        }elseif ($level == 2) {
            if ($type == 'day')
                $sel = "select CreateTime date,count(1) count from (select CreateTime,AgentID from tb_customers_project where CreateTime >= '$start' and CreateTime <= '$end') a inner join tb_account b on a.AgentID = b.AgentID and (b.AgentID = $agent or b.BossAgentID = $agent) group by date";
            elseif ($type == 'week')
                $sel = "select CreateTime date,COUNT(1) as count,(DATEDIFF(CreateTime, '$start') DIV 7) as groupNum from (select AgentID,CreateTime from tb_customers_project where CreateTime>='$start' and CreateTime<='$end') a inner join tb_account b on a.AgentID = b.AgentID and (b.AgentID = $agent or b.BossAgentID = $agent) group by groupNum";
            elseif ($type == 'month')
                $sel = "select DATE_FORMAT(CreateTime,'%Y-%m') date,count(1) count from (select CreateTime,AgentID from tb_customers_project where CreateTime >= '$start' and CreateTime <= '$end') a inner join tb_account b on a.AgentID = b.AgentID and (b.AgentID = $agent or b.BossAgentID = $agent) group by date";
            else
                $sel = "select DATE_FORMAT(CreateTime,'%Y') date,count(1) count from (select CreateTime,AgentID from tb_customers_project where CreateTime >= '$start' and CreateTime <= '$end') a inner join tb_account b on a.AgentID = b.AgentID and (b.AgentID = $agent or b.BossAgentID = $agent) group by date";
        } else {
            if ($type == 'day')
                $sel = "select CreateTime date,count(1) count from (select CreateTime,AgentID from tb_customers_project where CreateTime >= '$start' and CreateTime <= '$end') a inner join tb_account b on a.AgentID = b.AgentID and b.AgentID = $agent group by date";
            elseif ($type == 'week')
                $sel = "select CreateTime,COUNT(1) as count,(DATEDIFF(CreateTime, '$start') DIV 7) as groupNum from (select AgentID,CreateTime from tb_customers_project where CreateTime>='$start' and CreateTime<='$end') a inner join tb_account b on a.AgentID = b.AgentID and b.AgentID = $agent group by groupNum";
            elseif ($type == 'month')
                $sel = "select DATE_FORMAT(CreateTime,'%Y-%m') date,count(1) count from (select CreateTime,AgentID from tb_customers_project where CreateTime >= '$start' and CreateTime <= '$end') a inner join tb_account b on a.AgentID = b.AgentID and b.AgentID = $agent group by date";
            else
                $sel = "select DATE_FORMAT(CreateTime,'%Y') date,count(1) count from (select CreateTime,AgentID from tb_customers_project where CreateTime >= '$start' and CreateTime <= '$end') a inner join tb_account b on a.AgentID = b.AgentID and b.AgentID = $agent group by date";
        }
        $DB = new DB();
        $selData = $DB->Select($sel);
        $data = array();
        switch ($type){
            case 'day':
                $interval = $endInt - $startInt;
                $day = $interval/(24 * 60 * 60) + 1;
                for($k = 0; $k < $day; $k++){
                    $data['categories'][] = $k ? date('Y-m-d',strtotime('+' . $k . ' day', $startInt)) : $start;
                    $data['data'][] = 0;
                }
                if($selData){
                    foreach ($selData as $v){
                        $data['data'][array_search($v['date'], $data['categories'])] = $v['count'];
                    }
                }
                break;
            case 'week':
                $interval = $endInt - $startInt;
                $day = $interval/(24 * 60 * 60 * 7);
                $day = $day == ceil($day) ? ceil($day) + 2 : ceil($day) + 1;
                $timeStart = $startInt;
                for($k = 1; $k < $day; $k++){
                    $timeLast = strtotime('+6 day', $timeStart);
                    $data['categories'][] = $timeStart == $endInt ? date('Y-m-d', $timeStart) : date('Y-m-d', $timeStart) . '/' . ($timeLast < $endInt ? date('Y-m-d', $timeLast) : $end);
                    $timeStart = strtotime('+1 day', $timeLast);
                    $data['data'][] = 0;
                }
                if($selData){
                    foreach ($selData as $v){
                        $data['data'][$v['groupNum']] = $v['count'];
                    }
                }
                break;
            case 'month':
                $timeStart = strtotime(date('Y-m', $startInt));
                $timeLast = strtotime(date('Y-m', $endInt));
                for($k = $timeStart; $k <= $timeLast;){
                    $data['categories'][] = date('Y-m', $k);
                    $data['data'][] = 0;
                    $k = strtotime('+1 months', $k);
                }
                if($selData){
                    foreach ($selData as $v){
                        $data['data'][array_search($v['date'], $data['categories'])] = $v['count'];
                    }
                }
                break;
            case 'year':
                $timeStart = (int)date('Y', $startInt);
                $timeLast = (int)date('Y', $endInt);
                for($k = $timeStart; $k <= $timeLast;){
                    $data['categories'][] = $k;
                    $data['data'][] = 0;
                    $k += 1;
                }
                if($selData){
                    foreach ($selData as $v){
                        $data['data'][array_search($v['date'], $data['categories'])] = $v['count'];
                    }
                }
                break;
        }
        $result['data'] = $data;
        return $result;
    }
    
    //数据处理
    public function ModelCount() {
        $level = $_SESSION['Level'];
        $agent = $_SESSION['AgentID'];
        $start = date('Y-m-d',strtotime($this->_GET['start']));
        $end = date('Y-m-d',strtotime($this->_GET['end']));
        $return = array('err' => 0, 'msg' => '');
        if ($level == 1) {
            $DB = new DB();
            $sel = "select PC_model,count(1) count from tb_customers_project where CreateTime >= '$start' and CreateTime <= '$end' group by PC_model order by count desc limit 0,10";
            $pcData = $DB->Select($sel);
            $sel = "select Mobile_model,count(1) count from tb_customers_project where CreateTime >= '$start' and CreateTime <= '$end' group by Mobile_model order by count desc limit 0,10";
            $mobileData = $DB->Select($sel);
            $pcCount = 0;
            if($pcData){
                foreach ($pcData as $v){
                    if($v['PC_model']){
                        $data['categories'][$pcCount] = $v['PC_model'];
                        $data['num']['pc'][$pcCount] = $v['count'];
                        if($pcCount == 4)
                            break;
                        else
                            $pcCount++;
                    }
                }
            }
            $mobileCount = 0;
            if($mobileData)
                foreach ($mobileData as $v){
                    if($v['Mobile_model']){
                        $data['categories'][$mobileCount] = $data['categories'][$mobileCount] ? $data['categories'][$mobileCount] . '/' . $v['Mobile_model'] : '--/' . $v['Mobile_model'];
                        $data['num']['pc'][$mobileData] = $data['categories'][$mobileCount] ? $data['data']['pc'][$mobileData] : 0;
                        $data['num']['mobile'][] = $v['count'];
                        if($mobileCount == 4)
                            break;
                        else
                            $mobileCount++;
                    }
                }
            if($pcCount - $mobileCount > 0){
                for($i = 0; $i < $pcCount - $mobileCount; $i++){
                    $data['categories'][$pcCount - $i] += '/--';
                    $data['num']['mobile'][$pcCount - $i] += '0';
                }
            }
        }else{
            $return['err'] = 1000;
            $return['msg'] = '非法操作';
        }
        $return['data'] = $data;
        return $return;
    }
    //时间格式初始判断
    protected function ErrJudge($start, $end, $type) {
        $return = array('err' => 0, 'msg' => '');
        $interval = $end - $start;
        if(!$start || !$end || ($interval <= 0)){
            $return['err'] = 1001;
            $return['msg'] = $end && $start ? '起始时间不得小于结束时间' : '错误的时间格式';
            return $return;
        }
        switch ($type){
            case 'day':
                if($interval/1000 > 20 * 24 * 60 * 60){
                    $return['err'] = 1001;
                    $return['msg'] = '按天计算不能大于20天';
                }else
                    $return['err'] = 0;
                break;
            case 'week':
                if($interval/1000 > 10 * 7 * 24 * 60 * 60){
                    $return['err'] = 1001;
                    $return['msg'] = '按周计算不能大于10周';
                }else
                    $return['err'] = 0;
                break;
            case 'month':
                if($interval/1000 > 366 * 24 * 60 * 60){
                    $return['err'] = 1001;
                    $return['msg'] = '按月计算不能大于12月';
                }else
                    $return['err'] = 0;
                break;
            case 'year':
                if($interval/1000 > 12 * 366 * 24 * 60 * 60){
                    $return['err'] = 1001;
                    $return['msg'] = '按年计算不能大于12年';
                }else
                    $return['err'] = 0;
                break;
            default :
                $return['err'] = 1002;
                $return['msg'] = '错误请求';
                break;
        }
        return $return;
        
    }
}
