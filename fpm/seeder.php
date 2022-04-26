<?php
  require "db.php";

  $start_time = time();
  echo "시작시간: ".$start_time."\n";

  //랜덤 문자열 생성함수 정의
  function getRandomString($N) {
    $Strings = '0123456789abcdefghijklmnopqrstuvwxyz';
    return substr(str_shuffle($Strings), 1, rand(2, $N));
  }

  // DB 연결
  $con = getDB();
  // login_check 테이블 초기데이터 삽입
  if (!mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM login_check"))->num_rows) {
    $res = mysqli_query($con, "INSERT INTO login_check VALUES (1, 0, now(), FALSE);");
  }

  // member 테이블 초기데이터 삽입
  $total_data = 70000;
  $old_data = 6000;

  $cnt_cumul = 0;
  $cnt_old = 0;
  $cnt_old_temp = 0;
  $count_all = 0;
  $value = "";

  while ($count_all < $total_data) {  
    $mail = getRandomString(44);
    $domain = getRandomString(44);
    $name = getRandomString(50);
    $start = "2021-01-01 00:00:00";

    if ($cnt_old + $cnt_old_temp < $old_data && !rand(0, 9)) {
      $last = "2021-05-01 00:00:00";
      $cnt_old_temp += 1;
    } else {
      $last = "2022-03-01 00:00:00";
    }

    if ($cnt_cumul == 1000) {
      
      $res = mysqli_query($con, "INSERT INTO member (email, name, join_date, last_login_time) VALUES $value;");
      if (!$res) {
        printf("Error message: %s\n", mysqli_error($con));
        $cnt_old_temp = 0;
        $cnt_cumul = 0;
        $value = "";
      } else {
        $count_all += 1000;
        $cnt_old += $cnt_old_temp;
        $cnt_old_temp = 0;
        $cnt_cumul = 0;
        $value = "";
      }
    } else {
      if ($value == "") {
        $value = "(\"$mail@$domain.com\", \"$name\", \"$start\", \"$last\")";
      } else {
        $value = $value.",(\"$mail@$domain.com\", \"$name\", \"$start\", \"$last\")";
      }
      $cnt_cumul += 1;
    }
  }
  //DB 연결 해제
  mysqli_close($con);

  $end_time = time();
  echo "종료시간: ".$end_time."\n";

  $delay = $end_time - $start_time;
  echo "걸린시간: ".$delay."초\n";
?>