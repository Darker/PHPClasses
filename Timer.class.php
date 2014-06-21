class Timer implements ArrayAccess/* Iterator */ {
  private $start = 0;
  private $startm = 0;
  private $last = 0;
  private $lastm = 0;
  public $laps = array();   //name => array(t,tm)
  public $floats = true;
  public function __construct() {
    $start = $this->getTime();
    $this->last=$this->start = $start[0];
    $this->lastm=$this->startm = $start[1];
    //$start[2] = "START";
    $laps["START"] = $start;
    //var_dump($start,$this->start, $this->startm);
  }
  public function lap($lapname=NULL) {
    if(is_string($lapname)&&strpos($lapname,"-")!==false)
      return trigger_error("Timer::lap() -> Lap name must not contain \"-\" /minus/ character!",E_USER_WARNING)&&false;
    $now = $this->getTime();
    $this->last = $now[0];
    $this->lastm = $now[1];

    if(is_string($lapname)||is_int($lapname))
      $this->laps[$lapname]=$now;
    else
      $this->laps[]=$now;
  }
  public function __invoke($lapname=NULL) {
    $this->lap($lapname);
  }  
  private function getTime() {
    $start = explode(" ",microtime());
    $r = array();
    $r[] = (int)$start[1];
    $r[] = (int)substr($start[0],2,9);
    return $r;
  }
  private function diffTime($time,$time2=null,$micro=true) {
    if(!is_array($time))
      $time = array((int)$time,0);
    if(!is_array($time2))
      $time2 = array($this->start,$this->startm);
    /*if($micro) {
      $dm = $time2[1]-$time[1];  
      if($dm<0&&$time2[0]>=$time[0]) {                  //Pokud je mikro rozdil mensi nez 0
        $time2[0]--;
        $dm=1-$dm;      
      }
      elseif($dm>0&&$time2[0]<=$time[0]) {
        $time2[0]++;
        $dm=$dm-1; 
      }
    } 
    else $dm=0;    
    $d = abs($time2[0]-$time[0]);    */
    $time = $time[0]+$time[1]/1000000000;
    $time2 = $time2[0]+$time2[1]/1000000000;
    $d = abs($time-$time2);
    //$dm = round(($d-floor($d))*1000000000);
    //$d = floor($d);
    return $this->toArray($d);
  }
  private function toFloat(array $time) {
    return $time[0]+$time[1]/1000000000;
  }
  private function toArray($time) {
    $dm = round(($time-floor($time))*1000000000);
    $d = floor($time);
    return array((int)$d,(int)$dm);
  }
  public function range($lap,$lap2,$float=true) {
    if(!isset($this->laps[$lap]))
      return trigger_error("Timer::range -> Lap \"$lap\" not found.",E_USER_WARNING)&&false;
    if(!isset($this->laps[$lap2]))
      return trigger_error("Timer::range -> Lap \"$lap2\" not found.",E_USER_WARNING)&&false;
    $diff = $this->diffTime($this->laps[$lap],$this->laps[$lap2]);
    if($float) 
      return $diff[0]+$diff[1]/1000000000;
    return $diff;
  }
  public function started() {
    return array($this->start,$this->startm);  
  }
  public function offsetSet($offset, $value) {
    if (is_null($offset)) $this->lap();
    else $this->lap($offset);   
  }   
  public function offsetExists($offset) {
    return isset($this->laps[$offset]);
  }
  public function offsetUnset($offset) {
    unset($this->laps[$offset]);
  }
  public function offsetGet($offset) {
    if(strpos($offset,"-")!==false) {
      $offset = explode("-",$offset);
      if($offset[0]=="") 
        $offset[0] = $this->started();
      if($offset[count($offset)-1]=="") 
        $offset[count($offset)-1] = $this->getTime();
      foreach($offset as &$val) {
        if(is_array($val)) 
          continue;
        if(!isset($this->laps[$val])) {
          trigger_error("Timer::[] -> offset \"$val\" does not exist!",E_USER_NOTICE);
          unset($val);
          continue;
        }
        $val = $this->laps[$val];      
      }
      $diff = array();
      //$names = array_keys($offset);
      for($i=0; $i<count($offset)-1; $i++) {
        $diff[] = $this->floats?$this->toFloat($this->diffTime($offset[$i],$offset[$i+1])):$this->diffTime($offset[$i],$offset[$i+1]);
      }
      if(count($diff)==1)
        return $diff[0];
      //var_dump($offset);
      return $diff; 
    } 
    if($this->floats)
      return $this->toFloat($this->laps[$offset]);
    return $this->laps[$offset];   
  }
  /**Dump timer data as simple text**/
  public function dump() {
    $names = array_keys($this->laps);
    $text = "Saved times:";
    //Single ranges 
    for($i=0; $i<count($names)-1; $i++) {
      $text.="  {$names[$i]} -> {$names[$i+1]}: ".($this->range($names[$i], $names[$i+1])).PHP_EOL;
    }
    //Sum of all time
    $text.="Overall time: ".$this->range($names[$i], $names[count($names)-1]).PHP_EOL;
    return $text;
  }
}
